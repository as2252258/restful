<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/20 0020
 * Time: 10:57
 */

namespace yoc\http;


use Exception;
use yoc\base\Objects;

/**
 * Class Input
 *
 * @package yoc\http
 * @property $page
 * @property $size
 */
class Input extends Objects
{
	private $data = [];
	
	private $cookie = [];
	
	private $header = [];
	
	private $get = [];
	
	/**
	 * @param $key
	 * @param $value
	 */
	public function append($key , $value)
	{
		$this->data[$key] = $value;
	}
	
	/**
	 * @return bool
	 */
	public function validate()
	{
		$data = func_get_args();
		foreach ($data as $key => $val) {
			if (!isset($this->data[$val])) {
				\Yoc::getError()->addError('request' , ucfirst($val) . ' Not Exists In Request');
				return false;
			}
		}
		return true;
	}
	
	/**
	 * @param string $name
	 *
	 * @return string|array
	 */
	public function getHeader($name = '')
	{
		if (empty($name)) return $this->header;
		if (empty($this->header)) return null;
		if (!array_key_exists($name , $this->header)) {
			return null;
		}
		return $this->header[$name];
	}
	
	/**
	 * @param string $name
	 *
	 * @return string|array
	 */
	public function getCookie($name = '')
	{
		if (empty($name)) return $this->cookie;
		if (!array_key_exists($name , $this->cookie)) {
			return null;
		}
		return $this->cookie[$name];
	}
	
	/**
	 * @return mixed
	 */
	public function getIp()
	{
		if (!empty($this->header['x-forwarded-for'])) return $this->header['x-forwarded-for'];
		if (!empty($this->header['request-ip'])) return $this->header['request-ip'];
		return null;
	}
	
	/**
	 * @param      $key
	 * @param bool $isNeed
	 * @param null $default
	 *
	 * @return array|mixed|null
	 * @throws Exception
	 */
	public function get($key , $isNeed = false , $default = null)
	{
		$data = $this->getParamForRequestData($key);
		if ($data === null && $isNeed) {
			throw new Exception('request queryParam ' . $key . ' is empty or not exists');
		}
		return $data ?? $default;
	}
	
	/**
	 * @param string $key
	 *
	 * @return array|mixed|null
	 */
	private function getParamForRequestData($key = '')
	{
		$data = $this->all();
		if (empty($key) || empty($data)) {
			return null;
		}
		return isset($data[$key]) ? trim($data[$key]) : null;
	}
	
	/**
	 * @return array
	 */
	public function all()
	{
		return array_merge($this->data , $this->get);
	}
	
	/**
	 * @param $key
	 * @param $isNeed
	 *
	 * @return mixed|null|string
	 * @throws Exception
	 * 判断是否为合格的密码格式
	 */
	public function password($key , $isNeed)
	{
		$data = $this->getParamForRequestData($key);
		if ($isNeed && $data === null) {
			throw new Exception("key {$key} can not empty");
		}
		return $this->match('^[a-zA-Z0-9]{32}$' , $key , $data);
	}
	
	/**
	 * @param      $match
	 * @param      $name
	 * @param null $data
	 *
	 * @return mixed|null|string
	 * @throws \Exception
	 * 正则判断数据正确性
	 */
	public function match($match , $name , $data = null)
	{
		$string = $data;
		if (empty($string)) {
			$string = $this->string($name , true);
		}
		if (!preg_match('/' . $match . '/' , $string)) {
			throw new Exception('Param Error, Match ' . ucfirst($name) . ' Fail!');
		}
		return $string;
	}
	
