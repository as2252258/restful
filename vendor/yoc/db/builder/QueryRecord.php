<?php

namespace yoc\db\builder;

use yoc\cache\FileCache;
use yoc\db\ActiveRecord;
use yoc\db\Collection;
use yoc\di\Ioc;
use yoc\pool\ModelPool;

/**
 * Class Builder
 *
 * @package yoc\Db
 */
class QueryRecord extends Builder
{
	
	/**
	 * QueryRecord constructor.
	 *
	 * @param string $modelClass
	 * @param array  $config
	 */
	public function __construct(string $modelClass , $config = [])
	{
		$this->modelClass = $modelClass;
		parent::__construct();
	}
	
	/**
	 * @return ActiveRecord
	 */
	public function one()
	{
		list($cache , $sql) = $this->byCache($this->getSelectSql() , 'fetch');
		if (!empty($cache)) {
			$data = $this->findByPrimary($cache);
		} else {
			$data = $this->command($sql , $this->bindParam)->one();
			if (!empty($data) && is_array($data)) {
				\Yoc::$app->get('fileCache')->setSql($sql , 'fetch')->addCache($data);
				$data = $this->findByPrimary($data);
				if ($data instanceof ActiveRecord) {
					\Yoc::getRedis()->hMset($data->cache_key() , $data->getAttributes());
				}
			}
		}
		return $data;
	}
	
	/**
	 * @param $sql
	 *
	 * @return bool|mixed|null
	 */
	private function byCache($sql , $type = 'fetch' , $param = null)
	{
		$data = [null , $sql];
		if (empty($sql)) return $data;
		/** @var FileCache $fileCache */
		$fileCache = $this->getFileCache($sql , $type);
		if (!empty($param) && is_string($param)) {
			$fileCache->clearTale();
		} else if (!empty($param)) {
			$fileCache->addCache($param);
		} else {
			$cache = $fileCache->getCache();
			if (!empty($cache)) {
				$data = [$cache , $sql];
			}
		}
		return $data;
	}
	
	/**
	 * @param string $sql
	 * @param string $type
	 *
	 * @return FileCache
	 */
	private function getFileCache($sql = '' , $type = '')
	{
		/** @var FileCache $fileCache */
		$fileCache = Ioc::createObject(FileCache::className() , [$this]);
		if (!empty($sql) && !empty($type)) {
			$fileCache->setSql($sql , $type);
		}
		return $fileCache;
	}
	
	/**
	 * @param $value
	 *
	 * @return ActiveRecord
	 * @throws \Exception
	 */
	private function findByPrimary($value)
	{
		if (is_array($value)) $value = array_shift($value);
		$model = $this->getModel();
		$primary = $model->getPrimary();
		if (empty($primary) || !is_string($primary)) {
			return null;
		}
		$model->set($primary , $value);
		if (ModelPool::hasItem($model->cache_key())) {
			return $this->builder(ModelPool::getItem($model->cache_key()));
		}
		if (!empty($data = \Yoc::getRedis()->hGetAll($model->cache_key()))) {
			return $this->builder($model->setAttributes($data));
		}
		$this->select('*')->where([$primary => $value]);
		$data = $this->command($this->getSelectSql())->one();
		if (!empty($data)) {
			$data = $model->setAttributes($data);
			\Yoc::getRedis()->hMset($model->cache_key() , $data->toArray());
		}
		if (!empty($data)) {
			ModelPool::addItem($model->cache_key() , $data);
		}
		return $this->builder($data);
	}
	
	/**
	 * @param \yoc\db\ActiveRecord $model
	 *
	 * @return \yoc\db\ActiveRecord
	 * 获取额外数据
	 */
	private function builder($model)
	{
		if (empty($model)) {
			return null;
		}
		$model = $this->reset($model);
		if (!$model->hasAppends()) return $model;
		foreach ($model->getAppends() as $key => $val) {
			if (!$this->isExtension) {
				$model->append($val , []);
			} else {
				$model->append($val , $model->{'get' . ucfirst($val) . 'Attribute'}());
			}
		}
		return $model;
	}
	
	/**
	 * @param \yoc\db\ActiveRecord $model
	 *
	 * @return mixed
	 */
	private function reset($model)
	{
		$attributes = $model->getAttributes();
		if (empty($attributes) || !is_array($attributes)) {
			return $model;
		}
		foreach ($attributes as $key => $cal) {
			if (method_exists($model , 'set' . ucfirst($key))) {
				$model->set($key , $model->{'set' . ucfirst($key)}());
			} else {
				$model->set($key , $cal);
			}
		}
		return $model;
	}
	
