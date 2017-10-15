<?php

class Yoc
{
	
	/** @var \yoc\base\Application */
	public static $app;
	
	/** @var swoole_websocket_server $server */
	public static $server;
	
	/** @var array $classMap */
	private static $classMap = [];
	
	/**
	 * @param $className
	 *
	 * @throws Exception
	 * 自动加载
	 */
	public static function autoload($className)
	{
		if (empty(static::$classMap)) {
			static::$classMap = require_once(YOC_PATH . '/class.php');
		}
		$classFile = null;
		if (isset(static::$classMap[$className])) {
			$classFile = static::$classMap[$className];
		}
		
		if (empty($classFile)) {
			$classFile = BASE_PATH . '/' . str_replace('\\' , '/' , $className) . '.php';
		}
		
		if (!file_exists($classFile)) {
			throw new Exception("404 Not Find Page : '$className'");
		}
		
		include $classFile;
	}
	
	/**
	 * @param       $object
	 * @param array $config
	 *
	 * @return mixed
	 */
	public static function configure($object , array $config = [])
	{
		foreach ($config as $key => $val) {
			$object->$key = $val;
		}
		return $object;
	}
	
	/**
	 * @return \yoc\exception\ErrorHandler
	 */
	public static function getError()
	{
		return self::$app->get('error');
	}
	
	/**
	 * @return Redis
	 */
	public static function getRedis()
	{
		return self::$app->get('redis')->useRedis();
	}
	
	/**
	 * @return yoc\event\Event
	 */
	public static function getEvent()
	{
		return self::$app->get('event');
	}
	
	/**
	 * @return \yoc\http\request
	 */
	public static function getRequest()
	{
		return self::$app->get('request');
	}
	
	/**
	 * @return mixed|object|\yoc\di\Locator
	 */
	public static function getLocator()
	{
		return self::$app->locator;
	}
	
	/**
	 * @param $callBack
	 *
	 * @return bool
	 */
	public static function isCallBack($callBack)
	{
		$array = [true , ''];
		if (is_string($callBack)) {
			if (strpos($callBack , '::') != null) {
				$explode = explode('::' , $callBack);
				if (!class_exists($explode[0])) {
					$array = [false , 'Class ' . $explode[0] . ' Not Exists'];
				} else if (!method_exists(new $explode[0] , $explode[1])) {
					$array = [false , 'Method ' . $explode[0] . '::' . $explode[1] . ' Not Exists'];
				}
			} else if (!function_exists($callBack)) {
				$array = [false , 'Function ' . $callBack . ' Not Exists'];
			}
		} else if (is_array($callBack)) {
			if (empty($callBack[0]) || empty($callBack[1])) {
				$array = [false , 'Param Num Error'];
			} else if (is_object($callBack[0])) {
				if (!method_exists($callBack[0] , $callBack[1])) {
					$array = [false , 'Method ' . $callBack[0] . '->' . $callBack[1] . ' Not Exists'];
				}
			} else {
				$instance = \yoc\di\Ioc::createObject($callBack[0]);
				if (!method_exists($instance , $callBack[1])) {
					$array = [false , 'Method ' . $callBack[0] . '->' . $callBack[1] . ' Not Exists'];
				}
			}
		} else if (!is_callable($callBack , true)) {
			$array = [false , 'Method ' . $callBack[0] . '->' . $callBack[1] . ' Not Exists'];
		}
		list($results , $message) = $array;
		if (!empty($message)) {
			static::getError()->addError('application' , $message);
		}
		return $results;
	}
	
	/**
	 * @param string $event
	 * @param        $userId
	 * @param        $data
	 */
	public static function pushUser(string $event , $userId , $data = null)
	{
		if (is_object($data)) {
			if ($data instanceof \yoc\db\ActiveRecord || $data instanceof \yoc\db\Collection) {
				$data = $data->toArray();
			} else {
				$data = get_object_vars($data);
			}
		}
		if (!is_array($data)) $data = ['data' => $data];
		$message = json_encode(array_merge(['callback' => $event] , $data));
		$fd = static::$app->redis->sMembers(FD_LIST . $userId);
		foreach ($fd as $_key => $_val) {
			if (!static::$server->exist($_val)) {
				static::$app->redis->sRem(FD_LIST . $userId , $_val);
				continue;
			}
			static::$server->push($_val , $message);
		}
		return;
	}
	
	
	/**
	 * @param mixed  $string
	 * @param string $file
	 * @param string $line
	 *
	 * @return bool|int
	 */
	public static function trance($string , $file = '' , $line = '')
	{
		$runtime = RUNTIME_PATH . '/trance/';
		if (!is_dir($runtime)) {
			mkdir($runtime);
		}
		$runtime .= date('Y_m_d') . '.trance';
		if (!file_exists($runtime)) {
			touch($runtime);
		}
		$timestamp = date('Y-m-d H:i:s');
		$microtime = (string) microtime(true);
		$_string = '[' . $timestamp . strchr($microtime , '.') . ']';
		if (!is_string($string)) {
			$_string .= PHP_EOL . var_export($string);
		} else {
			$_string .= $string;
		}
		if (!empty($file)) {
			$_string .= '    ' . $file;
		}
		if (!empty($line)) {
			$_string .= '    ' . $line;
		}
		$fopen = fopen($runtime , 'w');
		$fread = fwrite($fopen , $_string . PHP_EOL . PHP_EOL);
		fclose($fopen);
		return $fread;
	}
	
	/**
	 * @return null|\app\model\User
	 */
	public static function getUser()
	{
		$user = static::getRequest()->input->getHeader('user');
		if (!empty($user)) {
			return \app\model\User::findOne($user);
		}
		return null;
	}
}

spl_autoload_register(['Yoc' , 'autoload'] , true , true);