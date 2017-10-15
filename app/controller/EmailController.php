<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Email;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class EmailController
 *
 * @package Controller
 */
class EmailController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Email();
		$model->setBatch([
            'fromId'        => $request->input->integer('fromId', true, [0,11]),           //邮件发送用户ID
            'fromName'      => $request->input->string('fromName', true, [0,100]),         //邮件发送用户名
            'fromUser'      => $request->input->string('fromUser', true, [0,255]),         //邮件发送用户邮箱
            'receiveId'     => $request->input->integer('receiveId', true, [0,11]),        //邮件接收用户ID
            'receiveName'   => $request->input->string('receiveName', true, [0,100]),      //邮件接收用户名
            'receiveUser'   => $request->input->string('receiveUser', true, [0,255]),      //邮件接收用户邮箱
            'title'         => $request->input->string('title', true, [0,255]),            //邮件标题
            'content'       => $request->input->string('content', true, null),             //邮件内容
            'addTime'       => $request->input->integer('addTime', false, [0,11]),         //
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionUpdate(Request $request){
		$model = Email::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'fromId'        => $request->input->integer('fromId', true, [0,11]),           //邮件发送用户ID
            'fromName'      => $request->input->string('fromName', true, [0,100]),         //邮件发送用户名
            'fromUser'      => $request->input->string('fromUser', true, [0,255]),         //邮件发送用户邮箱
            'receiveId'     => $request->input->integer('receiveId', true, [0,11]),        //邮件接收用户ID
            'receiveName'   => $request->input->string('receiveName', true, [0,100]),      //邮件接收用户名
            'receiveUser'   => $request->input->string('receiveUser', true, [0,255]),      //邮件接收用户邮箱
            'title'         => $request->input->string('title', true, [0,255]),            //邮件标题
            'content'       => $request->input->string('content', true, null),             //邮件内容
            'addTime'       => $request->input->integer('addTime', false, [0,11]),         //
		]);
		$results = $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
    public function actionDetail(Request $request){
        $check = $request->input->integer('id');
        if(empty($check)){
            throw new Exception('param id can not empty');
        }
        $model = Email::findOne($check);
        if(empty($model)){
            throw new Exception('Data Not Exists');
        }
        return Response::analysis(STATUS_SUCCESS,$model);
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
    public function actionDelete(Request $request){
		$_key = $request->input->integer('id', true);
		$pass = $request->input->password('password', true);
		if(empty($this->user) || strcmp(Str::encrypt($pass), $this->user->password)){
		    throw new \Exception('密码错误');
        }
		$model = Email::findOne($_key);
		if (empty($model)) {
		    throw new \Exception('数据不存在');
		}
        if(!$model->delete()){
            return Response::analysis(Code::ERROR, $model->getLastError());
        }
		return Response::analysis(Code::SUCCESS, 'delete success');
    }

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
    public function actionList(Request $request)
    {
        $pWhere = array();
        $pWhere['fromId']       = $request->input->get('fromId', false);                      //邮件发送用户ID
        $pWhere['fromName']     = $request->input->get('fromName', false);                    //邮件发送用户名
        $pWhere['fromUser']     = $request->input->get('fromUser', false);                    //邮件发送用户邮箱
        $pWhere['receiveId']    = $request->input->get('receiveId', false);                   //邮件接收用户ID
        $pWhere['receiveName']  = $request->input->get('receiveName', false);                 //邮件接收用户名
        $pWhere['receiveUser']  = $request->input->get('receiveUser', false);                 //邮件接收用户邮箱
        $pWhere['title']        = $request->input->get('title', false);                       //邮件标题
        $pWhere['content']      = $request->input->get('content', false);                     //邮件内容
        $pWhere['addTime']      = $request->input->get('addTime', false);                     //
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Email::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}