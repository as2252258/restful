<?php

namespace yoc\base;


use yoc\di\Ioc;
use yoc\http\request;
use yoc\http\Response;

class Server extends Components
{
	/** @var string $host */
	public $host;
	
	/** @var int $port */
	public $port;
	
	/** @var  array $setting */
	public $setting;
	
	/** @var array $config */
	public $config;
	
	/** @var array $callback */
	public $callback;
	
	/** @var int $httpPort */
	public $httpPort;
	
	/** @var int $httpHost */
	public $httpHost;
	
	/** @var null|array|callable $process */
	public $process = null;
	
	/** @var \swoole_websocket_server $server */
	public $server;
	public $paths = [];
	/** @var \swoole_http_response $response */
	private $response;
	
	public function start()
	{
		\Yoc::$server = new \swoole_websocket_server($this->host , $this->port);
		if (empty($this->setting) || empty($this->callback)) {
			throw new \Exception('缺少主要配置项');
		}
		\Yoc::$server->set($this->setting);
		foreach ($this->callback as $key => $val) {
			if (!is_object($val)) {
				$val = Ioc::createObject($val);
			}
			\Yoc::$server->on($key , [$val , 'on' . ucfirst($key)]);
		}
		\Yoc::$server->addlistener($this->httpHost , $this->httpPort , SWOOLE_TCP);
		\Yoc::$server->on('request' , [$this , 'onRequest']);
		if (!empty($this->process)) {
			if (!\Yoc::isCallback($this->process)) {
				throw new \Exception('');
			}
			$this->process = new \swoole_process($this->process);
			\Yoc::$server->addProcess($this->process);
		}
		file_put_contents(RUNTIME_PATH . '/socket/socket.sock' , '');
		\Yoc::$server->start();
	}
	
	/**
	 * @param \swoole_http_request  $request
	 * @param \swoole_http_response $response
	 */
	public function onRequest(\swoole_http_request $request , \swoole_http_response $response)
	{
		$time = microtime(true);
		try {
			$this->response = $response;
			$message = 'Not Found Request:' . $request->server['request_uri'];
			if (empty($request->server['request_uri'])) {
				throw new \Exception($message , 404);
			} else if ($request->server['request_uri'] == '/favicon.ico') {
				$data = [];
			} else {
				/** @var Request $_request */
				$data = $this->run($this->request($request));
				$this->on('afterAction' , [$this , 'clear'] , \Yoc::$app);
			}
		} catch (\Exception $exception) {
			\Yoc::getError()->setLogger($exception , \Code::ERROR_LEVEL_EXCEPTION);
			/** @var Request $_request */
			$code = $exception->getCode() == 0 ? 500 : $exception->getCode();
			$message = $exception->getMessage();
			if (is_numeric($message)) {
				$message = \Code::$INFO[$message];
			}
			$data = Response::analysis($code , $message , $exception->getTrace());
		}
		if ($this->response instanceof \swoole_http_response) {
			$this->send($data , $time);
		}
		$this->trigger('afterAction');
	}
	
	/**
	 * @param      $request
	 * @param null $response
	 *
	 * @return array
	 */
	private function request($request)
	{
		$request_uri = $request->server['request_uri'];
		if (empty($request_uri) || $request_uri == '/') {
			$request_uri = $request->server['request_uri'] = '/site/index';
		}
		$explode = explode('/' , ltrim($request_uri , '/'));
		$_class = [
			'class'      => 'yoc\http\Request' ,
			'controller' => empty($explode[0]) ? 'site' : $this->stanceRoute($explode[0]) ,
			'action'     => empty($explode[1]) ? 'index' : $this->stanceRoute($explode[1]) ,
		];
		$this->clear(\Yoc::$app);
		$this->set('request' , array_merge($_class , [
			'fd'    => $request->fd ,
			'input' => [
				'data'   => array_merge(
					$request->post ?? [] ,
					$request->files ?? []
				) ,
				'get'    => $request->get ?? [] ,
				'cookie' => $request->cookie ?? [] ,
				'header' => array_merge($request->server , $request->header) ,
			]
		]));
		return $this->get('request');
	}
	
	/**
	 * @param string $string
	 *
	 * @return string
	 * 路由重建
	 */
	private function stanceRoute(string $string)
	{
		$string = strtolower($string);
		if (strpos($string , '-') !== false) {
			$explode = explode('-' , $string);
			$string = '';
			foreach ($explode as $key => $val) {
				$string .= ucfirst($val);
			}
			$string = lcfirst($string);
		}
		return $string;
	}
	
	/**
	 * 清理
	 */
	public function clear($app)
	{
		$app->db->recovery(true);
		$app->db->recovery(false);
		$app->locator->remove('request');
		$app->locator->remove('response');
	}
	
	/**
	 * @param $data
	 * @param $time
	 */
	private function send($data , $time)
	{
		if (!is_string($data)) {
			$data = json_encode($data , JSON_UNESCAPED_UNICODE);
		}
		$this->response->gzip(4);
		$this->response->status(200);
		$this->response->header('Content-Type' , 'application/json');
		$this->response->header('Access-Control-Allow-Origin' , '*');
		$this->response->header("Access-Control-Allow-Headers" , "time,token,user,Authorization,source");
		$this->response->header('Run-Time' , sprintf('%.6f' , microtime(true) - $time));
		$this->response->end($data);
	}
	
	/**
	 * @param $filePath
	 */
	public function download($filePath)
	{
		$response = $this->response;
		try {
			$file = pathinfo($filePath);
			if (!$response instanceof \swoole_http_response) {
				throw new \Exception('response is not instance');
			}
			$response->status(200);
			$response->header('Content-Type' , 'application/octet-stream');
			$response->header('Content-Length' , filesize($filePath));
			$response->header('Content-Disposition' , 'attachment;filename=' . $file['basename']);
			flush();
			$download_rate = 1000;
			$fh = @fopen($filePath , 'r');
			while (!feof($fh)) {
				$response->write(fread($fh , round($download_rate * 2048)));
				flush();
				sleep(1);
			}
			@fclose($fh);
		} catch (\Exception $exception) {
			$_results['errorInfo'] = $exception->getMessage();
			\Yoc::getError()->setLogger(json_encode($_results) , ERROR_LEVEL_EXCEPTION);
			$response->status(500);
			$response->end($exception->getMessage());
		}
	}
}