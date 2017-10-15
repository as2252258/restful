<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Blacklist;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class BlacklistController
 *
 * @package Controller
 */
class BlacklistController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Blacklist();
		$model->setBatch([
            'ip'        => $request->input->string('ip', false, [0,200]),              //
            'url'       => $request->input->string('url', false, null),                //
            'name'      => $request->input->string('name', false, [0,200]),            //
            'type'      => $request->input->string('type', false, [0,50]),             //
            'status'    => $request->input->integer('status', false, [0,1]),           //
            'addTime'   => $request->input->integer('addTime', false, [0,10]),         //
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
		$model = Blacklist::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'ip'        => $request->input->string('ip', false, [0,200]),              //
            'url'       => $request->input->string('url', false, null),                //
            'name'      => $request->input->string('name', false, [0,200]),            //
            'type'      => $request->input->string('type', false, [0,50]),             //
            'status'    => $request->input->integer('status', false, [0,1]),           //
            'addTime'   => $request->input->integer('addTime', false, [0,10]),         //
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
        $model = Blacklist::findOne($check);
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
		$model = Blacklist::findOne($_key);
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
        $pWhere['ip']       = $request->input->get('ip', false);                          //
        $pWhere['url']      = $request->input->get('url', false);                         //
        $pWhere['name']     = $request->input->get('name', false);                        //
        $pWhere['type']     = $request->input->get('type', false);                        //
        $pWhere['status']   = $request->input->get('status', false);                      //
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
	    $model = Blacklist::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}