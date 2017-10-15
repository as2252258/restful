<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Colloquy
 * @package Inter\mysql
 *
 * @property $id
 * @property $groupId
 * @property $userId
 * @property $friendId
 * @property $mType
 * @property $lastNews
 * @property $state
 * @property $addTime
 * @property $modifyTime
 */
class Colloquy extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_colloquy';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['groupId', 'userId', 'friendId', 'mType', 'state'], 'integer'],
			['lastNews', 'string'],
			[['addTime', 'modifyTime'], 'timestamp'],
			['groupId', 'maxLength' => 11],
			[['userId', 'friendId'], 'maxLength' => 20],
			[['mType', 'state'], 'maxLength' => 1],
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
            'userId'            => '消息接收人',
            'friendId'          => '消息发送人',
            'mType'             => '消息类型',
            'lastNews'          => '最后一条消息内容',
            'state'             => '消息状态 0.已删除  1.正常  2.撤回  3.销毁',
            'addTime'           => '添加时间  创建时自动添加',
            'modifyTime'        => '修改时间  修改时自动更新',
        ];
    }

}