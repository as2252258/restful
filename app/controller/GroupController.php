<?php

namespace app\controller;

use app\components\ActiveController;
use app\logic\GroupLogic;
use app\model\Group;
use app\model\GroupUser;
use app\model\MemberDefaultAvatar;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class GroupController
 *
 * @package Controller
 */
class GroupController extends ActiveController
{
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new Group();
		$model->setBatch([
			'userId'        => $this->user->id ,          //群创建人
			'groupName'     => $request->input->string('groupName' , true , [0 , 50]) ,         //群名称
			'description'   => $request->input->string('description' , true , null) ,         //群简介
			'avatar'        => $request->input->string('avatar' , false , null) ,              //群头像
			'isPublic'      => $request->input->integer('isPublic' , false , [0 , 1]) ,         //群是否公开
			'messageAuth'   => $request->input->integer('messageAuth' , false , [0 , 1]) ,      //群发言设置
			'plusGroupAuth' => $request->input->integer('plusGroupAuth' , false , [0 , 1]) ,    //加群验证
			'status'        => $request->input->integer('status' , false , [0 , 1]) ,           //1.正常 2.封禁 3.删除
			'createTime'    => $request->input->datetime('createTime' , false , date('Y-m-d H:i:s')) ,//创建时间
			'dealwithTime'  => $request->input->datetime('dealwithTime' , false , date('Y-m-d H:i:s')) ,//修改时间
		]);
		if (empty($model->avatar)) {
			$model->avatar = MemberDefaultAvatar::queryRand('avatar');
		}
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		
		$user = new GroupUser();
		$user->userId = $this->user->id;
		$user->groupId = $model->id;
		$user->auth = 3;
		$user->remarks = $this->user->nickname;
		$user->status = 0;
		$user->isReceiveMessage = 0;
		$user->createTime = date('Y-m-d H:i:s');
		$user->dealwithTime = date('Y-m-d H:i:s');
		if (!$user->save()) {
			$model->delete();
			return Response::analysis(Code::SYSTEM_ERROR , $user->getLastError());
		}
		return Response::analysis(Code::SUCCESS , $user);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionUpdate(Request $request)
	{
		$model = Group::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'userId'        => $request->input->integer('userId' , false , [0 , 11]) ,          //群创建人
			'groupName'     => $request->input->string('groupName' , true , [0 , 50]) ,         //群名称
			'description'   => $request->input->string('description' , true , null) ,         //群简介
			'avatar'        => $request->input->string('avatar' , true , null) ,              //群头像
			'isPublic'      => $request->input->integer('isPublic' , false , [0 , 1]) ,         //群是否公开
			'messageAuth'   => $request->input->integer('messageAuth' , false , [0 , 1]) ,      //群发言设置
			'plusGroupAuth' => $request->input->integer('plusGroupAuth' , false , [0 , 1]) ,    //加群验证
			'status'        => $request->input->integer('status' , false , [0 , 1]) ,           //1.正常 2.封禁 3.删除
			'createTime'    => $request->input->datetime('createTime' , false , date('Y-m-d H:i:s')) ,//创建时间
			'dealwithTime'  => $request->input->datetime('dealwithTime' , false , date('Y-m-d H:i:s')) ,//修改时间
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
		$model = Group::findOne($check);
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
		$model = Group::findOne($_key);
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
	 */
	public function actionList(Request $request)
	{
		$pWhere = [];
		$pWhere['userId'] = $request->input->get('userId' , false);                      //群创建人
		$pWhere['groupName'] = $request->input->get('groupName' , false);                   //群名称
		$pWhere['description'] = $request->input->get('description' , false);                 //群简介
		$pWhere['avatar'] = $request->input->get('avatar' , false);                      //群头像
		$pWhere['isPublic'] = $request->input->get('isPublic' , false);                    //群是否公开
		$pWhere['messageAuth'] = $request->input->get('messageAuth' , false);                 //群发言设置
		$pWhere['plusGroupAuth'] = $request->input->get('plusGroupAuth' , false);               //加群验证
		$pWhere['status'] = $request->input->get('status' , false);                      //1.正常 2.封禁 3.删除
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
		$model = Group::where($pWhere)->orderBy($order);
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
	 * 解散群
	 */
	public function actionDissolution(Request $request)
	{
		$group = Group::findOne($request->input->integer('id' , true));
		if (empty($group)) {
			return Response::analysis(-1 , '群不存在或已解散');
		}
		$groupUser = GroupUser::where(['groupId' => $group->id , 'userId' => $this->user->id])->one();
		if (empty($groupUser)) {
			return Response::analysis(-1 , '您不是群成员');
		}
		if ($groupUser->auth != 3) {
			return Response::analysis(-1 , '您不是群主, 无相应权限进行操作');
		}
		if (!$group->delete()) {
			return Response::analysis(Code::SYSTEM_ERROR , $group->getLastError());
		}
		$this->asyncTask(GroupLogic::className() , 'dissolution' , [$group , $this->user]);
		return Response::analysis(0 , $group);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 * 群转让
	 */
	public function actionTransfer(Request $request)
	{
		$trance = \Yoc::$app->db->beginTransaction();
		try {
			if (!$request->isPost()) {
				throw new Exception(Code::REQUEST_METHOD_ERROR);
			}
			$group = Group::findOne($request->input->integer('groupId' , true));
			if (empty($group)) {
				throw new Exception(-1 , '群不存在或已解散');
			}
			$userId = $request->input->integer('userId');
			if ($this->user->id === $userId) {
				throw new Exception(-1 , '您已经是群主, 无法转让');
			}
			$groupUser = GroupUser::where(['groupId' => $group->id , 'userId' => $userId])->one();
			if (empty($groupUser)) {
				throw new Exception(-1 , '该用户不是群成员');
			}
			$group->userId = $userId;
			if (!$group->save()) {
				throw new Exception($group->getLastError());
			}
			$groupUser->auth = 3;
			if (!$groupUser->save()) {
				throw new Exception($groupUser->getLastError());
			}
			$me = GroupUser::where(['groupId' => $group->id , 'userId' => $this->user->id])->one();
			if (!$me->save(['auth' => 1])) {
				throw new Exception($me->getLastError());
			};
			$this->asyncTask(GroupLogic::className() , 'transfer' , [$groupUser , $group , $this->user]);
			$trance->commit();
			return Response::analysis(0 , $me);
		} catch (Exception $exception) {
			$trance->rollback();
			return Response::analysis(0 , $exception->getMessage());
		}
	}
}