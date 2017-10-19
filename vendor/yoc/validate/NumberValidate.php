<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/19 0019
 * Time: 13:00
 */

namespace yoc\validate;


class NumberValidate extends Validate
{
	public function notify()
	{
		if (empty($this->param)) return $this->isFail = true;
		if (is_array($this->fields)) {
			foreach ($this->fields as $key => $val) {
				if ($this->check($val)) {
					continue;
				}
				return $this->setMessage($val . ' Not A Number');
			}
		} else {
			if (!$this->check($this->fields)) {
				return $this->setMessage($this->fields . ' Not A Number');
			}
		}
		return true;
	}
	
	private function check($fields)
	{
		if (!isset($this->param[$fields])) {
			return $this->isFail = true;
		}
		if (intval($this->param[$fields]) != $this->param[$fields]) {
			return $this->isFail = false;
		}
		return $this->isFail = true;
	}
}