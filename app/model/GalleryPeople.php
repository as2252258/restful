<?php
namespace app\model;

use yoc\db\ActiveRecord;
		
/**
 * Class GalleryPeople
 * @package Inter\mysql
 *
 * @property $id
 * @property $galleryId
 * @property $userId
 * @property $createTime
 */
class GalleryPeople extends ActiveRecord
{
    protected $primary = 'id';

    protected $appends = [];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return 'xl_gallery_people';
    }
    

	/**
	 * @return array
	 */
    public function rules(){
        return [
			[['galleryId', 'userId', 'createTime'], 'integer'],
			[['galleryId', 'userId'], 'maxLength' => 11],
			['createTime', 'maxLength' => 10],
        ];
    }
        
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [
            'id'                => '',
            'galleryId'         => '相册ID',
            'userId'            => '指定查看用户',
            'createTime'        => '',
        ];
    }

}