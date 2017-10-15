<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/10/2 0002
 * Time: 1:55
 */

namespace app\logic;

use app\model\Group;
use app\model\GroupMessage;
use app\model\GroupUser;
use app\model\SystemLog;
use app\model\User;
use yoc\base\Components;

class GroupLogic extends Components
{
	
	/**
	 * @param \app\model\User      $user
	 * @param \app\model\SystemLog $model
	 *
	 * @return bool
	 * 申请进群通知
	 */
	public function applyForAdmission(User $user , SystemLog $model)
	{
		$admins = GroupUser::where('auth = 3 or auth = 2' , ['groupId' => $model->groupId])->all();
		if (!empty($admins)) foreach ($admins as $key => $val) {
			\Yoc::pushUser('Common::loadNews' , $val->userId , [
				'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
				'sendId'   => $user->id ,
				'nickname' => $user->nickname ,
				'avator'   => $user->avator
			]);
		}
		return true;
	}
	
	
	/**
	 * @param \app\model\User      $user
	 * @param \app\model\SystemLog $model
	 *
	 * @return bool
	 */
	public function kickOut(User $user , SystemLog $model)
	{
		$admins = GroupUser::where('auth = 3 or auth = 2' , ['groupId' => $model->groupId])->all();
		if (!empty($admins)) foreach ($admins as $key => $val) {
			\Yoc::pushUser('Common::loadNews' , $val->userId , [
				'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
				'sendId'   => $user->id ,
				'nickname' => $user->nickname ,
				'avator'   => $user->avator
			]);
		}
		\Yoc::pushUser('Common::loadNews' , $model->userId , [
			'message'  => $model->message . (empty($model->remarks) ? '' : ', 备注' . $model->remarks) ,
			'sendId'   => $user->id ,
			'nickname' => $user->nickname ,
			'avator'   => $user->avator
		]);
		return true;
	}
	
	/**
	 * @param \app\model\GroupUser $group_user
	 * @param \app\model\User      $user
	 * @param \app\model\Group     $group
	 * @param \app\model\SystemLog $message
	 * 进群通知
	 */
	public function join(GroupUser $group_user , User $user , Group $group , SystemLog $message)
	{
		$groupAdmin = GroupUser::where(['groupId' => $message->groupId , 'auth = 2 or auth = 3'])->all();
		//加群成功，群管理员通知
		if (!empty($groupAdmin)) {
			foreach ($groupAdmin as $key => $val) {
				$_message = $user->nickname . '加入了群' . $group->groupName;
				$data = Log::recordAndNotify($message->sendId , $_message , Log::CATE_INVITE , '' , $user , $message->groupId);
				$data->save(['status' => 2]);
				\Yoc::pushUser('Common::loadNews' , $val->userId , []);
			}
		}
		
		//告诉加群人加群成功了
		$data = Log::recordAndNotify($group_user->userId , '管理员已通过您的申请' , Log::CATE_KICK_GROUP , '' , $user , $group->id);
		$data->save(['status' => 2]);
		
		\Yoc::pushUser('Common::loadNews' , $group_user->userId , []);
		\Yoc::pushUser('Group::setElement' , $group_user->userId , [
			'groupName' => $group->groupName ,
			'groupId'   => $group->id ,
			'avatar'    => $group->avatar ,
			'id'        => $group_user->id
		]);
		
	}
	
	/**
	 * @param \app\model\GroupMessage $message
	 * @param \app\model\User         $user
	 * 群消息通知
	 */
	public function message(GroupMessage $message , User $user)
	{
		$groupAdmin = GroupUser::where(['groupId' => $message->groupId])->all();
		
		//加群成功，群管理员通知
		foreach ($groupAdmin as $key => $val) {
			if ($val->isReceiveMessage == 2) continue;
			if ($val->userId == $message->sendId) continue;
			\Yoc::pushUser('Group::message' , $val->userId , [
				'message'  => htmlspecialchars_decode($message->content) ,
				'avator'   => $user->avator ,
				'nickname' => $user->nickname ,
				'id'       => $message->id ,
				'sendId'   => $message->sendId ,
				'groupId'  => $val->groupId ,
			]);
		}
		return;
	}
	
	/**
	 * @param \app\model\GroupMessage $message
	 * @param \app\model\User         $user
	 */
	public function messageRollback(GroupMessage $message , User $user)
	{
		
		$groupAdmin = GroupUser::where(['groupId' => $message->groupId])->all();
		
		//加群成功，群管理员通知
		foreach ($groupAdmin as $key => $val) {
			if ($val->userId == $message->sendId) continue;
			\Yoc::pushUser('Message::rollback' , $val->userId , [
				'type'   => 2 ,
				'userId' => $message->groupId ,
				'id'     => $message->id ,
			]);
		}
	}
	
	/**
	 * @param \app\model\GroupMessage $message
	 * @param \app\model\User         $user
	 */
	public function messageDelete(GroupMessage $message , User $user)
	{
		$groupAdmin = GroupUser::where(['groupId' => $message->groupId])->all();
		
		//加群成功，群管理员通知
		foreach ($groupAdmin as $key => $val) {
			if ($val->userId == $message->sendId) continue;
			\Yoc::pushUser('Message::delete' , $val->userId , [
				'type'   => 2 ,
				'userId' => $message->groupId ,
				'id'     => $message->id ,
			]);
		}
	}
	
	/**
	 * @param \app\model\Group $group
	 * @param \app\model\User  $user
	 * 解散群消息通知
	 */
	public function dissolution(Group $group , User $user)
	{
		$groupUser = GroupUser::where(['groupId' => $group->id])->all();
		if ($groupUser->isEmpty()) {
			return;
		}
		foreach ($groupUser as $key => $val) {
			$model = new SystemLog();
			$model->userId = $val->userId;
			$model->sendId = $user->id;
			$model->category = Log::CATE_DISSOLUTION;
			$model->message = '群主' . $user->nickname . '解散了群' . $group->groupName;
			$model->remarks = '';
			$model->results = 1;
			$model->status = 2;
			$model->createTime = time();
			$model->modifyTime = time();
			$model->groupId = $group->id;
			$model->save();
			\Yoc::pushUser('Group::dissolution' , $model->userId , [
				'message' => $model->message ,
				'groupId' => $group->id ,
				'avatar'  => $group->avatar
			]);
			$val->delete();
		}
		return;
	}
	
	/**
	 * @param \app\model\GroupUser $groupUser
	 * @param \app\model\Group     $group
	 * @param \app\model\User      $user
	 * 群转让通知
	 */
	public function transfer(GroupUser $groupUser , Group $group , User $user)
	{
		$log = new SystemLog();
		$log->userId = $groupUser->userId;
		$log->groupId = $groupUser->groupId;
		$log->sendId = $user->id;
		$log->remarks = '';
		$log->results = 1;
		$log->status = 2;
		$log->message = $group->groupName . '的群主将群转给了你';
		$log->createTime = time();
		$log->modifyTime = time();
		if (!$log->save()) {
			throw new \Exception($log->getLastError());
		};
		\Yoc::pushUser('Group::transfer' , $groupUser->userId , [
			'message'       => $log->message ,
			'groupId' => $groupUser->groupId ,
			'groupUserInfo' => $groupUser->toArray() ,
			'sendUserInfo'  => $user->toArray() ,
			'groupInfo'     => $group->toArray()
		]);
	}
}