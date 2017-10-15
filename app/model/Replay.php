<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Replay
 * @package Inter\mysql
 *
 * @property $id
 * @property $type
 * @property $tid
 * @property $vid
 * @property $uid
 * @property $fid
 * @property $fName
 * @property $title
 * @property $content
 * @property $up
 * @property $down
 * @property $share
 * @property $commont
 * @property $status
 * @property $addTime
 */
class Replay extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_replay';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['content'], 'required'],
			[['type', 'tid', 'vid', 'uid', 'fid', 'up', 'down', 'share', 'commont', 'status', 'addTime'], 'integer'],
			[['fName', 'title', 'content'], 'string'],
			[['type', 'status'], 'maxLength' => 1],
			[['tid', 'vid', 'uid', 'fid', 'up', 'down', 'share', 'commont'], 'maxLength' => 11],
			['addTime', 'maxLength' => 10],
			[['fName', 'title'], 'maxLength' => 100],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'type'              => '消息类型',
            'tid'               => '回复ID',
            'vid'               => '视频ID',
            'uid'               => '发送用户',
            'fid'               => '接收用户',
            'fName'             => '接收用户ID',
            'title'             => '消息标题',
            'content'           => '回复内容',
            'up'                => '支持',
            'down'              => '反对',
            'share'             => '分享',
            'commont'           => '回复',
            'status'            => '状态',
            'addTime'           => '',
        ];
    }

}