<?php

namespace yoc\base;


use app\model\Auth;

class Rbac extends Objects
{
	
	/** @var Rbac $rbac */
	public static $rbac;
	
	/**
	 * @return array
	 */
	public static function update()
	{
		if (!static::$rbac instanceof Rbac) {
			static::$rbac = new Rbac();
		}
		$rbac = static::$rbac;
		$class = $rbac->getControllerFiles();
		$data = [];
		foreach ($class as $key => $val) {
			$auth = $rbac->reflection($val);
			if (empty($auth)) {
				continue;
			}
			foreach ($auth as $_key => $_val) {
				array_push($data, $_val);
			}
		}
		if (!empty($data)) {
			\Yoc::getRedis()->del(\Yoc::getRedis()->keys('xl_auth_*'));
			Auth::whereNotIn('id', array_column($data, 'id'))->delete();
		}
		return $data;
	}
	
	/**
	 * @param $name
	 *
	 * @return array
	 */
	public function reflection($name)
	{
		$class = new \ReflectionClass($name);
		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
		if (empty($methods)) {
			return [];
		}
		$save = [];
		$_array = explode('\\', $name);
		$className = end($_array);
		foreach ($methods as $key => $val) {
			if ($val->class != $name) continue;
			if (!preg_match('/^action/', $val->name)) continue;
			$save[] = $this->saveAuth($className, $val->name)->getAttributes();
		}
		return $save;
	}
	
	/**
	 * @param $controller
	 * @param $method
	 *
	 * @return Auth
	 * @throws \Exception
	 */
	public function saveAuth($controller, $method)
	{
		$controller = lcfirst($controller);
		if (strpos($controller, 'Controller')) {
			$controller = str_replace('Controller', '', $controller);
		}
		$method = lcfirst(str_replace('action', '', $method));
		$auth = Auth::find()->where(['action' => $method, 'controller' => $controller])->one();
		if (!empty($auth)) {
			return $auth;
		}
		$auth = new Auth();
		$auth->setBatch([
			'controller' => $controller,
			'module'     => 0,
			'alias'      => $controller . '_' . $method,
			'action'     => $method,
			'neeLogin'   => 1,
			'status'     => 1,
			'addTime'    => date('Y-m-d H:i:s'),
			'modifyTime' => date('Y-m-d H:i:s'),
		]);
		if (!$auth->save()) {
			throw new \Exception($auth->getLastError());
		}
		return $auth;
	}
	
	/**
	 * @return string
	 */
	public function getControllerDirPath()
	{
		return rtrim(\Yoc::$app->controllerPath['path'], '/');
	}
	
	/**
	 * @return array
	 */
	public function getControllerFiles()
	{
		$data = [];
		$name = \Yoc::$app->controllerPath['namespace'];
		$path = $this->getControllerDirPath();
		foreach (glob($path . '/*Controller.php') as $key => $val) {
			$end = explode('/', $val);
			$data[] = $name . '\\' . str_replace('.php', '', end($end));
		}
		return $data;
	}
}