<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class FriendLog
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $sendId
 * @property $logType
 * @property $groupId
 * @property $sendType
 * @property $status
 * @property $createTime
 * @property $dealwithTime
 */
class FriendLog extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_friend_log';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'sendId'], 'required'],
			[['userId', 'sendId', 'logType', 'groupId', 'sendType', 'status'], 'integer'],
			[['createTime', 'dealwithTime'], 'datetime'],
			[['userId', 'sendId'], 'maxLength' => 20],
			[['logType', 'sendType', 'status'], 'maxLength' => 1],
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
            'userId'            => '消息接受用户',
            'sendId'            => '消息触发用户',
            'logType'           => '1.个人消息  2.群消息',
            'groupId'           => '群ID',
            'sendType'          => '消息类型  1.好友添加  2.进群申请  3.踢出群  4.主动退群  5.添加管理员   6.撤销管理员  7.群升级消息   8.群解散消息',
            'status'            => '0.未读  1.已同意  2.已拒绝 3.忽略 4.已删除',
            'createTime'        => '消息创建时间',
            'dealwithTime'      => '消息处理时间',
        ];
    }

}