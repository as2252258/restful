<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class AdminGroup
 * @package Inter\mysql
 *
 * @property $id
 * @property $title
 * @property $auth
 * @property $status
 * @property $addTime
 */
class AdminGroup extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_admin_group';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['addTime'], 'required'],
			[['title', 'auth'], 'string'],
			['status', 'integer'],
			['addTime', 'datetime'],
			['title', 'maxLength' => 100],
			['status', 'maxLength' => 1],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'title'             => '组昵称',
            'auth'              => '权限组',
            'status'            => '状态',
            'addTime'           => '',
        ];
    }

}