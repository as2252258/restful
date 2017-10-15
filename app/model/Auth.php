<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Auth
 * @package Inter\mysql
 *
 * @property $id
 * @property $module
 * @property $alias
 * @property $controller
 * @property $action
 * @property $neeLogin
 * @property $status
 * @property $addTime
 * @property $modifyTime
 */
class Auth extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_auth';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['module', 'neeLogin', 'status'], 'integer'],
			[['alias', 'controller', 'action'], 'string'],
			[['addTime', 'modifyTime'], 'datetime'],
			[['module', 'status'], 'maxLength' => 2],
			['neeLogin', 'maxLength' => 1],
			['alias', 'maxLength' => 50],
			[['controller', 'action'], 'maxLength' => 30],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'module'            => '模块 1.前台  2.个人中心  3.后台',
            'alias'             => '操作名称',
            'controller'        => 'controller',
            'action'            => 'action',
            'neeLogin'          => '是否需要登录 0.不需要  1.需要',
            'status'            => '1.正常  2.待审  3.草稿   4.删除',
            'addTime'           => '注册时间',
            'modifyTime'        => '修改时间',
        ];
    }

}