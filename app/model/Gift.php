<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Gift
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $sendId
 * @property $goodId
 * @property $goodName
 * @property $goodIcon
 * @property $goodDesc
 * @property $number
 * @property $addTime
 * @property $modifyTime
 */
class Gift extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_gift';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'sendId', 'goodId', 'number'], 'integer'],
			[['goodName', 'goodIcon', 'goodDesc'], 'string'],
			[['addTime', 'modifyTime'], 'timestamp'],
			[['userId', 'sendId'], 'maxLength' => 20],
			['goodId', 'maxLength' => 10],
			['number', 'maxLength' => 11],
			[['goodName', 'goodIcon'], 'maxLength' => 100],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'userId'            => '',
            'sendId'            => '',
            'goodId'            => '商品ID',
            'goodName'          => '商品名称',
            'goodIcon'          => '商品图片',
            'goodDesc'          => '商品简介',
            'number'            => '',
            'addTime'           => '',
            'modifyTime'        => '',
        ];
    }

}