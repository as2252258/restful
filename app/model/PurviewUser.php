<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class PurviewUser
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $groupId
 * @property $addTime
 * @property $modifyTime
 */
class PurviewUser extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_purview_user';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'groupId'], 'integer'],
			[['addTime', 'modifyTime'], 'timestamp'],
			['userId', 'maxLength' => 20],
			['groupId', 'maxLength' => 11],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'userId'            => '用户组名',
            'groupId'           => '组ID',
            'addTime'           => '添加时间  创建时自动添加',
            'modifyTime'        => '修改时间  修改时自动更新',
        ];
    }

}