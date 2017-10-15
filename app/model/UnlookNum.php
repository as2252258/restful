<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class UnlookNum
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $mType
 * @property $groupId
 * @property $friendId
 * @property $num
 * @property $modifyTime
 */
class UnlookNum extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_unlook_num';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'mType', 'groupId', 'friendId', 'num'], 'integer'],
			['modifyTime', 'datetime'],
			[['userId', 'friendId', 'num'], 'maxLength' => 20],
			['mType', 'maxLength' => 1],
			['groupId', 'maxLength' => 11],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'userId'            => '用户ID',
            'mType'             => '消息类型 0.系统消息  1.群消息  2.好友消息',
            'groupId'           => '群ID',
            'friendId'          => '好友ID',
            'num'               => '未读数',
            'modifyTime'        => '更新时间',
        ];
    }

}