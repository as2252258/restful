<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/24 0024
 * Time: 19:20
 */

namespace yoc\observer;


use SplObserver;
use yoc\base\Objects;

class Subject extends Objects implements \SplSubject
{
	/** @var \SplObjectStorage $observer */
	private $observer;
	
	/** @var array $subject */
	private $subject;
	
	public function __construct(array $config = [])
	{
		if (!$this->observer instanceof \SplObjectStorage) {
			$this->observer = new \SplObjectStorage();
		}
		parent::__construct($config);
	}
	
	/**
	 * @param SplObserver $observer
	 * @param array $param
	 * @return $this
	 */
	public function addParam(SplObserver $observer, array $param = [])
	{
		$class = get_class($observer);
		$this->subject[$class] = $param;
		return $this;
	}
	
	/**
	 * @param $className
	 * @return mixed|null
	 */
	public function getParam($className)
	{
		return isset($this->subject[$className]) ? $this->subject[$className] : null;
	}
	
	/**
	 * @param SplObserver $observer
	 */
	public function attach(SplObserver $observer)
	{
		$this->observer->attach($observer);
	}
	
	/**
	 * @param SplObserver $observer
	 */
	public function detach(SplObserver $observer)
	{
		$this->observer->detach($observer);
		unset($this->subject[get_class($observer)]);
	}
	
	/**
	 * @return bool
	 */
	public function notify()
	{
		foreach ($this->observer as $key => $observer) {
			$observer->update($this);
			unset($this->subject[get_class($observer)]);
		}
		return true;
	}
	
	
	public function __destruct()
	{
		unset($this->observer, $this->subject);
	}
}