<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Albums
 * @package Inter\mysql
 *
 * @property $id
 * @property $uid
 * @property $tid
 * @property $tName
 * @property $title
 * @property $tag
 * @property $aDesc
 * @property $status
 * @property $addTime
 * @property $upTime
 */
class Albums extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_albums';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['uid', 'tid', 'status', 'addTime', 'upTime'], 'integer'],
			[['tName', 'title', 'tag', 'aDesc'], 'string'],
			[['uid', 'tid'], 'maxLength' => 11],
			['status', 'maxLength' => 1],
			[['addTime', 'upTime'], 'maxLength' => 10],
			['tName', 'maxLength' => 50],
			[['title', 'tag'], 'maxLength' => 100],
			['aDesc', 'maxLength' => 300],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'uid'               => '用户ID',
            'tid'               => '分类',
            'tName'             => '分类名称',
            'title'             => '专辑名称',
            'tag'               => '专辑标签',
            'aDesc'             => '专辑简介',
            'status'            => '专辑状态',
            'addTime'           => '添加时间',
            'upTime'            => '更新时间',
        ];
    }

}