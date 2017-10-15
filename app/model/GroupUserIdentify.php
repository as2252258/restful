<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GroupUserIdentify
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $groupId
 * @property $authId
 */
class GroupUserIdentify extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group_user_identify';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'groupId', 'authId'], 'integer'],
			['userId', 'maxLength' => 20],
			[['groupId', 'authId'], 'maxLength' => 11],
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
            'groupId'           => '群组ID',
            'authId'            => '权限ID',
        ];
    }

}