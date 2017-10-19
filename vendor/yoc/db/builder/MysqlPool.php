<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/16 0016
 * Time: 15:04
 */

namespace yoc\db\builder;


class MysqlPool
{
	
	/** @var \yoc\db\builder\MysqlPool */
	private static $pool;
	
	private $slaves;
	
	private $master;
	
	private $isMaster = false;
	
	public function __construct($isMaster)
	{
		$this->slaves = new \SplQueue();
		$this->master = new \SplQueue();
		$this->isMaster = $isMaster;
	}
	
	/**
	 * @param $isMaster
	 *
	 * @return \yoc\db\builder\MysqlPool
	 * 获取实例化
	 */
	public static function getPool($isMaster = false)
	{
		if (!static::$pool instanceof MysqlPool) {
			static::$pool = new MysqlPool($isMaster);
		}
		return static::$pool;
	}
	
	/**
	 * @param \PDO $PDO
	 */
	public function push(\PDO $PDO)
	{
		$this->getConnectType()->push($PDO);
	}
	
	/**
	 * @return \SplQueue
	 * 获取当前连接池
	 */
	public function getConnectType()
	{
		return $this->isMaster ? $this->master : $this->slaves;
	}
	
	/**
	 * @param $pdo
	 *
	 * 回收PDO连接
	 */
	public function recovery($pdo)
	{
		$pool = $this->getConnectType();
		if (!$pdo instanceof \PDO) {
			return;
		}
		$pool->push($pdo);
	}
	
	/**
	 * @return mixed|null
	 * 获取连接
	 */
	public function getConnect()
	{
		$connect = $this->getConnectType();
		if ($connect->count() > 0) {
			return $connect->shift();
		}
		return null;
	}
	
	/**
	 * @return mixed|null
	 * 获取主库连接
	 */
	public function getMasterConnect()
	{
		$this->isMaster(true);
		if ($this->hasConnect()) {
			return null;
		}
		return $this->getQueue()->shift();
	}
	
	/**
	 * @param bool $isMaster
	 *
	 * @return $this
	 * 设置获取连接类型
	 */
	public function isMaster($isMaster = false)
	{
		$this->isMaster = $isMaster;
		return $this;
	}
	
	/**
	 * @return bool
	 * 判断是否有连接
	 */
	public function hasConnect()
	{
		if (empty($this->getQueue())) {
			return false;
		}
		return $this->getQueue()->count() > 0;
	}
	
	/**
	 * @return null|\SplQueue
	 * 获取连接队列
	 */
	public function getQueue()
	{
		$list = $this->isMaster ? $this->master : $this->slaves;
		if ($list->isEmpty() || $list->count() < 1) {
			return null;
		}
		return $list;
	}
	
	/**
	 * @return mixed|null
	 * 获取连接
	 */
	public function getSlaveConnect()
	{
		$this->isMaster(false);
		if ($this->hasConnect()) {
			return null;
		}
		return $this->getQueue()->shift();
	}
	
	/**
	 * 初始化连接池
	 */
	public function unlink(){
		$this->slaves = new \SplQueue();
		$this->master = new \SplQueue();
	}
}