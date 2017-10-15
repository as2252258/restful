<?php

namespace app\model;

use yoc\db\ActiveRecord;

/**
 * Class SystemLog
 *
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $sendId
 * @property $category
 * @property $message
 * @property $remarks
 * @property $groupId
 * @property $results
 * @property $status
 * @property $createTime
 * @property $modifyTime
 */
class SystemLog extends ActiveRecord
{
	protected $primary = 'id';
	
	protected $appends = ['user'];
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'xl_system_log';
	}
	
	
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['userId' , 'sendId' , 'category' , 'groupId' , 'results' , 'status' , 'createTime' , 'modifyTime'] , 'integer'] ,
			[['message' , 'remarks'] , 'string'] ,
			[['userId' , 'sendId' , 'groupId'] , 'maxLength' => 20] ,
			[['category' , 'results' , 'status'] , 'maxLength' => 1] ,
			[['createTime' , 'modifyTime'] , 'maxLength' => 10] ,
			[['message' , 'remarks'] , 'maxLength' => 100] ,
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributes() : array
	{
		return [
			'id'         => '' ,
			'userId'     => '用户ID' ,
			'sendId'     => '发送人  系统消息为空' ,
			'groupId'    => '群ID' ,
			'category'   => '消息类型 ' ,
			'message'    => '消息内容' ,
			'remarks'    => '备注内容' ,
			'results'    => '处理结果  1.同意， 2.拒绝  3.忽略  4.删除' ,
			'status'     => '0.待处理 1.正常 2.已处理 3.已删除' ,
			'createTime' => '创建时间' ,
			'modifyTime' => '更新时间' ,
		];
	}
	
	
	public function getUserAttribute()
	{
		$user = User::findOne($this->sendId);
		if (empty($user)) {
			return [];
		}
		return $user->toArray();
	}
}