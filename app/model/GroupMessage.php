<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GroupMessage
 * @package Inter\mysql
 *
 * @property $id
 * @property $groupId
 * @property $sendId
 * @property $nickname
 * @property $avatar
 * @property $content
 * @property $status
 * @property $createTime
 * @property $dealwithTime
 */
class GroupMessage extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group_message';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['groupId', 'sendId', 'status'], 'integer'],
			[['nickname', 'avatar', 'content'], 'string'],
			[['createTime', 'dealwithTime'], 'datetime'],
			[['groupId', 'sendId'], 'maxLength' => 11],
			['status', 'maxLength' => 1],
			['nickname', 'maxLength' => 50],
			['avatar', 'maxLength' => 150],
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
            'sendId'            => '消息发送',
            'nickname'          => '昵称',
            'avatar'            => '头像',
            'content'           => '消息内容',
            'status'            => '0.未读  1.已读  2.屏蔽 3.忽略 4.删除',
            'createTime'        => '消息创建时间',
            'dealwithTime'      => '消息处理时间',
        ];
    }

    
    public function setContent(){
    	if($this->status == 5){
    		return '';
	    }
    	return htmlspecialchars_decode($this->content);
    }
}