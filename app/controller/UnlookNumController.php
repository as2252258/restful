<?php
namespace app\controller;

use Code;
use Exception;
use app\model\UnlookNum;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class UnlookNumController
 *
 * @package Controller
 */
class UnlookNumController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new UnlookNum();
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //用户ID
            'mType'        => $request->input->integer('mType', false, [0,1]),            //消息类型 0.系统消息  1.群消息  2.好友消息
            'groupId'      => $request->input->integer('groupId', false, [0,11]),         //群ID
            'friendId'     => $request->input->integer('friendId', false, [0,20]),        //好友ID
            'num'          => $request->input->integer('num', false, [0,20]),             //未读数
            'modifyTime'   => $request->input->datetime('modifyTime', false, date('Y-m-d H:i:s')),//更新时间
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
		$model = UnlookNum::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //用户ID
            'mType'        => $request->input->integer('mType', false, [0,1]),            //消息类型 0.系统消息  1.群消息  2.好友消息
            'groupId'      => $request->input->integer('groupId', false, [0,11]),         //群ID
            'friendId'     => $request->input->integer('friendId', false, [0,20]),        //好友ID
            'num'          => $request->input->integer('num', false, [0,20]),             //未读数
            'modifyTime'   => $request->input->datetime('modifyTime', false, date('Y-m-d H:i:s')),//更新时间
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
        $model = UnlookNum::findOne($check);
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
		$model = UnlookNum::findOne($_key);
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
        $pWhere['userId']      = $request->input->get('userId', false);                      //用户ID
        $pWhere['mType']       = $request->input->get('mType', false);                       //消息类型 0.系统消息  1.群消息  2.好友消息
        $pWhere['groupId']     = $request->input->get('groupId', false);                     //群ID
        $pWhere['friendId']    = $request->input->get('friendId', false);                    //好友ID
        $pWhere['num']         = $request->input->get('num', false);                         //未读数
        $pWhere['modifyTime <='] = $request->input->get('modifyTime', false);                  //更新时间
        $pWhere['modifyTime >='] = $request->input->get('modifyTime', false);                  //更新时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = UnlookNum::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}