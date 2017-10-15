<?php

namespace app\controller;


use app\components\ActiveController;
use app\model\User;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use yoc\base\Gii;
use yoc\base\Rbac;
use yoc\core\Str;
use yoc\http\request;
use yoc\http\Response;

class SiteController extends ActiveController
{
	
	/**
	 * @param request $request
	 *
	 */
	public function actionIndex(Request $request)
	{
		return Response::analysis(0 , hash('sha384' , '123456'));
	}
	
	/**
	 * @param request $request
	 *
	 * @throws \Exception
	 */
	public function actionLogin(Request $request)
	{
		if (!$request->isPost()) {
			throw new \Exception('Request Method Error:' . $request->getRequestMethod());
		}
		$_account = $request->input->match('^[a-zA-Z0-9]{6,20}$' , 'account');
		$password = $request->input->password('password' , true);
		$select = User::whereOr($_account , ['username' , 'phone' , 'email'])->one();
		if (empty($select)) {
			return Response::analysis(\Code::NOT_EXISTS , '用户不存在' , $select);
		}
		if (strcmp(Str::encrypt($password) , $select->password)) {
			return Response::analysis(\Code::AUTH_ERROR , '密码错误');
		}
		if ($select->status == \Code::STATUS_AUDITING) {
			return Response::analysis(\Code::USER_STATUS_ERROR , '用户审核中');
		} else if ($select->status == \Code::STATUS_DELETE) {
			return Response::analysis(\Code::USER_STATUS_ERROR , '该账户已被永久冻结');
//		} else if ($select->status == \Code::STATUS_UNKNOWN) {
//			return Response::analysis(\Code::USER_STATUS_ERROR, '未知的账户状态');
		} else {
			$history = \Yoc::$app->getRedis()->sMembers(FD_LIST . $select->id);
			if (!empty($history) && is_array($history)) {
				\Yoc::pushUser('kick' , $select->id , [
					'message' => '您的账号在新的地点登录。如非本人操作, 请立即修改密码!' ,
				]);
			}
			\Yoc::getRedis()->del(token_temp_key($select->id));
			$config = \Yoc::$app->auth->create($select);
			return Response::analysis(STATUS_SUCCESS , '' , $config);
		}
	}
	
	public function actionGii(Request $request)
	{
		return Response::analysis(0 , Gii::run($request));
	}
	
	public function actionClear(Request $request)
	{
		$table = $request->input->get('table');
		$fileCache = \Yoc::$app->fileCache;
		$fileCache->clearTale($table);
		if (empty($table)) {
			\Yoc::getRedis()->del(\Yoc::getRedis()->keys('xl_*'));
		} else {
			\Yoc::getRedis()->del(\Yoc::getRedis()->keys($table . '_*'));
		}
		return Response::analysis(0);
	}
	
	public function actionPool(Request $request)
	{
		return Response::analysis(0 , \Yoc::$app->pool->getAll());
	}
	
	public function actionAuth(Request $request)
	{
		return Response::analysis(0 , Rbac::update());
	}
	
	public function actionToken(Request $request)
	{
		$ak = '-UvQk2r_TzdS9sGOkZZYkSfjZY5prMDK6aGOkYan';
		$sk = 'M9auOZJl_oLxWPMQIUA3QNovznt8pW8LZYbkdbhP';
		$auth = new Auth($ak , $sk);
		$token = $auth->uploadToken('xlimages');
		$key = $request->input->get('key');
		if ($key) {
			$bucketMgr = new BucketManager($auth);
			$bucket = 'xlimages';
			list($ret , $err) = $bucketMgr->stat($bucket , $key);
			if ($err === null) {
				return Response::analysis(0 , $key);
			}
		}
		return Response::analysis($token);
	}
	
	public function actionLogout(Request $request)
	{
		$load = token_temp_key($this->user->id);
		if (\Yoc::getRedis()->exists($load)) {
			\Yoc::$app->redis->del($load);
		}
		return Response::analysis(0);
	}
	
	public function actionTaskHistory(Request $request)
	{
		$data = $request->input->date('date');
		if (empty($data)) {
			$data = date('Y_m_d');
		} else {
			$data = date('Y_m_d' , strtotime($data));
		}
		$list = \Yoc::getRedis()->lRange('task_' . $data , 0 , -1);
		return Response::analysis(0 , $list);
	}
	
	public function actionErrorLog(Request $request)
	{
		$key = 'error_' . date('Y_m_d');
		if (!\Yoc::getRedis()->exists($key)) {
			return Response::analysis(0 , []);
		}
		$data = \Yoc::getRedis()->hGetAll($key);
		if (!empty($data)) {
			krsort($data);
		}
		return Response::analysis(0 , $data);
	}
	
	public function actionDeleteIndex(Request $request)
	{
		$data = $request->input->date('date');
		if (empty($data)) {
			$data = date('Y_m_d');
		} else {
			$data = date('Y_m_d' , strtotime($data));
		}
		$list = \Yoc::getRedis()->lRange('task_' . $data , 0 , -1);
		return Response::analysis(0 , $list);
	}
	
	public function actionEncode(Request $request)
	{
		return Response::analysis(Str::rand(32));
	}
}