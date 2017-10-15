<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Word;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class WordController
 *
 * @package Controller
 */
class WordController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Word();
		$model->setBatch([
            'title'        => $request->input->string('title', false, [0,100]),           //关键字
            'keyword'      => $request->input->string('keyword', false, [0,100]),         //替换结果
            'state'        => $request->input->integer('state', false, [0,1]),            //是否禁用 0.否 1.是
            'createTime'   => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//添加时间
            'modifyTime'   => $request->input->datetime('modifyTime', false, date('Y-m-d H:i:s')),//更新时间
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
		$model = Word::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'title'        => $request->input->string('title', false, [0,100]),           //关键字
            'keyword'      => $request->input->string('keyword', false, [0,100]),         //替换结果
            'state'        => $request->input->integer('state', false, [0,1]),            //是否禁用 0.否 1.是
            'createTime'   => $request->input->datetime('createTime', false, date('Y-m-d H:i:s')),//添加时间
            'modifyTime'   => $request->input->datetime('modifyTime', false, date('Y-m-d H:i:s')),//更新时间
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
        $model = Word::findOne($check);
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
		$model = Word::findOne($_key);
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
        $pWhere['title']       = $request->input->get('title', false);                       //关键字
        $pWhere['keyword']     = $request->input->get('keyword', false);                     //替换结果
        $pWhere['state']       = $request->input->get('state', false);                       //是否禁用 0.否 1.是
        $pWhere['createTime <='] = $request->input->get('createTime', false);                  //添加时间
        $pWhere['createTime >='] = $request->input->get('createTime', false);                  //添加时间
        $pWhere['modifyTime <='] = $request->input->get('modifyTime', false);                  //更新时间
        $pWhere['modifyTime >='] = $request->input->get('modifyTime', false);                  //更新时间
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Word::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}