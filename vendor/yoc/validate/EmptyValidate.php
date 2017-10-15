<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/22 0022
 * Time: 23:59
 */

namespace yoc\validate;


class EmptyValidate extends Validate
{
	public function notify()
	{
		return true;
	}
}