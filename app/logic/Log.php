<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/29 0029
 * Time: 14:36
 */

namespace app\logic;

use app\model\GroupUser;
use app\model\SystemLog;
use app\model\User;


class Log
{
	
	const CATE_FRIEND_ADD = 1;
	const CATE_APPLY_GROUP = 2;
	const CATE_KICK_GROUP = 3;
	const CATE_SIGN_OUT_GROUP = 4;
	const CATE_ADD_ADMIN = 5;
	const CATE_REVOKE_ADMIN = 6;
	const CATE_UPGRADE = 7;
	const CATE_DISSOLUTION = 8;
	const CATE_INVITE = 9;
	
	/**
	 * @param                 $userId
	 * @param                 $message
	 * @param                 $category
	 * @param                 $remarks
	 * @param \app\model\User $user
	 *
	 * @return \app\model\SystemLog
	 * @throws \Exception
	 */
	public static function recordAndNotify($userId , $message , $category , $remarks , User $user , $groupId = null)
	{
		if (!empty($groupId)) {
			$model = SystemLog::where(['userId' => $userId , 'sendId' => $user->id , 'category' => $category , 'groupId' => $groupId])->one();
		} else {
			$model = SystemLog::where(['userId' => $userId , 'sendId' => $user->id , 'category' => $category])->one();
		}
		if (empty($model)) {
			$model = new SystemLog();
		}else if($model->status == 2){
			$model = new SystemLog();
		}
		$model->setBatch([
			'userId'     => $userId ,          //用户ID
			'sendId'     => $user->id ,          //发送人  系统消息为空
			'category'   => $category ,         //消息类型 1.好友添加 2.进群申请 3.踢出群 4.主动退群 5.添加管理员 6.撤销管理员 7.群升级消息 8.群解散消息
			'message'    => $message ,         //消息内容
			'remarks'    => $remarks ,         //备注内容
			'results'    => 0 ,          //处理结果  1.同意， 2.拒绝  3.忽略  4.删除
			'status'     => 0 ,           //0.待处理 1.正常 2.已处理 3.已删除
			'createTime' => time() ,      //创建时间
			'modifyTime' => time() ,      //更新时间
		]);
		if (!empty($groupId)) {
			$model->setAttribute('groupId' , $groupId);
		}
		$results = $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return $model;
	}
}