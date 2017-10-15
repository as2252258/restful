<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/30 0030
 * Time: 18:00
 */

namespace app\controller;


use app\components\ActiveController;
use yoc\http\Response;

class AcController extends ActiveController
{
	
	public function actionIndex(){
		return Response::analysis(0);
	}
}