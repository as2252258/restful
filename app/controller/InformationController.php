<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Information;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class InformationController
 *
 * @package Controller
 */
class InformationController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Information();
		$model->setBatch([
            'fid'       => $request->input->integer('fid', false, [0,11]),             //消息发送者
            'jid'       => $request->input->integer('jid', false, [0,11]),             //消息接受者 如果为0则为系统消息
            'type'      => $request->input->integer('type', false, [0,11]),            //消息类型
            'title'     => $request->input->string('title', false, [0,200]),           //标题
            'cont'      => $request->input->string('cont', true, null),                //内容
            'status'    => $request->input->integer('status', false, [0,1]),           //消息状态 0.未读  1.已查看
            'contact'   => $request->input->string('contact', false, [0,50]),          //联系方式
            'addTime'   => $request->input->integer('addTime', false, [0,10]),         //发送时间
            'upTime'    => $request->input->integer('upTime', false, [0,10]),          //更新时间
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
		$model = Information::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'fid'       => $request->input->integer('fid', false, [0,11]),             //消息发送者
            'jid'       => $request->input->integer('jid', false, [0,11]),             //消息接受者 如果为0则为系统消息
            'type'      => $request->input->integer('type', false, [0,11]),            //消息类型
            'title'     => $request->input->string('title', false, [0,200]),           //标题
            'cont'      => $request->input->string('cont', true, null),                //内容
            'status'    => $request->input->integer('status', false, [0,1]),           //消息状态 0.未读  1.已查看
            'contact'   => $request->input->string('contact', false, [0,50]),          //联系方式
            'addTime'   => $request->input->integer('addTime', false, [0,10]),         //发送时间
            'upTime'    => $request->input->integer('upTime', false, [0,10]),          //更新时间
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
        $model = Information::findOne($check);
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
		$model = Information::findOne($_key);
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
        $pWhere['fid']      = $request->input->get('fid', false);                         //消息发送者
        $pWhere['jid']      = $request->input->get('jid', false);                         //消息接受者 如果为0则为系统消息
        $pWhere['type']     = $request->input->get('type', false);                        //消息类型
        $pWhere['title']    = $request->input->get('title', false);                       //标题
        $pWhere['cont']     = $request->input->get('cont', false);                        //内容
        $pWhere['status']   = $request->input->get('status', false);                      //消息状态 0.未读  1.已查看
        $pWhere['contact']  = $request->input->get('contact', false);                     //联系方式
        $pWhere['addTime']  = $request->input->get('addTime', false);                     //发送时间
        $pWhere['upTime']   = $request->input->get('upTime', false);                      //更新时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Information::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}