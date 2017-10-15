<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/22 0022
 * Time: 16:28
 */

namespace yoc\di;

use Yoc;
use yoc\base\Objects;

class Inline extends Objects
{
	public $id;
	
	
	public function __construct($id, array $config = [])
	{
		$this->id = $id;
		parent::__construct($config);
	}
	
	public static function of($id)
	{
		return new static($id);
	}
	
	public function get($ioc = null)
	{
		if (!empty($ioc)) {
			return $ioc->get($this->id);
		}
		if (Yoc::$app->has($this->id)) {
			return Yoc::$app->get($this->id);
		} else {
			return Ioc::ioc()->get($this->id);
		}
	}
}