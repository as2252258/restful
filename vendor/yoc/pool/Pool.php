<?php

namespace yoc\pool;

use yoc\base\AbServer;
use yoc\base\Objects;

/**
 * Class Pool
 *
 * @package yoc\Pool
 */
class Pool extends AbServer
{
	protected static $pools = [];
	
	public static $maxPool = -1;
	
	public static $isUse = [];
	
	public static $notUse = [];
	
	/**
	 * @param       $key
	 * @param array $param
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function addItem($key, $param)
	{
		if (!static::hasItem($key)) {
			if (static::isGoBeyond()) {
				throw new \Exception('The pools is Go beyond');
			}
			$stack = new \SplStack();
			$stack->push($param);
			static::$pools[$key] = $stack;
		}
		return static::hasItem($key);
	}
	
	
	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function removeItem($key)
	{
		if (static::hasItem($key)) {
			unset(static::$pools[$key]);
		}
		return !isset(static::$pools[$key]);
	}
	
	/**
	 * @return int
	 */
	public static function clear()
	{
		static::$pools = [];
		return !count(static::$pools);
	}
	
	/**
	 * @param null $data
	 *
	 * @return \SplStack
	 */
	public static function getStack($data = null)
	{
		$stack = new \SplStack();
		if (!empty($data)) {
			$stack->push($stack);
		}
		return $stack;
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function getItem($key)
	{
		if (static::hasItem($key)) {
			$data = static::$pools[$key];
			if (!$data instanceof \SplStack) return null;
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
	public static function hasItem($key)
	{
		return array_key_exists($key, static::$pools) && !empty(static::$pools[$key]);
	}
	
	/**
	 * @return mixed
	 */
	public static function getAll()
	{
		return array_keys(static::$pools);
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public static function array_one($key)
	{
		$get = isset(static::$pools[$key]) ? static::$pools[$key] : [];
		if (empty($get) || !is_array($get)) {
			return null;
		}
		$array = $get->pop();
		$rand = array_shift($array);
		$get->push(array_values($array));
		return $rand;
	}
	
	/**
	 * @return bool
	 */
	public static function isGoBeyond()
	{
		if (static::$maxPool < 1) return false;
		return count(static::$pools) >= static::$maxPool;
	}
	
	/**
	 * @param int $count
	 *
	 * @throws \Exception
	 */
	public static function setMax(int $count = -1)
	{
		if ($count < 1) {
			throw new \Exception('连接池最大容量至少为1');
		}
		static::$maxPool = $count;
	}
	
	/**
	 * @param $patten
	 *
	 * @return bool
	 */
	public static function removeByPatten($patten)
	{
		foreach (static::$pools as $key => $val) {
			if (!preg_match('/' . preg_quote($patten) . '/', $key)) {
				continue;
			}
			unset(static::$pools[$key]);
		}
		return true;
	}
}