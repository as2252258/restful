<?php
namespace app\controller;

use Code;
use Exception;
use app\model\GroupBlack;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;

		
/**
 * Class GroupBlackController
 *
 * @package Controller
 */
class GroupBlackController extends ActiveController
{

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new GroupBlack();
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //被拉黑用户
            'groupId'      => $request->input->integer('groupId', false, [0,20]),         //群ID
            'sendId'       => $request->input->integer('sendId', false, [0,20]),          //操作用户
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
		$model = GroupBlack::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,20]),          //被拉黑用户
            'groupId'      => $request->input->integer('groupId', false, [0,20]),         //群ID
            'sendId'       => $request->input->integer('sendId', false, [0,20]),          //操作用户
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
        $model = GroupBlack::findOne($check);
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
		$model = GroupBlack::findOne($_key);
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
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionList(Request $request)
    {
        $pWhere = array();
        $pWhere['userId']      = $request->input->get('userId', false);                      //被拉黑用户
        $pWhere['groupId']     = $request->input->get('groupId', false);                     //群ID
        $pWhere['sendId']      = $request->input->get('sendId', false);                      //操作用户
        $pWhere['createTime']  = $request->input->get('createTime', false);                  //创建时间
        $pWhere['modifyTime']  = $request->input->get('modifyTime', false);                  //更新时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = GroupBlack::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }
    

}