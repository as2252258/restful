<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/25 0025
 * Time: 0:01
 */

namespace yoc\Event;


use yoc\base\AbServer;
use yoc\base\Objects;
use yoc\di\Ioc;

class Event extends AbServer
{
	private static $events = [];
	
	private static $params = [];
	
	private static $_class = [];
	
	/**
	 * @param       $class
	 * @param       $event
	 * @param       $callback
	 * @param array $param
	 *
	 * @return bool
	 */
	public static function on($event, string $class, $param = [])
	{
		if (is_object($class)) {
			$class = get_class($class);
		}
		static::$events[$event][$class] = [$class, $param];
		return self::hasHandlers($event, $class);
	}
	
	
	/**
	 * @param        $class
	 * @param string $event
	 * 关闭某个事件
	 */
	public static function off($event, $class = '')
	{
		if (empty($class)) {
			unset(static::$events[$event], static::$params[$event]);
		}
		if (isset(static::$events[$event][$class])) {
			unset(static::$events[$event][$class], static::$params[$event][$class]);
		}
	}
	
	/**
	 * @param        $name
	 * @param string $event
	 *
	 * @return bool
	 */
	public static function hasHandlers($event, $name = '')
	{
		if (empty($name)) {
			return isset(static::$events[$event]) && !empty(static::$events[$event]);
		}
		if (is_object($name)) {
			$name = get_class($name);
		}
		return isset(static::$events[$event][$name]) && !empty(static::$events[$event][$name]);
	}
	
	/**
	 * @return array
	 */
	public static function offAll()
	{
		return static::$events = static::$params = [];
	}
	
	/**
	 * @param        $class
	 * @param string $event
	 *
	 * @return bool
	 */
	public static function trigger($event, string $class = '', $onlyOne = false)
	{
		if (!isset(static::$events[$event])) {
			return true;
		}
		$events = static::$events[$event];
		if (isset($events[$class])) {
			if (!method_exists($events[$class], $event)) {
				return true;
			}
			if (!is_object($events[$class][0])) {
				$events[$class][0] = Ioc::createObject($events[$class][0]);
			}
			$call = call_user_func([$events[$class][0], $event], ...$events[$class][1]);
			if ($call === false) {
				throw new \Exception(\Code::SYSTEM_ERROR);
			}
			unset(static::$events[$event][$class]);
		} else {
			foreach ($events as $key => $val) {
				if (is_array($val[0]) && !method_exists($val[0], $event)) {
					continue;
				}
				if (!is_object($val[0])) {
					$val[0] = Ioc::createObject($val[0]);
				}
				$call = call_user_func([$val[0], $event], ...$val[1]);
				if ($call === false) {
					throw new \Exception(\Code::SYSTEM_ERROR);
				}
			}
			unset(static::$events[$event]);
		}
		if (!$onlyOne) {
			if (!empty($events)) {
				unset(static::$events[$event][$class]);
			} else {
				unset(static::$events[$event]);
			}
		}
		return true;
	}
	
	/**
	 * @param        $class
	 * @param string $event
	 *
	 * @return array|mixed
	 * 获取参数
	 */
	public static function getParam($event, $class = '')
	{
		if (!isset(static::$params[$event])) return [];
		if (empty($class) || !array_key_exists($class, static::$params[$class])) {
			return static::$params[$event][$class][1];
		}
		return static::$params[$event][$class][1];
	}
}