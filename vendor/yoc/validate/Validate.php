<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/22 0022
 * Time: 9:44
 */

namespace yoc\validate;


use yoc\base\Objects;
use yoc\di\Ioc;

abstract class Validate extends Objects
{
	
	private $message;
	
	private static $validate = [
		'required'  => 'yoc\validate\RequiredValidate' ,
		'not empty' => 'yoc\validate\NotEmptyValidate' ,
		'integer'   => 'yoc\validate\NumberValidate' ,
		'string'    => 'yoc\validate\StringValidate' ,
		'boolean'   => 'yoc\validate\RequiredValidate' ,
		'json'      => 'yoc\validate\JsonValidate' ,
		'serialize' => 'yoc\validate\SerializeValidate' ,
		'unique'    => 'yoc\validate\UniqueValidate' ,
		'time'      => [
			'class' => 'yoc\validate\DatetimeAbstractValidate' ,
			'type'  => DatetimeValidate::TYPE_TIME
		] ,
		'date'      => [
			'class' => 'yoc\validate\DatetimeValidate' ,
			'type'  => DatetimeValidate::TYPE_DATE
		] ,
		'datetime'  => [
			'class' => 'yoc\validate\DatetimeValidate' ,
			'type'  => DatetimeValidate::TYPE_DATE_TIME
		] ,
		'strtotime' => [
			'class' => 'yoc\validate\DatetimeValidate' ,
			'type'  => DatetimeValidate::TYPE_STRTOTIME
		] ,
		'timestamp' => [
			'class' => 'yoc\validate\DatetimeValidate' ,
			'type'  => DatetimeValidate::TYPE_DATE_TIME
		] ,
		'maxLength' => [
			'class' => 'yoc\validate\LengthValidate' ,
			'type'  => LengthValidate::MAX_LENGTH ,
		] ,
		'minLength' => [
			'class' => 'yoc\validate\LengthValidate' ,
			'type'  => LengthValidate::MIN_LENGTH
		] ,
		'length'    => [
			'class' => 'yoc\validate\LengthValidate' ,
			'type'  => LengthValidate::MIN_LENGTH
		]
	];
	
	/** @var array|string $fields */
	protected $fields;
	
	protected $param;
	
	protected $ruleValue;
	
	protected $value;
	
	protected $modelClass;
	
	public $isFail = true;
	
	
	public function __set($name , $value)
	{
		$this->$name = $value;
		return $this;
	}
	
	/**
	 * @param                      $type
	 * @param                      $fields
	 * @param null                 $ruleValue
	 * @param \yoc\db\ActiveRecord $param
	 *
	 * @return static
	 * @throws \Exception
	 */
	public static function createValidate($type , $fields , $ruleValue = null , $param)
	{
		$param = ['fields' => $fields , 'param' => $param->getAttributes() , 'ruleValue' => $ruleValue , 'modelClass' => $param];
		if (is_string($type)) {
			$class = self::$validate[$type];
			if (is_string($class)) {
				$class = ['class' => $class];
			}
		} else if (is_callable($type , true)) {
			return call_user_func($type , $fields , $param);
		} else {
			throw new \Exception('unknown validate type');
		}
		$class = array_merge($class , $param);
		return Ioc::createObject($class);
	}
	
	abstract public function notify();
	
	/**
	 * @param $message
	 *
	 * @return bool
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this->isFail = false;
	}
	
	/**
	 * @param        $type
	 * @param        $key
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	protected function desc($type , $key , $default = '')
	{
		$config = require BASE_PATH . '/config/message.php';
		if (!isset($config[$type])) {
			return $default;
		}
		$config = $config[$type];
		$_value = $this->param[$key];
		if (!isset($config[$key]) || empty($config[$key])) {
			return $default;
		}
		if (!isset($config[$key][0]) || empty($config[$key][0])) {
			return $default;
		}
		return str_replace('[:attribute]' , $_value , $config[$key][0]);
	}
	
	/**
	 * @return mixed
	 */
	public function getMessage()
	{
		return $this->message;
	}
}