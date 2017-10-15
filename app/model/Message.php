<?php

namespace app\model;

use yoc\db\ActiveRecord;

/**
 * Class Message
 *
 * @package Inter\mysql
 *
 * @property $id
 * @property $cid
 * @property $userId
 * @property $sendId
 * @property $content
 * @property $userDelete
 * @property $friendDelete
 * @property $status
 * @property $createTime
 * @property $dealwithTime
 */
class Message extends ActiveRecord
{
	protected $primary = 'id';
	
	protected $appends = ['sendInfo'];
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'xl_message';
	}
	
	
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['cid' , 'userId' , 'sendId' , 'userDelete' , 'friendDelete' , 'status'] , 'integer'] ,
			['content' , 'string'] ,
			[['createTime' , 'dealwithTime'] , 'datetime'] ,
			[['cid' , 'userId' , 'sendId'] , 'maxLength' => 20] ,
			[['userDelete' , 'friendDelete' , 'status'] , 'maxLength' => 1] ,
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributes() : array
	{
		return [
			'id'           => '' ,
			'cid'          => '' ,
			'userId'       => '消息接受用户' ,
			'sendId'       => '消息触发用户' ,
			'content'      => '消息内容' ,
			'userDelete'   => '删除人  0.正常  1.已删除' ,
			'friendDelete' => '好友删除  0.正常  1.已删除' ,
			'status'       => '0.未读  1.已读  2.屏蔽 3.忽略 4.删除' ,
			'createTime'   => '消息创建时间' ,
			'dealwithTime' => '消息处理时间' ,
		];
	}
	
	
	public function getSendInfoAttribute()
	{
		return User::findOne($this->sendId)->packet('id' , 'nickname' , 'avator');
	}
	
	
	public function setContent()
	{
		if ($this->status == 5) {
			return '';
		}
		return htmlspecialchars_decode($this->content);
	}
	
}