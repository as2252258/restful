<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class MemberDefaultAvatar
 * @package Inter\mysql
 *
 * @property $id
 * @property $avatar
 */
class MemberDefaultAvatar extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_member_default_avatar';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			['avatar', 'string'],
			['avatar', 'maxLength' => 100],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'avatar'            => '头像地址',
        ];
    }

}