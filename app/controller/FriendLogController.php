<?php
namespace app\controller;

use Code;
use Exception;
use app\model\FriendLog;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class FriendLogController
 *
 * @package Controller
 */
class FriendLogController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new FriendLog();
		$model->setBatch([
            'userId'         => $request->input->integer('userId', true, [0,20]),           //消息接受用户
            'sendId'         => $request->input->integer('sendId', true, [0,20]),           //消息触发用户
            'logType'        => $request->input->integer('logType', false, [0,1]),          //1.个人消息  2.群消息
            'groupId'        => $request->input->integer('groupId', false, [0,11]),         //群ID
            'sendType'       => $request->input->integer('sendType', false, [0,1]),         //消息类型  1.好友添加  2.进群申请  3.踢出群  4.主动退群  5.添加管理员   6.撤销管理员  7.群升级消息   8.群解散消息
            'status'         => $request->input->integer('status', false, [0,1]),           //0.未读  1.已同意  2.已拒绝 3.忽略 4.已删除
            'createTime'     => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//消息创建时间
            'dealwithTime'   => $request->input->datetime('dealwithTime', false, date('Y-m-d H:i:s')),//消息处理时间
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
		$model = FriendLog::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'         => $request->input->integer('userId', true, [0,20]),           //消息接受用户
            'sendId'         => $request->input->integer('sendId', true, [0,20]),           //消息触发用户
            'logType'        => $request->input->integer('logType', false, [0,1]),          //1.个人消息  2.群消息
            'groupId'        => $request->input->integer('groupId', false, [0,11]),         //群ID
            'sendType'       => $request->input->integer('sendType', false, [0,1]),         //消息类型  1.好友添加  2.进群申请  3.踢出群  4.主动退群  5.添加管理员   6.撤销管理员  7.群升级消息   8.群解散消息
            'status'         => $request->input->integer('status', false, [0,1]),           //0.未读  1.已同意  2.已拒绝 3.忽略 4.已删除
            'createTime'     => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//消息创建时间
            'dealwithTime'   => $request->input->datetime('dealwithTime', false, date('Y-m-d H:i:s')),//消息处理时间
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
        $model = FriendLog::findOne($check);
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
		$model = FriendLog::findOne($_key);
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
        $pWhere['userId']        = $request->input->get('userId', false);                      //消息接受用户
        $pWhere['sendId']        = $request->input->get('sendId', false);                      //消息触发用户
        $pWhere['logType']       = $request->input->get('logType', false);                     //1.个人消息  2.群消息
        $pWhere['groupId']       = $request->input->get('groupId', false);                     //群ID
        $pWhere['sendType']      = $request->input->get('sendType', false);                    //消息类型  1.好友添加  2.进群申请  3.踢出群  4.主动退群  5.添加管理员   6.撤销管理员  7.群升级消息   8.群解散消息
        $pWhere['status']        = $request->input->get('status', false);                      //0.未读  1.已同意  2.已拒绝 3.忽略 4.已删除
        $pWhere['createTime <='] = $request->input->get('createTime', false);                  //消息创建时间
        $pWhere['createTime >='] = $request->input->get('createTime', false);                  //消息创建时间
        $pWhere['dealwithTime <='] = $request->input->get('dealwithTime', false);                //消息处理时间
        $pWhere['dealwithTime >='] = $request->input->get('dealwithTime', false);                //消息处理时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = FriendLog::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}