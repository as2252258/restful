<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/22 0022
 * Time: 17:40
 */

namespace yoc\db;


use yoc\base\Components;
use yoc\pool\Connect as ConnectPool;

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
	public $initNum = 1;
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
	public function recovery($pool = self::SLAVES_POOL_NAME , $null = null)
	{
		$pdo = $pool == self::SLAVES_POOL_NAME ? $this->slavePdo : $this->pdo;
		if ($pool == self::SLAVES_POOL_NAME) {
			ConnectPool::push(self::SLAVES_POOL_NAME , $pdo);
			$this->slavePdo = null;
		} else {
			ConnectPool::push(self::MASTER_POOL_NAME , $pdo);
			$this->pdo = null;
		}
		return $this;
	}
	
	/**
	 * @param       $sql
	 * @param array $params
	 *
	 * @return Command
	 */
	public function command($sql , $params = [])
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
			$this->slavePdo = $this->getConnect(true);
		}
		return $this->slavePdo;
	}
	
	/**
	 * @return \PDO
	 */
	public function getMaster()
	{
		if (!$this->checkLink($this->pdo)) {
			$this->pdo = $this->getConnect();
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
		} catch (\Exception $e) {
			return false;
		}
		return true;
	}
	
	/**
	 * @param bool $isSlave
	 *
	 * @return \PDO
	 */
	private function getConnect($isSlave = false)
	{
		if ($isSlave) {
//			$connects = ConnectPool::getItem(self::SLAVES_POOL_NAME);
//			if (empty($connects)) {
			$connects = $this->loadSlavesLinks();
//			}
		} else {
//			$connects = ConnectPool::getItem(self::MASTER_POOL_NAME);
//			if (empty($connects)) {
			$connects = $this->loadMasterLinks();
//			}
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
		$zPools = [];
		if (empty($this->slaveConfigs) || !is_array($this->slaveConfigs)) {
			return $zPools;
		}
		$rand = $this->slaveConfigs[array_rand($this->slaveConfigs)];
		foreach ($this->slaveConfigs as $key => $val) {
			if (!isset($val[0])) continue;
			$username = $rand[1] ?? $this->username;
			$password = $rand[2] ?? $this->password;
			$zPools[] = $this->connectPdo($rand[0] , $username , $password);
		}
//		if (!empty($zPools)) {
//			ConnectPool::addItem(self::SLAVES_POOL_NAME , $zPools);
//		}
		echo '从库连接池初始化成功，初始化数量' . count($zPools);
		echo PHP_EOL;
		return $zPools;
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
		$zPools = [];
		for ($i = 0 ; $i < $this->initNum ; $i++) {
			$zPools[] = $this->connectPdo();
		}
//		$zPools = $this->connectPdo();
//		if (!empty($zPools)) {
//			ConnectPool::addItem(self::MASTER_POOL_NAME , $zPools);
//		}
		echo '主库连接池初始化成功，初始化数量' . count($zPools);
		echo PHP_EOL;
		return $zPools;
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