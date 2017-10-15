<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GroupShield
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $groupId
 * @property $shieldId
 * @property $createTime
 * @property $modifyTime
 */
class GroupShield extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group_shield';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'groupId', 'shieldId'], 'integer'],
			[['createTime', 'modifyTime'], 'timestamp'],
			[['userId', 'shieldId'], 'maxLength' => 20],
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
            'userId'            => '屏蔽人',
            'groupId'           => '群组ID',
            'shieldId'          => '被屏蔽人',
            'createTime'        => '创建时间',
            'modifyTime'        => '更新时间',
        ];
    }

}