	/**
	 * @param $sql
	 * @param $attribute
	 *
	 * @return \yoc\db\Command
	 */
	private function command($sql , $attribute = [])
	{
		return $this->getPdo()->createCommand($sql , $attribute);
	}
	
	/**
	 * @param string $field
	 *
	 * @return array|string
	 * @throws \Exception
	 */
	public function queryRand($field = '')
	{
		$data = $this->all()->toArray();
		if (empty($data)) {
			return empty($field) ? [] : '';
		}
		$rand = $data[array_rand($data)];
		if (!isset($rand[$field])) {
			throw new \Exception('unknown property ' . $field);
		}
		return $rand[$field];
	}
	
	/**
	 * @return Collection
	 */
	public function all()
	{
		list($data , $sql) = $this->byCache($this->getSelectSql() , 'fetchAll');
		if (empty($data) || !is_array($data)) {
			$data = $this->command($sql , $this->bindParam)->all();
			$this->getFileCache($sql , 'fetchAll')->addCache($data);
		}
		if (empty($data)) {
			return new Collection([] , $this->getModel());
		}
		$_tmp = [];
		foreach ($data as $key => $val) {
			$model = clone $this->getModel();
			if ($model->hasPrimary() && count($val) == 1) {
				$_data = $this->findByPrimary($val);
			} else {
				$_data = $this->builder($model->setAttributes($val));
			}
			if (empty($_data)) {
				continue;
			}
			$_tmp[] = $_data;
		}
		return new Collection($_tmp , $this->getModel());
	}
	
	/**
		* @return int
		*/
	public function count()
	{
		list($count , $sql) = $this->byCache($this->getCountSql(false) , 'count');
		if (empty($count)) {
			$count = $this->command($sql)->rowCount();
			$this->getFileCache($sql , 'count')->addCache($count);
		}
		if (is_array($count)) {
			$count = array_shift($count);
		}
		return $count;
	}
	
	/**
	 * @param ActiveRecord $model
	 * @param array        $param
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public function save(ActiveRecord $model , $param = [])
	{
		$sql = '';
		$execute = true;
		$command = \Yoc::$app->getDb()->getCommand();
		if (!empty($param) && is_array($param)) {
			$model->setBatch($param);
		}
		if ($this->hasEventHandlers($model::EVENT_SAVE_BEFORE , $model)) {
			$this->trigger($model::EVENT_SAVE_BEFORE , $this);
		}
		$model->trigger('SAVE_RULE');
		if ($model->isNewRecord) {
			$sql = $this->getInsertSql($model->getAttributes());
			$execute = $command->setSql($sql)->insert($model->getAttributes());
			if ($execute) {
				$model->setAttribute('id' , $execute);
			}
			foreach ($model->attributes() as $key => $vla) {
				if (array_key_exists($key , $model->getAttributes())) {
					continue;
				}
				$model->set($key , '');
			}
		} else {
			$renovate = $model->isRenovate();
			if (!empty($renovate)) {
				$this->where($model->getPrimaryValue());
				$execute = $command->setSql($sql = $this->getUpdateSql($renovate))->update($renovate);
			}
		}
		if (!$execute) {
			return false;
		}
		ModelPool::removeByPatten($this->getTableName());
		\Yoc::getRedis()->hMset($model->cache_key() , $model->getAttributes());
		$this->byCache($sql , 'fetch,fetchAll' , 'clear');
		return $model;
	}
	
	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function update($data)
	{
		$dataList = $this->all();
		if ($dataList->isEmpty()) {
			return true;
		}
		return $dataList->update($data);
	}
	
	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function delete()
	{
		$_delete = $this->getDeleteSql();
		$command = \Yoc::$app->db->getCommand();
		$results = $command->setSql($_delete)->execute();
		if (!$results) {
			throw new \Exception(\Yoc::getError()->lastError('mysql'));
		}
		$tableName = $this->getTableName();
		ModelPool::removeByPatten($tableName);
		swoole_timer_after(1 , function () use ($tableName) {
			$redis = \Yoc::getRedis();
			$redis->del($redis->keys($tableName));
		});
		$this->getFileCache($_delete)->clearTale($this->getTableName());
		return $results;
	}
	
	/**
	 * @param string $field
	 *
	 * @return array
	 */
	public function queryRaw(string $field)
	{
		$attribute = $this->getModel()->attributes();
		if (empty($field) || !isset($attribute[$field])) {
			return [];
		}
		$all = $this->all()->toArray();
		if (!empty($all)) {
			return array_column($all , $field);
		}
		return [];
	}
}