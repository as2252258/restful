<?php

namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GroupUser
 * @package Inter\mysql
 *
 * @property $id
 * @property $groupId
 * @property $userId
 * @property $auth
 * @property $isReceiveMessage
 * @property $remarks
 * @property $status
 * @property $createTime
 * @property $dealwithTime
 */
class GroupUser extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = ['group','user'];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group_user';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['groupId', 'userId', 'auth', 'isReceiveMessage', 'status'], 'integer'],
			['remarks', 'string'],
			[['createTime', 'dealwithTime'], 'datetime'],
			[['groupId', 'userId'], 'maxLength' => 11],
			[['auth', 'isReceiveMessage', 'status'], 'maxLength' => 1],
			['remarks', 'maxLength' => 50],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => 'Id',
            'groupId'           => '群ID',
            'userId'            => '用户ID',
            'auth'              => '群权限 1.普通用户  2.管理员  3.群主',
            'isReceiveMessage'  => '0.接收并提醒  1.接收不提醒  2.不接收',
            'remarks'           => '群昵称',
            'status'            => '1.正常 2.禁言 3.删除',
            'createTime'        => '创建时间',
            'dealwithTime'      => '修改时间',
        ];
    }

	/**
	 * @return Group
	 */
	public function getGroupAttribute()
	{
		return Group::findOne($this->groupId);
	}

	/**
	 * @return array
	 */
	public function getUserAttribute(){
		return User::findOne($this->userId)->packet('avator','id','isOnline','nickname');
	}

}