<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/29 0029
 * Time: 11:07
 */

namespace yoc\queue;


interface Queue
{
	public function push($value);
	
	public function getNext($value);
	
	public function remove($index);
	
	public function getOffset($value);
	
	public function pop();
	
	public function count($queueName);
}