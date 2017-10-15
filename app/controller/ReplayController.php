<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Replay;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;

		
/**
 * Class ReplayController
 *
 * @package Controller
 */
class ReplayController extends ActiveController
{

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Replay();
		$model->setBatch([
            'type'      => $request->input->integer('type', false, [0,1],0),           //消息类型
            'tid'       => $request->input->integer('tid', false, [0,11],0),           //回复ID
            'vid'       => $request->input->integer('vid', false, [0,11],0),           //视频ID
            'uid'       => $request->input->integer('uid', false, [0,11],0),           //发送用户
            'fid'       => $request->input->integer('fid', false, [0,11],0),           //接收用户
            'fName'     => $request->input->string('fName', false, [0,100]),           //接收用户ID
            'title'     => $request->input->string('title', false, [0,100]),           //消息标题
            'content'   => $request->input->string('content', true, null),             //回复内容
            'up'        => $request->input->integer('up', false, [0,11],0),            //支持
            'down'      => $request->input->integer('down', false, [0,11],0),          //反对
            'share'     => $request->input->integer('share', false, [0,11],0),         //分享
            'commont'   => $request->input->integer('commont', false, [0,11],0),       //回复
            'status'    => $request->input->integer('status', false, [0,1],0),         //状态
            'addTime'   => $request->input->integer('addTime', false, [0,10], time()), //
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionUpdate(Request $request){
		$model = Replay::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'type'      => $request->input->integer('type', false, [0,1],0),           //消息类型
            'tid'       => $request->input->integer('tid', false, [0,11],0),           //回复ID
            'vid'       => $request->input->integer('vid', false, [0,11],0),           //视频ID
            'uid'       => $request->input->integer('uid', false, [0,11],0),           //发送用户
            'fid'       => $request->input->integer('fid', false, [0,11],0),           //接收用户
            'fName'     => $request->input->string('fName', false, [0,100]),           //接收用户ID
            'title'     => $request->input->string('title', false, [0,100]),           //消息标题
            'content'   => $request->input->string('content', true, null),             //回复内容
            'up'        => $request->input->integer('up', false, [0,11],0),            //支持
            'down'      => $request->input->integer('down', false, [0,11],0),          //反对
            'share'     => $request->input->integer('share', false, [0,11],0),         //分享
            'commont'   => $request->input->integer('commont', false, [0,11],0),       //回复
            'status'    => $request->input->integer('status', false, [0,1],0),         //状态
            'addTime'   => $request->input->integer('addTime', false, [0,10], time()), //
		]);
		$results = $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionDetail(Request $request){
        $check = $request->input->integer('id');
        if(empty($check)){
            throw new Exception('param id can not empty');
        }
        $model = Replay::findOne($check);
        if(empty($model)){
            throw new Exception('Data Not Exists');
        }
        return Response::analysis(Code::SUCCESS, $model);
    }

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionDelete(Request $request){
		$_key = $request->input->integer('id', true);
		$pass = $request->input->password('password', true);
		if(empty($this->user) || strcmp(Str::encrypt($pass), $this->user->password)){
		    throw new \Exception('密码错误');
        }
		$model = Replay::findOne($_key);
		if (empty($model)) {
		    throw new \Exception('数据不存在');
		}
        if(!$model->delete()){
            return Response::analysis(Code::ERROR, $model->getLastError());
        }
		return Response::analysis(Code::SUCCESS, $model);
    }

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionList(Request $request)
    {
        $pWhere = array();
        $pWhere['type']     = $request->input->get('type', false);                        //消息类型
        $pWhere['tid']      = $request->input->get('tid', false);                         //回复ID
        $pWhere['vid']      = $request->input->get('vid', false);                         //视频ID
        $pWhere['uid']      = $request->input->get('uid', false);                         //发送用户
        $pWhere['fid']      = $request->input->get('fid', false);                         //接收用户
        $pWhere['fName']    = $request->input->get('fName', false);                       //接收用户ID
        $pWhere['title']    = $request->input->get('title', false);                       //消息标题
        $pWhere['content']  = $request->input->get('content', false);                     //回复内容
        $pWhere['up']       = $request->input->get('up', false);                          //支持
        $pWhere['down']     = $request->input->get('down', false);                        //反对
        $pWhere['share']    = $request->input->get('share', false);                       //分享
        $pWhere['commont']  = $request->input->get('commont', false);                     //回复
        $pWhere['status']   = $request->input->get('status', false);                      //状态
        $pWhere['addTime']  = $request->input->get('addTime', false);                     //
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Replay::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }
    

}