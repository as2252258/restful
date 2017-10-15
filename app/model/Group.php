<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Group
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $groupName
 * @property $description
 * @property $avatar
 * @property $isPublic
 * @property $messageAuth
 * @property $plusGroupAuth
 * @property $status
 * @property $createTime
 * @property $dealwithTime
 */
class Group extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['groupName', 'description', 'avatar'], 'required'],
			[['userId', 'isPublic', 'messageAuth', 'plusGroupAuth', 'status'], 'integer'],
			[['groupName', 'description', 'avatar'], 'string'],
			[['createTime', 'dealwithTime'], 'datetime'],
			['userId', 'maxLength' => 11],
			[['isPublic', 'messageAuth', 'plusGroupAuth', 'status'], 'maxLength' => 1],
			['groupName', 'maxLength' => 50],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'userId'            => '群创建人',
            'groupName'         => '群名称',
            'description'       => '群简介',
            'avatar'            => '群头像',
            'isPublic'          => '群是否公开',
            'messageAuth'       => '群发言设置',
            'plusGroupAuth'     => '加群验证',
            'status'            => '1.正常 2.封禁 3.删除',
            'createTime'        => '创建时间',
            'dealwithTime'      => '修改时间',
        ];
    }

}