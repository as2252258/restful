<?php

namespace yoc\base;


use yoc\di\Locator;

class Components extends Objects
{
	
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		} else if ($this->has($name)) {
			return $this->get($name);
		} else {
			return parent::__get($name);
		}
	}
	
	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function has($name)
	{
		return \Yoc::$app->locator->has($name);
	}
	
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get($name)
	{
		return \Yoc::$app->locator->get($name);
	}
	
	/**
	 * @param $name
	 * @param $param
	 *
	 * @return mixed
	 */
	public function set($name, $param)
	{
		return \Yoc::$app->locator->set($name, $param);
	}
	
	/**
	 * @param $configs
	 *
	 * @return $this
	 */
	public function setComponents($configs)
	{
		foreach ($configs as $key => $val) {
			$this->set($key, $val);
		}
		return $this;
	}
}