<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Process;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class ProcessController
 *
 * @package Controller
 */
class ProcessController extends ActiveController
{

	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Process();
		$model->setBatch([
            'name'         => $request->input->string('name', false, [0,100]),            //任务名称
            'onlyOne'      => $request->input->integer('onlyOne', false, [0,1]),          //是否一次性任务, 如果为一次性任务, 完成后删除
            'type'         => $request->input->integer('type', false, [0,1]),             //任务类型  1.执行shell命令  2.执行文件  3.访问某个路由
            'command'      => $request->input->string('command', false, [0,255]),         //命令，当type为1的时候为shell命令 为2的时候为文件路径   为3的时候为要请求的地址
            'param'        => $request->input->string('param', false, [0,255]),           //被执行时需要的参数
            'runNum'       => $request->input->integer('runNum', false, [0,11]),          //已被执行的次数
            'runTime'      => $request->input->integer('runTime', false, [0,10]),         //执行时间 执行间隔为分钟，至少1分钟，如果为空，则每分钟都执行
            'status'       => $request->input->integer('status', false, [0,1]),           //当前任务状态 0.不可执行  1.可执行
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
		$model = Process::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'name'         => $request->input->string('name', false, [0,100]),            //任务名称
            'onlyOne'      => $request->input->integer('onlyOne', false, [0,1]),          //是否一次性任务, 如果为一次性任务, 完成后删除
            'type'         => $request->input->integer('type', false, [0,1]),             //任务类型  1.执行shell命令  2.执行文件  3.访问某个路由
            'command'      => $request->input->string('command', false, [0,255]),         //命令，当type为1的时候为shell命令 为2的时候为文件路径   为3的时候为要请求的地址
            'param'        => $request->input->string('param', false, [0,255]),           //被执行时需要的参数
            'runNum'       => $request->input->integer('runNum', false, [0,11]),          //已被执行的次数
            'runTime'      => $request->input->integer('runTime', false, [0,10]),         //执行时间 执行间隔为分钟，至少1分钟，如果为空，则每分钟都执行
            'status'       => $request->input->integer('status', false, [0,1]),           //当前任务状态 0.不可执行  1.可执行
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
        $model = Process::findOne($check);
        if(empty($model)){
            throw new Exception('Data Not Exists');
        }
        return Response::analysis(STATUS_SUCCESS,$model);
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
		$model = Process::findOne($_key);
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
        $pWhere['name']        = $request->input->get('name', false);                        //任务名称
        $pWhere['onlyOne']     = $request->input->get('onlyOne', false);                     //是否一次性任务, 如果为一次性任务, 完成后删除
        $pWhere['type']        = $request->input->get('type', false);                        //任务类型  1.执行shell命令  2.执行文件  3.访问某个路由
        $pWhere['command']     = $request->input->get('command', false);                     //命令，当type为1的时候为shell命令 为2的时候为文件路径   为3的时候为要请求的地址
        $pWhere['param']       = $request->input->get('param', false);                       //被执行时需要的参数
        $pWhere['runNum']      = $request->input->get('runNum', false);                      //已被执行的次数
        $pWhere['runTime']     = $request->input->get('runTime', false);                     //执行时间 执行间隔为分钟，至少1分钟，如果为空，则每分钟都执行
        $pWhere['status']      = $request->input->get('status', false);                      //当前任务状态 0.不可执行  1.可执行
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
	    $model = Process::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}