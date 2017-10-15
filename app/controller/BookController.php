<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Book;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class BookController
 *
 * @package Controller
 */
class BookController extends ActiveController
{

	/**
	 * @param \yoc\http\Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function actionAdd(Request $request){
		$model = new Book();
		$model->setBatch([
            'userId'        => $request->input->integer('userId', false, [0,20]),          //用户ID
            'title'         => $request->input->string('title', false, [0,255]),           //标题
            'mood'          => $request->input->integer('mood', false, [0,1]),             //心情
            'toDay'         => $request->input->integer('toDay', false, [0,10]),           //所属日期
            'description'   => $request->input->string('description', false, null),        //日记内容
            'state'         => $request->input->integer('state', false, [0,1]),            //日记状态 0.正常 1.销毁
            'createTime'    => $request->input->integer('createTime', false, [0,10]),      //发布日期
            'modifyTime'    => $request->input->integer('modifyTime', false, [0,10]),      //更新日期
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
		$model = Book::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'userId'        => $request->input->integer('userId', false, [0,20]),          //用户ID
            'title'         => $request->input->string('title', false, [0,255]),           //标题
            'mood'          => $request->input->integer('mood', false, [0,1]),             //心情
            'toDay'         => $request->input->integer('toDay', false, [0,10]),           //所属日期
            'description'   => $request->input->string('description', false, null),        //日记内容
            'state'         => $request->input->integer('state', false, [0,1]),            //日记状态 0.正常 1.销毁
            'createTime'    => $request->input->integer('createTime', false, [0,10]),      //发布日期
            'modifyTime'    => $request->input->integer('modifyTime', false, [0,10]),      //更新日期
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
        $model = Book::findOne($check);
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
		$model = Book::findOne($_key);
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
        $pWhere['userId']       = $request->input->get('userId', false);                      //用户ID
        $pWhere['title']        = $request->input->get('title', false);                       //标题
        $pWhere['mood']         = $request->input->get('mood', false);                        //心情
        $pWhere['toDay']        = $request->input->get('toDay', false);                       //所属日期
        $pWhere['description']  = $request->input->get('description', false);                 //日记内容
        $pWhere['state']        = $request->input->get('state', false);                       //日记状态 0.正常 1.销毁
        $pWhere['createTime']   = $request->input->get('createTime', false);                  //发布日期
        $pWhere['modifyTime']   = $request->input->get('modifyTime', false);                  //更新日期
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Book::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}