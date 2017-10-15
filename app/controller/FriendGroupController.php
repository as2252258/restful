<?php

namespace app\controller;

use app\components\ActiveController;
use app\model\Friend;
use app\model\FriendGroup;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class FriendGroupController
 *
 * @package Controller
 */
class FriendGroupController extends ActiveController
{
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new FriendGroup();
		$model->setBatch([
			'userId'     => $this->user->id ,          //用户ID
			'name'       => $request->input->string('name' , false , [0 , 50]) ,             //组名称
			'status'     => $request->input->integer('status' , false , [0 , 1]) ,           //状态
			'createTime' => time() ,      //
			'modifyTime' => time() ,      //
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
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
		$model = FriendGroup::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'userId'     => $request->input->integer('userId' , false , [0 , 20]) ,          //用户ID
			'name'       => $request->input->string('name' , false , [0 , 50]) ,             //组名称
			'status'     => $request->input->integer('status' , false , [0 , 1]) ,           //状态
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
		$model = FriendGroup::findOne($check);
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
		$trance = \Yoc::$app->db->beginTransaction();
		try {
			$_key = $request->input->integer('id' , true);
			$pass = $request->input->password('password' , true);
			if (empty($this->user) || strcmp(Str::encrypt($pass) , $this->user->password)) {
				throw new \Exception('密码错误');
			}
			$model = FriendGroup::findOne($_key);
			if (empty($model)) {
				throw new \Exception('数据不存在');
			}
			if (!$model->delete()) {
				return Response::analysis(Code::ERROR , $model->getLastError());
			}
			$users = Friend::where(['groupId' => $model->id])->all();
			if (!empty($users)) {
				$first = FriendGroup::where(['userId' => $this->user->id])->orderBy('createTime asc')->one();
				if (empty($first)) {
					$first = new FriendGroup();
					$first->name = '我的好友';
					$first->userId = $this->user->id;
					$first->createTime = time();
					$first->modifyTime = time();
					if (!$first->save()) {
						throw new Exception($first->getLastError());
					}
				}
				foreach ($users as $key => $val) {
					$val->groupId = $first->id;
					if (!$val->save()) {
						throw new Exception($val->getLastError());
					}
				}
			}
			$trance->commit();
			return Response::analysis(Code::SUCCESS , $model->toArray());
		} catch (Exception $exception) {
			$trance->rollback();
			return Response::analysis(Code::ERROR , $exception->getMessage());
		}
	}
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionList(Request $request)
	{
		$pWhere = [];
		$pWhere['userId'] = $this->user->id;                      //用户ID
		$pWhere['name'] = $request->input->get('name' , false);                        //组名称
		$pWhere['status'] = $request->input->get('status' , false);                      //状态
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
		$model = FriendGroup::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
	
}