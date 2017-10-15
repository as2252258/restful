<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/30 0030
 * Time: 12:03
 */

namespace app\logic;

use yoc\http\Response;
use yoc\http\Request;
use yoc\pool\ModelPool;

class Data
{
	
	public function actionGetTable()
	{
		preg_match('/dbname=(.*?);/', \Yoc::$app->db->cds, $results);
		if (empty($results[1])) {
			return Response::analysis(-1, '未知的表名, ' . implode(':', $results));
		}
		return Response::analysis(0, \DB::quote('SHOW TABLE STATUS FROM ' . $results[1], null, false));
	}
	
	public function actionInfo(Request $request)
	{
		$tableName = $request->input->string('tableName', true);
		return Response::analysis(0, \DB::quote('show table status from xl_advanced where Name=\'' . $tableName . '\' ', null, false));
	}
	
	public function actionTableInfo(Request $request)
	{
		$tableName = $request->input->string('tableName', true);
		return Response::analysis(0, \DB::quote('DESC ' . $tableName, null, false));
	}
	
	
	public function actionClearTable(Request $request)
	{
		$tableName = $request->input->string('tableName', true);
		return Response::analysis(0, \DB::quote('DESC ' . $tableName, null, false));
	}
	
	/**
	 * @param Request $request
	 *
	 * @return array
	 */
	public function actionDeleteTable(Request $request)
	{
		if (!$request->isDelete()) {
			return Response::analysis(-1, 'only delete request');
		}
		$redis = \Yoc::getRedis();
		$tableName = $request->input->string('table', true);
		$redis->del($redis->keys($tableName . '_*'));
		Pool::removeByPatten($tableName . '_*');
		$results = \DB::table($tableName)->drop();
		if ($results) {
			return Response::analysis(0);
		}
		return Response::analysis(-1, \DB::getLastSql());
	}
	
	public function actionData(Request $request)
	{
		$tableName = $request->input->string('tableName', true);
		$list = \DB::table($tableName)
			->limit($request->input->page, $request->input->size)
			->orderBy($request->input->string('order'), $request->input->string('isDesc'));
		$count = 0;
		if ($request->input->integer('count') == 1) {
			$count = $list->count();
		}
		$tableInfo = \DB::table($tableName)->desc();
		return Response::analysis(0, '', $list->get(), $count, $tableInfo);
	}
	
	public function actionRedisList()
	{
		$redis = \Yoc::getRedis();
		$data = $redis->keys('*');
		$_data = [];
		if (!empty($data)) {
			$type = [
				\Redis::REDIS_STRING    => 'string',
				\Redis::REDIS_SET       => 'set',
				\Redis::REDIS_LIST      => 'list',
				\Redis::REDIS_ZSET      => 'zset',
				\Redis::REDIS_HASH      => 'hash',
				\Redis::REDIS_NOT_FOUND => 'other'
			];
			foreach ($data as $key => $val) {
				$_tmp['value'] = $val;
				$_tmp['type'] = $type[$redis->type($val)];
				$_data[] = $_tmp;
			}
		}
		return Response::analysis(0, $_data);
	}
	
	/**
	 * @param Request $request
	 *
	 * - string: Redis::REDIS_STRING
	 * - set:   Redis::REDIS_SET
	 * - list:  Redis::REDIS_LIST
	 * - zset:  Redis::REDIS_ZSET
	 * - hash:  Redis::REDIS_HASH
	 * - other: Redis::REDIS_NOT_FOUND
	 */
	public function actionRedisDetail(Request $request)
	{
		$redis = \Yoc::getRedis();
		$_cate = $redis->type($request->input->string('key', true));
		switch (trim($_cate)) {
			case \Redis::REDIS_SET:
				$_data = $redis->sMembers($request->input->string('key'));
				break;
			case \Redis::REDIS_ZSET:
				$_data = $redis->sMembers($request->input->string('key'));
				break;
			case \Redis::REDIS_HASH:
				$_data = $redis->hGetAll($request->input->string('key'));
				break;
			case \Redis::REDIS_STRING:
				$_data = $redis->get($request->input->string('key'));
				break;
			case \Redis::REDIS_LIST:
				$key = $request->input->string('key');
				$_data = $redis->lRange($key, 0, $redis->lLen($key));
				break;
			default:
				$_data = $redis->get($request->input->string('key'));
		}
		return Response::analysis(0, '', $_data);
	}
	
	
	public function actionRedisDelete(Request $request)
	{
		$check = $request->input->string('key', true);
		$password = $request->input->string('password', true, 32);
		if (strcmp($this->user->password, Str::encrypt($password))) {
			return Response::analysis(-1, '密码错误');
		}
		$redis = \Yoc::getRedis();
		$redis->del($check);
		if ($redis->exists($check)) {
			return Response::analysis(-1, '缓存删除失败');
		}
		return Response::analysis(0);
	}
	
	
	public function actionDeleteData(Request $request)
	{
		$check = $request->input->string('tableName', true);
		$password = $request->input->string('password', true, 32);
		if (strcmp($this->user->password, Str::encrypt($password))) {
			return Response::analysis(-1, '密码错误');
		}
		$redis = \Yoc::getRedis();
		$redis->del($check . '_' . $request->input->integer('id', true));
		$delete = \DB::table($check)->where(['id' => $request->input->integer('id', true)])->delete();
		return Response::analysis(0, $delete);
	}
	
	public function actionTableClear(Request $request)
	{
		$table = $request->input->string('table', true);
		ModelPool::removeByPatten($table . '_*');
		$truncate = \DB::table($table)->truncate();
		if ($truncate) {
			return Response::analysis(0);
		}
		return Response::analysis($truncate);
	}
}