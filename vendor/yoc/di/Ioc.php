<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/18 0018
 * Time: 10:50
 */

namespace yoc\di;


use yoc\base\Objects;

class Ioc
{
	private $_signal = [];
	
	private $_config = [];
	
	private $reflect = [];
	
	private static $ioc = null;
	
	/**
	 * Ioc constructor.
	 */
	private function __construct()
	{
	}
	
	/**
	 * @return static
	 */
	public static function single()
	{
		if (static::$ioc === null) {
			static::$ioc = new static();
		}
		return static::$ioc;
	}
	
	/**
	 * @param       $className
	 * @param array $param
	 *
	 * @throws \Exception
	 */
	public function set($className , $param = [])
	{
		if (empty($param)) {
			unset($this->_config[$className] , $this->_signal[$className]);
		}
		if (!is_array($param)) {
			throw new \Exception('The Config Must A Array');
		}
		$this->_config[$className] = $param;
	}
	
	/**
	 * @param       $className
	 * @param array $construct
	 * @param array $config
	 *
	 * @return mixed|object
	 * @throws \Exception()
	 */
	public function get($className , $construct = [] , $config = [])
	{
		if (isset($this->_signal[$className])) {
			if (empty($construct)) {
				return $this->dealing(clone $this->_signal[$className] , $config);
			}
			unset($this->_signal[$className] , $this->_config[$className]);
		}
		if (!isset($this->_config[$className])) {
			return $this->build($className , $construct , $config);
		}
		$construct = $this->mergeParam($this->_config[$className] , $construct);
		if (is_string($className)) {
			$object = $this->build($className , $construct , $config);
			$this->_signal[$className] = clone $object;
			return $object;
		} else if (is_callable($className , true)) {
			return call_user_func($className , $construct , $config);
		} else {
			throw new \Exception('Unknown Class ' . $className);
		}
	}
	
	/**
	 * @param $class
	 * @param $config
	 *
	 * @return mixed
	 */
	private function dealing($class , $config)
	{
		if (!empty($config) && is_array($config)) {
			foreach ($config as $key => $val) {
				$class->$key = $val;
			}
		}
		return $class;
	}
	
	/**
	 * @param       $className
	 * @param array $construct
	 * @param array $config
	 *
	 * @return mixed|object
	 * @throws \Exception
	 */
	public static function createObject($className , $construct = [] , $config = [])
	{
		$ioc = Ioc::single();
		if (is_string($className)) {
			return $ioc->get($className , $construct , $config);
		}
		if (is_callable($className , true)) {
			return call_user_func($className , $construct , $config);
		}
		if (is_array($className) && isset($className['class'])) {
			$_className = $className['class'];
			unset($className['class']);
			$config = $ioc->mergeParam($className , $config);
			$ioc->_signal[$_className] = $ioc->build($_className , $construct , $config);
			return clone $ioc->_signal[$_className];
		} else {
			throw new \Exception('Unknown Class : ' . $className);
		}
	}
	
	/**
	 * @param $name
	 *
	 * @return bool
	 */
	protected function hasSignal($name)
	{
		return isset($this->_signal[$name]) && !empty($this->_signal[$name]);
	}
	
	/**
	 * @param $name
	 */
	public function remove($name)
	{
		unset($this->_config[$name] , $this->_signal[$name] , $this->reflect[$name]);
	}
	
	/**
	 * @param $name
	 *
	 * @return bool
	 */
	protected function hasConfig($name)
	{
		return isset($this->_config[$name]);
	}
	
	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	protected function getConfig($name)
	{
		return $this->hasConfig($name) ? $this->_config[$name] : null;
	}
	
	/**
	 * @param       $className
	 * @param array $construct
	 * @param array $config
	 *
	 * @return object
	 */
	protected function build($className , $construct = [] , $config = [])
	{
		/** @var \ReflectionClass $reflection */
		list($reflection , $definition) = $this->reflection($className);
		
		$merge = $this->mergeParam($definition , $construct);
		
		$instance = $reflection->newInstanceArgs($merge);
		
		return $this->dealing($instance , $config);
	}
	
	/**
	 * @param      $className
	 * @param bool $isCreate
	 *
	 * @return \ReflectionClass
	 */
	public function getReflection($className , $isCreate = true)
	{
		if (isset($this->reflect[$className])) {
			return $this->reflect[$className];
		}
		if (!$isCreate) {
			return null;
		}
		list($reflection , $definition) = $this->reflection($className);
		return $reflection;
	}
	
	/**
	 * @param       $className
	 * @param array $construct
	 * @param array $config
	 *
	 * @return {$className}
	 */
	public function getInstance($className , $construct = [] , $config = [])
	{
		return $this->build($className , $construct , $config);
	}
	
	/**
	 * @param       $class
	 * @param array $construct
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function reflection($class)
	{
		$_class = new \ReflectionClass($class);
		if (!$_class->isInstantiable()) {
			throw new \Exception($_class->name);
		}
		
		$_definition = $this->getConstructorParam($_class);
		
		$this->reflect[$class] = $_class;
		$this->_config[$class] = $_definition;
		
		return [$_class , $_definition];
	}
	
	/**
	 * @param \ReflectionClass $reflection
	 *
	 * @return array
	 */
	private function getConstructorParam(\ReflectionClass $reflection)
	{
		$_param = [];
		if ($constructor = $reflection->getConstructor()) {
			foreach ($constructor->getParameters() as $key => $val) {
				if ($val->isDefaultValueAvailable()) {
					$_param[] = $val->getDefaultValue();
				} else {
					$class = $val->getClass();
					$_param[] = Inline::of($class ? $class->getName() : null);
				}
			}
		}
		return $_param;
	}
	
	/**
	 * @param $oldParam
	 * @param $newParam
	 *
	 * @return mixed
	 */
	protected function mergeParam($oldParam , $newParam)
	{
		if (empty($oldParam) && empty($newParam)) {
			return $oldParam;
		} else if (empty($oldParam)) {
			return $newParam;
		} else if (empty($newParam)) {
			return $oldParam;
		} else {
			foreach ($newParam as $key => $value) {
				$oldParam[$key] = $value;
			}
			return $oldParam;
		}
	}
}