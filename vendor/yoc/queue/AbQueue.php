<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/29 0029
 * Time: 11:11
 */

namespace yoc\queue;


use yoc\base\AbServer;

abstract class AbQueue extends AbServer implements Queue
{
	
	/** @var bool 是否异步 */
	public $isAsync = true;
	
	/** @var string 队列名称 */
	public $queueKey = 'message';
	
	public function check()
	{
	
	}
}