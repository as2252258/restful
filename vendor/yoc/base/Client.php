<?php

namespace yoc\base;


use yoc\core\ArrayAccess;

class Client extends AbServer
{
	
	/** @var \swoole_client $client */
	private $client;
	
	/**
	 * @param $host
	 * @param $port
	 * @param $data
	 *
	 * @return Client
	 */
	public static function send($host , $port , $data , $isException = false)
	{
		static $client = null;
		if ($client === null) $client = new Client();
		return $client->push($host , $port , $data , $isException);
	}
	
	/**
	 * @param $host
	 * @param $port
	 * @param $data
	 *
	 * @return $this
	 * @throws \Exception
	 */
	private function push($host , $port , $data , $isException)
	{
		$this->client = new \swoole_client(SWOOLE_TCP);
		if (!$this->client->connect($host , $port)) {
			throw new \Exception('Host ' . $host . ' Connect Fail!');
		};
		$data = $this->toString($data);
		$results = $this->client->send($data);
		if ($results === false && $isException) {
			throw new \Exception($this->client->errCode);
		}
		return $this;
	}
	
	/**
	 * @param $data
	 *
	 * @return string
	 */
	private function toString($data)
	{
		if (!is_object($data) && !is_array($data)) {
			return $data;
		}
		$data = ArrayAccess::toArray($data);
		return json_encode($data);
	}
	
	/**
	 * @return string
	 */
	public function recv()
	{
		return $this->client->recv();
	}
}