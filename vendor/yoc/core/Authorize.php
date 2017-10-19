<?php

namespace yoc\core;

use app\model\User;
use Yoc;
use yoc\base\Components;

/**
 * Class Authorize
 */
class Authorize extends Components
{
	
	/** @var yoc\db\ActiveRecord $user */
	private $user;
	
	private $data;
	
	private $source = ['pc' , 'browser' , 'android' , 'iphone'];
	
	private $config = [
		'user'  => '' ,
		'token' => '' ,
		'time'  => '' ,
	];
	
	public function __construct()
	{
		$this->config['time'] = time();
		parent::__construct([]);
	}
	
	/**
	 * @param $user
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function create($user , $header = [])
	{
		$this->user = $user;
		if (empty($user) || !is_object($user)) {
			throw new \Exception('您还未登录或已登录超时');
		}
		$source = $header['source'] ?? 'browser';
		if (empty($source) || !in_array($source , $this->source)) {
			throw new \Exception('未知的登录设备');
		}
		$param = $this->assembly(array_merge($this->config , [
			'user'  => $user->id ,
			'token' => $this->token($user , ['device' => Str::rand(128)] , $this->config['time']) ,
		]) , true);
		return array_intersect_key($param , $this->config);
	}
	
	/**
	 * @param User  $user
	 * @param array $param
	 *
	 * @return array
	 */
	private function assembly(array $param , $update = false)
	{
		if (isset($param['sign'])) {
			unset($param['sign']);
		}
		$param = $this->initialize($param);
		asort($param , SORT_STRING);
		$_tmp = [];
		foreach ($param as $key => $val) {
			$_tmp[] = $key . '=>' . $val;
		}
		$param['sign'] = md5(implode(':' , $_tmp));
		if ($update) {
			$this->setCache($param);
		}
		return $param;
	}
	
	/**
	 * @param $param
	 *
	 * @return mixed
	 */
	private function initialize(array $param)
	{
		$_param = [
			'version' => '1' ,
			'source'  => 'pc' ,
		];
		if (!isset($param['device'])) {
			$param['device'] = Str::rand(128);
		}
		return array_merge($param , $_param);
	}
	
	/**
	 * @param array $data
	 */
	private function setCache(array $data)
	{
		$redis = Yoc::getRedis();
		$redis->hMset($this->authKey() , $data);
		$redis->expire($this->authKey() , 3600 * 24 * 30);
	}
	
	/**
	 * @return string
	 */
	private function authKey()
	{
		$source = $this->data['source'];
		if (empty($source)) $source = 'browser';
		return TMP_TOKEN . '_' . strtoupper($source) . '_' . $this->user->getPrimaryValue();
	}
	
	/**
	 * @param User  $user
	 * @param array $param
	 * @param int   $requestTime
	 *
	 * @return string
	 * @throws \Exception()
	 */
	private function token(User $user , $param = [] , $requestTime)
	{
		$str = '';
		$_user = str_split(md5($user->username . md5($user->password)));
		ksort($_user);
		foreach ($_user as $key => $val) {
			$str .= md5(sha1($key . $val . 'www.xshucai.com'));
		}
		$arr = ['password' , 'loginTime' , 'loginType' , 'device'];
		foreach ($param as $key => $val) {
			if (!in_array($key , $arr)) {
				throw new \Exception('主参数' . $key . '不存在');
			}
			$str .= md5($str . sha1($key . md5($val)));
		}
		$str .= sha1(base64_encode($requestTime));
		return $this->preg(md5($str . $user->password));
	}
	
	/**
	 * @param string $str
	 *
	 * @return mixed
	 * 将字符串替换成指定格式
	 */
	private function preg($str)
	{
		$preg = '/(\w{10})(\w{5})(\w{7})(\w{5})(\w{5})/';
		return preg_replace($preg , '$1-$2-$3-$4-$5' , $str);
	}
	
	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	public function check($data , $user)
	{
		$this->data = $data;
		$this->user = $user;
		if (empty($data['source']) || empty($this->user)) return false;
		$cache = $this->getUserModel();
		if (empty($cache)) {
			return false;
		}
		$merge = $this->assembly(array_merge($cache , [
			'user'  => $data['user'] ,
			'token' => $data['token'] ,
			'time'  => $data['time'] ,
		]));
		$check = array_diff_assoc($this->initialize($cache) , $merge);
		return !((bool) count($check));
	}
	
	/**
	 * @param $data
	 *
	 * @return bool|array
	 * @throws \Exception
	 */
	private function getUserModel()
	{
		return Yoc::$app->redis->hGetAll($this->authKey());
	}
}