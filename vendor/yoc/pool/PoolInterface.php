<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/8/30 0030
 * Time: 10:15
 */

namespace yoc\Pool;


interface PoolInterface
{
	
	public static function remove($key);
	
	public static function get($key);
	
	public static function update($key, $object);
}