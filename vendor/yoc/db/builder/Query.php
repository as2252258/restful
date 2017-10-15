<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/2/18 0018
 * Time: 3:50
 */

namespace yoc\db\builder;


use Exception;
use yoc\Di\Ioc;

/**
 * Trait Query
 *
 * @package yoc\db\builder
 */
trait Query
{
	public $where = [];
	
	public $group = null;
	
	public $select = 'id';
	
	public $order = null;
	
	public $limit = null;
	
	public $alias = 't1';
	
	public $join = [];
	
	public $offset = 0;
	
	public $limits = 20;
	
	public $with = [];
	
	public $fields = [];
	
	
	/**
	 * value,value,value
	 * [value,value,value]
	 *
	 * @param null|array|string $value
	 *
	 * @return $this
	 * @throws \Exception
	 * 获取指定字段
	 */
	public function column($value = null)
	{
		if (empty($value)) return $this;
		if (!is_string($value) && !is_array($value)) {
			throw new \Exception('请使用正确的格式');
		}
		if (is_array($value)) {
			$this->fields = $value;
		} else {
			$this->fields = array_filter(explode(',' , $value));
		}
		return $this;
	}
	
	/**
	 * @param bool $isArray
	 *
	 * @return array|string
	 */
	public function getWhere($isArray = false , $isClear = false)
	{
		if ($isArray) {
			$where = $this->where;
		} else {
			if (empty($this->where)) return '';
			$where = '(' . implode(') and (' , $this->where) . ')';
		}
		if ($isClear) {
			$this->clear();
		}
		return $where;
	}
	
	/**
	 * @param $alias
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function alias($alias)
	{
		if (empty($alias)) {
			throw new \Exception('the mysql table alias con\'t empty');
		}
		$this->alias = $alias;
		return $this;
	}
	
	/**
	 * @param integer $page
	 * @param integer $size
	 *
	 * @return $this
	 * limit 分页用
	 */
	public function limit($page = 1 , $size = 20)
	{
		$size = !is_numeric($size) ? 20 : intval($size);
		if ($size > 0) {
			if (!empty($page) && !empty($size)) {
				$page = intval($page) - 1 <= 0 ? 0 : (intval($page) - 1) * intval($size);
				$this->offset($page);
			}
			$this->limit = $size;
		}
		return $this;
	}
	
