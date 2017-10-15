<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GroupBlack
 * @package Inter\mysql
 *
 * @property $id
 * @property $userId
 * @property $groupId
 * @property $sendId
 * @property $createTime
 * @property $modifyTime
 */
class GroupBlack extends ActiveRecord
{
	protected $primary = 'id';

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group_black';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['userId', 'groupId', 'sendId', 'createTime', 'modifyTime'], 'integer'],
			[['userId', 'groupId', 'sendId'], 'maxLength' => 20],
			[['createTime', 'modifyTime'], 'maxLength' => 10],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'userId'            => '被拉黑用户',
            'groupId'           => '群ID',
            'sendId'            => '操作用户',
            'createTime'        => '创建时间',
            'modifyTime'        => '更新时间',
        ];
    }

}