<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/28 0028
 * Time: 15:27
 */

namespace yoc\pool;


class ModelPool extends Pool
{
	
	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public static function remove($key)
	{
		if (static::hasItem($key)) {
			unset(static::$pools[$key]);
		}
		return true;
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public static function get($key)
	{
		if (!static::hasItem($key)) {
			return null;
		}
		/** @var \SplStack $_stack */
		$_stack = static::$pools[$key];
		if (empty($_stack)) return null;
		$object = $_stack->pop();
		if (!empty($object)) {
			$_stack->push($object);
		}
		static::$pools[$key] = $_stack;
		return $object;
	}
	
	/**
	 * @param $key
	 * @param $object
	 *
	 * @return bool
	 */
	public static function update($key, $object)
	{
		static::remove($key);
		static::addItem($key, $object);
		return static::hasItem($key);
	}
}