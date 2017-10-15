<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/27 0027
 * Time: 23:18
 */

namespace yoc\base;


class Pool extends Objects
{
	private $pools = [];
	
	public $maxPool = -1;
	
	public $isUse = [];
	
	public $notUse = [];
	
	/**
	 * @param       $key
	 * @param array $param
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function addItem($key, $param)
	{
		if (!$this->hasItem($key)) {
			if ($this->isGoBeyond()) {
				throw new \Exception('The pools is Go beyond');
			}
			$stack = new \SplStack();
			$stack->push($param);
			$this->pools[$key] = $stack;
		} else {
			$this->update($key, $param);
		}
		return $this->hasItem($key);
	}
	
	
	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function removeItem($key)
	{
		if ($this->hasItem($key)) {
			unset($this->pools[$key]);
		}
		return !isset($this->pools[$key]);
	}
	
	/**
	 * @return int
	 */
	public function clear()
	{
		$this->pools = [];
		return !count($this->pools);
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getItem($key)
	{
		if ($this->hasItem($key)) {
			$data = $this->pools[$key];
			if (empty($data) || !$data instanceof \SplStack) return null;
			$pop = $data->pop();
			if (!empty($pop)) {
				$data->push($pop);
			};
			return $pop;
		}
		return null;
	}
	
	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function hasItem($key)
	{
		return array_key_exists($key, $this->pools) && !empty($this->pools[$key]);
	}
	
	/**
	 * @return mixed
	 */
	public function getAll()
	{
		return array_keys($this->pools);
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function array_one($key)
	{
		$get = isset($this->pools[$key]) ? $this->pools[$key] : [];
		if (empty($get) || !is_array($get)) {
			return null;
		}
		$array = $get->pop();
		$rand = array_shift($array);
		$get->push(array_values($array));
		return $rand;
	}
	
	/**
	 * @param $key
	 * @param $object
	 *
	 * @return bool
	 */
	public function update($key, $object)
	{
		if (!$this->hasItem($key)) {
			$this->addItem($key, $object);
		} else {
			$this->pools[$key] = $object;
		}
		return isset($this->pools[$key]);
	}
	
	/**
	 * @return bool
	 */
	public function isGoBeyond()
	{
		if ($this->maxPool < 1) return false;
		return count($this->pools) >= $this->maxPool;
	}
	
	public function setMax(int $count = -1)
	{
		if ($count < 1) {
			throw new \Exception('连接池最大容量至少为1');
		}
		$this->maxPool = $count;
	}
	
	public function removeByPatten($patten)
	{
		foreach ($this->pools as $key => $val) {
			if (!preg_match('/' . preg_quote($patten) . '/', $key)) {
				continue;
			}
			unset($this->pools[$key]);
		}
		return true;
	}
	
	/**
	 * @param $key
	 * @param $data
	 *
	 * @return bool
	 */
	public function push($key, $data)
	{
		if (!$data) return true;
		if (!$this->hasItem($key)) {
			return false;
		}
		if (!is_array($this->getItem($key))) {
			return false;
		}
		$tack = $this->pools[$key];
		$count = $tack->count();
		$tack->push(array_push($tack->pop(), $data));
		return $tack->count() - 1 == $count;
	}
}