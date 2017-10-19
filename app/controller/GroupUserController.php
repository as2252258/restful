<?php

namespace app\controller;

use app\components\ActiveController;
use app\logic\GroupLogic;
use app\logic\Log;
use app\model\Group;
use app\model\GroupBlack;
use app\model\GroupUser;
use app\model\SystemLog;
use app\model\User;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class GroupUserController
 *
 * @package Controller
 */
class GroupUserController extends ActiveController
{
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new GroupUser();
		$results = $model->load() && $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS , $results);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionUpdate(Request $request)
	{
		$model = GroupUser::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'groupId'          => $request->input->integer('groupId' , false , [0 , 11]) ,         //群ID
			'userId'           => $request->input->integer('userId' , false , [0 , 11]) ,          //用户ID
			'auth'             => $request->input->integer('auth' , false , [0 , 1]) ,             //群权限 1.普通用户  2.管理员  3.群主
			'isReceiveMessage' => $request->input->integer('isReceiveMessage' , false , [0 , 1]) , //0.接收并提醒  1.接收不提醒  2.不接收
			'status'           => $request->input->integer('status' , false , [0 , 1]) ,           //1.正常 2.禁言 3.删除
			'createTime'       => $request->input->datetime('createTime' , false , date('Y-m-d H:i:s')) ,//创建时间
			'dealwithTime'     => $request->input->datetime('dealwithTime' , false , date('Y-m-d H:i:s')) ,//修改时间
		]);
		$results = $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS , $results);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionDetail(Request $request)
	{
		$check = $request->input->integer('id');
		if (empty($check)) {
			throw new Exception('param id can not empty');
		}
		$model = GroupUser::findOne($check);
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
		$model = GroupUser::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		return Response::analysis(Code::SUCCESS , 'delete success');
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionList(Request $request)
	{
		$pWhere = [];
		$pWhere['groupId'] = $request->input->get('groupId' , false);                     //群ID
		$pWhere['userId'] = $request->input->get('userId' , false);                      //用户ID
		$pWhere['auth'] = $request->input->get('auth' , false);                        //群权限 1.普通用户  2.管理员  3.群主
		$pWhere['isReceiveMessage'] = $request->input->get('isReceiveMessage' , false);            //0.接收并提醒  1.接收不提醒  2.不接收
		$pWhere['status'] = $request->input->get('status' , false);                      //1.正常 2.禁言 3.删除
		$pWhere['createTime <='] = $request->input->get('createTime' , false);                  //创建时间
		$pWhere['createTime >='] = $request->input->get('createTime' , false);                  //创建时间
		$pWhere['dealwithTime <='] = $request->input->get('dealwithTime' , false);                //修改时间
		$pWhere['dealwithTime >='] = $request->input->get('dealwithTime' , false);                //修改时间
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = GroupUser::where($pWhere)->orderBy('auth desc,createTime desc');
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 */
	public function actionApply(Request $request)
	{
		$groupId = $request->input->integer('groupId' , true);
		$userId = GroupUser::where(['userId' => $this->user->id , 'groupId' => $groupId])->one();
		if (!empty($userId)) {
			return Response::analysis(-1 , '您已经在群里了');
		}
		
		$black = GroupBlack::where(['userId' => $this->user->id , 'groupId' => $groupId])->one();
		if (!empty($black)) {
			return Response::analysis(-1 , '您已被群管理员拉黑，无法加入');
		}
		
		$group = Group::findOne($groupId);
		
		$remarks = $request->input->string('remarks' , true);
		$admins = GroupUser::whereOr('auth' , [2 , 3])->all();
		if (!empty($admins)) foreach ($admins as $key => $val) {
			$model = Log::recordAndNotify($val->userId , $this->user->nickname . '申请加入' . $group->groupName , Log::CATE_KICK_GROUP , $remarks , $this->user);
			\Yoc::pushUser('Common::loadNews' , $model->userId , [
				'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
				'sendId'   => $this->user->id ,
				'nickname' => $this->user->nickname ,
				'avator'   => $this->user->avator
			]);
		}
		return Response::analysis(0 , '您的申请已提交，请等待管理员审核');
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * 将群成员踢出群
	 */
	public function actionKick(Request $request)
	{
		$remarks = $request->input->string('remarks');
		
		$user = GroupUser::where(['userId' => $this->user->id])->one();
		if (empty($user) || !in_array($user->auth , [2 , 3])) {
			return Response::analysis(-1 , '您不是群主或者管理员');
		}
		
		$userId = $request->input->integer('id' , true);
		$groupId = $request->input->integer('groupId' , true);
		
		$groupUser = GroupUser::where(['userId' => $userId , 'groupId' => $groupId])->one();
		if (empty($groupUser)) {
			return Response::analysis(-1 , '该用户已不在群里');
		}
		$groupUser->delete();
		
		$group = Group::findOne($groupId);
		if (empty($group)) {
			return Response::analysis(-1 , '该群组已被管理员删除');
		}
		
		$_user = User::findOne($groupUser->userId);
		if (empty($_user)) {
			return Response::analysis(-1 , '该账户已被管理员删除');
		}
		
		$admins = GroupUser::whereOr('auth' , [2 , 3])->all();
		if (!empty($admins)) foreach ($admins as $key => $val) {
			$message = '管理员' . $this->user->nickname . '已将成员' . $_user->nickname . '移除' . $group->groupName;
			$model = Log::recordAndNotify($val->userId , $message , Log::CATE_KICK_GROUP , $remarks , $this->user , $groupId);
			\Yoc::pushUser('Common::loadNews' , $model->userId , [
				'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
				'groupId'  => $model->groupId ,
				'sendId'   => $this->user->id ,
				'nickname' => $group->groupName ,
				'avatar'   => $group->avatar
			]);
		}
		
		$message = '您已被管理员移除' . $group->groupName;
		$model = Log::recordAndNotify($groupUser->userId , $message , Log::CATE_KICK_GROUP , $remarks , $this->user , $groupId);
		\Yoc::pushUser('Group::kick' , $model->userId , [
			'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
			'groupId'  => $model->groupId ,
			'sendId'   => $this->user->id ,
			'nickname' => $group->groupName ,
			'avatar'   => $group->avatar
		]);
		
		return Response::analysis(0);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 */
	public function actionInvite(Request $request)
	{
		$group = Group::findOne($gid = $request->input->integer('gid' , true));
		if (empty($group)) {
			return Response::analysis(-1 , '该群已被解散或不存在');
		}
		$ids = $request->input->array('uids' , true);
		foreach ($ids as $val) {
			$user = User::findOne($val);
			if (empty($user)) {
				continue;
			}
			$message = $this->user->nickname . '邀请您加入群' . $group->groupName;
			$log = Log::recordAndNotify($val , $message , Log::CATE_INVITE , '' , $this->user , $gid);
			\Yoc::pushUser('Common::loadNews' , $val , [
				'message'  => $log->message ,
				'sendId'   => $log->sendId ,
				'avator'   => $user->avator ,
				'nickname' => $user->nickname ,
			]);
		}
		return Response::analysis(0);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 */
	public function actionResults(Request $request)
	{
		$id = $request->input->string('id' , true);
		$message = SystemLog::findOne($id);
		if (empty($message) || $message->category != 9) {
			return Response::analysis(-1 , '错误的消息编号');
		}
		$message->results = $request->input->integer('status' , true);
		$message->status = 2;
		$message->modifyTime = time();
		if (!$message->save()) {
			return Response::analysis(-1 , $message->getLastError());
		}
		if ($message->results == 1) {
			$group = Group::findOne($message->groupId);
			if (empty($group)) {
				return Response::analysis(-1 , '该群已被解散或不存在');
			}
			$groupUser = GroupUser::where(['groupId' => $group->id , 'userId' => $this->user->id])->one();
			if (!empty($groupUser)) {
				return Response::analysis(-1 , '您不能重复处理该事件');
			}
			$groupUser = new GroupUser();
			$groupUser->userId = $this->user->id;
			$groupUser->groupId = $group->id;
			$groupUser->auth = 1;
			$groupUser->status = 1;
			$groupUser->createTime = date('Y-m-d H:i:s');
			$groupUser->dealwithTime = date('Y-m-d H:i:s');
			if (!$groupUser->save()) {
				return Response::analysis(-1 , $groupUser->getLastError());
			}
			$this->asyncTask(GroupLogic::className() , 'join' , [$groupUser , $this->user , $group , $message]);
			return Response::analysis(0 , array_merge($groupUser->toArray() , [
				'group' => $group->toArray()
			]));
		} else if ($message->status == 2) {
			$_message = $this->user->nickname . '拒绝了您的邀请';
			Log::recordAndNotify($message->sendId , $_message , Log::CATE_INVITE , '' , $this->user , $message->groupId);
			\Yoc::pushUser('Common::loadNews' , $message->sendId , []);
		}
		return Response::analysis(0);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 */
	public function actionRemarks(Request $request)
	{
		$userId = $request->input->integer('id' , true);
		$groupId = $request->input->integer('groupId' , true);
		$groupUser = GroupUser::where(['userId' => $userId , 'groupId' => $groupId])->one();
		if (empty($groupUser)) {
			return Response::analysis(Code::DATA_EMPTY);
		}
		$groupUser->remarks = $request->input->string('remarks' , true , [1 , 20]);
		if (!$groupUser->save()) {
			return Response::analysis(Code::DB_ERROR , $groupUser->getLastError());
		}
		return Response::analysis(Code::SUCCESS , $groupUser);
	}
	
}