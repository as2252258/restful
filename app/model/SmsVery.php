<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class SmsVery
 * @package Inter\mysql
 *
 * @property $id
 * @property $telephone
 * @property $veryCode
 * @property $sType
 * @property $createTime
 * @property $expirationTime
 */
class SmsVery extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_smsVery';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['telephone', 'veryCode', 'sType', 'createTime', 'expirationTime'], 'integer'],
			['telephone', 'maxLength' => 11],
			['veryCode', 'maxLength' => 6],
			['sType', 'maxLength' => 2],
			[['createTime', 'expirationTime'], 'maxLength' => 10],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'telephone'         => '',
            'veryCode'          => '',
            'sType'             => '短信类型 1.注册 2.修改密码 3.找回密码',
            'createTime'        => '',
            'expirationTime'    => '过期时间',
        ];
    }

}