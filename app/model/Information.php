<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Information
 * @package Inter\mysql
 *
 * @property $id
 * @property $fid
 * @property $jid
 * @property $type
 * @property $title
 * @property $cont
 * @property $status
 * @property $contact
 * @property $addTime
 * @property $upTime
 */
class Information extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_information';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['cont'], 'required'],
			[['fid', 'jid', 'type', 'status', 'addTime', 'upTime'], 'integer'],
			[['title', 'cont', 'contact'], 'string'],
			[['fid', 'jid', 'type'], 'maxLength' => 11],
			['status', 'maxLength' => 1],
			[['addTime', 'upTime'], 'maxLength' => 10],
			['title', 'maxLength' => 200],
			['contact', 'maxLength' => 50],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'fid'               => '消息发送者',
            'jid'               => '消息接受者 如果为0则为系统消息',
            'type'              => '消息类型',
            'title'             => '标题',
            'cont'              => '内容',
            'status'            => '消息状态 0.未读  1.已查看',
            'contact'           => '联系方式',
            'addTime'           => '发送时间',
            'upTime'            => '更新时间',
        ];
    }

}