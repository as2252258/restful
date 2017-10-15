<?php

namespace app\controller;

use app\components\ActiveController;
use app\logic\GroupLogic;
use app\model\GroupMessage;
use app\model\GroupUser;
use Code;
use Exception;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class GroupMessageController
 *
 * @package Controller
 */
class GroupMessageController extends ActiveController
{
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new GroupMessage();
		$model->setBatch([
			'groupId'      => $request->input->integer('groupId' , false , [0 , 11]) ,         //群ID
			'sendId'       => $this->user->id ,          //消息发送
			'nickname'     => $this->user->nickname ,         //昵称
			'avatar'       => $this->user->avator ,          //头像
			'content'      => $request->input->string('content' , false , null) ,            //消息内容
			'status'       => $request->input->integer('status' , false , [0 , 1]) ,           //0.未读  1.已读  2.屏蔽 3.忽略 4.删除
			'createTime'   => $request->input->datetime('createTime' , false , date('Y-m-d H:i:s')) ,//消息创建时间
			'dealwithTime' => $request->input->datetime('dealwithTime' , false , date('Y-m-d H:i:s')) ,//消息处理时间
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		$this->asyncTask(GroupLogic::className() , 'message' , [$model , $this->user]);
		return Response::analysis(Code::SUCCESS , $results);
	}
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionUpdate(Request $request)
	{
		$model = GroupMessage::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'groupId'      => $request->input->integer('groupId' , false , [0 , 11]) ,         //群ID
			'sendId'       => $request->input->integer('sendId' , false , [0 , 11]) ,          //消息发送
			'nickname'     => $request->input->string('nickname' , false , [0 , 50]) ,         //昵称
			'avatar'       => $request->input->string('avatar' , false , [0 , 150]) ,          //头像
			'content'      => $request->input->string('content' , false , null) ,            //消息内容
			'status'       => $request->input->integer('status' , false , [0 , 1]) ,           //0.未读  1.已读  2.屏蔽 3.忽略 4.删除
			'createTime'   => $request->input->datetime('createTime' , false , date('Y-m-d H:i:s')) ,//消息创建时间
			'dealwithTime' => $request->input->datetime('dealwithTime' , false , date('Y-m-d H:i:s')) ,//消息处理时间
		]);
		$results = $model->save();
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
			throw new Exception('param id can not empty');
		}
		$model = GroupMessage::findOne($check);
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
		$model = GroupMessage::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		$groupUser = GroupUser::where(['userId' => $this->user->id])->one();
		if ($groupUser->auth != 2 && $groupUser->auth != 3 && $model->sendId != $this->user->id) {
			return Response::analysis(-1 , '您无权进行该操作');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		$this->asyncTask(GroupLogic::className() , 'messageDelete' , [$model , $this->user]);
		return Response::analysis(Code::SUCCESS , 'delete success');
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 * 消息回撤
	 */
	public function actionWithdraw(Request $request)
	{
		$_key = $request->input->integer('id' , true);
		$model = GroupMessage::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		$groupUser = GroupUser::where(['userId' => $this->user->id])->one();
		if ($groupUser->auth != 2 && $groupUser->auth != 3 && $model->sendId != $this->user->id) {
			return Response::analysis(-1 , '您无权进行该操作');
		}
		if (time() > strtotime($model->createTime) + 5 * 60) {
			return Response::analysis(Code::ERROR , '该消息发送时间已超过5分钟，无法撤回');
		}
		$model->status = 5;
		if (!$model->save()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		$this->asyncTask(GroupLogic::className() , 'messageRollback' , [$model , $this->user]);
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
		$pWhere['groupId'] = $request->input->get('groupId' , false);                     //群ID
		$pWhere['sendId'] = $request->input->get('sendId' , false);                      //消息发送
		$pWhere['nickname'] = $request->input->get('nickname' , false);                    //昵称
		$pWhere['avatar'] = $request->input->get('avatar' , false);                      //头像
		$pWhere['content'] = $request->input->get('content' , false);                     //消息内容
		$pWhere['status'] = $request->input->get('status' , false);                      //0.未读  1.已读  2.屏蔽 3.忽略 4.删除
		$pWhere['createTime <='] = $request->input->get('createTime' , false);                  //消息创建时间
		$pWhere['createTime >='] = $request->input->get('createTime' , false);                  //消息创建时间
		$pWhere['dealwithTime <='] = $request->input->get('dealwithTime' , false);                //消息处理时间
		$pWhere['dealwithTime >='] = $request->input->get('dealwithTime' , false);                //消息处理时间
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = GroupMessage::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
	
}