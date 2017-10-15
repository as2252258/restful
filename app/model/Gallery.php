<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Gallery
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $title
 * @property $discration
 * @property $frontCover
 * @property $publicAuth
 * @property $password
 * @property $status
 * @property $createTime
 * @property $modifyTime
 */
class Gallery extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_gallery';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'publicAuth', 'status', 'createTime', 'modifyTime'], 'integer'],
			[['title', 'discration', 'frontCover', 'password'], 'string'],
			['userId', 'maxLength' => 11],
			['publicAuth', 'maxLength' => 2],
			['status', 'maxLength' => 1],
			[['createTime', 'modifyTime'], 'maxLength' => 10],
			['title', 'maxLength' => 30],
			[['discration', 'frontCover'], 'maxLength' => 255],
			['password', 'maxLength' => 32],
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
            'title'             => '相册名',
            'discration'        => '相册简介',
            'frontCover'        => '相册封面',
            'publicAuth'        => '相册权限 1.公开  2.需要密码  3.私有   4.指定用户可看',
            'password'          => '相册密码',
            'status'            => '相册状态',
            'createTime'        => '创建时间',
            'modifyTime'        => '修改时间',
        ];
    }

}