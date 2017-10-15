<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/9/22 0022
 * Time: 15:40
 */

namespace yoc\db\implement;


interface Databases
{
	/**
	 * @return string
	 */
	public static function tableName();
	
	/**
	 * @return array
	 * @inheritdoc
	 */
	public function rules();
	
	
	/**
	 * @return array
	 * @inheritdoc
	 */
	public function attributes();
}