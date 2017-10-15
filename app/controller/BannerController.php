<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Banner;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class BannerController
 *
 * @package Controller
 */
class BannerController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Banner();
		$results = $model->load() && $model->save();
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
		$model = Banner::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$results = $model->load() && $model->save();
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
        $model = Banner::findOne($check);
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
		$model = Banner::findOne($_key);
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
        $pWhere['title']    = $request->input->get('title', false);                       //标题
        $pWhere['url']      = $request->input->get('url', false);                         //链接
        $pWhere['imgKey']   = $request->input->get('imgKey', false);                      //图片
        $pWhere['status']   = $request->input->get('status', false);                      //状态   1.可用  2.禁用
        $pWhere['addTime']  = $request->input->get('addTime', false);                     //添加时间
        $pWhere['type']     = $request->input->get('type', false);                        //广告附属  默认0
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Banner::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}