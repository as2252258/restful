<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Dynamic
 * @package Inter\mysql
 *
 * @property $id
 * @property $uid
 * @property $title
 * @property $tag
 * @property $type
 * @property $intro
 * @property $content
 * @property $view_count
 * @property $reply_count
 * @property $thumbnails
 * @property $is_hot
 * @property $is_top
 * @property $status
 * @property $addTime
 * @property $upTime
 */
class Dynamic extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_dynamic';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['content', 'reply_count'], 'required'],
			[['uid', 'type', 'view_count', 'reply_count', 'is_hot', 'is_top', 'status', 'addTime', 'upTime'], 'integer'],
			[['title', 'tag', 'intro', 'content', 'thumbnails'], 'string'],
			[['uid', 'type', 'view_count', 'reply_count'], 'maxLength' => 11],
			[['is_hot', 'is_top', 'status'], 'maxLength' => 1],
			[['addTime', 'upTime'], 'maxLength' => 10],
			[['title', 'tag'], 'maxLength' => 200],
			['intro', 'maxLength' => 500],
			['thumbnails', 'maxLength' => 100],
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
            'title'             => '标题',
            'tag'               => '标签',
            'type'              => '文章类型',
            'intro'             => '导读',
            'content'           => '内容',
            'view_count'        => '浏览量',
            'reply_count'       => '回复量',
            'thumbnails'        => '封面图',
            'is_hot'            => '是否热搜 0.否 1.是',
            'is_top'            => '是否置顶 0.否 1.是',
            'status'            => '文章状态',
            'addTime'           => '发布时间',
            'upTime'            => '更新时间',
        ];
    }

}