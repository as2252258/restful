<?php

namespace yoc\db;

use yoc\base\Objects;


/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/2/18 0018
 * Time: 3:14
 */
class Collection extends Objects implements \IteratorAggregate
{
	protected $item = null;
	
	/** @var ActiveRecord $model */
	protected $model;
	
	public function __construct($item , $model , $config = [])
	{
		$this->item = $item;
		$this->model = $model;
		parent::__construct($config);
	}
	
	/**
	 * @param array ...$fields
	 *
	 * @return $this
	 */
	public function remove(...$fields)
	{
		if (empty($this->item) || empty($fields)) return $this;
		foreach ($this->item as $key => $val) {
			/** @var ActiveRecord $val */
			$this->item[$key] = $val->unset($fields);
		}
		return $this;
	}
	
	/**
	 * @param $field
	 *
	 * @return array
	 */
	public function column($field , callable $callback = null)
	{
		$data = array_column($this->toArray() , $field);
		if (is_callable($callback , true)) {
			$data = call_user_func($callback , $data);
		}
		return $data;
	}
	
	/**
	 * @return array
	 */
	public function toArray()
	{
		return array_map(function ($value) {
			return $value instanceof ActiveRecord ? $value->toArray() : $value;
		} , $this->item);
	}
	
	/**
	 * @param     $field
	 * @param int $sortType
	 *
	 * @return $this|static
	 * @throws \Exception
	 * 排序
	 */
	public function orderBy(string $field , $sortType = SORT_DESC)
	{
		$data = $this->toArray();
		if (empty($data)) return $this;
		if (!array_key_exists($field , $this->model->attributes())) {
			throw new \Exception('Field not exists in Model ' . get_class($this->model));
		}
		$_array = array_column($data , $field);
		arsort($_array , $sortType);
		$_data = [];
		foreach ($_array as $key => $val) {
			$_data[] = $this->item[$key];
		}
		$this->item = array_values($_data);
		return $this;
	}
	
	/**
	 * @param $field
	 *
	 * @return $this
	 * @throws \Exception
	 * 分组
	 */
	public function groupBy(string $field)
	{
		if ($this->item) return $this;
		if (!array_key_exists($field , $this->model->attributes())) {
			throw new \Exception('Field not exists in Model ' . get_class($this->model));
		}
		$data = [];
		foreach ($this->item as $key => $val) {
			if (array_key_exists($val[$field] , $data)) continue;
			$data[$val[$field]] = $val;
		}
		$this->item = array_values($data);
		return $this;
	}
	
	/**
	 * @param $start
	 * @param $length
	 *
	 * @return $this
	 * @throws \Exception
	 * 获取指定位置的数据
	 */
	public function slice(int $start , int $length = 100)
	{
		if ($start < 0 || $length < 0) {
			throw new \Exception('错误的起始点');
		}
		if (count($this->item) > $start + $length) {
			throw new \Exception('错误的起始点和结束点');
		}
		if (count($this->item) > $start + $length) {
			$length = count($this->item) - $start;
		}
		$this->item = array_slice($this->item , $start , $length);
		return $this;
	}
	
	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->item);
	}
	
	/**
	 * @return mixed|null
	 */
	public function queryRand(int $num = 1)
	{
		if (empty($this->item)) return null;
		if ($num < 1 || !is_numeric($num)) {
			return $this->item[array_rand($this->item)];
		}
		$_tmp = [];
		for ($i = 0 ; $i < $num ; $i++) {
			$_tmp[] = $this->item[array_rand($this->item)];
		}
		return $_tmp;
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return !is_array($this->item) || empty($this->item);
	}
	
	/**
	 * @param $data
	 *
	 * @return bool
	 * @throws \Exception
	 * 批量更新
	 */
	public function update(array $data)
	{
		if (empty($data) || !is_array($data)) return false;
		if (empty($this->item) || !is_array($this->item)) return false;
		$trance = \Yoc::$app->db->beginTransaction();
		foreach ($this->item as $key => $val) {
			/** @var ActiveRecord $val */
			$val->setBatch($data);
			if (!$val->save()) {
				$trance->rollback();
				throw new \Exception($val->getLastError());
			}
		}
		$trance->commit();
		return true;
	}
	
	/**
	 * @return bool
	 */
	public function delete()
	{
		if (empty($this->item)) return true;
		$trance = \Yoc::$app->db->beginTransaction();
		foreach ($this->item as $key => $val) {
			/** @var ActiveRecord $val */
			if (!$val->delete()) {
				$trance->rollback();
				throw new \Exception($val->getLastError());
			};
		}
		$trance->commit();
		return true;
	}
}