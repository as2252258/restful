<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Set
 * @package Inter\mysql
 *
 * @property $id
 * @property $pid
 * @property $path
 * @property $name
 * @property $url
 * @property $display
 * @property $addTime
 * @property $updateTime
 */
class Set extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = ['child'];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_set';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['path'], 'required'],
			[['pid', 'display', 'addTime', 'updateTime'], 'integer'],
			[['path', 'name', 'url'], 'string'],
			[['pid', 'display', 'addTime', 'updateTime'], 'maxLength' => 11],
			['path', 'maxLength' => 255],
			['name', 'maxLength' => 40],
			['url', 'maxLength' => 200],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'pid'               => '',
            'path'              => '',
            'name'              => '',
            'url'               => '',
            'display'           => '',
            'addTime'           => '',
            'updateTime'        => '',
        ];
    }

	/**
	 * @return \yoc\db\Collection
	 * 获取子节点
	 */
    public function getChildAttribute(){
    	return Set::where(['pid'=>$this->id])->isExtension(false)->all();
    }

}