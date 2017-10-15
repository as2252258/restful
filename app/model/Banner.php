<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Banner
 * @package Inter\mysql
 *
 * @property $id
 * @property $title
 * @property $url
 * @property $imgKey
 * @property $status
 * @property $addTime
 * @property $type
 */
class Banner extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_banner';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['title', 'url', 'imgKey'], 'string'],
			[['status', 'addTime', 'type'], 'integer'],
			[['title', 'imgKey'], 'maxLength' => 100],
			['url', 'maxLength' => 200],
			[['status', 'type'], 'maxLength' => 1],
			['addTime', 'maxLength' => 10],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'title'             => '标题',
            'url'               => '链接',
            'imgKey'            => '图片',
            'status'            => '状态   1.可用  2.禁用',
            'addTime'           => '添加时间',
            'type'              => '广告附属  默认0',
        ];
    }

}