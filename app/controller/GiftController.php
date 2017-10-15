<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Gift;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class GiftController
 *
 * @package Controller
 */
class GiftController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Gift();
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //
            'sendId'       => $request->input->integer('sendId', false, [0,20]),          //
            'goodId'       => $request->input->integer('goodId', false, [0,10]),          //商品ID
            'goodName'     => $request->input->string('goodName', false, [0,100]),        //商品名称
            'goodIcon'     => $request->input->string('goodIcon', false, [0,100]),        //商品图片
            'goodDesc'     => $request->input->string('goodDesc', false, null),           //商品简介
            'number'       => $request->input->integer('number', false, [0,11]),          //
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
		$model = Gift::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //
            'sendId'       => $request->input->integer('sendId', false, [0,20]),          //
            'goodId'       => $request->input->integer('goodId', false, [0,10]),          //商品ID
            'goodName'     => $request->input->string('goodName', false, [0,100]),        //商品名称
            'goodIcon'     => $request->input->string('goodIcon', false, [0,100]),        //商品图片
            'goodDesc'     => $request->input->string('goodDesc', false, null),           //商品简介
            'number'       => $request->input->integer('number', false, [0,11]),          //
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
        $model = Gift::findOne($check);
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
		$model = Gift::findOne($_key);
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
        $pWhere['userId']      = $request->input->get('userId', false);                      //
        $pWhere['sendId']      = $request->input->get('sendId', false);                      //
        $pWhere['goodId']      = $request->input->get('goodId', false);                      //商品ID
        $pWhere['goodName']    = $request->input->get('goodName', false);                    //商品名称
        $pWhere['goodIcon']    = $request->input->get('goodIcon', false);                    //商品图片
        $pWhere['goodDesc']    = $request->input->get('goodDesc', false);                    //商品简介
        $pWhere['number']      = $request->input->get('number', false);                      //
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Gift::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}