<?php

namespace app\controller;

use app\components\ActiveController;
use app\logic\FriendLogic;
use app\logic\UserLogic;
use app\model\Friend;
use app\model\Message;
use Code;
use Exception;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class MessageController
 *
 * @package Controller
 */
class MessageController extends ActiveController
{
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new Message();
		$model->setBatch([
			'sendId'       => $this->user->id ,          //消息触发用户
			'cid'          => $request->input->integer('cid' , false , [0 , 20]) ,             //
			'userId'       => $request->input->integer('userId' , true , [0 , 20]) ,          //消息接受用户
			'content'      => $request->input->string('content' , false , null) ,            //消息内容
			'status'       => $request->input->integer('status' , false , [0 , 1] , 0) ,           //0.未读  1.已读  2.屏蔽 3.忽略 4.删除
			'createTime'   => $request->input->datetime('createTime' , false , date('Y-m-d H:i:s')) ,//消息创建时间
			'dealwithTime' => $request->input->datetime('dealwithTime' , false , date('Y-m-d H:i:s')) ,//消息处理时间
		]);
		if (empty($model->cid) && !is_numeric($model->cid)) {
			$cid = [$this->user->id , $model->userId];
			arsort($cid , SORT_ASC);
			$model->setAttribute('cid' , implode('' , $cid));
		}
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		$select = Friend::where(['userId' => $model->userId , 'friendId' => $this->user->id])->one();
		if ($select->status != 4 && $select->status != 2) {
			$this->asyncTask(UserLogic::className() , 'message' , [$this->user , $model]);
		}
		return Response::analysis(Code::SUCCESS , $model->toArray());
	}
	
	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionUpdate(Request $request)
	{
		$model = Message::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'cid'          => $request->input->integer('cid' , false , [0 , 20]) ,             //
			'userId'       => $request->input->integer('userId' , false , [0 , 20]) ,          //消息接受用户
			'sendId'       => $request->input->integer('sendId' , false , [0 , 20]) ,          //消息触发用户
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
		$model = Message::findOne($check);
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
	public function actionWithdraw(Request $request)
	{
		$_key = $request->input->integer('id' , true);
		$model = Message::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (time() > strtotime($model->createTime) + 5 * 60) {
			return Response::analysis(Code::ERROR , '该消息发送时间已超过5分钟，无法撤回');
		}
		$model->status = 5;
		if (!$model->save()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		$this->asyncTask(FriendLogic::className() , 'messageRollback' , [$model , $this->user]);
		return Response::analysis(Code::SUCCESS , 'delete success');
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
		$model = Message::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		$this->asyncTask(FriendLogic::className() , 'messageDelete' , [$model , $this->user]);
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
		$pWhere['cid'] = $request->input->get('cid' , false);                         //
		$pWhere['userId'] = $request->input->get('userId' , false);                      //消息接受用户
		$pWhere['sendId'] = $request->input->get('sendId' , false);                      //消息触发用户
		$pWhere['content'] = $request->input->get('content' , false);                     //消息内容
		$pWhere['status'] = $request->input->get('status' , false);                      //0.未读  1.已读  2.屏蔽 3.忽略 4.删除 5.撤回
		$pWhere['createTime <='] = $request->input->get('createTime' , false);                  //消息创建时间
		$pWhere['createTime >='] = $request->input->get('createTime' , false);                  //消息创建时间
		$pWhere['dealwithTime <='] = $request->input->get('dealwithTime' , false);                //消息处理时间
		$pWhere['dealwithTime >='] = $request->input->get('dealwithTime' , false);                //消息处理时间
		
		if ($request->input->get('id')) {
			$_cid = [$this->user->id , $request->input->get('id')];
			arsort($_cid);
			$pWhere['cid'] = implode('' , $_cid);
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
		$model = Message::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
	
}