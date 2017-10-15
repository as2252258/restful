<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Albums;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class AlbumsController
 *
 * @package Controller
 */
class AlbumsController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Albums();
		$model->setBatch([
            'uid'       => $request->input->integer('uid', false, [0,11]),             //用户ID
            'tid'       => $request->input->integer('tid', false, [0,11]),             //分类
            'tName'     => $request->input->string('tName', false, [0,50]),            //分类名称
            'title'     => $request->input->string('title', false, [0,100]),           //专辑名称
            'tag'       => $request->input->string('tag', false, [0,100]),             //专辑标签
            'aDesc'     => $request->input->string('aDesc', false, [0,300]),           //专辑简介
            'status'    => $request->input->integer('status', false, [0,1]),           //专辑状态
            'addTime'   => $request->input->integer('addTime', false, [0,10]),         //添加时间
            'upTime'    => $request->input->integer('upTime', false, [0,10]),          //更新时间
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
		$model = Albums::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'uid'       => $request->input->integer('uid', false, [0,11]),             //用户ID
            'tid'       => $request->input->integer('tid', false, [0,11]),             //分类
            'tName'     => $request->input->string('tName', false, [0,50]),            //分类名称
            'title'     => $request->input->string('title', false, [0,100]),           //专辑名称
            'tag'       => $request->input->string('tag', false, [0,100]),             //专辑标签
            'aDesc'     => $request->input->string('aDesc', false, [0,300]),           //专辑简介
            'status'    => $request->input->integer('status', false, [0,1]),           //专辑状态
            'addTime'   => $request->input->integer('addTime', false, [0,10]),         //添加时间
            'upTime'    => $request->input->integer('upTime', false, [0,10]),          //更新时间
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
        $model = Albums::findOne($check);
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
		$model = Albums::findOne($_key);
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
        $pWhere['uid']      = $request->input->get('uid', false);                         //用户ID
        $pWhere['tid']      = $request->input->get('tid', false);                         //分类
        $pWhere['tName']    = $request->input->get('tName', false);                       //分类名称
        $pWhere['title']    = $request->input->get('title', false);                       //专辑名称
        $pWhere['tag']      = $request->input->get('tag', false);                         //专辑标签
        $pWhere['aDesc']    = $request->input->get('aDesc', false);                       //专辑简介
        $pWhere['status']   = $request->input->get('status', false);                      //专辑状态
        $pWhere['addTime']  = $request->input->get('addTime', false);                     //添加时间
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
	    $model = Albums::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}