<?php

namespace yoc\di;


use yoc\base\Objects;
use yoc\base\Components;

class Locator extends Components
{
	private $_components = [];
	
	private $_definition = [];
	
	/** @var static $locator */
	private static $locator;
	
	/**
	 * @return Locator
	 */
	public static function single()
	{
		if (!static::$locator instanceof Locator) {
			static::$locator = new static();
		}
		return static::$locator;
	}
	
	/**
	 * @param       $name
	 * @param array $param
	 *
	 * @return static
	 */
	public function set($name, $param = [], $isGet = false)
	{
		if (empty($param)) {
			unset($this->_components[$name], $this->_definition[$name]);
			return $this;
		}
		$this->_definition[$name] = $param;
		if ($isGet) {
			return $this->get($name);
		}
		return $this;
	}
	
	/**
	 * @param $name
	 *
	 * @return array|mixed|object
	 * @throws \Exception
	 */
	public function get($name)
	{
		if (!is_string($name)) return $name;
		if (isset($this->_components[$name])) {
			return $this->_components[$name];
		} else if (!isset($this->_definition[$name])) {
			return null;
//			throw new \Exception('Get Unknown Locator : ' . $name);
		}
		$definition = $this->_definition[$name];
		if (is_callable($definition, true)) {
			return call_user_func($definition);
		}
		if (is_object($definition)) {
			$this->_components[$name] = $definition;
		} else {
			$this->_components[$name] = Ioc::createObject($definition);
		}
		return $this->_components[$name];
	}
	
	public function remove()
	{
		foreach (func_get_args() as $key => $vla) {
			if ($this->has($vla)) {
				$define = $this->_definition[$vla];
				if (isset($define['class'])) {
					Ioc::single()->remove($define['class']);
				}
			}
			unset($this->_components[$vla], $this->_definition[$vla]);
		}
	}
	
	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->_components[$name]) || isset($this->_definition[$name]);
	}
	
	/**
	 * @return array
	 */
	public function getDefinitions()
	{
		return $this->_definition;
	}
	
	/**
	 * @return array
	 */
	public function getComponents()
	{
		return $this->_components;
	}
	
	/**
	 * @param $all
	 *
	 * @return static::single()
	 */
	public function batchSet($all)
	{
		if (!is_array($all)) return static::single();
		foreach ($all as $key => $val) {
			$this->set($key, $val);
		}
		return static::single();
	}
	
	/**
	 * @param array $value
	 *
	 * @return bool
	 */
	public function register($value = [])
	{
		foreach ($value as $key => $val) {
			$this->set($key, $val);
		}
		return true;
	}
}