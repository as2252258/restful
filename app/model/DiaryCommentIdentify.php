<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class DiaryCommentIdentify
 * @package Inter\mysql
 *
 * @property $id
 * @property $journalId
 * @property $userId
 */
class DiaryCommentIdentify extends ActiveRecord
{
	protected $primary = 'id';

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_journal_comment_identify';
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