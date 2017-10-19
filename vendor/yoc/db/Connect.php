<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/22 0022
 * Time: 17:40
 */

namespace yoc\db;


use yoc\base\Components;
use yoc\db\builder\MysqlPool;

class Connect extends Components
{
	
	const MASTER_POOL_NAME = 'mysql_master_pool';
	const SLAVES_POOL_NAME = 'mysql_slaves_pool';
	/** @var string */
	public $cds;
	/** @var string */
	public $username;
	/** @var string */
	public $password;
	/** @var string */
	public $prefix = 'xl_';
	/**
	 * @var array
	 * [
	 *      ['mysql:dbname=database;host=127.0.0.1','username','password'],
	 *      ['mysql:dbname=database;host=127.0.0.1','username','password'],
	 *      ['mysql:dbname=database;host=127.0.0.1','username','password'],
	 * ]
	 */
	public $slaveConfigs = [];
	/** @var int */
	public $max = 20;
	/** @var int */
	public $initNum = 20;
	/** @var \PDO $pdo */
	private $pdo;
	/** @var \PDO $slavePdo */
	private $slavePdo;
	private $transactionLevel = 0;
	
	/**
	 * @param string $pool
	 *
	 * @return $this
	 */
	public function recovery($pool = false , $null = null)
	{
		if ($pool) {
			$pdo = $this->slavePdo;
			$this->slavePdo = null;
		} else {
			$pdo = $this->pdo;
			$this->pdo = null;
		}
		if ($this->checkLink($pdo) && !$pdo->inTransaction()) {
			MysqlPool::getPool($pool)->recovery($pdo);
		}
		return $this;
	}
	
	/**
	 * @param       $sql
	 * @param array $params
	 *
	 * @return Command
	 */
	public function createCommand($sql , $params = [])
	{
		$command = new Command($this->getSlave() , $sql);
		if (!empty($params)) {
			$command->bindParams($params);
		}
		return $command;
	}
	
	/**
	 * @return \PDO
	 */
	public function getSlave()
	{
		if (empty($this->slaveConfigs)) {
			return $this->getMaster();
		}
		if (!$this->checkLink($this->slavePdo)) {
			$this->slavePdo = $this->link();
		}
		return $this->slavePdo;
	}
	
	/**
	 * @return \PDO
	 */
	public function getMaster()
	{
		if (!$this->checkLink($this->pdo)) {
			$this->pdo = $this->link(true);
		}
		return $this->pdo;
	}
	
	/**
	 * @param \PDO $link
	 *
	 * @return bool
	 */
	private function checkLink($link)
	{
		if (!$link instanceof \PDO) return false;
		try {
			$link->getAttribute(\PDO::ATTR_SERVER_INFO);
		} catch (\PDOException $e) {
			\Yoc::getError()->addError('mysql' , $e->getMessage());
			return false;
		}
		return true;
	}
	
	/**
	 * @param bool $isMaster
	 *
	 * @return \PDO
	 * 获取合法连接
	 */
	private function link($isMaster = false)
	{
		$connect = $this->getConnect($isMaster);
		if (!$this->checkLink($connect)) {
			$this->link($isMaster);
		}
		return $connect;
	}
	
	/**
	 * @param bool $isSlave
	 *
	 * @return \PDO
	 */
	private function getConnect($isMaster = false)
	{
		$connects = MysqlPool::getPool($isMaster)->getConnect();
		if (empty($connects)) {
			if (!$isMaster && !empty($this->slaveConfigs)) {
				$connects = $this->loadSlavesLinks();
			} else {
				$connects = $this->loadMasterLinks();
			}
		}
		if (is_array($connects)) {
			return array_shift($connects);
		}
		return $connects;
	}
	
	/**
	 * @return array
	 */
	private function loadSlavesLinks()
	{
		if (empty($this->slaveConfigs) || !is_array($this->slaveConfigs)) {
			return [];
		}
		$zPools = MysqlPool::getPool(false);
		$rand = $this->slaveConfigs[array_rand($this->slaveConfigs)];
		foreach ($this->slaveConfigs as $key => $val) {
			if (!isset($val[0])) continue;
			$username = $rand[1] ?? $this->username;
			$password = $rand[2] ?? $this->password;
			for ($i = 0 ; $i < $this->initNum ; $i++) {
				$zPools->push($this->connectPdo($rand[0] , $username , $password));
			}
		}
		echo '从库连接池初始化成功，初始化数量' . $zPools->getConnectType()->count();
		echo PHP_EOL;
		return $zPools->getConnect();
	}
	
	/**
	 * @param string $cds
	 * @param string $username
	 * @param string $password
	 *
	 * @return \PDO
	 */
	public function connectPdo($cds = '' , $username = '' , $password = '')
	{
		$cds = !empty($cds) ? $cds : $this->cds;
		$username = !empty($username) ? $username : $this->username;
		$password = !empty($password) ? $password : $this->password;
		
		$link = new \PDO($cds , $username , $password ,
			[
				\PDO::ATTR_PERSISTENT => true ,
				\PDO::ATTR_TIMEOUT    => 3600 * 24 * 30 ,
			]
		);
		$link->setAttribute(\PDO::ATTR_CASE , \PDO::CASE_NATURAL);
		$link->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY , true);    //使用缓冲查询
		$link->setAttribute(\PDO::ATTR_EMULATE_PREPARES , true);
		$link->setAttribute(\PDO::ATTR_ERRMODE , \PDO::ERRMODE_EXCEPTION);
		return $link;
	}
	
	/**
	 * @return array|\PDO
	 */
	private function loadMasterLinks()
	{
		$zPools = MysqlPool::getPool(true);
		for ($i = 0 ; $i < $this->initNum ; $i++) {
			$zPools->push($this->connectPdo());
		}
		echo '主库连接池初始化成功，初始化数量' . $zPools->getConnectType()->count();
		echo PHP_EOL;
		return $zPools->getConnect();
	}
	
	/**
	 * 初始化连接池
	 */
	public function beginConnectPool()
	{
		$count = MysqlPool::getPool(true)->getQueue();
		if (!$count || $count->count() < 1) {
			$this->loadMasterLinks();
		}
		if (!empty($this->slaveConfigs)) {
			$count = MysqlPool::getPool(false)->getQueue();
			if (!$count || $count->count() < 1) {
				$this->loadSlavesLinks();
			}
		}
	}
	
	/**
	 * @return bool
	 * 判断是否有连接
	 */
	public function hasConnect()
	{
		$master = $this->getConnect(true);
		$_slave = $this->getConnect(false);
		if (!empty($master) || !empty($_slave)) {
			return true;
		}
		return false;
	}
	
	/**
	 * @return Command
	 */
	public function getCommand()
	{
		return new Command($this->getMaster());
	}
	
	/**
	 * @return $this
	 */
	public function beginTransaction()
	{
		++$this->transactionLevel;
		if ($this->transactionLevel == 1) {
			\Yoc::$app->redis->multi();
			$this->getMaster()->beginTransaction();
		}
		return $this;
	}
	
	/**
	 * 事务回滚
	 */
	public function rollback()
	{
		if ($this->transactionLevel == 1) {
			\Yoc::$app->redis->discard();
			$this->getMaster()->rollBack();
		} else {
			--$this->transactionLevel;
		}
	}
	
	/**
	 * 事务提交
	 */
	public function commit()
	{
		if ($this->transactionLevel == 1) {
			\Yoc::$app->redis->exec();
			$this->getMaster()->commit();
		}
		--$this->transactionLevel;
	}
	
	/**
	 *
	 */
	public function __destruct()
	{
		echo 'im destruct';
	}
}