<?php
namespace app\controller;

use Code;
use Exception;
use app\model\SmsVery;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class SmsVeryController
 *
 * @package Controller
 */
class SmsVeryController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new SmsVery();
		$model->setBatch([
            'telephone'        => $request->input->integer('telephone', false, [0,11]),       //
            'veryCode'         => $request->input->integer('veryCode', false, [0,6]),         //
            'sType'            => $request->input->integer('sType', false, [0,2]),            //短信类型 1.注册 2.修改密码 3.找回密码
            'createTime'       => $request->input->integer('createTime', false, [0,10]),      //
            'expirationTime'   => $request->input->integer('expirationTime', false, [0,10]),  //过期时间
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
		$model = SmsVery::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'telephone'        => $request->input->integer('telephone', false, [0,11]),       //
            'veryCode'         => $request->input->integer('veryCode', false, [0,6]),         //
            'sType'            => $request->input->integer('sType', false, [0,2]),            //短信类型 1.注册 2.修改密码 3.找回密码
            'createTime'       => $request->input->integer('createTime', false, [0,10]),      //
            'expirationTime'   => $request->input->integer('expirationTime', false, [0,10]),  //过期时间
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
        $model = SmsVery::findOne($check);
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
		$model = SmsVery::findOne($_key);
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
        $pWhere['telephone']       = $request->input->get('telephone', false);                   //
        $pWhere['veryCode']        = $request->input->get('veryCode', false);                    //
        $pWhere['sType']           = $request->input->get('sType', false);                       //短信类型 1.注册 2.修改密码 3.找回密码
        $pWhere['createTime']      = $request->input->get('createTime', false);                  //
        $pWhere['expirationTime']  = $request->input->get('expirationTime', false);              //过期时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = SmsVery::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}