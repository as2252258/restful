<?php
/**
 * Created by PhpStorm.
 * User: Cloud
 * Date: 2017/3/21 0021
 * Time: 19:28
 */

namespace yoc\cache;

use yoc\base\Objects;

class FileCache extends Objects
{
	/**
	 * @var  string
	 */
	public $sql;
	
	/**
	 * @var string
	 */
	public $type;
	
	/**
	 * @var string
	 */
	public $table;
	
	/**
	 * @var null|string
	 */
	private $cacheId = null;
	
	/**
	 * @var string
	 */
	private $cacheDir = FILE_CACHE_DIR;
	
	
	/**
	 * FileCache constructor.
	 */
	public function __construct()
	{
		if (!is_dir($this->cacheDir)) {
			mkdir($this->cacheDir);
		}
		parent::__construct(null);
	}
	
	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function setSql($value , $type = 'fetch')
	{
		$this->sql = $value;
		$this->type = $type;
		$tables = $this->getJoinCacheKey(false);
		if (count($tables) == 1) {
			$this->table = array_shift($tables);
		}
		return $this;
	}
	
	/**
	 * @return bool|mixed
	 * 判断是否有缓存
	 */
	public function haveCache()
	{
		if (empty($this->getCacheId())) return false;
		if ($this->isJoin()) {
			$data = $this->getJoinCacheFileContent();
			if (empty($data)) {
				return null;
			}
			return array_key_exists($this->getCacheId() , $data) ? $data[$this->getCacheId()] : null;
		}
		$cache = unserialize($this->getContent());
		if (!is_array($cache)) {
			return false;
		}
		if (!array_key_exists($this->getCacheId() , $cache)) {
			return false;
		}
		return $cache[$this->getCacheId()];
	}
	
	public function getCacheId()
	{
		return $this->cacheId = hash('sha224' , $this->sql . $this->type);
	}
	
	/**
	 * @return bool|mixed|null
	 * 获取缓存
	 */
	public function getCache()
	{
		return $this->isJoin() ? $this->hasJoinCache() : $this->haveCache();
	}
	
	/**
	 * @return array|bool|null|string
	 * 获取所有缓存
	 */
	public function getAll()
	{
		if ($this->isJoin()) {
			return $this->getJoinCacheFileContent();
		}
		$ac = $this->getContent();
		if (empty($ac)) return false;
		return unserialize($ac);
	}
	
	/**
	 * @return bool
	 * 清除缓存
	 */
	public function clearCache()
	{
		if ($this->isJoin()) {
			$this->clearJoinCache();
		}
		if (empty($this->getCacheId())) return true;
		if (!($content = $this->getContent())) {
			return false;
		}
		$cache = unserialize($content);
		if (!is_array($cache)) return true;
		if (!array_key_exists($this->getCacheId() , $cache)) return true;
		unset($cache[$this->getCacheId()]);
		$this->putFileContent($cache);
		return true;
	}
	
	public function clearAll()
	{
		foreach (glob($this->cacheDir . '/*') as $key => $val) {
			if (is_dir($val)) {
				foreach (glob($val . '/*') as $_key => $_val) {
					unlink($_val);
				}
			} else {
				unlink($val);
			}
		}
		return true;
	}
	
	/**
	 * @return bool
	 * 清除指定表的缓存
	 */
	public function clearTale(...$tableName)
	{
		if (!empty($tableName)) {
			foreach ($tableName as $val) {
				if (is_array($this->cacheDir . '/' . $val)) {
					foreach (glob($this->cacheDir . '/*' . $val . '/*.log') as $_val) {
						@unlink($_val);
					}
				} else {
					@unlink($this->cacheDir . '/' . $val . '.log');
				}
			}
		} else {
			$this->clearJoinCache();
			$file = $this->getFilePath(false);
			if (file_exists($file)) {
				$this->putFileContent('');
			}
		}
		return true;
	}
	
	private function unlink($path)
	{
		foreach (glob($path . '/*') as $_val) {
			if (is_dir($_val)) {
				$this->unlink($_val);
			} else {
				@unlink($_val);
			}
		}
	}
	
