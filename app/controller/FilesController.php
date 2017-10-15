<?php

namespace app\controller;

use app\logic\UserLogic;
use Code;
use Exception;
use app\model\Files;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;

/**
 * Class FilesController
 *
 * @package Controller
 */
class FilesController extends ActiveController
{
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request)
	{
		$model = new Files();
		$model->setBatch([
			'cid'       => $request->input->integer('cid' , false , [0 , 20]) ,          //
			'file_md5'  => $request->input->string('file_md5' , false , [0 , 32]) ,         //唯一HASH值
			'file_name' => $request->input->string('file_name' , false , [0 , 255]) ,       //文件名称
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
		$model = Files::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
			'cid'       => $request->input->integer('cid' , false , [0 , 20]) ,          //
			'file_md5'  => $request->input->string('file_md5' , false , [0 , 32]) ,         //唯一HASH值
			'file_name' => $request->input->string('file_name' , false , [0 , 255]) ,       //文件名称
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
		$model = Files::findOne($check);
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
		$model = Files::findOne($_key);
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
		$pWhere['userId'] = $request->input->get('userId' , false);                      //
		$pWhere['file_md5'] = $request->input->get('file_md5' , false);                    //唯一HASH值
		$pWhere['file_name'] = $request->input->get('file_name' , false);                   //文件名称
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = Files::where($pWhere)->orderBy($order);
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
	public function actionUpload(Request $request)
	{
		if ($request->getRequestMethod() == 'options') {
			return Response::analysis(0);
		}
		$file = $request->input->array('data');
		if (empty($file['tmp_name'])) {
			return Response::analysis(-1 , 'not find file');
		}
		$name = $request->input->string('name');
		$total = $request->input->integer('total');
		$_md5 = $request->input->string('file_md5');
		$index = $request->input->integer('identifier');
		$tmp_dir = RUNTIME_PATH . '/tmp_file';
		if (!is_dir($tmp_dir)) {
			mkdir($tmp_dir);
		}
		$tmp_dir .= '/' . $_md5;
		if (!is_dir($tmp_dir)) {
			mkdir($tmp_dir);
		}
		$exec = $tmp_dir . '/' . $name;
		if (file_exists($exec)) {
			return Response::analysis(-200);
		}
		$newName = $name . '.part.' . ($index < 10 ? '0' . $index : $index);
		if (!file_exists($tmp_dir . '/' . $newName)) {
			move_uploaded_file($file['tmp_name'] , $tmp_dir . '/' . $newName);
			if (!file_exists($tmp_dir . '/' . $newName)) {
				return Response::analysis(-1 , '保存失败, 无权限操作');
			}
		}
		$count = count(glob($tmp_dir . '/*'));
		if ((int) $count == $total) {
			$data = $request->input->all();
			$this->asyncTask(UserLogic::className() , 'merge' , [array_merge($data , ['dir' => $tmp_dir])]);
			
//			$file = new Files();
//			$file->cid = $request->input->string('cid' , true);
//			$file->file_md5 = $_md5;
//			$file->file_name = $name;
//			$file->createTime = time();
//			$file->modifyTime = time();
//			$file->save();
			
			return Response::analysis(-200);
		}
		return Response::analysis(0);
	}
	
	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * 检查文件是否已上传过
	 */
	public function actionCheck(Request $request)
	{
		$md5 = $request->input->string('md5' , true);
		$file = $request->input->string('file' , true);
		$tmp_dir = RUNTIME_PATH . '/tmp_file';
		if (!is_dir($tmp_dir) || !is_dir($tmp_dir . '/' . $md5)) {
			return Response::analysis(-500);
		}
		if (!file_exists($tmp_dir . '/' . $md5 . '/' . $file)) {
			return Response::analysis(-500);
		}
		return Response::analysis(0);
	}
	
	public function actionDownload(Request $request)
	{
		$md5 = $request->input->get('token' , true);
		$tmp_dir = RUNTIME_PATH . '/tmp_file';
		if (!is_dir($tmp_dir) || !is_dir($tmp_dir . '/' . $md5)) {
			throw new Exception(404);
		}
		$files = glob($tmp_dir . '/' . $md5 . '/*');
		if (count($files) != 1) {
			throw new Exception('file  not upload all');
		}
		return \Yoc::$app->socket->download(array_shift($files));
	}
}