<?php

namespace app\controller;

use app\logic\FriendLogic;
use app\logic\Log;
use app\logic\UserLogic;
use app\model\FriendGroup;
use app\model\Group;
use app\model\Message;
use app\model\SystemLog;
use app\model\User;
use Code;
use Exception;
use app\model\Friend;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;

/**
 * Class FriendController
 *
 * @package Controller
 */
class FriendController extends ActiveController
{
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new Friend();
		$model->setBatch([
			'cid'        => $request->input->integer('cid' , true , [0 , 20]) ,              //
			'userId'     => $request->input->integer('userId' , true , [0 , 20]) ,           //用户ID
			'groupId'    => $request->input->integer('groupId' , false , [0 , 11]) ,         //用户分组ID
			'friendId'   => $request->input->integer('friendId' , false , [0 , 20]) ,        //好友ID
			'remarks'    => $request->input->string('remarks' , false , [0 , 50]) ,          //好友备注
			'status'     => $request->input->integer('status' , false , [0 , 1]) ,           //1.正常  2.拉黑  3.删除
			'createTime' => $request->input->integer('createTime' , false , [0 , 10]) ,      //
			'modifyTime' => $request->input->integer('modifyTime' , false , [0 , 10]) ,      //
		]);
		$results = $model->save();
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
		$model = Friend::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'cid'        => $request->input->integer('cid' , false , [0 , 20]) ,              //
			'userId'     => $request->input->integer('userId' , false , [0 , 20]) ,           //用户ID
			'groupId'    => $request->input->integer('groupId' , false , [0 , 11]) ,         //用户分组ID
			'friendId'   => $request->input->integer('friendId' , false , [0 , 20]) ,        //好友ID
			'remarks'    => $request->input->string('remarks' , false , [0 , 50]) ,          //好友备注
			'status'     => $request->input->integer('status' , false , [0 , 1]) ,           //1.正常  2.拉黑  3.删除
			'createTime' => $request->input->integer('createTime' , false , [0 , 10]) ,      //
			'modifyTime' => $request->input->integer('modifyTime' , false , [0 , 10]) ,      //
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
		$model = Friend::findOne($check);
		if (empty($model)) {
			throw new Exception('Data Not Exists');
		}
		return Response::analysis(STATUS_SUCCESS , $model);
	}
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionDelete(Request $request)
	{
		$_key = $request->input->integer('id' , true);
		$cid = [$this->user->id , $_key];
		arsort($cid);
		$model = Friend::where(['cid' => implode('' , $cid)])->all();
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR);
		}
		swoole_timer_after(10 , function () use ($cid , $_key) {
			Message::where(['cid' => $cid])->delete();
			\Yoc::$app->fileCache->clearTale('xl_friend' , 'xl_friend_group');
			\Yoc::pushUser('Friend::delete' , $_key , [
				'userId'   => $_key ,
				'sendId'   => $this->user->id ,
				'nickname' => $this->user->nickname ,
				'avator'   => $this->user->avator
			]);
		});
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
		$pWhere['userId'] = $request->input->get('userId' , false);                      //用户ID
		$pWhere['groupId'] = $request->input->get('groupId' , false);                     //用户分组ID
		$pWhere['friendId'] = $request->input->get('friendId' , false);                    //好友ID
		$pWhere['remarks'] = $request->input->get('remarks' , false);                     //好友备注
		$pWhere['status'] = $request->input->get('status' , false);                      //1.正常  2.拉黑  3.删除
		$pWhere['createTime'] = $request->input->get('createTime' , false);                  //
		$pWhere['modifyTime'] = $request->input->get('modifyTime' , false);                  //
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = Friend::where($pWhere)->orderBy($order);
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
	public function actionLog(Request $request)
	{
		$data = [];
		$user = \Yoc::getRedis()->sMembers(LOG_HISTORY . $this->user->id);
		if (!empty($user)) {
			foreach ($user as $key => $val) {
				$explode = explode('_' , $val);
				if ($explode[0] == 1) {
					$_data = User::findOne($explode[1]);
				} else {
					$_data = Group::findOne($explode[1]);
				}
				if (empty($_data)) {
					continue;
				}
				$data[] = $_data->toArray();
			}
		}
		return Response::analysis(Code::SUCCESS , $data);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * 天津好友
	 */
	public function actionAddFriend(Request $request)
	{
		$user = $request->input->integer('userId' , true);
		$user = User::findOne($user);
		if (empty($user)) {
			return Response::analysis(-1 , '相关用户不存在');
		}
		$friend = Friend::where(['userId' => $this->user->id , 'friendId' => $user])->one();
		if (!empty($friend)) {
			return Response::analysis(-1 , '该用户已经是您的好友了');
		}
		$message = $user->nickname . '申请添加您为好友';
		$remarks = $request->input->string('remarks');
		$model = Log::recordAndNotify($user->id , $message , Log::CATE_FRIEND_ADD , $remarks , $this->user);
		\Yoc::pushUser('Common::loadNews' , $model->userId , [
			'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
			'sendId'   => $user->id ,
			'nickname' => $user->nickname ,
			'avator'   => $user->avator
		]);
		return Response::analysis(0 , $model);
	}
	
	public function actionIsFriend(Request $request)
	{
		$friend = Friend::where(['userId' => $this->user->id , 'friendId' => $request->input->get('id')])->one();
		if (empty($friend)) {
			return Response::analysis(-1);
		}
		return Response::analysis(0);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 */
	public function actionAudit(Request $request)
	{
		$trance = \Yoc::$app->db->beginTransaction();
		try {
			$id = $request->input->integer('id' , true);
			$status = $request->input->integer('status' , true);
			$systemLog = SystemLog::findOne($id);
			if (empty($systemLog)) {
				throw new Exception('消息已不存在');
			}
			if ($systemLog->status == 2 || $systemLog->status == 3) {
				throw new Exception('该消息可能已被处理');
			}
			$systemLog->results = $status;
			$systemLog->status = 2;
			if (!$systemLog->save()) {
				return Response::analysis(-1 , $systemLog->getLastError());
			}
			
			if ($systemLog->results == 1) {
				
				$cid = [$systemLog->userId , $systemLog->sendId];
				arsort($cid);
				$cid = implode('' , $cid);
				//审核人的
				$group = FriendGroup::where(['userId' => $this->user->id])->orderBy('id asc')->one();
				if (empty($group)) {
					$group = new FriendGroup();
					$group->userId = $this->user->id;
					$group->name = '我的好友';
					$group->createTime = time();
					$group->modifyTime = time();
					$group->save();
				}
				$friends = new Friend();
				$friends->userId = $this->user->id;
				$friends->cid = $cid;
				$friends->groupId = $group->id;
				$friends->friendId = $systemLog->sendId;
				$friends->createTime = time();
				$friends->modifyTime = time();
				if (!$friends->save()) {
					throw new Exception($friends->getLastError());
				}
				
				//申请人的
				$group = FriendGroup::where(['userId' => $systemLog->sendId])->orderBy('id asc')->one();
				if (empty($group)) {
					$group = new FriendGroup();
					$group->userId = $systemLog->sendId;
					$group->name = '我的好友';
					$group->createTime = time();
					$group->modifyTime = time();
					$group->save();
				}
				$_friend = new Friend();
				$_friend->userId = $systemLog->sendId;
				$_friend->cid = $cid;
				$_friend->groupId = $group->id;
				$_friend->friendId = $this->user->id;
				$_friend->createTime = time();
				$_friend->modifyTime = time();
				if (!$_friend->save()) {
					throw new Exception($_friend->getLastError());
				}
			}
			
			if ($systemLog->results == 1 || $systemLog->results == 2) {
				$this->asyncTask(FriendLogic::class , 'notify' , [$this->user , $systemLog]);
			}
			$trance->commit();
			return Response::analysis(0 , $systemLog);
		} catch (Exception $exception) {
			$trance->rollback();
			return Response::analysis(-1 , $exception->getMessage());
		}
	}
	
}