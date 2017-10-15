<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/25 0025
 * Time: 18:08
 */

namespace app\components;


use app\model\Auth;
use app\model\Packet;
use app\model\User;
use yoc\base\Controller;
use yoc\http\Request;

class ActiveController extends Controller
{
	
	/** @var  User $user */
	protected $user;
	
	protected $unChecks = ['site' , 'user/add' , 'user/list' , 'friendGroup/list' , 'message/clear' , 'set/list' , 'auth/list' , 'files/download'];
	
	/**
	 * @param Request $request
	 *
	 * @return bool
	 * @throws \Exception
	 * @throws \Exception()
	 */
	public function beforeAction($request)
	{
		if ($request->input->getHeader('user')) {
			$this->user = $this->getUserModel();
			if (!\Yoc::$app->auth->check($request->input->getHeader() , $this->user)) {
				throw new \Exception(\Code::AUTH_ERROR);
			}
		}
		if ($request->is($this->unChecks)) return true;
		if (!$request->checkSource()) {
			throw new \Exception('unknown device on the request');
		}
		if ($request->isDebug()) return true;
		$return = $this->checkRequestAuth($request);
		if (!$return) {
			throw new \Exception(\Code::AUTH_ERROR);
		}
		return true;
	}
	
	/**
	 * @return User
	 * @throws \Exception
	 */
	public function getUserModel()
	{
		$uid = \Yoc::getRequest()->input->getHeader('user');
		if (empty($uid)) {
			return null;
		}
		return User::findOne($uid);
	}
	
	/**
	 * @param Request $request
	 *
	 * @return bool|string
	 */
	public function checkRequestAuth(Request $request)
	{
		if ($request->getRequestMethod() && $this->checkRouteAuth($request)) {
			$authorize = \Yoc::$app->auth->check($request->getHttpHeaders() , $this->user);
			if (is_array($authorize) && !empty($this->user)) {
				return false;
			} else {
				return $request->getRequestMethod();
			}
		}
		return false;
	}
	
	/**
	 * @return bool
	 * @throws \Exception
	 */
	private function checkRouteAuth(Request $request)
	{
		$find = Auth::where(['controller' => $request->controller , 'action' => $request->action])->one();
		if (!empty($find) && $find->neeLogin != 0) {
			return $this->checkUserGroup($find);
		}
		return false;
	}
	
	/**
	 * @return bool
	 * @throws \Exception
	 */
	private function checkUserGroup($find)
	{
		$user = $this->checkUserStatus();
		$groupAuth = Packet::where(['itemId' => $user->groupId , 'authId' => $find->id])->one();
		if (empty($groupAuth)) {
			throw new \Exception(\Code::AUTH_GROUP_ERROR);
		}
		return true;
	}
	
	/**
	 * @return bool|User
	 *
	 * 1.正常 2.待审 3.冻结 4.删除 5.拉黑
	 *
	 * @throws \Exception
	 */
	private function checkUserStatus()
	{
		if (empty($this->user)) {
			throw new \Exception('您可能还未登录或已超时!!但是您访问的地址被要求登录，请先登录');
		} else if ($this->user->status == \Code::STATUS_DELETE) {
			throw new \Exception('您的账户已被冻结,原因：涉嫌欺骗少女感情. 解封剩余时间-1时-1分-1秒！！');
		} else if ($this->user->status == \Code::STATUS_DELETE) {
			throw new \Exception('账户不存在, 请仔细检查您的账户名是否拼写正确');
//		} else if ($this->user->status == \Code::STATUS_UNKNOWN) {
//			throw new \Exception('您的账户已被禁止使用, 无法恢复');
		}
		return $this->user;
	}
	
	/**
	 * @return mixed|null
	 */
	protected function getLastRequestError()
	{
		return \Yoc::getError()->lastError('request');
	}
}