<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/19 0019
 * Time: 9:40
 */

namespace yoc\validate;


use yoc\validate\Validate;

class DatetimeValidate extends Validate
{
	const TYPE_TIME = 'time';
	
	const TYPE_DATE_TIME = 'datetime';
	
	const TYPE_DATE = 'date';
	
	const TYPE_STRTOTIME = 'strtotime';
	
	public $type;
	
	/**
	 * @return bool
	 */
	public function notify()
	{
		if (is_array($this->fields)) {
			foreach ($this->fields as $key => $val) {
				if (!isset($this->param[$val])) {
					continue;
				}
				$check = $this->{$this->type}($this->param[$val]);
				if ($check) {
					continue;
				}
				return $this->setMessage('Format Error, This Is Not A Date Format');
			}
		} else {
			if (!isset($this->param[$this->fields])) {
				return true;
			}
			$data = $this->{$this->type}($this->param[$this->fields]);
			if (!$data) {
				return $this->setMessage('Format Error, This Is Not A Date Format');
			}
		}
		return $this->isFail = true;
	}
	
	public function strtotime($value)
	{
		return is_numeric($value) && mb_strlen($value) == 11;
	}
	
	public function time($value)
	{
		return preg_match('/\d{2}:\d{2}(:\d{2}){0,}/' , $value);
	}
	
	public function date($value)
	{
		return preg_match('/\d{4}-\d{2}-\d{2}/' , $value);
	}
	
	public function datetime($value)
	{
		if (preg_match('/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/' , $value)) {
			return true;
		} else if (preg_match('/\d{4}\d{2}\d{2}\d{2}\d{2}\d{2}/' , $value)) {
			return true;
		}
		return false;
	}
}