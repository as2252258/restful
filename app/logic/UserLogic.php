<?php

namespace app\logic;


use app\model\Friend;
use app\model\Message;
use app\model\User;
use yoc\base\Components;

class UserLogic extends Components
{
	/**
	 * @param $fd
	 * 离线处理
	 */
	public function close($fd)
	{
		$userId = \Yoc::getRedis()->get(USER_FD . $fd);
		\Yoc::getRedis()->del(USER_FD . $fd);
		if (empty($userId)) {
			return;
		}
		$user = User::findOne($userId);
		if (empty($user)) {
			return;
		}
		$data = \Yoc::getRedis()->sMembers(FD_LIST . $userId);
		if (empty($data)) {
			$user->isOnline = 0;
		} else {
			$user->isOnline = 1;
		}
		$user->save();
		$friends = Friend::where(['userId' => $userId])->queryRaw('friendId');
		if (empty($friends) || !is_array($friends)) {
			return;
		}
		foreach ($friends as $key => $val) {
			if (empty($val) || !is_numeric($val)) continue;
			\Yoc::pushUser('logout' , $val , [
				'message' => '您的好友' . $user->nickname . '下线啦' ,
				'userId'  => $user->id
			]);
		}
		return;
	}
	
	
	/**
	 * @param User $user
	 * @param      $fd
	 * 当用户连接上长连接时的任务事件
	 */
	public function connect(User $user , $fd)
	{
		try {
			if (empty($user) || empty($fd)) {
				return;
			}
			$redis = \Yoc::$app->getRedis();
			if ($redis->exists(FD_LIST . $user->id)) {
				$this->clearDesktop($redis , $user);
			}
			$redis->sAdd(FD_LIST . $user->id , $fd);
			$redis->set(USER_FD . $fd , $user->id);
			$user->isOnline = 1;
			$user->save();
			$friends = Friend::where(['userId' => $user->id])->all();
			if (empty($friends) || $friends->isEmpty()) {
				return;
			}
			foreach ($friends as $key => $val) {
				/** @var Friend $val */
				if (empty($val) || !is_object($val)) continue;
				$nickname = $val->remarks ?? $user->nickname;
				\Yoc::pushUser('reConnect' , $val->friendId , [
					'message' => '您的好友' . $nickname . '上线啦' ,
					'userId'  => $user->id
				]);
			}
		} catch (\Exception $exception) {
			var_dump($exception->getMessage());
		}
		return;
	}
	
	
	/**
	 * @param \Redis $redis
	 * @param        $user
	 */
	private function clearDesktop($redis , $user)
	{
		$list = $redis->sMembers(FD_LIST . $user->id);
//		if (!empty($list) && is_array($list)) {
//			foreach ($list as $key => $val) {
//				\Yoc::$server->push($val , json_encode([
//					'callback' => 'Common::closeWindows' ,
//				]));
//				\Yoc::$server->close($val);
//			}
//		}
//		$redis->del(FD_LIST . $user->id);
	}
	
	/**
	 * @param Message $message
	 * @param null    $user
	 * 聊天室消息推送
	 */
	public function chart_room(Message $message , $user = null)
	{
		$start = 0;
		$userId = 0;
		$nickname = '匿名用户';
		if (!empty($user) && $user instanceof User) {
			$userId = $user->id;
			$nickname = $user->nickname;
		}
		$data = json_encode([
			'nickname' => $nickname ,
			'content'  => htmlspecialchars_decode($message->content) ,
			'callback' => 'Room::message' ,
			'sendId'   => $userId ,
		] , JSON_UNESCAPED_UNICODE);
		while ($_data = true) {
			$connects = \Yoc::$server->connection_list($start , 100);
			if (empty($connects) || !is_array($connects)) {
				break;
			}
			foreach ($connects as $key => $val) {
				if (!\Yoc::$server->exist($val)) {
					continue;
				}
				\Yoc::$server->push($val , $data);
			}
			$start += 1;
		}
		return;
	}
	
	/**
	 * @param User    $user
	 * @param Message $message
	 *
	 * 给指定人员发送消息
	 */
	public function message(User $user , Message $message)
	{
		\Yoc::pushUser('Friend::message' , $message->userId , [
			'message'  => htmlspecialchars_decode($message->content) ,
			'sendId'   => $message->sendId ,
			'avator'   => $user->avator ,
			'nickname' => $user->nickname ,
			'id'       => $message->id ,
		]);
		$redis = \Yoc::getRedis();
		if (!$redis->sMembers(log_history_key($message->sendId))) {
			$redis->sAdd(log_history_key($message->sendId) , '1_' . $message->userId);
		}
		if (!$redis->sMembers(log_history_key($message->userId))) {
			$redis->sAdd(log_history_key($message->userId) , '1_' . $message->sendId);
		}
		return;
	}
	
	/**
	 * @param $data
	 * 分片上传，文件合并
	 */
	public function merge($data)
	{
		$file = $data['dir'] . '/' . $data['name'];
		if (file_exists($file) && md5_file($file) == $data['file_md5']) {
			return;
		}
		foreach (glob($data['dir'] . '/*.part.*') as $key => $val) {
			$content = file_get_contents($val);
			if (!empty($content)) {
				file_put_contents($file , $content , FILE_APPEND);
			}
			unlink($val);
		}
		$download = 'http://114.55.105.117/files/download?token=' . md5_file($file);
		\Yoc::pushUser('Friend::download' , $data['userId'] , [
			'url' => $download
		]);
		return;
	}
}