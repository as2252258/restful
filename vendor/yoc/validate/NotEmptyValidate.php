<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/19 0019
 * Time: 11:01
 */

namespace yoc\validate;


class NotEmptyValidate extends Validate
{
	/**
	 * @return bool
	 */
	public function notify()
	{
		if (is_array($this->fields)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($this->param[$val])) {
					continue;
				}
				return $this->setMessage($val . ' Must Not Empty');
			}
		}
		if (!isset($this->param[$this->fields]) || empty($this->param[$this->fields])) {
			if (!is_numeric($this->param[$this->fields])) {
				return $this->setMessage($this->fields . ' Must Not Empty');
			}
		}
		return true;
	}
}