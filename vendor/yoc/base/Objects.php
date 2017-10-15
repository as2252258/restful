<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/21 0021
 * Time: 17:21
 */

namespace yoc\base;


use yoc\event\Event;

class Objects extends AbServer
{
	
	private $_events = [];
	
	public static function className()
	{
		return get_called_class();
	}
	
	/**
	 * @param $class
	 * @param $event
	 *
	 * @return bool
	 */
	public function hasEventHandlers($event , $class = '')
	{
		return isset($this->_events[$event]) || Event::hasHandlers($event , $class);
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function push($key , $value)
	{
		return \Yoc::$app->pool->push($key , $value);
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function add($key , $value)
	{
		return \Yoc::$app->pool->addItem($key , $value);
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function updatePool($key , $value)
	{
		return \Yoc::$app->pool->update($key , $value);
	}
	
	/**
	 * @param $controller
	 * @param $action
	 * @param $request
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	protected function run($request)
	{
		$controller = $this->createController($request->controller , $request);
		$action = 'action' . ucfirst($request->action);
		if (!method_exists($controller , $action)) {
			throw new \Exception('Page ' . $action . ' Not Find' , 404);
		}
		$this->on('beforeAction' , [$controller , 'beforeAction'] , [$request]);
		if (method_exists($controller , 'afterAction')) {
			$this->on('afterAction' , [$controller , 'afterAction'] , [$request]);
		}
		$this->trigger('beforeAction');
		return $controller->{$action}($request);
	}
	
	/**
	 * @param $controller
	 * @param $request
	 *
	 * @return object
	 * @throws \Exception
	 */
	protected function createController($controller , $request)
	{
		$path = \Yoc::$app->controllerPath;
		$controller = ucfirst($controller) . 'Controller';
		if (empty($path['path'])) {
			$path['path'] = BASE_PATH . '/app/controller';
		}
		$file_path = ltrim($path['path'] , '/') . '/' . $controller . '.php';
		if (!file_exists('/' . $file_path)) {
			throw new \Exception('Request Error: Not Find ' . $controller);
		}
		if (empty($path['namespace'])) {
			$path['namespace'] = 'app\\controller';
		}
		$namespace = $path['namespace'] . '\\' . $controller;
		$reflect = new \ReflectionClass($namespace);
		if (!$reflect->isInstantiable()) {
			throw new \Exception($reflect->getName());
		}
		return $reflect->newInstanceArgs([$request]);
	}
	
	/**
	 * @param       $class
	 * @param       $event
	 * @param       $callback
	 * @param array $param
	 *
	 * @return bool
	 */
	public function on($event , $callback , $param = [])
	{
		if (empty($this->_events[$event])) {
			$this->_events[$event][] = [$callback , $param];
		} else {
			array_unshift($this->_events[$event] , [$callback , $param]);
		}
		return isset($this->_events[$event]);
	}
	
	/**
	 * @param        $event
	 * @param string $class
	 * @param bool   $onlyOne
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function trigger($event , $class = '' , $onlyOne = true)
	{
		if (is_object($class)) {
			$class = get_class($class);
		}
		if (!empty($this->_events) && isset($this->_events[$event])) {
			$events = $this->_events[$event];
			unset($this->_events[$event]);
			foreach ($events as $key => $val) {
				$callback = $val[0];
				if (is_array($callback) && isset($callback[1]) && isset($callback[1])) {
					$callback[0]->{$callback[1]}(...$val[1]);
				} else if (is_callable($callback , true)) {
					call_user_func($callback , ...$val[1]);
				}
				unset($this->_events[$key]);
			}
		}
		return Event::trigger($event , $class , $onlyOne);
	}
}