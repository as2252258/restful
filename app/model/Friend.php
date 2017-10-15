<?php

namespace app\model;

use yoc\db\ActiveRecord;
use Yoc;
use Symfony\Component\Debug\FatalErrorHandler;

/**
 * Class Friend
 *
 * @package Inter\mysql
 *
 * @property $id
 * @property $cid
 * @property $userId
 * @property $groupId
 * @property $friendId
 * @property $remarks
 * @property $status
 * @property $createTime
 * @property $modifyTime
 */
class Friend extends ActiveRecord
{
	protected $primary = 'id';
	
	protected $appends = ['user'];
	
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'xl_friend';
	}
	
	
	/**
	 * @return array
	 */
	public function rules()
	{
		return [
			[['cid' , 'userId'] , 'required'] ,
			[['cid' , 'userId' , 'groupId' , 'friendId' , 'status' , 'createTime' , 'modifyTime'] , 'integer'] ,
			['remarks' , 'string'] ,
			[['cid' , 'userId' , 'friendId'] , 'maxLength' => 20] ,
			['groupId' , 'maxLength' => 11] ,
			['status' , 'maxLength' => 1] ,
			[['createTime' , 'modifyTime'] , 'maxLength' => 10] ,
			['remarks' , 'maxLength' => 50] ,
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
			'userId'     => '用户ID' ,
			'groupId'    => '用户分组ID' ,
			'friendId'   => '好友ID' ,
			'remarks'    => '好友备注' ,
			'status'     => '1.正常  2.拉黑  3.删除' ,
			'createTime' => '' ,
			'modifyTime' => '' ,
		];
	}
	
	/**
	 * @return array
	 */
	public function getUserAttribute()
	{
		$remarks = $this->remarks;
		$data = User::findOne($this->friendId);
		if (!empty($data)) {
			$data = $data->packet('id' , 'nickname' , 'avator' , 'isOnline');
			$data['remarks'] = empty($remarks) ? $data['nickname'] : $remarks;
			$data['userId'] = $this->friendId;
			$data['status'] = $this->status;
			$data['id'] = $this->id;
		}
		return $data;
	}
	
}