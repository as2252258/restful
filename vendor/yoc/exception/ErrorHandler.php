<?php

namespace yoc\exception;


use Swoole\Exception;
use yoc\http\Response;

class ErrorHandler
{
	public static $errors = [];
	
	public $loggerPath = BASE_PATH . '/runtime';
	
	public $task = BASE_PATH . '/runtime/task';
	
	public $error = BASE_PATH . '/runtime/error';
	
	public $shutdown = BASE_PATH . '/runtime/shutdown';
	
	public $exception = BASE_PATH . '/runtime/exception';
	
	public function __construct()
	{
		if (!is_dir($this->loggerPath)) {
			mkdir($this->loggerPath);
		}
		if (!is_dir($this->loggerPath . '/socket')) {
			mkdir($this->loggerPath . '/socket');
		}
		if (!is_dir($this->task)) mkdir($this->task);
		if (!is_dir($this->error)) mkdir($this->error);
		if (!is_dir($this->shutdown)) mkdir($this->shutdown);
		if (!is_dir($this->exception)) mkdir($this->exception);
	}
	
	/**
	 * @param string $class
	 * @param string $message
	 */
	public function setLog($class , $message)
	{
		$this->addError($class , $message);
	}
	
	/**
	 * @param $class
	 * @param $message
	 *
	 * @return $this
	 */
	public function addError($class , $message)
	{
		if (array_key_exists($class , static::$errors)) {
			array_push(static::$errors[$class] , $message);
		} else {
			static::$errors[$class][] = $message;
		}
		return $this;
	}
	
	/**
	 * @param $class
	 *
	 * @return mixed|null
	 * 获取最后一条错误信息
	 */
	public function lastError($class)
	{
		if (!array_key_exists($class , static::$errors)) {
			return null;
		}
		$count = count(static::$errors[$class]);
		if ($count < 1) return '';
		if ($count != 1) {
			$error = static::$errors[$class][$count];
		} else {
			$error = end(static::$errors[$class]);
		}
		$this->clear($class);
		return $error;
	}
	
	/**
	 * @param $class
	 *
	 * @return mixed
	 * 清除错误信息
	 */
	public function clear($class = '')
	{
		if (!empty($class)) {
			unset(static::$errors[$class]);
		} else {
			static::$errors = [];
		}
		return $this;
	}
	
	/**
	 * @param $class
	 *
	 * @return int
	 * 获取错误信息数量
	 */
	public function size($class = '')
	{
		return array_key_exists($class , static::$errors) ? count(static::$errors[$class]) : count(static::$errors);
	}
	
	public function getErrors()
	{
		$errors = static::$errors;
		$this->clear();
		return $errors;
	}
	
	/**
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @return mixed
	 */
	public function error_handler($errno , $errstr , $errfile , $errline)
	{
		$this->clear();
		$this->setLogger($this->getFormat($errstr , $errfile , $errline , $errno) , \Code::ERROR_LEVEL_ERROR);
		return Response::analysis(-1 , $errstr , [
			'file'   => $errfile ,
			'line'   => $errline ,
			'trance' => ''
		]);
	}
	
	/**
	 * @param $message
	 * @param $level
	 */
	public function setLogger($message , $level = ERROR_LEVEL_ERROR)
	{
		$file = date('Y_m_d');
		if ($message instanceof \Exception) {
			$message = $this->getFormat($message->getMessage() , $message->getFile() , $message->getLine() , $message->getCode());
		} else if (is_array($message)) {
			if (empty($message)) {
				return;
			}
			$message = "[" . date('Y-m-d H:i:s') . "][" . microtime(true) . "]" . implode(' ' , $message);
		}
		$time = sprintf('%.6f' , microtime(true));
		\Yoc::$app->redis->hMset('error_' . $file , [date('H:i:s') . strchr($time , '.') => $message]);
	}
	
	public function getFormat($message , $file , $line , $code)
	{
		if (empty($message) && empty($code)) {
			return false;
		}
		if (is_numeric($message) && isset(\Code::$INFO[$message])) {
			$message = \Code::$INFO[$message];
		}
		$array = [
			"[" . date('Y-m-d H:i:s') . "] A Error Code: " . $code , 'Message:' . $message , 'In Line :' . $line , 'At File:' . $file ,
		];
		return implode(', ' , $array);
	}
	
	/**
	 * @param \Exception $exception
	 *
	 * @return mixed
	 */
	public function exception_handler($exception)
	{
		$this->clear();
		$string = $this->getFormat($exception->getMessage() , $exception->getFile() , $exception->getLine() , $exception->getCode());
		$this->setLogger($string , \Code::ERROR_LEVEL_EXCEPTION);
		return Response::analysis(-1 , $exception->getMessage() , [
			'file'   => $exception->getFile() ,
			'line'   => $exception->getLine() ,
			'trance' => $exception->getTrace()
		]);
	}
	
	public function error_shutdown_function()
	{
		$this->clear();
		$error = error_get_last();
		if (empty($error) || !is_array($error)) {
			return Response::analysis(0 , $error);
		}
		if (!isset($error['message']) || !isset($error['file']) || !isset($error['line'])) {
			return Response::analysis(0 , $error);
		}
		$this->setLogger($this->getFormat($error['message'] , $error['file'] , $error['line'] , 0) , \Code::ERROR_LEVEL_SHUTDOWN);
		$message = preg_match('/(.*?)Stack?/sx' , $error['message'] , $value);
		if (!$message) {
			$value = $error['message'];
		} else if (is_array($value)) {
			$value = end($value);
		} else {
			$value = $error['message'];
		}
		return Response::analysis(-1 , $value , [
			'file'   => $error['file'] ,
			'line'   => $error['line'] ,
			'trance' => $error
		]);
	}
	
	public function register()
	{
		ini_set('display_errors' , true);
		set_exception_handler([$this , 'exception_handler']);
		if (defined('HHVM_VERSION')) {
			set_error_handler([$this , 'error_handler']);
		} else {
			set_error_handler([$this , 'error_handler']);
		}
		register_shutdown_function([$this , 'error_shutdown_function']);
	}
}