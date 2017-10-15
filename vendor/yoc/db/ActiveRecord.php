<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/2/18 0018
 * Time: 3:23
 */

namespace yoc\db;


use yoc\base\Objects;
use yoc\core\ArrayAccess;
use yoc\db\builder\QueryRecord;
use yoc\db\implement\Databases;
use yoc\di\Ioc;
use yoc\validate\Validate;

/**
 * Class Model
 *
 * @package yoc\Db
 *
 * @property $isNewRecord
 *
 * @method bindParam(array | object $data = [])
 * @method static QueryRecord where(...$args)
 * @method static QueryRecord read()
 * @method static QueryRecord count()
 * @method static QueryRecord fetch() as
 * @method static QueryRecord orderBy($where)
 * @method static QueryRecord groupBy($where)
 * @method static QueryRecord whereOr(string $value , array $field)
 * @method static QueryRecord queryRand($value = '')
 * @method static QueryRecord queryRaw($field)
 * @method static QueryRecord remove($value)
 * @method static QueryRecord select($value)
 * @method static QueryRecord withJoin($value , $tableId , $joinId)
 * @method static QueryRecord alias($value)
 * @method static QueryRecord column($value)
 * @method static QueryRecord clear()
 * @method static QueryRecord whereBetween($key , $value)
 * @method static QueryRecord whereIn($key , $value)
 * @method static QueryRecord whereNotIn(string $key , array $value)
 */
abstract class ActiveRecord extends Objects implements Databases
{
	const EVENT_SAVE_BEFORE = 'beforeSave';
	
	const EVENT_SAVE_AFTER = 'afterSave';
	
	const EVENT_SELECT_BEFORE = 'beforeSelect';
	
	const EVENT_SELECT_AFTER = 'afterSelect';
	
	const EVENT_DELETE_BEFORE = 'beforeDelete';
	
	const EVENT_DELETE_AFTER = 'afterDelete';
	/** @var string $primary */
	protected $primary = '';
	/** @var array $appends */
	protected $appends = [];
	private $_attributes = null;
	private $_oldAttributes = null;
	private $_extended = [];
	
	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic($name , $arguments)
	{
		return static::find()->$name(...$arguments);
	}
	
	/**
	 * @return QueryRecord
	 */
	public static function find()
	{
		static $model = null;
		if ($model === null) {
			$model = Ioc::createObject(QueryRecord::className() , [get_called_class()]);
		}
		return clone $model;
	}
	
	/**
	 * @param $id
	 *
	 * @return static
	 */
	public static function findOne($id)
	{
		return static::find()->where($id)->one();
	}
	
	/**
	 * @param array $condition
	 *
	 * @return mixed|null
	 */
	public static function deleteAll($condition = [])
	{
		$query = static::find();
		if (!empty($condition)) {
			$query->where($condition);
		}
		return $query->all()->delete();
	}
	
	/**
	 * @param $callback
	 *
	 * @return bool
	 */
	public function beforeSave($callback)
	{
		return $this->on(self::EVENT_SAVE_BEFORE , $callback , $this);
	}
	
