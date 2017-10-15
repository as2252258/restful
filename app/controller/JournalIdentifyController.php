<?php
namespace app\controller;

use Code;
use Exception;
use app\model\DiaryIdentify;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;

		
/**
 * Class JournalIdentifyController
 *
 * @package Controller
 */
class JournalIdentifyController extends ActiveController
{

    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new DiaryIdentify();
		$model->setBatch([
            'journalId'   => $request->input->integer('journalId', false, [0,11],0),     //日志ID
            'userId'      => $request->input->integer('userId', false, [0,20],0),        //用户ID
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
		$model = DiaryIdentify::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'journalId'   => $request->input->integer('journalId', false, [0,11],0),     //日志ID
            'userId'      => $request->input->integer('userId', false, [0,20],0),        //用户ID
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
        $model = DiaryIdentify::findOne($check);
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
		$model = DiaryIdentify::findOne($_key);
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
        $pWhere['journalId']  = $request->input->get('journalId', false);                   //日志ID
        $pWhere['userId']     = $request->input->get('userId', false);                      //用户ID
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = DiaryIdentify::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }
    

}