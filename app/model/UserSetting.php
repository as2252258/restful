<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class UserSetting
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $sendKey
 * @property $state
 * @property $createTime
 * @property $modifyTime
 */
class UserSetting extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_user_setting';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'sendKey', 'state'], 'integer'],
			[['createTime', 'modifyTime'], 'datetime'],
			['userId', 'maxLength' => 20],
			[['sendKey', 'state'], 'maxLength' => 1],
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
            'sendKey'           => '消息发送设置 0.ctrl+enter  1.enter',
            'state'             => '0.失效  1.正常',
            'createTime'        => '创建时间',
            'modifyTime'        => '更新时间',
        ];
    }

}