	/**
	 * @param           $string
	 * @param bool      $isNeed
	 * @param array|int $length
	 * @param null      $default
	 *
	 * @return string|mixed|null
	 * @throws \Exception
	 * 获取字符串
	 */
	public function string($string , $isNeed = false , $length = [] , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null) {
			throw new Exception("key {$string} can not empty");
		}
		if (!$this->checkLength($data , $length)) return $default;
		return htmlspecialchars(trim($data));
	}
	
	/**
	 * @param       $data
	 * @param array $length
	 *
	 * @return bool
	 * 检查长度是否合格
	 */
	private function checkLength($data , $length = [])
	{
		$_length = mb_strlen((string) trim($data) , 'utf8');
		if (empty($length)) return $data;
		if (!is_array($length)) {
			if ($_length != $length) {
				return false;
			}
		} else {
			if ($_length < current($length) || $_length > end($length)) {
				return false;
			}
		}
		return $data;
	}
	
	/**
	 * @param      $string
	 * @param bool $isNeed
	 *
	 * @return array|mixed
	 * @throws Exception
	 */
	public function array($string , $isNeed = false)
	{
		if (array_key_exists($string , $this->data)) {
			$arr = is_array($this->data[$string]);
			if (!$arr && $isNeed) {
				throw new Exception("key {$string} is not a array");
			}
			return $arr ? $this->data[$string] : [];
		}
		if ($isNeed) {
			throw new Exception("key {$string} is not a array");
		}
		return [];
	}
	
	/**
	 * @param       $string
	 * @param bool  $isNeed
	 * @param null  $default
	 *
	 * @return null|string
	 * @throws Exception
	 */
	public function datetime($string , $isNeed = false , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null) {
			throw new Exception("key {$string} can not empty");
		}
		if (is_numeric($data) && strlen($data) == 10) {
			return date('Y-m-d H:i:s' , $data);
		}
		if (preg_match('/\d{4}\-\d{2}\-\d{2}(\s+\d{2}:\d{2}:\d{2}){0,}/' , $data)) {
			return $data;
		}
		return $default === null ? null : $default;
	}
	
	/**
	 * @param      $string
	 * @param bool $isNeed
	 * @param null $default
	 *
	 * @return array|false|mixed|null|string
	 * @throws Exception
	 */
	public function date($string , $isNeed = false , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null) {
			throw new Exception("key {$string} can not empty");
		}
		if (is_numeric($data) && strlen($data) == 10) {
			return date('Y-m-d' , $data);
		}
		if (preg_match('/\d{4}\-\d{2}\-\d{2}/' , $data)) {
			return $data;
		}
		return $default === null ? null : $default;
	}
	
	/**
	 * @param       $string
	 * @param bool  $isNeed
	 * @param null  $default
	 *
	 * @return float|mixed|null
	 * @throws Exception
	 */
	public function float($string , $isNeed = false , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null) {
			throw new Exception("key {$string} can not empty");
		}
		if (!is_float($data) || $data != floatval($data)) {
			return $default === null ? null : $default;
		}
		return $data;
	}
	
	/**
	 * @param       $string
	 * @param bool  $isNeed
	 * @param null  $default
	 *
	 * @return integer|mixed|null
	 * @throws Exception
	 */
	public function phone($string , $isNeed = false , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null) {
			throw new Exception("key {$string} can not empty");
		}
		if (!preg_match('/^1[35789]\d{9}/' , $data)) {
			return $default === null ? null : $default;
		}
		return $data;
	}
	
	/**
	 * @param       $string
	 * @param bool  $isNeed
	 * @param array $length
	 * @param null  $default
	 *
	 * @return string|mixed|null
	 * @throws Exception
	 */
	public function email($string , $isNeed = false , $length = [] , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null) {
			throw new Exception("key {$string} can not empty");
		}
		if (!preg_match('/[a-zA-Z\d\.]+@[a-zA-Z\d]+(\.[a-zA-Z\d]+)+/' , $data)) {
			return $default === null ? null : $default;
		}
		if (!empty($length)) {
			$check = $this->checkLength($data , $length);
			if (!$check) {
				throw new Exception('邮箱号码长度超出');
			}
		}
		return $data;
	}
	
	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->integer('size' , false , null , 20);
	}
	
	/**
	 * @param       $string
	 * @param bool  $isNeed
	 * @param array $length
	 * @param null  $default
	 *
	 * @return int|mixed|null
	 * @throws \Exception
	 * 获取整数
	 */
	public function integer($string , $isNeed = false , $length = [] , $default = null)
	{
		$data = $this->getParamForRequestData($string);
		if ($isNeed && $data === null && !is_numeric($data)) {
			throw new Exception("key {$string} can not empty");
		}
		if (!$this->checkLength($data , $length)) return $default;
		if (is_numeric($data) && intval($data) == $data) return $data;
		return $default;
	}
	
	/**
	 * @return int
	 */
	public function getPage()
	{
		return $this->integer('page' , false , null , 1);
	}
	
	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->integer('count' , false , null , -1);
	}
	
	/**
	 * @param      $type
	 * @param      $oldKey
	 * @param null $checkParam
	 *
	 * @return bool
	 */
	public function checkRequestParam($type , $oldKey , $checkParam = null)
	{
		$default = ['integer' , 'string' , 'phone' , 'float' , 'email'];
		if (!empty($type) && !in_array($type , $default)) {
			return false;
		}
		return strcmp($this->$type($oldKey) , $checkParam);
	}
	
	/**
	 * @param $name
	 *
	 * @return null
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);
		if (method_exists($this , $method)) {
			return $this->$method();
		}
		if (empty($this->data)) return null;
		if (array_key_exists($name , $this->data)) {
			return $this->data[$name];
		}
		return null;
	}
	
	/**
	 * @param $name
	 * @param $value
	 *
	 * @throws \Exception
	 */
	public function __set($name , $value)
	{
		if (property_exists($this , $name)) {
			$this->$name = $value;
		} else if (method_exists($this , 'set' . ucfirst($name))) {
			$this->{'set' . ucfirst($name)}($value);
		} else {
			throw new \Exception('Set Unknown Property ' . $name);
		}
	}
}