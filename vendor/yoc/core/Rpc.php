<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/16 0016
 * Time: 14:31
 */

namespace yoc\core;


use yoc\base\Objects;

class Rpc extends Objects
{
	
	/**
	 * @param       $host
	 * @param       $port
	 * @param       $param
	 * @param array $header
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function get($host , $port , $param , $header = [])
	{
		$client = new \swoole_client(SWOOLE_TCP);
		if (!$client->connect($host , $port)) {
			throw new \Exception('Host:' . $host . ' Connect Fail');
		}
		$send = $client->send($param);
		if ($send === false) {
			throw new \Exception($client->errCode);
		}
		$data = $client->recv();
		$client->close();
		return $data;
	}
	
	
	private static function createToken($host , $port , $param)
	{
	
	}
}