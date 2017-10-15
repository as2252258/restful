<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GroupIdentify
 * @package Inter\mysql
 *
 * @property $id
 * @property $alias
 * @property $route
 */
class GroupIdentify extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_group_identify';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['alias', 'route'], 'string'],
			['alias', 'maxLength' => 200],
			['route', 'maxLength' => 50],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'alias'             => '权限别名',
            'route'             => '',
        ];
    }

}