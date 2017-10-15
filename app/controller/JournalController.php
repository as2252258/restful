<?php

namespace app\controller;

use app\components\ActiveController;
use app\model\Diary;
use app\model\DiaryCommentIdentify;
use app\model\DiaryIdentify;
use Code;
use Exception;
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;


/**
 * Class JournalController
 *
 * @package Controller
 */
class JournalController extends ActiveController
{
	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request)
	{
		$trance = \Yoc::$app->db->beginTransaction();
		try {
			$model = new Diary();
			if (!$model->load() || !$model->save()) {
				throw new Exception($model->getLastError());
			}
			if ($model->auth == 1 || $model->auth == 2) {
				$this->identifyIds($request , $model);
			}
			if (in_array($model->comment_authority , [1 , 2])) {
				$this->commentIdentifyIds($request , $model);
			}
			$trance->commit();
			return Response::analysis(0 , $model);
		} catch (Exception $exception) {
			$trance->rollback();
			return Response::analysis(Code::ERROR , $exception->getMessage());
		}
	}
	
	/**
	 * @param \app\model\Diary $journal
	 *
	 * @throws \Exception
	 * 更新查看权限
	 */
	private function identifyIds(Request $request , Diary $journal)
	{
		$userIds = $request->input->array('identifyIds');
		if (empty($userIds)) {
			return;
		}
		foreach ($userIds as $key => $val) {
			$idt = new DiaryIdentify();
			$idt->userId = $val;
			$idt->journalId = $journal->id;
			if ($idt->save()) {
				throw new Exception($idt->getLastError());
			}
		}
	}
	
	/**
	 * @param \app\model\Diary $journal
	 *
	 * @throws \Exception
	 * 更新评论权限
	 */
	private function commentIdentifyIds(Request $request , Diary $journal)
	{
		$userIds = $request->input->array('commentIdentifyIds');
		if (empty($userIds)) {
			return;
		}
		foreach ($userIds as $key => $val) {
			$idt = new DiaryCommentIdentify();
			$idt->userId = $val;
			$idt->journalId = $journal->id;
			if ($idt->save()) {
				throw new Exception($idt->getLastError());
			}
		}
	}
	
	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionUpdate(Request $request)
	{
		$trance = \Yoc::$app->db->beginTransaction();
		try {
			$model = Diary::findOne($request->input->integer('id'));
			if (empty($model)) {
				throw new \Exception('指定数据不存在');
			}
			if (!$model->load() || !$model->save()) {
				throw new Exception($model->getLastError());
			}
			if ($model->auth == 1 || $model->auth == 2) {
				$this->identifyIds($request , $model);
			}
			if (in_array($model->comment_authority , [1 , 2])) {
				$this->commentIdentifyIds($request , $model);
			}
			$trance->commit();
			return Response::analysis(Code::SUCCESS , $model);
		} catch (Exception $exception) {
			$trance->rollback();
			return Response::analysis(Code::ERROR , $exception->getMessage());
		}
	}
	
	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionDetail(Request $request)
	{
		$check = $request->input->integer('id');
		if (empty($check)) {
			throw new Exception('param id can not empty');
		}
		$model = Diary::findOne($check);
		if (empty($model)) {
			throw new Exception('Data Not Exists');
		}
		return Response::analysis(Code::SUCCESS , $model);
	}
	
	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionDelete(Request $request)
	{
		$_key = $request->input->integer('id' , true);
		$pass = $request->input->password('password' , true);
		if (empty($this->user) || strcmp(Str::encrypt($pass) , $this->user->password)) {
			throw new \Exception('密码错误');
		}
		$model = Diary::findOne($_key);
		if (empty($model)) {
			throw new \Exception('数据不存在');
		}
		if (!$model->delete()) {
			return Response::analysis(Code::ERROR , $model->getLastError());
		}
		
		DiaryIdentify::where(['journalId' => $model->id])->delete();
		DiaryCommentIdentify::where(['journalId' => $model->id])->delete();
		
		return Response::analysis(Code::SUCCESS , $model);
	}
	
	/**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionList(Request $request)
	{
		$pWhere = [];
		$pWhere['uid'] = $request->input->get('uid');                         //用户ID
		$pWhere['title'] = $request->input->get('title');                       //标题
		$pWhere['tag'] = $request->input->get('tag');                         //标签
		$pWhere['content'] = $request->input->get('content');                     //内容
		$pWhere['view_count'] = $request->input->get('view_count');                  //浏览量
		$pWhere['reply_count'] = $request->input->get('reply_count');                 //回复量
		$pWhere['auth'] = $request->input->get('auth');                        //查看权限 0.全部  1.指定人可看  2.指定人不可看  3.除自己都不可以看
		$pWhere['comment_authority'] = $request->input->get('comment_authority');           //评论权限 0.全部  1.指定人可评论  2.指定人不可评论  3.都不能评论
		$pWhere['thumbnails'] = $request->input->get('thumbnails');                  //封面图
		$pWhere['is_hot'] = $request->input->get('is_hot');                      //是否热搜 0.否 1.是
		$pWhere['is_top'] = $request->input->get('is_top');                      //是否置顶 0.否 1.是
		$pWhere['status'] = $request->input->get('status');                      //文章状态
		$pWhere['addTime'] = $request->input->get('addTime');                     //发布时间
		$pWhere['upTime'] = $request->input->get('upTime');                      //更新时间
		
		//分页处理
		$count = $request->input->get('count' , false , -1);
		$order = $request->input->get('order' , false , 'id');
		if (!empty($order)) {
			$order .= $request->input->get('isDesc') ? ' asc' : ' desc';
		} else {
			$order = 'id desc';
		}
		
		//列表输出
		$model = Diary::where($pWhere)->orderBy($order);
		if ($count != -100) {
			$model->limit($request->input->page , $request->input->size);
		}
		if ((int) $count === 1) {
			$count = $model->count();
		}
		return Response::analysis(Code::SUCCESS , $model->all() , $count);
	}
}