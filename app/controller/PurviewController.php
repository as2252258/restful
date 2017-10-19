<?php

namespace app\controller;

use app\components\ActiveController;
use app\model\Packet;
use app\model\Purview;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;

/**
 * Class PurviewController
 *
 * @package Controller
 */
class PurviewController extends ActiveController
{
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new Purview();
		$model->setBatch([
			'itemName' => $request->input->string('itemName' , false , [0 , 30]) ,         //用户组名
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
		$model = Purview::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'itemName' => $request->input->string('itemName' , false , [0 , 30]) ,         //用户组名
		]);
		$model->afterSave(function ($model) {
			$primary = $model->id;
			$array = \Yoc::getRequest()->input->array('auth');
			if (empty($array)) {
				throw new Exception('用户组权限不能为空');
			}
			Packet::where(['itemId' => $primary])->delete();
			foreach ($array as $key => $val) {
				$_packet = new Packet();
				$_packet->itemId = $primary;
				$_packet->authId = $val;
				$_packet->addTime = date('Y-m-d H:i:s');
				$_packet->modifyTime = date('Y-m-d H:i:s');
				$_packet->save();
			}
		});
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
		$model = Purview::findOne($check);
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
		$model = Purview::findOne($_key);
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
		$pWhere['itemName'] = $request->input->get('itemName' , false);                    //用户组名
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = Purview::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		$model->isExtension(false);
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
	
}