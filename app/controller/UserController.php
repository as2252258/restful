<?php

namespace app\controller;

use app\components\ActiveController;
use app\model\Friend;
use app\model\FriendGroup;
use app\model\Group;
use app\model\GroupUser;
use app\model\MemberDefaultAvatar;
use app\model\Message;
use app\model\SystemLog;
use app\model\User;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class UserController
 *
 * @package Controller
 */
class UserController extends ActiveController
{
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionAdd(Request $request)
	{
		$beginTrance = \Yoc::$app->db->beginTransaction();
		try {
			$model = new User();
			if (!$request->input->validate('username' , 'password')) {
				throw new Exception($this->getLastError());
			}
			$model->load();
			$model->password = Str::encrypt($model->password);
			if (empty($model->avator)) {
				$model->avator = MemberDefaultAvatar::queryRand('avatar');
			}
			$model->addTime = date('Y-m-d H:i:s');
			$model->modifyTime = date('Y-m-d H:i:s');
			if (empty($model->nickname)) {
				$model->nickname = preg_replace('/(\w{3})*(\w{3})$/' , '$1***$2' , $model->username);
			}
			$results = $model->save();
			if (!$results) {
				throw new Exception($model->getLastError());
			}
			
			FriendGroup::where(['userId' => $model->id])->delete();
			
			$group = new FriendGroup();
			$group->userId = $model->id;
			$group->name = '我的好友';
			$group->status = 1;
			$group->createTime = time();
			$group->modifyTime = time();
			if (!$group->save()) {
				throw new Exception($group->getLastError());
			}
			$beginTrance->commit();
			return Response::analysis(Code::SUCCESS , $results);
		} catch (Exception $exception) {
			$beginTrance->rollback();
			return Response::analysis(Code::ERROR , $exception->getMessage());
		}
	}
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionUpdate(Request $request)
	{
		$model = User::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		if ($this->user->id != $model->id && $this->user->level != 5) {
			return Response::analysis(-1 , '您不能修改他人信息');
		}
		$results = $model->load() && $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS , $results);
	}
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionDetail(Request $request)
	{
		$check = $request->input->integer('id');
		if (empty($check)) {
			if (!empty($this->user)) {
				return Response::analysis(0 , $this->user->unset('password'));
			}
			return Response::analysis(-1 , []);
		}
		$model = User::findOne($check);
		if (empty($model)) {
			throw new Exception('Data Not Exists');
		}
		return Response::analysis(STATUS_SUCCESS , $model);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionDelete(Request $request)
	{
		$_key = $request->input->integer('id' , true);
		$pass = $request->input->password('password' , true);
		if (empty($this->user) || strcmp(Str::encrypt($pass) , $this->user->password)) {
			throw new \Exception('密码错误');
		}
		$model = User::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		
		Group::where(['userId' => $model->id])->delete();
		GroupUser::where(['userId' => $model->id])->delete();
		FriendGroup::where(['userId' => $model->id])->delete();
		Message::whereOr($model->id , ['userId' , 'sendId'])->delete();
		Friend::whereOr($model->id , ['userId' , 'friendId'])->delete();
		SystemLog::whereOr($model->id , ['userId' , 'sendId'])->delete();
		
		return Response::analysis(Code::SUCCESS , 'delete success');
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 */
	public function actionList(Request $request)
	{
		$pWhere = [];
		$pWhere['sex'] = $request->input->get('sex' , false);                         //性别  1.男  2.女  3.未知
		$pWhere['glob'] = $request->input->get('glob' , false);                        //金币
		$pWhere['point'] = $request->input->get('point' , false);                       //积分
		$pWhere['email'] = $request->input->get('email' , false);                       //电子邮箱
		$pWhere['phone'] = $request->input->get('phone' , false);                       //手机号码
		$pWhere['avator'] = $request->input->get('avator' , false);                      //头像
		$pWhere['status'] = $request->input->get('status' , false);                      //1.正常  2.待审  3.冻结   4.删除  5.拉黑
		$pWhere['groupId'] = $request->input->get('groupId' , false);                     //
		$pWhere['isOnline'] = $request->input->get('isOnline' , false);                    //是否在线 0.不在线 1.在线
		$pWhere['username'] = $request->input->get('username' , false);                    //用户名
		$pWhere['nickname'] = $request->input->get('nickname' , false);                    //昵称
		$pWhere['password'] = $request->input->get('password' , false);                    //登陆密码
		$pWhere['addTime <='] = $request->input->get('addTime' , false);                     //注册时间
		$pWhere['addTime >='] = $request->input->get('addTime' , false);                     //注册时间
		$pWhere['birthday <='] = $request->input->get('birthday' , false);                    //生日
		$pWhere['birthday >='] = $request->input->get('birthday' , false);                    //生日
		$pWhere['modifyTime <='] = $request->input->get('modifyTime' , false);                  //修改时间
		$pWhere['modifyTime >='] = $request->input->get('modifyTime' , false);                  //修改时间
		$pWhere['lastloginTime'] = $request->input->get('lastloginTime' , false);               //最后登录时间
		
		
		$keyword = $request->input->get('keyword');
		if (!empty($keyword)) {
			$pWhere[] = \DB::whereLike($keyword , ['email' , 'phone' , 'username' , 'nickname']);
		}
		
		if (!empty($keyword)) {
			$pWhere[] = 'id <> ' . $this->user->id;
		}
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = User::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all()->toArray() , $count);
	}
	
	public function actionUserStatus()
	{
		return Response::analysis();
	}
	
	public function actionGeneralize(Request $request)
	{
	
	}
}