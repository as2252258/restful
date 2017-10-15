<?php
namespace app\controller;

use Code;
use Exception;
use app\model\SystemLog;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;


/**
 * Class SystemLogController
 *
 * @package Controller
 */
class SystemLogController extends ActiveController
{

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new SystemLog();
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //用户ID
            'sendId'       => $request->input->integer('sendId', false, [0,20]),          //发送人  系统消息为空
            'category'     => $request->input->integer('category', false, [0,1]),         //消息类型
            'message'      => $request->input->string('message', false, [0,100]),         //消息内容
            'remarks'      => $request->input->string('remarks', false, [0,100]),         //备注内容
            'results'      => $request->input->integer('results', false, [0,1]),          //处理结果  1.同意， 2.拒绝  3.忽略  4.删除
            'status'       => $request->input->integer('status', false, [0,1]),           //0.待处理 1.正常 2.已处理 3.已删除
            'createTime'   => $request->input->integer('createTime', false, [0,10]),      //创建时间
            'modifyTime'   => $request->input->integer('modifyTime', false, [0,10]),      //更新时间
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
		$model = SystemLog::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //用户ID
            'sendId'       => $request->input->integer('sendId', false, [0,20]),          //发送人  系统消息为空
            'category'     => $request->input->integer('category', false, [0,1]),         //消息类型
            'message'      => $request->input->string('message', false, [0,100]),         //消息内容
            'remarks'      => $request->input->string('remarks', false, [0,100]),         //备注内容
            'results'      => $request->input->integer('results', false, [0,1]),          //处理结果  1.同意， 2.拒绝  3.忽略  4.删除
            'status'       => $request->input->integer('status', false, [0,1]),           //0.待处理 1.正常 2.已处理 3.已删除
            'createTime'   => $request->input->integer('createTime', false, [0,10]),      //创建时间
            'modifyTime'   => $request->input->integer('modifyTime', false, [0,10]),      //更新时间
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
        $model = SystemLog::findOne($check);
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
//		$pass = $request->input->password('password', true);
//		if(empty($this->user) || strcmp(Str::encrypt($pass), $this->user->password)){
//		    throw new \Exception('密码错误');
//        }
		$model = SystemLog::findOne($_key);
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
        $pWhere['userId']      = $this->user->id;                      //用户ID
        $pWhere['sendId']      = $request->input->get('sendId', false);                      //发送人  系统消息为空
        $pWhere['category']    = $request->input->get('category', false);                    //消息类型
        $pWhere['message']     = $request->input->get('message', false);                     //消息内容
        $pWhere['remarks']     = $request->input->get('remarks', false);                     //备注内容
        $pWhere['results']     = $request->input->get('results', false);                     //处理结果  1.同意， 2.拒绝  3.忽略  4.删除
        $pWhere['status']      = $request->input->get('status', false);                      //0.待处理 1.正常 2.已处理 3.已删除
        $pWhere['createTime >=']  = $request->input->get('startTime', false);                  //创建时间
        $pWhere['createTime <=']  = $request->input->get('endTime', false);                  //创建时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = SystemLog::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }
    

}