<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class Process
 * @package Inter\mysql
 *
 * @property $id
 * @property $name
 * @property $onlyOne
 * @property $type
 * @property $command
 * @property $method
 * @property $isHttps
 * @property $host
 * @property $param
 * @property $runNum
 * @property $runTime
 * @property $status
 * @property $createTime
 * @property $modifyTime
 */
class Process extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_process';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['name', 'command', 'host', 'param'], 'string'],
			[['onlyOne', 'type', 'method', 'isHttps', 'runNum', 'runTime', 'status', 'createTime', 'modifyTime'], 'integer'],
			[['name', 'host'], 'maxLength' => 100],
			[['command', 'param'], 'maxLength' => 255],
			[['onlyOne', 'type', 'method', 'isHttps', 'status'], 'maxLength' => 1],
			['runNum', 'maxLength' => 11],
			[['runTime', 'createTime', 'modifyTime'], 'maxLength' => 10],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'name'              => '任务名称',
            'onlyOne'           => '是否一次性任务, 如果为一次性任务, 完成后删除',
            'type'              => '任务类型  1.执行shell命令  2.执行文件  3.访问某个路由',
            'command'           => '命令，当type为1的时候为shell命令 为2的时候为文件路径   为3的时候为要请求的地址',
            'method'            => '请求类型  默认为0.get  1.post  2.delete  3.option',
            'isHttps'           => '是否https  默认0.否  1.是',
            'host'              => '请求的域名',
            'param'             => '被执行时需要的参数',
            'runNum'            => '已被执行的次数',
            'runTime'           => '执行时间 执行间隔为分钟，至少1分钟，如果为空，则每分钟都执行',
            'status'            => '当前任务状态 0.不可执行  1.可执行',
            'createTime'        => '创建时间',
            'modifyTime'        => '更新时间',
        ];
    }

}