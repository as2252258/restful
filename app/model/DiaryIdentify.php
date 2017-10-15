<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class DiaryIdentify
 * @package Inter\mysql
 *
 * @property $id
 * @property $journalId
 * @property $userId
 */
class DiaryIdentify extends ActiveRecord
{
	protected $primary = 'id';

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_journal_identify';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['journalId', 'userId'], 'integer'],
			['journalId', 'maxLength' => 11],
			['userId', 'maxLength' => 20],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'journalId'         => '日志ID',
            'userId'            => '用户ID',
        ];
    }

}