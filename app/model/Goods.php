<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Goods
 * @package Inter\mysql
 *
 * @property $id
 * @property $title
 * @property $description
 * @property $price
 * @property $icon
 * @property $is_act
 * @property $is_top
 * @property $display
 * @property $total
 * @property $scale
 * @property $addTime
 * @property $modifyTime
 */
class Goods extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_goods';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['title', 'description', 'icon'], 'string'],
			[['price', 'is_act', 'is_top', 'display', 'total', 'scale'], 'integer'],
			[['addTime', 'modifyTime'], 'timestamp'],
			[['title', 'icon'], 'maxLength' => 100],
			['price', 'maxLength' => 10],
			[['is_act', 'is_top'], 'maxLength' => 1],
			[['display', 'total', 'scale'], 'maxLength' => 11],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'title'             => '商品名称',
            'description'       => '商品描述',
            'price'             => '商品单价',
            'icon'              => '商品图片',
            'is_act'            => '是否参加活动',
            'is_top'            => '是否置顶',
            'display'           => '商品排序',
            'total'             => '商品库存 -1无限多  否则就是填写数量',
            'scale'             => '精度',
            'addTime'           => '',
            'modifyTime'        => '',
        ];
    }

}