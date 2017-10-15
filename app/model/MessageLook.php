<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class MessageLook
 * @package Inter\mysql
 *
 * @property $id
 * @property $groupId
 * @property $userId
 * @property $friendId
 * @property $createTime
 */
class MessageLook extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_message_look';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['groupId', 'userId', 'friendId'], 'integer'],
			['createTime', 'datetime'],
			[['groupId', 'userId', 'friendId'], 'maxLength' => 11],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'groupId'           => '群ID',
            'userId'            => '用户ID',
            'friendId'          => '好友ID',
            'createTime'        => '查看时间',
        ];
    }

}