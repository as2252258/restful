<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Colloquy;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class ColloquyController
 *
 * @package Controller
 */
class ColloquyController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Colloquy();
		$model->setBatch([
            'groupId'      => $request->input->integer('groupId', false, [0,11]),         //群ID
            'userId'       => $request->input->integer('userId', false, [0,20]),          //消息接收人
            'friendId'     => $request->input->integer('friendId', false, [0,20]),        //消息发送人
            'mType'        => $request->input->integer('mType', false, [0,1]),            //消息类型
            'lastNews'     => $request->input->string('lastNews', false, null),           //最后一条消息内容
            'state'        => $request->input->integer('state', false, [0,1]),            //消息状态 0.已删除  1.正常  2.撤回  3.销毁
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionUpdate(Request $request){
		$model = Colloquy::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'groupId'      => $request->input->integer('groupId', false, [0,11]),         //群ID
            'userId'       => $request->input->integer('userId', false, [0,20]),          //消息接收人
            'friendId'     => $request->input->integer('friendId', false, [0,20]),        //消息发送人
            'mType'        => $request->input->integer('mType', false, [0,1]),            //消息类型
            'lastNews'     => $request->input->string('lastNews', false, null),           //最后一条消息内容
            'state'        => $request->input->integer('state', false, [0,1]),            //消息状态 0.已删除  1.正常  2.撤回  3.销毁
		]);
		$results = $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function actionDetail(Request $request){
        $check = $request->input->integer('id');
        if(empty($check)){
            throw new Exception('param id can not empty');
        }
        $model = Colloquy::findOne($check);
        if(empty($model)){
            throw new Exception('Data Not Exists');
        }
        return Response::analysis(STATUS_SUCCESS,$model);
    }

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function actionDelete(Request $request){
		$_key = $request->input->integer('id', true);
		$pass = $request->input->password('password', true);
		if(empty($this->user) || strcmp(Str::encrypt($pass), $this->user->password)){
		    throw new \Exception('密码错误');
        }
		$model = Colloquy::findOne($_key);
		if (empty($model)) {
		    throw new \Exception('数据不存在');
		}
        if(!$model->delete()){
            return Response::analysis(Code::ERROR, $model->getLastError());
        }
		return Response::analysis(Code::SUCCESS, 'delete success');
    }

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
    public function actionList(Request $request)
    {
        $pWhere = array();
        $pWhere['groupId']     = $request->input->get('groupId', false);                     //群ID
        $pWhere['userId']      = $request->input->get('userId', false);                      //消息接收人
        $pWhere['friendId']    = $request->input->get('friendId', false);                    //消息发送人
        $pWhere['mType']       = $request->input->get('mType', false);                       //消息类型
        $pWhere['lastNews']    = $request->input->get('lastNews', false);                    //最后一条消息内容
        $pWhere['state']       = $request->input->get('state', false);                       //消息状态 0.已删除  1.正常  2.撤回  3.销毁
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Colloquy::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}