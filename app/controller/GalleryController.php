<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Gallery;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class GalleryController
 *
 * @package Controller
 */
class GalleryController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Gallery();
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,11]),          //用户ID
            'title'        => $request->input->string('title', false, [0,30]),            //相册名
            'discration'   => $request->input->string('discration', false, [0,255]),      //相册简介
            'frontCover'   => $request->input->string('frontCover', false, [0,255]),      //相册封面
            'publicAuth'   => $request->input->integer('publicAuth', false, [0,2]),       //相册权限 1.公开  2.需要密码  3.私有   4.指定用户可看
            'password'     => $request->input->string('password', false, [0,32]),         //相册密码
            'status'       => $request->input->integer('status', false, [0,1]),           //相册状态
            'createTime'   => $request->input->integer('createTime', false, [0,10]),      //创建时间
            'modifyTime'   => $request->input->integer('modifyTime', false, [0,10]),      //修改时间
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
		$model = Gallery::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'       => $request->input->integer('userId', false, [0,11]),          //用户ID
            'title'        => $request->input->string('title', false, [0,30]),            //相册名
            'discration'   => $request->input->string('discration', false, [0,255]),      //相册简介
            'frontCover'   => $request->input->string('frontCover', false, [0,255]),      //相册封面
            'publicAuth'   => $request->input->integer('publicAuth', false, [0,2]),       //相册权限 1.公开  2.需要密码  3.私有   4.指定用户可看
            'password'     => $request->input->string('password', false, [0,32]),         //相册密码
            'status'       => $request->input->integer('status', false, [0,1]),           //相册状态
            'createTime'   => $request->input->integer('createTime', false, [0,10]),      //创建时间
            'modifyTime'   => $request->input->integer('modifyTime', false, [0,10]),      //修改时间
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
        $model = Gallery::findOne($check);
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
		$model = Gallery::findOne($_key);
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
        $pWhere['userId']      = $request->input->get('userId', false);                      //用户ID
        $pWhere['title']       = $request->input->get('title', false);                       //相册名
        $pWhere['discration']  = $request->input->get('discration', false);                  //相册简介
        $pWhere['frontCover']  = $request->input->get('frontCover', false);                  //相册封面
        $pWhere['publicAuth']  = $request->input->get('publicAuth', false);                  //相册权限 1.公开  2.需要密码  3.私有   4.指定用户可看
        $pWhere['password']    = $request->input->get('password', false);                    //相册密码
        $pWhere['status']      = $request->input->get('status', false);                      //相册状态
        $pWhere['createTime']  = $request->input->get('createTime', false);                  //创建时间
        $pWhere['modifyTime']  = $request->input->get('modifyTime', false);                  //修改时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Gallery::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}