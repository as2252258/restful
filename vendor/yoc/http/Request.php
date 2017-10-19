<?php

namespace yoc\http;

use Exception;
use yoc\base\Components;
use yoc\Di\Ioc;

/**
 * Class Request
 *
 * @property \yoc\http\Input    $input
 * @property \yoc\http\Response $response
 */
class Request extends Components
{
	public $fd;
	
	public $startTime = null;
	
	public $action;
	
	public $controller;
	
	public $isSocket = false;
	
	public $isHttp = true;
	
	/**
	 * @var Response
	 */
	private $_resp = null;
	
	/**
	 * @var null
	 */
	private $_input = null;
	
	/**
	 * @param $key
	 * @param $value
	 */
	public function append($key , $value)
	{
		$this->input->append($key , $value);
	}
	
	/**
	 * @param $value
	 *
	 * @return mixed|object
	 */
	public function setResponse($value)
	{
		return $this->_resp = Ioc::createObject('yoc\http\Response' , [] , $value);
	}
	
	/**
	 * @return bool
	 */
	public function checkSource()
	{
		$source = $this->input->getHeader('source');
		if (in_array($source , ['pc' , 'browser' , 'mobile'])) {
			return true;
		}
		return false;
	}
	
	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);
		if (method_exists($this , $method)) {
			return $this->{$method}();
		} else if (array_key_exists($name , $this->data)) {
			return $this->data[$name];
		} else if (property_exists($this , $name)) {
			return $this->$name;
		} else {
			return null;
		}
	}
	
	/**
	 * @param $name
	 * @param $value
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __set($name , $value)
	{
		$method = 'set' . ucfirst($name);
		if (method_exists($this , $method)) {
			$this->$method($value);
		} else if (property_exists($this , $name)) {
			$this->$name = $value;
		} else {
			throw new \Exception('Set Unknown Property ' . $name);
		}
		return $this;
	}
	
	/**
	 * @return string
	 * @throws Exception
	 */
	public function getAction()
	{
		if ($this->isDelete() && empty($this->action)) {
			$this->action = 'delete';
		}
		if (!preg_match('/^[a-zA-Z]+$/' , $this->action)) {
			throw new Exception('404 Page Error');
		}
		return 'action' . ucfirst($this->action);
	}
	
	/**
	 * @return bool
	 */
	public function isDelete()
	{
		return strtolower($this->input->getHeader('request_method')) == 'delete';
	}
	
	/**
	 * @return string
	 * @throws Exception()
	 */
	public function getController()
	{
		if (!preg_match('/^[a-zA-Z]+$/' , $this->controller)) {
			throw new Exception('404 Page Error');
		}
		return 'Controller\\' . ucfirst($this->controller) . 'Controller';
	}
	
	/**
	 * @return mixed|null
	 */
	public function getLastError()
	{
		return \Yoc::getError()->lastError('error');
	}
	
	/**
	 * @param $route
	 *
	 * @return bool
	 */
	public function is($route)
	{
		if (!is_array($route)) {
			return $this->check($route);
		}
		foreach ($route as $val) {
			if (strpos($val , '/') === false) {
				if ($this->controller == $val) {
					return true;
				};
			} else {
				if ($this->getUrlPath() == $val && $this->check($val)) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * @param $route
	 *
	 * @return bool
	 */
	private function check($route)
	{
		if (strpos($route , '/') !== null) {
			return $route == $this->getUrlPath();
		} else {
			return $this->action == $route || $this->controller == $route;
		}
	}
	
	/**
	 * @return string
	 */
	public function getUrlPath()
	{
		return $this->controller . '/' . $this->action;
	}
	
	/**
	 * @param $patten
	 *
	 * @return int
	 */
	public function match($patten)
	{
		return preg_match('/^' . preg_quote($patten , '/') . '/' , $this->getUrlPath());
	}
	
	/**
	 * @return bool
	 */
	public function isDebug()
	{
		return $this->input->getHeader('debug') == 1;
	}
	
	/**
	 * @return bool
	 */
	public function isPost()
	{
		return strtolower($this->input->getHeader('request_method')) == 'post';
	}
	
	/**
	 * @return bool
	 */
	public function isGet()
	{
		return strtolower($this->input->getHeader('request_method')) == 'get';
	}
	
	/**
	 * @return string
	 */
	public function getRequestMethod()
	{
		return strtolower($this->input->getHeader('request_method'));
	}
	
	/**
	 * @return array
	 */
	public function getHttpHeaders()
	{
		return $this->input->getHeader('header');
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function getCookie($key)
	{
		return array_key_exists($key , $this->cookie) ? $this->cookie[$key] : null;
	}
	
	public function load()
	{
		return $this->getInput()->all();
	}
	
	/**
	 * @return \yoc\http\Input
	 */
	protected function getInput()
	{
		if ($this->_input instanceof Input) {
			return $this->_input;
		}
		return Ioc::single()->get('yoc\http\Input');
	}
	
	/**
	 * @param $value
	 *
	 * @return mixed|object
	 */
	public function setInput($value)
	{
		return $this->_input = Ioc::createObject('yoc\http\Input' , [] , $value);
	}
	
	/**
	 * @return Response
	 */
	protected function getResponse()
	{
		if ($this->_resp !== null) {
			return $this->_resp;
		}
		$this->_resp = Ioc::single()->get('yoc\http\Response');
		return $this->_resp;
	}
}