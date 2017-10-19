<?php

namespace app\model;

use yoc\db\ActiveRecord;

/**
 * Class User
 *
 * @package Inter\mysql
 *
 * @property $id
 * @property $username
 * @property $nickname
 * @property $password
 * @property $email
 * @property $phone
 * @property $sex
 * @property $birthday
 * @property $avator
 * @property $glob
 * @property $point
 * @property $groupId
 * @property $isOnline
 * @property $status
 * @property $addTime
 * @property $modifyTime
 * @property $lastloginTime
 */
class User extends ActiveRecord
{
	protected $primary = 'id';
	
	protected $appends = [];
	
	/**
	 * @inheritdoc
	 */
	public function attributes() : array
	{
		return [
			'id'            => 'Id' ,
			'username'      => '用户名' ,
			'nickname'      => '昵称' ,
			'password'      => '登陆密码' ,
			'email'         => '电子邮箱' ,
			'phone'         => '手机号码' ,
			'sex'           => '性别  1.男  2.女  3.未知' ,
			'birthday'      => '生日' ,
			'avator'        => '头像' ,
			'glob'          => '金币' ,
			'point'         => '积分' ,
			'groupId'       => 'GroupId' ,
			'isOnline'      => '是否在线 0.不在线 1.在线' ,
			'status'        => '1.正常  2.待审  3.冻结   4.删除  5.拉黑' ,
			'addTime'       => '注册时间' ,
			'modifyTime'    => '修改时间' ,
			'lastloginTime' => '最后登录时间' ,
		];
	}
	
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['avator'] , 'required'] ,
			[['username' , 'nickname' , 'password' , 'email' , 'phone' , 'avator'] , 'string'] ,
			[['sex' , 'glob' , 'point' , 'groupId' , 'isOnline' , 'status' , 'lastloginTime'] , 'integer'] ,
			['birthday' , 'date'] ,
			[['addTime' , 'modifyTime'] , 'datetime'] ,
			['username' , 'maxLength' => 40] ,
			['nickname' , 'maxLength' => 30] ,
			['password' , 'maxLength' => 96] ,
			['email' , 'maxLength' => 50] ,
			['phone' , 'maxLength' => 11] ,
			['avator' , 'maxLength' => 100] ,
			[['sex' , 'isOnline'] , 'maxLength' => 1] ,
			[['glob' , 'point' , 'groupId' , 'lastloginTime'] , 'maxLength' => 10] ,
			['status' , 'maxLength' => 2] ,
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'xl_user';
	}
	
	/**
	 * @return int|string
	 */
	public function setStatus()
	{
		if (!is_numeric($this->status)) {
			return 1;
		}
		return $this->status;
	}
}