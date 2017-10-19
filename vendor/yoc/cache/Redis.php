<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/23 0023
 * Time: 0:01
 */

namespace yoc\cache;

use yoc\base\Components;
use yoc\db\ActiveRecord;

//use yoc\pool\Connect;

class Redis extends Components
{
	
	const REDIS_POOL_NAME = 'redis_pool_name';
	public $host;
	public $auth;
	public $database;
	public $port;
	private $redis;
	
	/**
	 * @param $patten
	 *
	 * @return bool
	 */
	public function clear($patten)
	{
		if (empty($patten)) return false;
		$start = 0;
		$redis = $this->useRedis();
		while (($scan = $redis->scan($start , $patten)) != 0) {
			$start = 0;
			foreach ($scan as $key => $val) {
				$redis->delete($val);
			}
		}
		return true;
	}
	
	/**
	 * @return \Redis
	 */
	public function useRedis()
	{
		$connects = $this->getConnects();
		$redis = array_shift($connects);
//		Connect::update(self::REDIS_POOL_NAME, $connects);
		if (empty($redis) || !$redis->ping()) {
			$redis = $this->useRedis();
		}
		$this->redis = $redis;
		return $redis;
	}
	
	/**
	 * @return [\Redis]
	 */
	private function getConnects()
	{
		$lists = null;
		if (empty($lists)) {
			$lists = [];
			for ($i = 0 ; $i < 1 ; $i++) {
				$lists[] = $this->connect();
			}
			if (!empty($lists)) {
			}
		}
		return $lists;
	}
	
	/**
	 * @throws \Exception
	 */
	public function connect()
	{
		$link = new \Redis();
		if (!$link->connect($this->host , $this->port)) {
			throw new \Exception('Redis Connect Failed!');
		}
		$link->auth($this->auth);
		return $link;
	}
	
	/**
	 * @return bool
	 */
	public function clearAll()
	{
		return $this->useRedis()->flushDB();
	}
	
	/**
	 * @param $key
	 * @param $map
	 *
	 * @return $this|bool
	 */
	public function hMSet($key , $map)
	{
		if (is_object($map)) {
			if ($map instanceof ActiveRecord) {
				$map = $map->toArray();
			} else {
				$map = get_object_vars($map);
			}
		}
		if (empty($map) || !is_array($map)) {
			return $this;
		}
		foreach ($map as $key => $value) {
			$array = is_object($value) ? get_object_vars($value) : $value;
			if (is_array($array)) {
				$array = json_encode($array);
			}
			$map[$key] = $array;
		}
		$data = $this->useRedis()->hGetAll($key);
		if (!empty($data)) {
			$map = array_merge($data , $map);
		}
		return $this->useRedis()->hMset($key , $map);
	}
	
	/**
	 * @param $key
	 *
	 * @return array|null
	 */
	public function hGetAll($key)
	{
		if (!$this->useRedis()->exists($key)) {
			return null;
		}
		$data = $this->useRedis()->hGetAll($key);
		if (empty($data)) {
			return null;
		}
		foreach ($data as $key => $val) {
			$_val = !is_null(json_decode($val)) ? json_decode($val , true) : $val;
			$data[$key] = $_val;
		}
		return $data;
	}
}