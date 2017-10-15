<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Dynamic;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class DynamicController
 *
 * @package Controller
 */
class DynamicController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Dynamic();
		$model->setBatch([
            'uid'           => $request->input->integer('uid', false, [0,11]),             //用户ID
            'title'         => $request->input->string('title', false, [0,200]),           //标题
            'tag'           => $request->input->string('tag', false, [0,200]),             //标签
            'type'          => $request->input->integer('type', false, [0,11]),            //文章类型
            'intro'         => $request->input->string('intro', false, [0,500]),           //导读
            'content'       => $request->input->string('content', true, null),             //内容
            'view_count'    => $request->input->integer('view_count', false, [0,11]),      //浏览量
            'reply_count'   => $request->input->integer('reply_count', true, [0,11]),      //回复量
            'thumbnails'    => $request->input->string('thumbnails', false, [0,100]),      //封面图
            'is_hot'        => $request->input->integer('is_hot', false, [0,1]),           //是否热搜 0.否 1.是
            'is_top'        => $request->input->integer('is_top', false, [0,1]),           //是否置顶 0.否 1.是
            'status'        => $request->input->integer('status', false, [0,1]),           //文章状态
            'addTime'       => $request->input->integer('addTime', false, [0,10]),         //发布时间
            'upTime'        => $request->input->integer('upTime', false, [0,10]),          //更新时间
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
		$model = Dynamic::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'uid'           => $request->input->integer('uid', false, [0,11]),             //用户ID
            'title'         => $request->input->string('title', false, [0,200]),           //标题
            'tag'           => $request->input->string('tag', false, [0,200]),             //标签
            'type'          => $request->input->integer('type', false, [0,11]),            //文章类型
            'intro'         => $request->input->string('intro', false, [0,500]),           //导读
            'content'       => $request->input->string('content', true, null),             //内容
            'view_count'    => $request->input->integer('view_count', false, [0,11]),      //浏览量
            'reply_count'   => $request->input->integer('reply_count', true, [0,11]),      //回复量
            'thumbnails'    => $request->input->string('thumbnails', false, [0,100]),      //封面图
            'is_hot'        => $request->input->integer('is_hot', false, [0,1]),           //是否热搜 0.否 1.是
            'is_top'        => $request->input->integer('is_top', false, [0,1]),           //是否置顶 0.否 1.是
            'status'        => $request->input->integer('status', false, [0,1]),           //文章状态
            'addTime'       => $request->input->integer('addTime', false, [0,10]),         //发布时间
            'upTime'        => $request->input->integer('upTime', false, [0,10]),          //更新时间
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
        $model = Dynamic::findOne($check);
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
		$model = Dynamic::findOne($_key);
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
        $pWhere['uid']          = $request->input->get('uid', false);                         //用户ID
        $pWhere['title']        = $request->input->get('title', false);                       //标题
        $pWhere['tag']          = $request->input->get('tag', false);                         //标签
        $pWhere['type']         = $request->input->get('type', false);                        //文章类型
        $pWhere['intro']        = $request->input->get('intro', false);                       //导读
        $pWhere['content']      = $request->input->get('content', false);                     //内容
        $pWhere['view_count']   = $request->input->get('view_count', false);                  //浏览量
        $pWhere['reply_count']  = $request->input->get('reply_count', false);                 //回复量
        $pWhere['thumbnails']   = $request->input->get('thumbnails', false);                  //封面图
        $pWhere['is_hot']       = $request->input->get('is_hot', false);                      //是否热搜 0.否 1.是
        $pWhere['is_top']       = $request->input->get('is_top', false);                      //是否置顶 0.否 1.是
        $pWhere['status']       = $request->input->get('status', false);                      //文章状态
        $pWhere['addTime']      = $request->input->get('addTime', false);                     //发布时间
        $pWhere['upTime']       = $request->input->get('upTime', false);                      //更新时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Dynamic::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}