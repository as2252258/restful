<?php
namespace app\controller;

use Code;
use Exception;
use app\model\MessageLook;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class MessageLookController
 *
 * @package Controller
 */
class MessageLookController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new MessageLook();
		$model->setBatch([
            'groupId'      => $request->input->integer('groupId', false, [0,11]),         //群ID
            'userId'       => $request->input->integer('userId', false, [0,11]),          //用户ID
            'friendId'     => $request->input->integer('friendId', false, [0,11]),        //好友ID
            'createTime'   => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//查看时间
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
		$model = MessageLook::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'groupId'      => $request->input->integer('groupId', false, [0,11]),         //群ID
            'userId'       => $request->input->integer('userId', false, [0,11]),          //用户ID
            'friendId'     => $request->input->integer('friendId', false, [0,11]),        //好友ID
            'createTime'   => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//查看时间
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
        $model = MessageLook::findOne($check);
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
		$model = MessageLook::findOne($_key);
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
        $pWhere['groupId']     = $request->input->get('groupId', false);                     //群ID
        $pWhere['userId']      = $request->input->get('userId', false);                      //用户ID
        $pWhere['friendId']    = $request->input->get('friendId', false);                    //好友ID
        $pWhere['createTime <='] = $request->input->get('createTime', false);                  //查看时间
        $pWhere['createTime >='] = $request->input->get('createTime', false);                  //查看时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = MessageLook::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}