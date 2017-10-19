<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/10/2 0002
 * Time: 2:21
 */

namespace app\logic;


use app\model\Friend;
use app\model\FriendGroup;
use app\model\Message;
use app\model\SystemLog;
use app\model\User;
use yoc\base\Components;

class FriendLogic extends Components
{
	
	/**
	 * @param \app\model\Message $message
	 * @param \app\model\User    $user
	 * 消息回撤
	 */
	public function messageRollback(Message $message , User $user)
	{
		$userId = $message->sendId == $user->id ? $message->userId : $message->sendId;
		\Yoc::pushUser('Message::rollback' , $userId , [
			'type'   => 1 ,
			'userId' => $userId == $message->userId ? $message->sendId : $message->userId,
			'id'     => $message->id ,
		]);
	}
	
	/**
	 * @param \app\model\Message $message
	 * @param \app\model\User    $user
	 * 消息删除
	 */
	public function messageDelete(Message $message , User $user)
	{
		$userId = $message->sendId == $user->id ? $message->userId : $message->sendId;
		\Yoc::pushUser('Message::deleteMessage' , $userId , [
			'type'   => 1 ,
			'userId' => $userId == $message->userId ? $message->sendId : $message->userId,
			'id'     => $message->id ,
		]);
	}
	
	/**
	 * @param \app\model\User      $user
	 * @param \app\model\SystemLog $log
	 * 消息通知
	 */
	public function notify(User $user , SystemLog $log)
	{
		$group = FriendGroup::where(['userId' => $log->sendId])->one();
		if (empty($group)) {
			return;
		}
		$friend = User::findOne($log->sendId);
		$add = new SystemLog();
		$add->message = $log->results == 1 ? $user->nickname . '同意了您的好友申请' : $user->nickname . '拒绝了您的好友申请';
		$add->userId = $log->sendId;
		$add->status = 2;
		$add->results = $log->results;
		$add->sendId = $user->id;
		$add->createTime = time();
		$add->modifyTime = time();
		$add->save();
		if ($log->results == 1) {
			
			$friends = Friend::where(['userId' => $log->sendId , 'friendId' => $log->userId])->one();
			$group = FriendGroup::where(['userId' => $log->sendId])->orderBy('id desc')->one();
			if (empty($group)) {
				$group = new FriendGroup();
				$group->userId = $log->sendId;
				$group->name = '我的好友';
				$group->status = 1;
				$group->createTime = time();
				$group->modifyTime = time();
				$group->save();
			}
			\Yoc::pushUser('Friend::setElement' , $log->sendId , [
				'message'  => $add->message ,
				'id'       => $friends->id ,
				'sendId'   => $user->id ,
				'groupId'  => $group->id ,
				'nickname' => $user->nickname ,
				'avator'   => $user->avator
			]);
			
			
			$friends = Friend::where(['userId' => $log->userId , 'friendId' => $log->sendId])->one();
			$group = FriendGroup::where(['userId' => $log->userId])->orderBy('id desc')->one();
			if (empty($group)) {
				$group = new FriendGroup();
				$group->userId = $log->sendId;
				$group->name = '我的好友';
				$group->status = 1;
				$group->createTime = time();
				$group->modifyTime = time();
				$group->save();
			}
			\Yoc::pushUser('Friend::setElement' , $log->userId , [
				'message'  => $add->message ,
				'id'       => $friends->id ,
				'sendId'   => $friend->id ,
				'groupId'  => $group->id ,
				'nickname' => $friend->nickname ,
				'avator'   => $friend->avator
			]);
		} else {
			\Yoc::pushUser('Common::loadNews' , $log->sendId , [
				'message'  => $add->message ,
				'sendId'   => $user->id ,
				'nickname' => $user->nickname ,
				'avator'   => $user->avator
			]);
		}
	}
}