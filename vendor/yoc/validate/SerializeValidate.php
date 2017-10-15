<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/19 0019
 * Time: 13:03
 */

namespace yoc\validate;


class SerializeValidate extends Validate
{
	public function notify()
	{
		if (is_null(unserialize($this->value))) {
			return $this->setMessage('The Not\'s A Serialize');
		}
		return true;
	}
}