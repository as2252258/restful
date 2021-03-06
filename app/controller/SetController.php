<?php

namespace app\controller;

use app\components\ActiveController;
use app\model\Set;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class SetController
 *
 * @package Controller
 */
class SetController extends ActiveController
{
	
	/**
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new Set();
		$model->setBatch([
			'pid'        => $request->input->integer('pid' , false , [0 , 11]) ,             //
			'path'       => $request->input->string('path' , true , [0 , 255]) ,             //
			'name'       => $request->input->string('name' , false , [0 , 40]) ,             //
			'url'        => $request->input->string('url' , false , [0 , 200]) ,             //
			'display'    => $request->input->integer('display' , false , [0 , 11]) ,         //
			'addTime'    => $request->input->integer('addTime' , false , [0 , 11]) ,         //
			'updateTime' => $request->input->integer('updateTime' , false , [0 , 11]) ,      //
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		$list = Set::where(['pid' => $model->id])->all();
		
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
		$model = Set::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'pid'        => $request->input->integer('pid' , false , [0 , 11]) ,             //
			'path'       => $request->input->string('path' , true , [0 , 255]) ,             //
			'name'       => $request->input->string('name' , false , [0 , 40]) ,             //
			'url'        => $request->input->string('url' , false , [0 , 200]) ,             //
			'display'    => $request->input->integer('display' , false , [0 , 11]) ,         //
			'addTime'    => $request->input->integer('addTime' , false , [0 , 11]) ,         //
			'updateTime' => $request->input->integer('updateTime' , false , [0 , 11]) ,      //
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
		$model = Set::findOne($check);
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
		$pass = $request->input->password('password' , true);
		if (empty($this->user) || strcmp(Str::encrypt($pass) , $this->user->password)) {
			throw new \Exception('密码错误');
		}
		$model = Set::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		return Response::analysis(Code::SUCCESS , 'delete success');
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
		$pWhere['pid'] = $request->input->get('pid' , false);                         //
		$pWhere['path'] = $request->input->get('path' , false);                        //
		$pWhere['name'] = $request->input->get('name' , false);                        //
		$pWhere['url'] = $request->input->get('url' , false);                         //
		$pWhere['display'] = $request->input->get('display' , false);                     //
		$pWhere['addTime'] = $request->input->get('addTime' , false);                     //
		$pWhere['updateTime'] = $request->input->get('updateTime' , false);                  //
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = Set::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		$model->isExtension(false);
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
	
	public function actionSort(Request $request)
	{
		$editId = $request->input->integer('editId' , true);
		$affect = $request->input->integer('affectId' , true);
		$upOrDown = $request->input->integer('isUp' , true);
		
		$begin = \Yoc::$app->db->beginTransaction();
		
		$edit = Set::findOne($editId);
		$affect = Set::findOne($affect);
		if (empty($edit) || empty($affect)) {
			return Response::analysis(Code::DATA_EMPTY);
		}
		if ($upOrDown == 1) {
			$edit->display -= 1;
			$affect->display += 1;
		} else {
			$edit->display += 1;
			$affect->display -= 1;
		}
		if (!$edit->save() || !$affect->save()) {
			$begin->rollback();
			return Response::analysis(Code::ERROR , $affect->getLastError() ?? $edit->getLastError());
		}
		$begin->commit();
		return Response::analysis(0);
	}
}