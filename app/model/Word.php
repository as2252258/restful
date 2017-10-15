<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Word
 * @package Inter\mysql
 *
 * @property $id
 * @property $title
 * @property $keyword
 * @property $state
 * @property $createTime
 * @property $modifyTime
 */
class Word extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_word';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['title', 'keyword'], 'string'],
			['state', 'integer'],
			[['createTime', 'modifyTime'], 'datetime'],
			[['title', 'keyword'], 'maxLength' => 100],
			['state', 'maxLength' => 1],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'title'             => '关键字',
            'keyword'           => '替换结果',
            'state'             => '是否禁用 0.否 1.是',
            'createTime'        => '添加时间',
            'modifyTime'        => '更新时间',
        ];
    }

}