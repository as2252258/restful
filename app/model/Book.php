<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Book
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $title
 * @property $mood
 * @property $toDay
 * @property $description
 * @property $state
 * @property $createTime
 * @property $modifyTime
 */
class Book extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_book';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'mood', 'toDay', 'state', 'createTime', 'modifyTime'], 'integer'],
			[['title', 'description'], 'string'],
			['userId', 'maxLength' => 20],
			[['mood', 'state'], 'maxLength' => 1],
			[['toDay', 'createTime', 'modifyTime'], 'maxLength' => 10],
			['title', 'maxLength' => 255],
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
            'title'             => '标题',
            'mood'              => '心情',
            'toDay'             => '所属日期',
            'description'       => '日记内容',
            'state'             => '日记状态 0.正常 1.销毁',
            'createTime'        => '发布日期',
            'modifyTime'        => '更新日期',
        ];
    }

}