	/**
	 * @param $callback
	 *
	 * @return bool
	 */
	public function afterSave($callback)
	{
		return $this->on(self::EVENT_SAVE_AFTER , $callback , $this);
	}
	
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return self::tableName();
	}
	
	/**
	 * @param string|array $key
	 *
	 * @return $this
	 */
	public function unset(...$args)
	{
		if (empty($this->_attributes) || empty($args)) {
			return $this->_attributes;
		}
		if (is_array(current($args))) {
			$args = array_shift($args);
		}
		foreach ($this->_attributes as $_key => $val) {
			if (!in_array($_key , $args)) continue;
			unset($this->_attributes[$_key] , $this->_oldAttributes[$_key]);
		}
		return $this;
	}
	
	/**
	 * @param bool $toArray
	 *
	 * @return ActiveRecord|null|array
	 */
	public function reload($toArray = false)
	{
		$this->_oldAttributes = $this->_attributes;
		return $toArray ? $this->_attributes : $this;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function set($key , $value)
	{
		if ($value === null) return $this;
		$this->_attributes[$key] = $value;
		$this->_oldAttributes[$key] = $value;
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setBatch(array $data)
	{
		if (empty($data)) return $this;
		foreach ($data as $key => $val) {
			if ($val === null) continue;
			if (!$this->hasProperty($key)) continue;
			$this->setAttribute($key , $val);
		}
		return $this;
	}
	
	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function hasProperty($key)
	{
		return array_key_exists($key , $this->attributes());
	}
	
	/**
	 * @param $key
	 * @param $data
	 *
	 * @return $this
	 */
	public function setAttribute($key , $data)
	{
		if (empty($data)) return $this;
		$this->_attributes[$key] = $data;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function load($data = [])
	{
		if (empty($data) || !is_array($data)) {
			$data = \Yoc::getRequest()->input->all();
		}
		if (empty($data)) {
			\Yoc::getError()->addError('mysql' , 'Data Is Empty');
			return false;
		}
		$attributes = $this->attributes();
		foreach ($data as $key => $val) {
			if (!isset($attributes[$key]) || $val === null) {
				continue;
			}
			$this->setAttribute($key , htmlspecialchars($val));
		}
		if (empty($this->_attributes)) {
			\Yoc::getError()->addError('mysql' , 'Not Find Field Data');
			return false;
		}
		return true;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function append($key , $value)
	{
		if ($this->hasProperty($key)) return $this;
		if ($value instanceof ActiveRecord) {
			$value = $value->toArray();
		} else if ($value instanceof Collection) {
			$value = $value->toArray();
		} else if (is_object($value)) {
			$value = ArrayAccess::toArray($value);
		}
		return $this->_extended[$key] = $value;
	}
	
	/**
	 * @return array
	 * 返回数组
	 */
	public function toArray()
	{
		return array_merge($this->_attributes , $this->_extended);
	}
	
	/**
	 * @return mixed
	 * 获取最后一条Mysql错误
	 */
	public function getLastError()
	{
		return \Yoc::getError()->lastError('mysql');
	}
	
	/**
	 * @param $name
	 *
	 * @return mixed|null
	 * 获取指定属性
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);
		if (method_exists($this , $method)) {
			return $this->{$method}();
		} else if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		} else if (isset($this->_extended[$name])) {
			return $this->_extended[$name];
		}
		return null;
	}
	
	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name , $value)
	{
		if (is_null($value)) {
			return;
		}
		if (array_key_exists($name , $this->attributes())) {
			$this->_attributes[$name] = $value;
		} else {
			parent::__set($name , $value); // TODO: Change the autogenerated stub
		}
	}
	
	/**
	 * @return array
	 */
	public function getAppends()
	{
		return !empty($this->appends) && is_array($this->appends) ? $this->appends : [];
	}
	
	/**
	 * @return bool
	 */
	public function hasAppends()
	{
		return !empty($this->appends) && is_array($this->appends);
	}
	
	/**
	 * @return bool
	 * 判断是否有数据更新
	 */
	public function isRenovate()
	{
		if (empty($this->_oldAttributes)) {
			return $this->_attributes;
		}
		return array_diff_assoc($this->_attributes , $this->_oldAttributes);
	}
	
	/**
	 * @param array $data
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public function save($data = [])
	{
		if (!$this->hasPrimary()) {
			throw new \Exception('Can\'t isset primary');
		}
		$rules = $this->addRule();
		if (!empty($rules) && is_array($rules)) {
			$this->on('SAVE_RULE' , [$this , 'notify'] , $rules);
		}
		$save = static::find()->save($this , $data);
		if ($this->hasEventHandlers(self::EVENT_SAVE_AFTER)) {
			$this->trigger(self::EVENT_SAVE_AFTER);
		}
		return $save;
	}
	
	/**
	 * @return bool
	 * 判断是否设置了主键
	 */
	public function hasPrimary()
	{
		return isset($this->primary) && array_key_exists($this->primary , $this->attributes());
	}
	
	/**
	 * @return array|bool
	 */
	private function addRule()
	{
		if (empty($this->rules())) return [];
		$validate = [];
		foreach ($this->rules() as $Key => $val) {
			$field = array_shift($val);
			if (empty($val)) {
				continue;
			}
			foreach ($val as $_Key => $_Val) {
				if (is_string($_Key)) {
					$validate[] = Validate::createValidate($_Key , $field , $_Val , $this);
				} else {
					$validate[] = Validate::createValidate($_Val , $field , null , $this);
				}
			}
		}
		return $validate;
	}
	
	/**
	 * @return bool
	 */
	public function getIsNewRecord()
	{
		return !is_array($this->_oldAttributes) || count($this->_oldAttributes) != count($this->_attributes);
	}
	
	/**
	 * @return array
	 */
	public function getOldAttributes()
	{
		return $this->_oldAttributes;
	}
	
	/**
	 * @return array
	 */
	public function packet(... $args)
	{
		$data = $this->_attributes;
		foreach ($data as $key => $val) {
			if (in_array($key , $args)) continue;
			unset($data[$key]);
		}
		return $data;
	}
	
	/**
	 * @return mixed
	 */
	public function delete()
	{
		return static::find()->where([
			$this->getPrimary() => $this->getPrimaryValue()
		])->delete();
	}
	
	/**
	 * @return mixed|null
	 * 获取主键
	 */
	public function getPrimary()
	{
		if ($this->hasPrimary()) {
			return $this->primary;
		}
		return null;
	}
	
	/**
	 * @return mixed|null
	 */
	public function getPrimaryValue()
	{
		$primary = $this->getPrimary();
		if (empty($primary)) {
			return null;
		}
		return $this->$primary;
	}
	
	/**
	 * @return string
	 */
	public function cache_key()
	{
		return static::tableName() . '_' . $this->getPrimaryValue();
	}
	
	/**
	 * @return $this
	 */
	public function clearExtended()
	{
		$this->_extended = [];
		return $this;
	}
	
	/**
	 * @param $rules
	 *
	 * @return bool
	 * @throws \Exception
	 * 执行rule效验
	 */
	protected function notify($rules)
	{
		foreach ($rules as $key => $val) {
			/** @var Validate $val */
			if ($val->notify()) {
				continue;
			}
			throw new \Exception($val->getMessage());
		}
		return true;
	}
	
	/**
	 * @param static $model
	 * @param        $modelId
	 * @param string $thisId
	 *
	 * @return array|ActiveRecord|null
	 */
	protected function beginOne($model , $modelId)
	{
		if (!isset($model->attributes()[$modelId])) {
			return null;
		}
		return $model::find()->where([$modelId => $this->getPrimaryValue()])->one();
	}
	
	/**
	 * @param object $model
	 * @param        $modelId
	 * @param string $thisId
	 *
	 * @return array|Collection
	 */
	protected function hasMany($model , $modelId , $thisId = '')
	{
		$key = empty($thisId) ? $this->getPrimary() : $thisId;
		if (!array_key_exists($key , $this->getAttributes())) {
			return [];
		}
		return $model::where([$modelId => $this->$key])->all();
	}
	
	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setAttributes(array $data)
	{
		if (empty($data)) return $this;
		foreach ($data as $key => $val) {
			if ($val === null) continue;
			if (!$this->hasProperty($key)) continue;
			$this->_attributes[$key] = $val;
			$this->_oldAttributes[$key] = $val;
		}
		return $this;
	}
}