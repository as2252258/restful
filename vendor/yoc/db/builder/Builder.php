<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/9 0009
 * Time: 13:03
 */

namespace yoc\db\builder;


use yoc\base\Objects;
use yoc\db\ActiveRecord;
use yoc\db\Connect;
use yoc\di\Ioc;

class Builder extends Objects
{
	
	use Query;
	
	public $bindParam = [];
	public $tableName = '';
	public $out = [];
	public $useCache = true;
	public $isExtension = true;
	public $append = true;
	public $asArray = false;
	/** @var ActiveRecord $modelClass */
	protected $modelClass;
	
	/**
	 * @param bool $bool
	 *
	 * @return $this
	 */
	public function useAppend($bool = true)
	{
		$this->append = $bool;
		return $this;
	}
	
	/**
	 * @param bool|int $isCache
	 *
	 * @return $this
	 */
	public function useCache(bool $isCache = true)
	{
		$this->useCache = (bool) $isCache;
		return $this;
	}
	
	/**
	 * @param bool $false
	 *
	 * @return $this
	 */
	public function asArray($false = true)
	{
		$this->asArray = $false;
		return $this;
	}
	
	/**
	 * @param bool $isExtension
	 *
	 * @return $this
	 */
	public function isExtension($isExtension = true)
	{
		$this->isExtension = $isExtension;
		return $this;
	}
	
	/**
	 * @param $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function remove($value)
	{
		if (empty($value)) return $this;
		if (!is_string($value) && !is_array($value)) {
			throw new \Exception('请使用正确的格式');
		}
		if (!is_array($value)) {
			$fields = explode(',' , $value);
			$value = array_filter($fields);
		}
		$this->out = $value;
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getInsertSql($data = [])
	{
		if (!empty($data)) $this->bindParam($data);
		$keys = $this->getInsertFields();
		$sql = ['insert into' , $this->getTableName() , "({$keys})" , 'values' , '(:' . implode(',:' , $this->getInsertFields(false)) . ')'];
		return $this->sqlLog(implode(' ' , $sql));
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	public function bindParam(array $data)
	{
		if (empty($data) || !is_array($data)) return $this;
		$this->bindParam = $this->filter($data);
		return $this;
	}
	
	/**
	 * @param $arr
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function filter($arr)
	{
		if (empty($arr) || !is_array($arr)) {
			throw new \Exception('update or insert data can not empty');
		}
		return array_filter($arr , function ($value) {
			return !is_array($value) && !is_object($value) && $value !== null;
		});
	}
	
	/**
	 * @param bool $isString
	 *
	 * @return array|string
	 */
	private function getInsertFields($isString = true)
	{
		$keys = array_keys($this->bindParam);
		return $isString ? implode(',' , $keys) : $keys;
	}
	
	/**
	 * @return string
	 */
	public function getTableName()
	{
		if (!empty($this->tableName)) return $this->tableName;
		return $this->modelClass::tableName();
	}
	
	private function sqlLog($sql , $isClear = true)
	{
		if ($isClear) $this->clear();
		\DB::TranceSql($sql);
		return $sql;
	}
	
	/**
	 * @return string
	 * 生成更新SQL语句
	 */
	public function getUpdateSql($data = [])
	{
		$update = [];
		if (!empty($data)) $this->bindParam($data);
		if (empty($this->bindParam)) return true;
		foreach ($this->bindParam as $key => $val) {
			$update[] = $key . '=:' . $key;
		}
		$sql = ['update' , $this->getTableName() . ' as ' . $this->alias , "set" , implode(',' , $update) , 'where ' . $this->getWhere()];
		return $this->sqlLog(implode(' ' , $sql));
	}
	
	/**
	 * @return mixed
	 * 生成删除语句
	 */
	public function getDeleteSql($isClear = true)
	{
		/** @var ActiveRecord $model */
		$where = $this->getWhere();
		$sql = implode('' , [
			'delete from' , ' ' . $this->getTableName() ,
			empty($where) ? '' : ' where ' . $where ,
			empty($this->limit) ? '' : ' limit ' . $this->limit ,
		]);
		return $this->sqlLog($sql , $isClear);
	}
	
	/**
	 * @param bool $isClear
	 *
	 * @return mixed
	 * 生成查询语句
	 */
	public function getSelectSql($isClear = true)
	{
		$implode = implode('' , [
			' select ' . (empty($this->join) ? $this->select : 'distinct ' . $this->alias . '.' . $this->select) ,
			' from ' . $this->getTableName() ,
			empty($this->alias) ? '' : ' as ' . $this->alias ,
			empty($this->join) ? '' : ' ' . implode(' ' , $this->join) ,
			empty($this->where) ? '' : ' where ' . $this->getWhere() ,
			empty($this->group) ? '' : ' ' . $this->group ,
			empty($this->order) ? '' : ' ' . $this->order ,
			empty($this->limit) ? '' : ' limit ' . $this->offset . ',' . $this->limit ,
		]);
		return $this->sqlLog($implode , $isClear);
	}
	
	/**
	 * @param bool $isClear
	 *
	 * @return mixed
	 * 生成查询语句
	 */
	public function getCountSql($isClear = true)
	{
		$implode = implode('' , [
			' select ' . (empty($this->join) ? $this->select : 'distinct ' . $this->select) ,
			' from ' . $this->getTableName() ,
			empty($this->alias) ? '' : ' as ' . $this->alias ,
			empty($this->join) ? '' : ' ' . implode(' ' , $this->join) ,
			empty($this->where) ? '' : ' where ' . $this->getWhere() ,
			empty($this->group) ? '' : ' ' . $this->group ,
		]);
		return $this->sqlLog($implode , $isClear);
	}
	
	/**
	 * @return Connect
	 */
	public function getPdo()
	{
		return \Yoc::getLocator()->get('db');
	}
	
	/**
	 * @return string
	 * 获取缓存键名
	 */
	public function getMakeCache()
	{
		return 'cache_' . $this->getTableName();
	}
	
	/**
	 * @param $tableName
	 *
	 * @return $this
	 */
	public function tableName($tableName = '')
	{
		if (empty($tableName)) {
			$tableName = $this->getTableName();
		}
		$this->tableName = $tableName;
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function clearSqlLogParam()
	{
		$this->bindParam = [];
		return $this;
	}
	
	/**
	 * @return ActiveRecord
	 */
	public function getModel() : ActiveRecord
	{
		return Ioc::createObject($this->modelClass , [get_called_class()]);
	}
}