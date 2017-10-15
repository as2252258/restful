<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/24 0024
 * Time: 19:15
 */

namespace yoc\observer;


use SplSubject;
use yoc\base\Objects;

class Observer extends Objects implements \SplObserver
{
	public function update(SplSubject $subject)
	{
		// TODO: Implement update() method.
	}
}