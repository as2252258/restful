<?php
namespace app\controller;

use Code;
use Exception;
use app\model\Goods;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
		
/**
 * Class GoodsController
 *
 * @package Controller
 */
class GoodsController extends ActiveController
{

	/**
	 * @param Request $request
	 * @param Response $response
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new Goods();
		$model->setBatch([
            'title'         => $request->input->string('title', false, [0,100]),           //商品名称
            'description'   => $request->input->string('description', false, null),        //商品描述
            'price'         => $request->input->integer('price', false, [0,10]),           //商品单价
            'icon'          => $request->input->string('icon', false, [0,100]),            //商品图片
            'is_act'        => $request->input->integer('is_act', false, [0,1]),           //是否参加活动
            'is_top'        => $request->input->integer('is_top', false, [0,1]),           //是否置顶
            'display'       => $request->input->integer('display', false, [0,11]),         //商品排序
            'total'         => $request->input->integer('total', false, [0,11]),           //商品库存 -1无限多  否则就是填写数量
            'scale'         => $request->input->integer('scale', false, [0,11]),           //精度
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
		$model = Goods::findOne($request->input->integer('id'));
		if (empty($model)) {
			throw new \Exception('指定数据不存在');
		}
		$model->setBatch([
            'title'         => $request->input->string('title', false, [0,100]),           //商品名称
            'description'   => $request->input->string('description', false, null),        //商品描述
            'price'         => $request->input->integer('price', false, [0,10]),           //商品单价
            'icon'          => $request->input->string('icon', false, [0,100]),            //商品图片
            'is_act'        => $request->input->integer('is_act', false, [0,1]),           //是否参加活动
            'is_top'        => $request->input->integer('is_top', false, [0,1]),           //是否置顶
            'display'       => $request->input->integer('display', false, [0,11]),         //商品排序
            'total'         => $request->input->integer('total', false, [0,11]),           //商品库存 -1无限多  否则就是填写数量
            'scale'         => $request->input->integer('scale', false, [0,11]),           //精度
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
        $model = Goods::findOne($check);
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
		$model = Goods::findOne($_key);
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
        $pWhere['title']        = $request->input->get('title', false);                       //商品名称
        $pWhere['description']  = $request->input->get('description', false);                 //商品描述
        $pWhere['price']        = $request->input->get('price', false);                       //商品单价
        $pWhere['icon']         = $request->input->get('icon', false);                        //商品图片
        $pWhere['is_act']       = $request->input->get('is_act', false);                      //是否参加活动
        $pWhere['is_top']       = $request->input->get('is_top', false);                      //是否置顶
        $pWhere['display']      = $request->input->get('display', false);                     //商品排序
        $pWhere['total']        = $request->input->get('total', false);                       //商品库存 -1无限多  否则就是填写数量
        $pWhere['scale']        = $request->input->get('scale', false);                       //精度
        
        //分页处理
	    $count   = $request->input->get('count', false, -1);
	    $order   = $request->input->get('order', false, 'id');
	    if(!empty($order)) {
	        $order .= $request->input->get('isDesc') ? ' asc' : ' desc';
	    }else{
	        $order = 'id desc';
	    }
	    
	    //列表输出
	    $model = Goods::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }

}