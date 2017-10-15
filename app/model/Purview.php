<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Purview
 * @package Inter\mysql
 *
 * @property $id
 * @property $itemName
 * @property $addTime
 * @property $modifyTime
 */
class Purview extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = ['packet'];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_purview';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			['itemName', 'string'],
			[['addTime', 'modifyTime'], 'timestamp'],
			['itemName', 'maxLength' => 30],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'itemName'          => '用户组名',
            'addTime'           => '添加时间  创建时自动添加',
            'modifyTime'        => '修改时间  修改时自动更新',
        ];
    }

	
    public function getPacketAttribute(){
    	return Packet::where(['itemId'=>$this->id])->all();
    }

}