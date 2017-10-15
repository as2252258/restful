<?php

namespace app\model;

use yoc\db\ActiveRecord;

/**
 * Class FriendGroup
 *
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $name
 * @property $status
 * @property $createTime
 * @property $modifyTime
 */
class FriendGroup extends ActiveRecord
{
	protected $primary = 'id';
	
	protected $appends = ['userList'];
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'xl_friend_group';
	}
	
	
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['userId' , 'status' , 'createTime' , 'modifyTime'] , 'integer'] ,
			['name' , 'string'] ,
			['userId' , 'maxLength' => 20] ,
			['status' , 'maxLength' => 1] ,
			[['createTime' , 'modifyTime'] , 'maxLength' => 10] ,
			['name' , 'maxLength' => 50] ,
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
			'name'       => '组名称' ,
			'status'     => '状态' ,
			'createTime' => '' ,
			'modifyTime' => '' ,
		];
	}
	
	/**
	 * @return array
	 */
	public function getUserListAttribute()
	{
		$collect = Friend::where(['groupId' => $this->id , 'userId' => $this->userId])->all();
		if ($collect->isEmpty()) {
			return $collect->toArray();
		}
		return $collect->column('user' , function ($user) {
			$data = [];
			foreach ($user as $key => $val) {
				if (empty($val)) continue;
				$data[] = $val;
			}
			return $data;
		});
	}
	
}