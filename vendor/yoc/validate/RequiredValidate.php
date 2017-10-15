<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/19 0019
 * Time: 9:17
 */

namespace yoc\validate;


class RequiredValidate extends Validate
{
	
	/**
	 * @return bool
	 */
	public function notify()
	{
		if (is_string($this->fields)) {
			if (!isset($this->param[$this->fields])) {
				return $this->setMessage($this->fields . ' is required');
			}
			$param = $this->param[$this->fields];
			if (empty($param) && !is_numeric($param)) {
				return $this->setMessage($this->fields . ' is required');
			}
		} else {
			foreach ($this->fields as $value) {
				if (isset($this->param[$value]) && !empty($this->param[$value])) {
					continue;
				}
				return $this->setMessage($value . ' is required');
			}
		}
		return true;
	}
	
}