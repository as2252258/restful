<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/19 0019
 * Time: 9:38
 */

namespace yoc\validate;


class LengthValidate extends Validate
{
	
	const MAX_LENGTH = 'maxLength';
	
	const MIN_LENGTH = 'minLength';
	
	const TYPE_LENGTH = 'fixedLength';
	
	public $type;
	
	public $length;
	
	public $value;
	
	/**
	 * @return bool
	 * 检查
	 */
	public function notify()
	{
		
		if (empty($this->param)) return $this->isFail = true;
		if (is_array($this->fields)) {
			foreach ($this->fields as $key => $val) {
				if (empty($this->param[$val])) {
					continue;
				}
				$data = $this->{$this->type}($this->param[$val]);
				if ($data) {
					continue;
				}
				return $this->setMessage($val . ' Length At Least One');
			}
		} else {
			if (empty($this->param[$this->fields])) {
				return true;
			}
			$data = $this->{$this->type}($this->param[$this->fields]);
			if (!$data) {
				return $this->setMessage($this->fields . ' Length At Least Two');
			}
		}
		return $this->isFail = true;
	}
	
	/**
	 * @return bool
	 * 是否为最大长度
	 */
	protected function maxLength($value)
	{
		return mb_strlen($value) <= $this->ruleValue;
	}
	
	/**
	 * @return bool
	 * 是否为最小长度
	 */
	protected function minLength($value)
	{
		return mb_strlen($value) >= $this->ruleValue;
	}
	
	/**
	 * @return bool
	 * 检查是否为指定长度
	 */
	protected function fixedLength($value)
	{
		return mb_strlen($value) == $this->ruleValue;
	}
}