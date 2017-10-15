<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Auth;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class AuthController
 *
 * @package Controller
 */
class AuthController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Auth();
		$model->setBatch([
            'module'       => $request->input->integer('module', false, [0,2]),           //模块 1.前台  2.个人中心  3.后台
            'alias'        => $request->input->string('alias', false, [0,50]),            //操作名称
            'controller'   => $request->input->string('controller', false, [0,30]),       //controller
            'action'       => $request->input->string('action', false, [0,30]),           //action
            'neeLogin'     => $request->input->integer('neeLogin', false, [0,1]),         //是否需要登录 0.不需要  1.需要
            'status'       => $request->input->integer('status', false, [0,2]),           //1.正常  2.待审  3.草稿   4.删除
            'addTime'      => $request->input->datetime('addTime', false, date('Y-m-d H:i:s')),//注册时间
            'modifyTime'   => $request->input->datetime('modifyTime', false, date('Y-m-d H:i:s')),//修改时间
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
		$model = Auth::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'module'       => $request->input->integer('module', false, [0,2]),           //模块 1.前台  2.个人中心  3.后台
            'alias'        => $request->input->string('alias', false, [0,50]),            //操作名称
            'controller'   => $request->input->string('controller', false, [0,30]),       //controller
            'action'       => $request->input->string('action', false, [0,30]),           //action
            'neeLogin'     => $request->input->integer('neeLogin', false, [0,1]),         //是否需要登录 0.不需要  1.需要
            'status'       => $request->input->integer('status', false, [0,2]),           //1.正常  2.待审  3.草稿   4.删除
            'addTime'      => $request->input->datetime('addTime', false, date('Y-m-d H:i:s')),//注册时间
            'modifyTime'   => $request->input->datetime('modifyTime', false, date('Y-m-d H:i:s')),//修改时间
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
        $model = Auth::findOne($check);
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
		$model = Auth::findOne($_key);
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
        $pWhere['module']      = $request->input->get('module', false);                      //模块 1.前台  2.个人中心  3.后台
        $pWhere['alias']       = $request->input->get('alias', false);                       //操作名称
        $pWhere['controller']  = $request->input->get('controller', false);                  //controller
        $pWhere['action']      = $request->input->get('action', false);                      //action
        $pWhere['neeLogin']    = $request->input->get('neeLogin', false);                    //是否需要登录 0.不需要  1.需要
        $pWhere['status']      = $request->input->get('status', false);                      //1.正常  2.待审  3.草稿   4.删除
        $pWhere['addTime <=']  = $request->input->get('addTime', false);                     //注册时间
        $pWhere['addTime >=']  = $request->input->get('addTime', false);                     //注册时间
        $pWhere['modifyTime <='] = $request->input->get('modifyTime', false);                  //修改时间
        $pWhere['modifyTime >='] = $request->input->get('modifyTime', false);                  //修改时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Auth::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if($count == 1){
	        $count = $model->count();
        }
		return Response::analysis(Code::SUCCESS,'success',$model->all(),$count);
    }

}