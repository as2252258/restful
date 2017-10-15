<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/25 0025
 * Time: 10:22
 */

namespace yoc\base;


use yoc\cache\FileCache;
use yoc\core\Authorize;
use yoc\db\Connect;
use yoc\di\Ioc;
use yoc\di\Locator;
use yoc\Event\Event;
use yoc\http\request;
use yoc\http\Response;

/**
 * Class Application
 *
 * @package yoc\base
 *
 * @property Authorize $auth
 * @property \Redis    $redis
 * @property Connect   $db
 * @property FileCache $fileCache
 * @property Server    $socket
 * @property Response  $response
 */
class Application extends Components
{
	
	/** @var string $serverName */
	public $serverName;
	
	/** @var  array $modelPath */
	public $modelPath;
	
	/** @var [path, namespace] $controllerPath */
	public $controllerPath;
	
	/** @var Locator $locator */
	public $locator;
	
	public function __construct(array $config = [])
	{
		\Yoc::$app = $this;
		$this->locator = Ioc::createObject(Locator::className());
		Components::__construct($this->errorHandler($config));
	}
	
	/**
	 * @param $config
	 *
	 * @return mixed
	 * error handler register
	 */
	public function errorHandler($config)
	{
		if (isset($config['components']['error'])) {
			$this->set('error', $config['components']['error']);
			$this->get('error')->register();
			unset($config['components']['error']);
		}
		return $config;
	}
	
	/**
	 * @return Connect
	 */
	public function getDb()
	{
		return $this->get('db');
	}
	
	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->get('request');
	}
	
	/**
	 * @return \Redis
	 */
	public function getRedis()
	{
		return $this->get('redis')->useRedis();
	}
	
	/**
	 * @return Authorize
	 */
	public function getAuth()
	{
		return Ioc::createObject(Authorize::className());
	}
	
	/**
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->get('response');
	}
	
	/**
	 * 启动服务
	 */
	public function start()
	{
		\Yoc::$app->socket->start();
		return \Yoc::$server;
	}
}