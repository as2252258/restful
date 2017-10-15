<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Blacklist
 * @package Inter\mysql
 *
 * @property $id
 * @property $ip
 * @property $url
 * @property $name
 * @property $type
 * @property $status
 * @property $addTime
 */
class Blacklist extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_blacklist';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['ip', 'url', 'name', 'type'], 'string'],
			[['status', 'addTime'], 'integer'],
			[['ip', 'name'], 'maxLength' => 200],
			['type', 'maxLength' => 50],
			['status', 'maxLength' => 1],
			['addTime', 'maxLength' => 10],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'ip'                => '',
            'url'               => '',
            'name'              => '',
            'type'              => '',
            'status'            => '',
            'addTime'           => '',
        ];
    }

}