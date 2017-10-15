<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/25 0025
 * Time: 11:19
 */

namespace yoc\core;


use yoc\base\Objects;

class ArrayAccess extends Objects
{
	/**
	 * @return array|mixed
	 * 数据合并
	 */
	public static function merger()
	{
		$params = func_get_args();
		if (is_callable($params , true)) {
			return call_user_func($params);
		} else if (!is_array($params) || empty($params)) {
			return [];
		} else {
			$first = array_shift($params);
			if (empty($params)) {
				return is_array($first) ? $first : [];
			}
			foreach ($params as $value) {
				foreach ($value as $childKey => $childValue) {
					$first[$childKey] = $childValue;
					if (!is_numeric($childKey)) {
						if (array_key_exists($childKey , $first)) {
							if (is_array($childValue)) {
								$first[$childKey] = self::merger($first[$childKey] , $childValue);
							} else {
								$first[$childKey] = $childValue;
							}
						} else {
							$first[$childKey] = $childValue;
							if (is_array($childValue)) {
								$first[$childKey] = self::merger($first[$childKey] , $childValue);
							}
						}
					} else if (is_array($childValue)) {
						$first[$childKey] = self::merger($first[$childKey] , $childValue);
					} else {
						$first[$childKey] = $childValue;
					}
				}
			}
			return $first;
		}
	}
	
	/**
	 * @param $array
	 *
	 * @return array
	 */
	public static function toArray($array)
	{
		if (empty($array) || is_numeric($array)) return [];
		if (is_string($array)) {
			if (is_null(json_decode($array , true))) {
				return [];
			}
			$array = json_decode($array , true);
		} else if (is_object($array)) {
			if (method_exists($array , 'toArray')) {
				$array = $array->toArray();
			} else {
				$array = get_object_vars($array);
			}
		}
		
		$_tmp = [];
		foreach ($array as $key => $val) {
			if (is_object($val) || is_array($val)) {
				$_tmp[$key] = self::toArray($val);
			} else {
				$_tmp[$key] = $val;
			}
		}
		
		return $_tmp;
	}
	
	/**
	 * @param $data
	 * @param $start
	 * @param $length
	 *
	 * @return array|mixed
	 * @throws \Exception
	 * 对数据进行分割操作
	 */
	public static function scan(array $data , int $start = 0 , int $length = 20)
	{
		if (is_callable($data , true)) {
			$data = call_user_func($data);
		}
		if (!is_array($data)) {
			throw new \Exception('Data Must Array');
		}
		if (count($data) > $length) return $data;
		if ($start >= $length) {
			$start = $length - 1;
		}
		return array_slice($data , $start , $length);
	}
	
	/**
	 * @param array $array
	 *
	 * @return object
	 * 将数组转为字符串
	 */
	public static function toObject(array $array)
	{
		return (object) $array;
	}
}