<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/16 0016
 * Time: 11:03
 */

namespace yoc\core;


use yoc\base\Components;

class UrlManager extends Components
{
	public $rules = [];
	
	public $enableSuffix = false;
	
	public $suffix = 'html';
	
	public function checkUrlPath()
	{
		$this->enableSuffix();
		$path = $this->requestUri();
		if ($this->enableSuffix) {
			$path = rtrim($path , '.' . $this->suffix);
		}
		if (empty($this->rules)) return true;
		foreach ($this->rules as $key => $val) {
			if (empty($key) || empty($val)) continue;
			if(!preg_match($key)){
			
			}
		}
	}
	
	/**
	 * @throws \Exception
	 * 检查路由尾缀
	 */
	public function enableSuffix()
	{
		if (!$this->enableSuffix) {
			return;
		}
		$uri = $this->requestUri();
		$pathInfo = pathinfo($uri);
		if (empty($pathInfo['extension'])) {
			throw new \Exception(\Code::AUTH_GROUP_ERROR);
		}
		if ($pathInfo['extension'] != $this->suffix) {
			throw new \Exception(\Code::AUTH_GROUP_ERROR);
		}
	}
	
	/**
	 * @return array|string
	 */
	private function requestUri()
	{
		$uri = \Yoc::getRequest()->input->getHeader('request_uri');
		return ltrim($uri , '/');
	}
}