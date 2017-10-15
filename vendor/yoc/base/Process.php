<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/29 0029
 * Time: 11:46
 */

namespace yoc\base;


class Process extends AbServer
{
	
	private $_queue = [];
	
	private $_callback = [];
	
	private $_param = [];
	
	/** @var Process $_server */
	private static $_server;
	
	/**
	 * @return Process
	 */
	public static function load()
	{
		if (static::$_server === null) {
			static::$_server = new static();
		}
		return static::$_server;
	}
	
	/**
	 * @throws \Exception
	 */
	public function execute()
	{
		$queueCount = count($this->_queue);
		$callBackCount = count($this->_callback);
		if ($queueCount < 1 || $callBackCount != $queueCount) {
			throw new \Exception('回调执行函数不能为空');
		}
		for ($num = 0; $num < $queueCount; $num++) {
			$key = $this->_queue[$num];
			$_server = new \swoole_process($this->_callback[$key]);
			if (!empty($this->_param[$key])) {
				$_server->useQueue($num, \swoole_process::IPC_NOWAIT);
				$_server->push(json_encode($this->_param[$key]));
			}
			$_server->start();
		}
	}
	
	/**
	 * @return \swoole_server
	 * @throws \Exception
	 */
	public function getServer()
	{
		if (!$this->_server instanceof \swoole_server) {
			throw new \Exception('service is not start');
		}
		return $this->_server;
	}
	
	public function clear()
	{
//		unset($this->_param, $this->_queue, $this->_callback);
		$this->_param = [];
		$this->_queue = [];
		$this->_callback = [];
	}
	
	/**
	 * @param $key
	 * @param $value
	 */
	public function addQueue($key, $value, $callBack)
	{
		if (!in_array($key, $this->_queue)) {
			array_push($this->_queue, $key);
		}
		$this->addValue($key, $value);
		$this->bindCallBack($key, $callBack);
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * $value = []
	 * $value = ''
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function addValue($key, $value)
	{
		if (!isset($this->_param[$key])) {
			$this->_param[$key] = [];
		}
		if (empty($value)) return $this;
		array_push($this->_param[$key], $value);
		return $this;
	}
	
	/**
	 * @param $key
	 * @param $callBack
	 *
	 * $callBack = [
	 *     '\App\Demo::callBack',
	 *     ['\App\Demo', 'callBack'],
	 *     'callBack',
	 *     function (){
	 *     }
	 * ]
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function bindCallBack($key, $callBack)
	{
		if (!in_array($key, $this->_queue)) {
			throw new \Exception('The queue key ' . $key . ' not exists');
		}
		if (!\Yoc::isCallBack($callBack)) {
			throw new \Exception('这不是一个合格的回调函数');
		}
		$this->_callback[$key] = $callBack;
		return $this;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function delParamInQueue($key, $value)
	{
		if (!isset($this->_param[$key])) {
			return $this;
		}
		$search = array_search($value, $this->_param);
		if (is_numeric($search)) {
			unset($this->_param[$key][$search]);
		}
		return $this;
	}
	
	/**
	 * @param $key
	 *
	 * @return bool
	 */
	private function hasKey($key)
	{
		if (!in_array($key, $this->_queue)) {
			return false;
		}
		return true;
	}
	
	/**
	 * @param $key
	 *
	 * @return false|int|string
	 * @throws \Exception
	 */
	private function getIndex($key)
	{
		if (!$this->hasKey($key)) {
			throw new \Exception('con\'t find key in array');
		}
		return array_search($key, $this->_queue);
	}
	
	/**
	 * @param $key
	 *
	 */
	public function delKey($key)
	{
		if (in_array($key, $this->_queue)) {
			if ($this->hasKey($key)) {
				unset($this->_queue[$this->getIndex($key)]);
			}
			unset($this->_callback[$key], $this->_param[$key]);
		}
	}
	
}