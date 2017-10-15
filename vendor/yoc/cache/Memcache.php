<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/23 0023
 * Time: 0:17
 */

namespace yoc\cache;


use yoc\base\Components;

class Memcache extends Components
{
	
	public $host;
	
	public $port;
	
	/** @var \Memcached $memcache */
	private $memcache;
	
	/**
	 * @return \Memcached
	 * @throws \Exception
	 * 创建一个和缓存链接
	 */
	private function connect()
	{
		$link = new \Memcached();
		$link->addServer($this->host, $this->port);
		if (!$link->isPersistent()) {
			throw new \Exception('Memcached Connect Failed!');
		}
		return $link;
	}
	
	
	/**
	 * @return \Memcached
	 * 获取一个缓存链接
	 */
	private function getMemcache()
	{
		if (!$this->memcache instanceof \Memcached) {
			$this->memcache = $this->connect();
		}
		return $this->memcache;
	}
	
	/**
	 * @param $name
	 * @return mixed
	 */
	public function get($name)
	{
		return $this->getMemcache()->get($name);
	}
	
	/**
	 * @param $name
	 * @param $value
	 * @return mixed
	 */
	public function set($name, $value)
	{
		if ($this->get($name)) {
			return $this->getMemcache()->replace($name, $value);
		} else {
			return $this->getMemcache()->add($name, $value);
		}
	}
	
	/**
	 * @param $key
	 * @return int
	 */
	public function decrement($key)
	{
		return $this->getMemcache()->decrement($key);
	}
	
}