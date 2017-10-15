<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/21 0021
 * Time: 17:20
 */

namespace yoc\base;


use yoc\di\Ioc;
use yoc\Event\Event;

abstract class AbServer
{
	
	public function __construct($config = [])
	{
		if (!empty($config) && is_array($config)) {
			\Yoc::configure($this, $config);
		}
		$this->init();
	}
	
	public function init()
	{
		//初始化事件池
	}
	
	/**
	 * @param $name
	 * @param $value
	 *
	 * @throws \Exception
	 */
	public function __set($name, $value)
	{
		$name = 'set' . ucfirst($name);
		if (method_exists($this, $name)) {
			$this->$name($value);
		} else if (method_exists($this, 'get' . ucfirst($name))) {
			throw new \Exception('Set Only Get Method ' . $name);
		} else {
			throw new \Exception('Set Unknown Method ' . $name);
		}
	}
	
	/**
	 * @param $name
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $name)) {
			return $this->$name();
		} else if (method_exists($this, $method)) {
			return $this->$method();
		} else if (method_exists($this, 'set' . ucfirst($name))) {
			throw new \Exception('Get Only Set Method ' . $name);
		} else {
			throw new \Exception('Get Unknown Method ' . $name);
		}
	}
	
	/**
	 * @param       $class
	 * @param       $name
	 * @param array $param
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function asyncTask($class, $name, $param = [])
	{
		if (is_object($class)) {
			$class = get_class($class);
		}
		$taskId = mt_rand(0, \Yoc::$server->setting['task_worker_num'] - 1);
		$push = \Yoc::$server->task(['class' => $class, 'name' => [$name, $param]], $taskId);
		if ($push === false) {
			throw new \Exception('任务池投递失败');
		}
		return true;
	}
	
	/**
	 * @param $server
	 * @param $name
	 *
	 * @return bool
	 */
	public function recovery($server, $name)
	{
		$method = 'get' . ucfirst($server);
		if (!method_exists(\Yoc::$app, $method)) {
			return true;
		}
		\Yoc::$app->{$server}->recovery($name);
		return true;
	}
	
}