	/**
	 * @param $index
	 *
	 * @return $this
	 */
	public function offset($index)
	{
		$this->offset = $index;
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param array  $value
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function whereNotIn(string $key , array $value)
	{
		if (!is_array($value) || empty($value)) {
			return $this;
		}
		$this->where[] = $key . ' not in(\'' . implode('\',\'' , $value) . '\')';
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function select($field = '*')
	{
		if (empty($field)) $field = '*';
		if (!is_array($field)) {
			$this->select = $field;
		} else {
			$this->select = implode(',' , $field);
		}
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function where()
	{
		$_args = func_get_args();
		$where = array_shift($_args);
		if (is_numeric($where)) {
			$this->where[] = $this->getModel()->getPrimary() . '=' . $where;
		} else if (is_string($where)) {
			$this->where[] = $where;
		} else {
			if (empty($where)) return $this;
			foreach ($where as $key => $val) {
				if (empty($val) && !is_numeric($val)) continue;
				if (is_array($val)) {
					$this->resolve($val);
				} else {
					$this->addWhere($key , $val);
				}
			}
		}
		return $this;
	}
	
	/**
	 * @param array $array
	 *
	 * @return $this
	 */
	private function resolve(array $array)
	{
		if (empty($array)) return $this;
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$this->resolve($array);
			} else {
				$this->addWhere($key , $val);
			}
		}
		return $this;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	private function addWhere($key , $value)
	{
		if (strpos($key , '<') !== false || strpos($key , '>') != false) {
			$this->where[] = $this->keyAlias($key) . (is_numeric($value) ? $value : "'{$value}'");
		} else {
			if (is_numeric($key)) {
				$this->where[] = $value;
			} else {
				$this->where[] = $this->keyAlias($key) . '=' . (is_numeric($value) ? $value : "'{$value}'");
			}
		}
		return $this;
	}
	
	/**
	 * @param $key
	 *
	 * @return string
	 * 别名
	 */
	private function keyAlias($key)
	{
		if (!empty($this->join) && is_array($this->join)) {
			if (strpos($key , $this->alias . '.') === false) {
				return $this->alias . '.' . $key;
			}
		}
		return $key;
	}
	
	/**
	 * @param       $field
	 * @param array $values
	 *
	 * @return $this
	 */
	public function whereIn($field , array $values = [])
	{
		if (!is_array($values)) return $this;
		if (empty($values) || trim($field) == '') return $this;
		$this->where[] = $field . ' in(\'' . implode('\',\'' , $values) . '\')';
		return $this;
	}
	
	/**
	 * @param string $value
	 * @param array  $fields
	 *
	 * @return $this
	 */
	public function withLike(string $value , array $fields)
	{
		if (empty($value) || empty($fields) || !is_array($fields)) return $this;
		$this->where[] = 'CONCAT(' . implode(',' , $fields) . ') like \'%' . $value . '%\'';
		return $this;
	}
	
	/**
	 * @param       $field
	 * @param array $condition
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function whereBetween($field , $condition = [])
	{
		if (!is_array($condition) || count($condition) <> 2) {
			throw new \Exception('条件必须为数组');
		}
		$this->where[] = $field . ' BETWEEN ' . implode(' AND ' , $condition);
		return $this;
	}
	
	/**
	 * @param $value
	 * @param $fields
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function whereOr($value , $fields)
	{
		$res = [];
		if (empty($fields) || !is_array($fields)) {
			throw new \Exception('查询字典不能为空');
		}
		foreach ($fields as $key => $val) {
			$res[] = $val . '=\'' . $value . '\'';
		}
		$this->where[] = implode(' or ' , $res);
		return $this;
	}
	
	/**
	 * @param $group
	 *
	 * @return $this
	 * 分组查询
	 */
	public function groupBy(string $group)
	{
		if (!empty($group)) {
			$this->group = ' group by ' . $group;
		}
		return $this;
	}
	
	/**
	 * @param string $order
	 * @param array  ...$sort
	 *
	 * @return $this
	 */
	public function orderBy($order , ...$sort)
	{
		if (empty($order)) return $this;
		if (empty($sort)) {
			$this->order = 'order by ' . $order;
		} else {
			$sort = strtolower(end($sort));
			if (!in_array($sort , ['asc' , 'desc'])) {
				$this->order = 'order by ' . $order . ' ' . ($sort ? 'desc' : 'asc');
			} else {
				$this->order = 'order by ' . $order . ' ' . $sort;
			}
		}
		return $this;
	}
	
	/**
	 * @param string $table
	 * @param string $tableId
	 * @param string $joinId
	 *
	 * @return $this
	 */
	public function withJoin(string $table , string $tableId , string $joinId)
	{
		if (!empty($table) && !empty($tableId) && !empty($joinId)) {
			if (strpos($joinId , $this->alias . '.') === false) {
				$joinId = $this->alias . '.' . $joinId;
			}
			$condition = $table . '.' . $tableId . '=' . $joinId;
			if (preg_match('/^(inner|right|left)/' , $table)) {
				$this->join[] = $table . ' on ' . $condition;
			} else {
				$this->join[] = 'inner join ' . $table . ' on ' . $condition;
			}
		}
		return $this;
	}
	
	/**
	 * @param string $table
	 * @param array  $condition
	 *
	 * @return $this
	 */
	public function whereNotExists(string $table , array $condition = [])
	{
		$_tmp = [];
		$where = 'NOT EXISTS(SELECT * FROM ' . $table;
		foreach ($condition as $key => $val) {
			if (is_numeric($key)) {
				$_tmp[] = $val;
			} else {
				$_tmp[] = $key . "='{$val}'";
			}
		}
		$this->where[] = $where . ' WHERE ' . implode(' and ' , $_tmp) . ')';
		return $this;
	}
	
	/**
	 * @param string $table
	 * @param array  $condition
	 *
	 * @return $this
	 */
	public function whereExists(string $table , array $condition = [])
	{
		$_tmp = [];
		$where = 'EXISTS(SELECT * FROM ' . $table;
		foreach ($condition as $key => $val) {
			if (is_numeric($key)) {
				$_tmp[] = $val;
			} else {
				$_tmp[] = $key . "='{$val}'";
			}
		}
		$this->where[] = $where . ' WHERE ' . implode(' and ' , $_tmp) . ')';
		return $this;
	}
	
	/**
	 * @param $callback
	 *
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function call($callback)
	{
		if (!\Yoc::isCallBack($callback)) {
			throw new \Exception(\Yoc::getError()->lastError('application'));
		}
		if (is_array($callback)) {
			$first = array_shift($callback);
			if (!is_object($first)) {
				$first = Ioc::createObject($first);
			}
			return call_user_func([$first , array_shift($callback)] , $this);
		} else {
			return call_user_func($callback , $this);
		}
	}
	
	public function clear()
	{
		$reflect = Ioc::single()->getReflection(__CLASS__);
		if ($reflect->getDefaultProperties()) {
			$defaults = ['modelClass' , 'out' , 'useCache' , 'isExtension' , 'append' , 'asArray'];
			foreach ($reflect->getDefaultProperties() as $key => $val) {
				if (in_array($key , $defaults)) continue;
				$this->$key = $val;
			}
		}
	}
}
