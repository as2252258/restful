<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Packet
 * @package Inter\mysql
 *
 * @property $id
 * @property $itemId
 * @property $authId
 * @property $addTime
 * @property $modifyTime
 */
class Packet extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_packet';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['authId'], 'required'],
			[['itemId', 'authId'], 'integer'],
			[['addTime', 'modifyTime'], 'timestamp'],
			[['itemId', 'authId'], 'maxLength' => 11],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'itemId'            => '組名',
            'authId'            => '拥有权限',
            'addTime'           => '添加时间  创建时自动添加',
            'modifyTime'        => '修改时间  修改时自动更新',
        ];
    }

}