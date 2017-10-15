<?php

namespace app\model;

use yoc\db\ActiveRecord;

/**
 * Class Files
 *
 * @package Inter\mysql
 *
 * @property $id
 * @property $cid
 * @property $file_md5
 * @property $file_name
 * @property $createTime
 * @property $modifyTime
 */
class Files extends ActiveRecord
{
	protected $primary = 'id';
	
	protected $appends = [];
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'xl_files';
	}
	
	
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['file_md5' , 'file_name' , 'cid'] , 'string'] ,
			[['createTime' , 'modifyTime'] , 'timestamp'] ,
			['cid' , 'maxLength' => 200] ,
			['file_md5' , 'maxLength' => 32] ,
			['file_name' , 'maxLength' => 255] ,
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributes() : array
	{
		return [
			'id'         => '' ,
			'cid'        => '' ,
			'file_md5'   => '唯一HASH值' ,
			'file_name'  => '文件名称' ,
			'createTime' => '创建时间' ,
			'modifyTime' => '更新时间' ,
		];
	}
	
}