<?php

use yoc\db\builder\Builder;

/**
 * Class DB
 */
class DB extends Builder
{
	public static $executeSqlTrance = [];
	
	/**
	 * @param $sql
	 *
	 * @return mixed
	 */
	public static function TranceSql($sql)
	{
		static::$executeSqlTrance[] = $sql;
		return trim($sql);
	}
	
	public static function getLastError()
	{
		return Yoc::getError()->lastError('mysql');
	}
	
	/**
	 * @return mixed|string
	 * 获取执行的最后一条SQL
	 */
	public static function getLastSql()
	{
		$trance = static::getSqlTrance();
		return empty($trance) ? '' : $trance[count($trance) - 1];
	}
	
	public static function whereLike($value , array $fields)
	{
		$db = new static();
		return $db->withLike($value , $fields)->getWhere(false , true);
	}
	
	/**
	 * @return array
	 * 返回SQL记录
	 */
	public static function getSqlTrance()
	{
		$results = static::$executeSqlTrance;
		static::clearTrance();
		return $results;
	}
	
	/**
	 * 清空sql日志
	 */
	public static function clearTrance()
	{
		static::$executeSqlTrance = [];
	}
	
	/**
	 * @param $tableName
	 *
	 * @return static
	 */
	public static function table($tableName)
	{
		static $db = null;
		if ($db === null) $db = new DB();
		$db->tableName($tableName);
		return $db;
	}
	
	/**
	 * @return mixed|null
	 * 单条查询
	 */
	public function fetch()
	{
		$this->select('*');
		return Yoc::$app->db->createCommand($this->getSelectSql())->one();
	}
	
	/**
	 * @return bool
	 */
	public function drop()
	{
		$db = Yoc::$app->db->getCommand();
		return $db->drop($this->getTableName());
	}
	
	/**
	 * @return bool
	 */
	public function truncate()
	{
		$db = Yoc::$app->db->getCommand();
		return $db->truncate($this->getTableName());
	}
	
	/**
	 * @return mixed|null
	 * 删除指定条件数据，如不指定条件，将被全部清除
	 */
	public function delete()
	{
		$db = Yoc::$app->db->getCommand();
		return $db->setSql($this->getDeleteSql())->execute();
	}
	
	/**
	 * @return mixed|null
	 * 获取多条数据
	 */
	public function get()
	{
		$this->select('*');
		return Yoc::$app->db->createCommand($this->getSelectSql())->all();
	}
	
	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 * 获取总数
	 */
	public function count($key = 'id')
	{
		$this->select($key);
		return Yoc::$app->db->createCommand($this->getSelectSql())->rowCount();
	}
	
	/**
	 * @param       $sql
	 * @param array $attribute
	 *
	 * @return mixed|null
	 * 获取数据
	 */
	public function query($sql , $attribute = [])
	{
		if (empty($sql)) return [];
		if (!empty($attribute) && is_array($attribute)) {
			$this->bindParam($attribute);
		}
		return Yoc::$app->db->createCommand($sql , $this->bindParam)->all();
	}
	
	/**
	 * @param int   $num
	 * @param array $attribute
	 *
	 * @return mixed|null
	 * 从结果中随机获取单条或多条数据
	 */
	public function rand($num = 1 , $attribute = [])
	{
		if (!empty($attribute) && is_array($attribute)) {
			$this->bindParam($attribute);
		}
		$data = $this->get();
		if (!empty($data)) {
			$data = array_rand($data , $num);
		}
		return $data;
	}
	
	/**
	 * @param $field
	 *
	 * @return array|mixed|null
	 *
	 * 从结果集中获取指定字段
	 */
	public function queryRaw(string $field)
	{
		$this->select('*');
		$data = $this->get();
		if (!empty($data)) {
			$data = array_column($data , $field);
		}
		return $data;
	}
	
	/**
	 * @return mixed|null
	 */
	public function desc()
	{
		$sql = 'SHOW FULL FIELDS FROM ' . $this->tableName;
		return $this->query($sql);
	}
	
	/**
	 * @param $data
	 *
	 * @return string
	 * @throws Exception
	 * 添加数据
	 */
	public function insert($data)
	{
		$db = Yoc::$app->db->getCommand();
		return $db->setSql($this->getInsertSql())->insert($data);
	}
	
	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function insertAll($data)
	{
		$pdo = $this->getPdo()->getCommand();
		$pdo->getPdo()->beginTransaction();
		try {
			$pdo->batch($this->getTableName() , $data);
			$pdo->getPdo()->commit();
			return true;
		} catch (Exception $exception) {
			$pdo->getPdo()->rollBack();
			return false;
		}
	}
	
	/**
	 * @param       $data
	 *
	 * @return mixed|null
	 * 更新数据
	 */
	public function update(array $data)
	{
		$pdo = Yoc::$app->db->getCommand();
		return $pdo->setSql($this->getUpdateSql())->update($data);
	}
	
	/**
	 * @param       $sql
	 * @param array $attributes
	 * @param bool  $useCache
	 *
	 * @return mixed|null
	 */
	public static function quote($sql , $attributes = [] , $useCache = true)
	{
		if ($useCache) {
			return Yoc::$app->db->createCommand($sql , $attributes)->all();
		} else {
			return Yoc::$app->db->createCommand($sql , $attributes)->all();
		}
	}
	
	public static function find($sql , $attributes = [] , $useCache = true){
		return Yoc::$app->db->createCommand($sql , $attributes)->one();
	}
	
	/**
	 * @return DB|null
	 */
	public static function getQueryBuilder()
	{
		static $db = null;
		if ($db === null) $db = new DB();
		return $db;
	}
	
	/**
	 * @param          $tableName
	 * @param callable $callBack
	 *
	 * @return mixed|null
	 */
	public static function createTable($tableName , callable $callBack)
	{
//		$sql = call_user_func_array($callBack, [$tableName, \yoc\di\Ioc::createObject(\Yoc\Db\Table::class, [get_called_class()])]);
//		return static::getQueryBuilder()->course($sql);
	}
	
	/**
	 * @param string $oldTableName
	 * @param string $newTableName
	 *
	 * @return mixed|null
	 * 复制表结构
	 */
	public static function copyTable(string $oldTableName , string $newTableName)
	{
		return Yoc::$app->db->getCommand()->copy($oldTableName , $newTableName);
	}
	
	/**
	 * @return mixed|null
	 * 获取所有表
	 */
	public static function getTables()
	{
		$command = Yoc::$app->db->getCommand();
		return $command->setSql('show tables')->execute();
	}
	
}