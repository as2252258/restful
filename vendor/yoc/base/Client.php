<?php

namespace yoc\base;


use yoc\db\ActiveRecord;
use yoc\db\Collection;

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
	public static function send($host, $port, $data, $isException = false)
	{
		static $client = null;
		if ($client === null) $client = new Client();
		return $client->push($host, $port, $data, $isException);
	}
	
	/**
	 * @param $host
	 * @param $port
	 * @param $data
	 *
	 * @return $this
	 * @throws \Exception
	 */
	private function push($host, $port, $data, $isException)
	{
		$this->client = new \swoole_client(SWOOLE_TCP);
		if (!$this->client->connect($host, $port)) {
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
		return json_encode($this->toArray($data));
	}
	
	/**
	 * @param $data
	 *
	 * @return array
	 */
	private function toArray($data)
	{
		if (!is_array($data) || empty($data)) {
			return [];
		}
		$tmp = [];
		foreach ($data as $key => $val) {
			if (is_array($val)) {
				$tmp[$key] = $this->toArray($val);
			} else if (is_object($val)) {
				if ($val instanceof Collection) {
					$tmp[$key] = $val->toArray();
				} else if ($val instanceof ActiveRecord) {
					$tmp[$key] = $val->toArray();
				} else {
					$tmp[$key] = get_object_vars($val);
				}
			} else {
				$tmp[$key] = $val;
			}
		}
		return $tmp;
	}
	
	/**
	 * @return string
	 */
	public function recv()
	{
		return $this->client->recv();
	}
}