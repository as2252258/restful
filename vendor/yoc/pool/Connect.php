<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/28 0028
 * Time: 13:42
 */

namespace yoc\pool;

class Connect extends Pool
{
	public static function remove($key)
	{
		// TODO: Implement remove() method.
	}
	
	/**
	 * @param $key
	 *
	 * @return \PDO
	 */
	public static function get($key)
	{
		$rand = null;
		if (!self::hasItem($key)) return $rand;
		$pools = static::$pools[$key];
		if (empty($pools) || !$pools instanceof \SplStack) {
			unset(static::$pools[$key]);
		} else {
			if ($pools->count() < 1) return $rand;
			$pools = $pools->pop();
			if (is_array($pools)) {
				$rand = $pools[($_key = array_rand($pools))];
				unset($pools[$_key]);
				self::update($key , $pools);
			}
		}
		return $rand;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public static function push($key , $value)
	{
		if (empty($value)) return true;
		if (static::hasItem($key)) {
			$stack = static::$pools[$key];
			if (empty($stack) || !$stack instanceof \SplStack) {
				$stack = new \SplStack();
			} else {
				$data = $stack->count() > 0 ? $stack->pop() : [];
				$value = array_push($data , $value);
			}
		} else {
			$stack = new \SplStack();
		}
		if (!empty($value) && is_array($value)) {
			$stack->push($value);
		}
		static::$pools[$key] = $stack;
		return isset(static::$pools[$key]);
	}
	
	
	/**
	 * @param $key
	 * @param $object
	 *
	 * @return bool
	 */
	public static function update($key , $object)
	{
		if (!static::hasItem($key)) {
			static::addItem($key , $object);
		} else {
			$stack = new \SplStack();
			$stack->push($object);
			static::$pools[$key] = $stack;
		}
		return isset(static::$pools[$key]);
	}
}