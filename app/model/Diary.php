<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Diary
 * @package Inter\mysql
 *
 * @property $id
 * @property $uid
 * @property $title
 * @property $tag
 * @property $content
 * @property $view_count
 * @property $reply_count
 * @property $auth
 * @property $comment_authority
 * @property $thumbnails
 * @property $is_hot
 * @property $is_top
 * @property $status
 * @property $addTime
 * @property $upTime
 */
class Diary extends ActiveRecord
{
	protected $primary = 'id';

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_journal';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['content', 'title'], 'required'],
			[['uid', 'view_count', 'reply_count', 'auth', 'comment_authority', 'is_hot', 'is_top', 'status', 'addTime', 'upTime'], 'integer'],
			[['title', 'tag', 'content', 'thumbnails'], 'string'],
			[['uid', 'view_count', 'reply_count'], 'maxLength' => 11],
			[['auth', 'comment_authority', 'is_hot', 'is_top', 'status'], 'maxLength' => 1],
			[['addTime', 'upTime'], 'maxLength' => 10],
			[['title', 'tag'], 'maxLength' => 200],
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
            'content'           => '内容',
            'view_count'        => '浏览量',
            'reply_count'       => '回复量',
            'auth'              => '查看权限 0.全部  1.指定人可看  2.指定人不可看  3.除自己都不可以看',
            'comment_authority' => '评论权限 0.全部  1.指定人可评论  2.指定人不可评论  3.都不能评论',
            'thumbnails'        => '封面图',
            'is_hot'            => '是否热搜 0.否 1.是',
            'is_top'            => '是否置顶 0.否 1.是',
            'status'            => '文章状态',
            'addTime'           => '发布时间',
            'upTime'            => '更新时间',
        ];
    }
}