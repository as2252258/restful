<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/25 0025
 * Time: 13:33
 */

namespace yoc\base;


use yoc\http\Request;

class Controller extends Objects
{
	
	/**
	 * @return mixed|null
	 */
	public function getLastError()
	{
		return \Yoc::getError()->lastError('request');
	}
	
	/**
	 * @param                   $controller [controller className]
	 * @param                   $action     [controller actionName]
	 * @param \yoc\http\Request $request
	 *
	 * @return mixed
	 * @throws \Exception
	 * 重定向
	 */
	public function redirect($controller , $action , Request $request , $param = [])
	{
		$request->action = $action;
		$request->controller = $controller;
		if (!empty($param) && is_array($param)) {
			foreach ($param as $key => $val) {
				$request->input->append($key , $val);
			}
		}
		return $this->run($request);
	}
	
	/**
	 * 注销回调
	 */
	public function afterAction()
	{
		\Yoc::$app->locator->remove('request');
	}
}