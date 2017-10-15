<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/22 0022
 * Time: 11:03
 */

namespace yoc\db;


use function PHPSTORM_META\type;
use yoc\base\Objects;
use yoc\di\Ioc;

class Command extends Objects
{
	/**
	 * @var \PDO
	 */
	private $pdo;
	
	/**
	 * @var string
	 */
	private $sql;
	
	/**
	 * @var \PDOStatement
	 */
	private $PDOStatement;
	
	public function __construct(\PDO $pdo, string $sql = '', array $config = [])
	{
		$this->pdo = $pdo;
		$this->setSql($sql);
		parent::__construct($config);
	}
	
	/**
	 * @return \PDO
	 */
	public function getPdo(){
		return $this->pdo;
	}
	
	/**
	 * @param $sql
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function setSql($sql)
	{
		$this->sql = str_replace('{prefix}', \Yoc::$app->db->prefix, $sql);
//		echo $this->sql . PHP_EOL;
		$this->PDOStatement = $this->pdo->prepare($this->sql);
		if (!empty($this->PDOStatement->errorCode())) {
			throw new \Exception($this->PDOStatement->errorInfo()[2], $this->PDOStatement->errorCode());
		}
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 * 批量绑定
	 */
	public function bindParams(array $data)
	{
		foreach ($data as $key => $val) {
			$this->bindParam($key, $val);
		}
		return $this;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 * 绑定参数
	 */
	public function bindParam($key, $value)
	{
		if (is_null($value)) {
			return $this;
		}
		if (strpos($key, ':') !== 0) {
			$key = ':' . $key;
		}
		if (strpos($this->sql, $key) === false) return $this;
		$this->PDOStatement->bindParam($key, $value);
		return $this;
	}
	
	/**
	 * @param $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function setParam($value)
	{
		foreach ($value as $key => $val) {
			if (preg_match('/^:/', $key)) {
				throw new \Exception('Pdo Param Format Error');
			}
			$this->PDOStatement->bindColumn($key, $val);
		}
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function one()
	{
		$this->PDOStatement->execute();
		return $this->PDOStatement->fetch(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * @return array
	 */
	public function all()
	{
		$this->PDOStatement->execute();
		return $this->PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * @return array
	 */
	public function column()
	{
		$this->PDOStatement->execute();
		return $this->PDOStatement->fetchColumn(0);
	}
	
	/**
	 * @return int
	 */
	public function rowCount()
	{
		$this->PDOStatement->execute();
		return $this->PDOStatement->rowCount();
	}
	
	/**
	 * @return bool|string
	 */
	public function insert($array = [])
	{
		if (!empty($array) && is_array($array)) {
			$this->bindParams($array);
		}
		return $this->execute() ? $this->pdo->lastInsertId() : false;
	}
	
	/**
	 * @return bool
	 */
	public function update($array = [])
	{
		if (!empty($array) && is_array($array)) {
			$this->bindParams($array);
		}
		return $this->execute();
	}
	
	public function execute()
	{
		$results = $this->PDOStatement->execute();
		if (!empty($this->pdo->errorCode())) {
			\Yoc::getError()->addError('mysql', $this->pdo->errorInfo()[2]);
		}
		return $results;
	}
	
	/**
	 * @return \PDOStatement
	 */
	public function getPDOStatement()
	{
		return $this->PDOStatement;
	}
	
	/**
	 * @param $tableName
	 *
	 * @return bool
	 * 清空表数据
	 */
	public function truncate($tableName)
	{
		$this->setSql('TRUNCATE ' . $tableName);
		return $this->PDOStatement->execute();
	}
	
	/**
	 * @param $tableName
	 *
	 * @return bool
	 * 删除表
	 */
	public function drop($tableName)
	{
		$this->setSql('TRUNCATE ' . $tableName);
		return $this->PDOStatement->execute();
	}
	
	/**
	 * 析构函数，清空当前类
	 */
	public function __destruct()
	{
//		$db = \Yoc::$app->db->cds;
//		$info = $this->pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
//		preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $info, $preg);
//		preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $db, $master);
//		if (!empty($preg) && is_array($preg)) {
//			$preg = array_shift($preg);
//		}
//		if (!empty($master) && is_array($master)) {
//			$master = array_shift($master);
//		}
//		if ($preg != $master) {
//			$this->recovery('db', Connect::SLAVES_POOL_NAME);
//		}
//		if (!$this->pdo->inTransaction()) {
//			if ($preg == $master) {
//				$this->recovery('db', Connect::MASTER_POOL_NAME);
//			}
//			unset($this->pdo, $this->PDOStatement);
//		}
	}
	
	/**
	 * @param $newTableName
	 * @param $oldTableName
	 *
	 * @return bool
	 * 拷贝表
	 */
	public function copy($newTableName,$oldTableName){
		$this->setSql('CREATE TABLE ' . $newTableName . ' LIKE ' . $oldTableName);
		return $this->execute();
	}
	
	/**
	 * @param $table
	 * @param $data
	 *
	 * @return array|bool
	 */
	public function batch($table, $data)
	{
		$sql = '';
		$values = [];
		foreach ($data as $key => $val) {
			if (empty($sql)) {
				$sql = "INSERT INTO {$table} (" . implode(array_keys($val)) . ")";
			}
			$values[] = '(\'' . implode('\',\'', $val) . '\')';
		}
		if (empty($values)) {
			return [];
		}
		$sql .= ' values ' . implode(',', $values);
		return $this->pdo->prepare($sql)->execute();
	}
}