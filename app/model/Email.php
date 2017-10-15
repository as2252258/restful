<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Email
 * @package Inter\mysql
 *
 * @property $id
 * @property $fromId
 * @property $fromName
 * @property $fromUser
 * @property $receiveId
 * @property $receiveName
 * @property $receiveUser
 * @property $title
 * @property $content
 * @property $addTime
 */
class Email extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_email';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['fromId', 'fromName', 'fromUser', 'receiveId', 'receiveName', 'receiveUser', 'title', 'content'], 'required'],
			[['fromId', 'receiveId', 'addTime'], 'integer'],
			[['fromName', 'fromUser', 'receiveName', 'receiveUser', 'title', 'content'], 'string'],
			[['fromId', 'receiveId', 'addTime'], 'maxLength' => 11],
			[['fromName', 'receiveName'], 'maxLength' => 100],
			[['fromUser', 'receiveUser', 'title'], 'maxLength' => 255],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'fromId'            => '邮件发送用户ID',
            'fromName'          => '邮件发送用户名',
            'fromUser'          => '邮件发送用户邮箱',
            'receiveId'         => '邮件接收用户ID',
            'receiveName'       => '邮件接收用户名',
            'receiveUser'       => '邮件接收用户邮箱',
            'title'             => '邮件标题',
            'content'           => '邮件内容',
            'addTime'           => '',
        ];
    }

}