	/**
	 * @param $data
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function addCache($data)
	{
		if (empty($this->getCacheId())) {
			throw new \Exception('cache key not exists');
		}
		if ($this->isJoin()) {
			return $this->addJoinCache($data);
		}
		$content = $this->getContent(true);
		if (!is_array($content)) {
			$content = [$this->getCacheId() => $data];
		} else {
			$content[$this->getCacheId()] = $data;
		}
		return $this->putFileContent($content);
	}
	
	/**
	 * @param $cache
	 *
	 * @return int
	 * 更新缓存内容，方式为异步更新
	 */
	public function putFileContent($cache)
	{
		file_put_contents($this->getFilePath() , serialize($cache) , LOCK_EX);
		return true;
	}
	
	/**
	 * @param bool $isData
	 *
	 * @return array|mixed|string
	 * 获取缓存文件内容
	 */
	private function getContent($isData = false)
	{
		$content = file_get_contents($this->getFilePath());
		if ($isData === true) {
			if (!empty($content)) {
				return unserialize($content);
			} else {
				return [];
			}
		}
		if ($this->isJoin()) {
			return $this->getJoinCacheFileContent();
		}
		return $content;
	}
	
	/**
	 * @param bool $isCreate
	 *
	 * @return string
	 * 返回文件路径
	 */
	private function getFilePath($isCreate = true)
	{
		$filePath = $this->cacheDir . '/' . $this->table . '.log';
		if (!file_exists($filePath) && $isCreate) {
			touch($filePath);
		}
		return $filePath;
	}
	
	/**
	 * @return bool
	 * 清空所有缓存
	 */
	public function flush()
	{
		$this->clearJoinCache();
		$files = glob($this->cacheDir . '/*');
		foreach ($files as $key => $val) {
			@unlink($val);
		}
		return true;
	}
	
	/**
	 * @param $data
	 *
	 * @return bool|int
	 * @throws \Exception
	 */
	private function addJoinCache($data)
	{
		if (empty($this->sql)) {
			throw new \Exception('unknown cache key');
		}
		$cache = $this->getJoinCacheFileContent();
		$cache[$this->getCacheId()] = $data;
		return file_put_contents($this->getJoinCacheFile() , serialize($cache));
	}
	
	/**
	 * @return bool
	 * 清除连表数据缓存
	 */
	private function clearJoinCache()
	{
		$key = $this->getJoinCacheKey(false);
		if (!$key) {
			return true;
		}
		foreach ($key as $_key => $val) {
			$files = glob($this->getJoinCachePath() . '*' . $val . '*');
			if (!empty($files) && is_array($files)) {
				foreach ($files as $f_key => $_val) {
					file_put_contents($_val , serialize([]));
				}
			}
		}
		return true;
	}
	
	/**
	 * @return bool|string|array
	 */
	private function getJoinCacheKey($isJoin = true)
	{
		$preg = '(from|join|insert\s+into|update)\s+(\w+)+?\s';
		$tables = preg_match_all('/' . $preg . '/' , $this->sql , $ss);
		if (!$tables || empty($ss[2])) {
			return false;
		}
		return $isJoin ? implode('_' , $ss[2]) : $ss[2];
	}
	
	/**
	 * @return string
	 * 获取文件路径
	 */
	private function getJoinCachePath()
	{
		$dir = $this->cacheDir . '/joins/';
		if (!is_dir($dir)) mkdir($dir);
		return $dir;
	}
	
	/**
	 * @return string
	 */
	private function getJoinCacheFile()
	{
		$key = $this->getJoinCacheKey();
		if (!$key) {
			return '';
		}
		$dir = $this->getJoinCachePath();
		if (!is_file($dir . $key)) {
			touch($dir . $key);
		}
		return $dir . $key;
	}
	
	/**
	 * @return mixed|null
	 * 获取缓存文件内容
	 */
	private function getJoinCacheFileContent()
	{
		$content = file_get_contents($this->getJoinCacheFile());
		return !empty($content) ? unserialize($content) : null;
	}
	
	/**
	 * @return array|mixed|null
	 * 是否存在缓存
	 */
	private function hasJoinCache()
	{
		$content = $this->getJoinCacheFileContent();
		if (empty($content)) return null;
		if (!array_key_exists($this->getCacheId() , $content)) {
			return null;
		}
		return $content[$this->getCacheId()];
	}
	
	/**
	 * @return bool
	 * 是否连表
	 */
	private function isJoin()
	{
		$tables = $this->getJoinCacheKey(false);
		if (is_array($tables) && count($tables) > 1) {
			return true;
		}
		return false;
	}
}