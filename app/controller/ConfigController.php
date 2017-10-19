<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/10/17 0017
 * Time: 16:24
 */

namespace app\controller;


use app\components\ActiveController;
use yoc\http\Response;

class ConfigController extends ActiveController
{
	public function actionUserStatus()
	{
		return Response::analysis([
			\Code::STATUS_UNKNOWN  => '待处理' ,       //未处理
			\Code::STATUS_SUCCESS  => '正常' ,       //正常
			\Code::STATUS_AUDITING => '审核中' ,      //审核中
			\Code::STATUS_DELETE   => '已删除' ,        //已删除
		]);
	}
}