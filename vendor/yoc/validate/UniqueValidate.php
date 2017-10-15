<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/10/8 0008
 * Time: 12:56
 */

namespace yoc\validate;


use yoc\di\Ioc;

class UniqueValidate extends Validate
{
	
	public function notify()
	{
		if (is_array($this->fields)) {
			foreach ($this->fields as $key => $val) {
				$this->select($val , $this->param[$val]);
				if ($this->isFail) {
					continue;
				}
				return $this->setMessage($this->desc('unique' , $val));
			}
		} else {
			$this->select($this->fields , $this->param[$this->fields]);
			if ($this->isFail) {
				return true;
			}
			return $this->setMessage($this->desc('unique' , $this->fields));
		}
		return true;
	}
	
	
	private function select($key , $value)
	{
		$model = $this->modelClass;
		if (is_string($model)) {
			$model = Ioc::createObject($this->modelClass);
		}
		/** @var \yoc\db\ActiveRecord $model */
		if ($model->getPrimaryValue()) {
			$select = $model::where([$key => $value , $model->getPrimary() . ' <> ' . $model->getPrimaryValue()])->one();
		} else {
			$select = $model::where([$key => $value])->one();
		}
		if (!empty($select)) {
			$this->isFail = false;
		} else {
			$this->isFail = true;
		}
	}
	
}