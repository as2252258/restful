<?php
namespace app\controller;

use Code;
use Exception;
use app\model\UserSetting;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class UserSettingController
 *
 * @package Controller
 */
class UserSettingController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new UserSetting();
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //用户ID
            'sendKey'      => $request->input->integer('sendKey', false, [0,1]),          //消息发送设置 0.ctrl+enter  1.enter
            'state'        => $request->input->integer('state', false, [0,1]),            //0.失效  1.正常
            'createTime'   => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//创建时间
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
		$model = UserSetting::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //用户ID
            'sendKey'      => $request->input->integer('sendKey', false, [0,1]),          //消息发送设置 0.ctrl+enter  1.enter
            'state'        => $request->input->integer('state', false, [0,1]),            //0.失效  1.正常
            'createTime'   => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//创建时间
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
        $model = UserSetting::findOne($check);
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
		$model = UserSetting::findOne($_key);
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
        $pWhere['sendKey']     = $request->input->get('sendKey', false);                     //消息发送设置 0.ctrl+enter  1.enter
        $pWhere['state']       = $request->input->get('state', false);                       //0.失效  1.正常
        $pWhere['createTime <='] = $request->input->get('createTime', false);                  //创建时间
        $pWhere['createTime >='] = $request->input->get('createTime', false);                  //创建时间
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
	    $model = UserSetting::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}