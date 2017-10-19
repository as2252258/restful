<?php
/**
 * Created by PhpStorm.
 * User: 向林
 * Date: 2016/8/9 0009
 * Time: 17:43
 */

namespace yoc\base;

use yoc\http\Request;
use yoc\pool\ModelPool;

/**
 * Class Gii
 *
 * @package Inter\utility
 */
class Gii
{
	public $rules = [];
	public $type = [
		'integer'  => ['tinyint' , 'smallint' , 'mediumint' , 'int' , 'bigint'] ,
		'string'   => ['char' , 'varchar' , 'tinytext' , 'text' , 'mediumtext' , 'longtext' ,] ,
		'date'     => ['date'] ,
		'time'     => ['time'] ,
		'year'     => ['year'] ,
		'datetime' => ['datetime'] ,
		'float'    => ['float' , 'double' , 'decimal' ,] ,
	];
	private $tableName = null;
	private $document = null;
	private $isUpdate = false;
	private $fileList = [];
	private $keyword = ['ADD' , 'ALL' , 'ALTER' , 'AND' , 'AS' , 'ASC' , 'ASENSITIVE' , 'BEFORE' , 'BETWEEN' , 'BIGINT' , 'BINARY' , 'BLOB' , 'BOTH' , 'BY' , 'CALL' , 'CASCADE' , 'CASE' , 'CHANGE' , 'CHAR' , 'CHARACTER' , 'CHECK' , 'COLLATE' , 'COLUMN' , 'CONDITION' , 'CONNECTION' , 'CONSTRAINT' , 'CONTINUE' , 'CONVERT' , 'CREATE' , 'CROSS' , 'CURRENT_DATE' , 'CURRENT_TIME' , 'CURRENT_TIMESTAMP' , 'CURRENT_USER' , 'CURSOR' , 'DATABASE' , 'DATABASES' , 'DAY_HOUR' , 'DAY_MICROSECOND' , 'DAY_MINUTE' , 'DAY_SECOND' , 'DEC' , 'DECIMAL' , 'DECLARE' , 'DEFAULT' , 'DELAYED' , 'DELETE' , 'DESC' , 'DESCRIBE' , 'DETERMINISTIC' , 'DISTINCT' , 'DISTINCTROW' , 'DIV' , 'DOUBLE' , 'DROP' , 'DUAL' , 'EACH' , 'ELSE' , 'ELSEIF' , 'ENCLOSED' , 'ESCAPED' , 'EXISTS' , 'EXIT' , 'EXPLAIN' , 'FALSE' , 'FETCH' , 'FLOAT' , 'FLOAT4' , 'FLOAT8' , 'FOR' , 'FORCE' , 'FOREIGN' , 'FROM' , 'FULLTEXT' , 'GOTO' , 'GRANT' , 'GROUP' , 'HAVING' , 'HIGH_PRIORITY' , 'HOUR_MICROSECOND' , 'HOUR_MINUTE' , 'HOUR_SECOND' , 'IF' , 'IGNORE' , 'IN' , 'INDEX' , 'INFILE' , 'INNER' , 'INOUT' , 'INSENSITIVE' , 'INSERT' , 'INT' , 'INT1' , 'INT2' , 'INT3' , 'INT4' , 'INT8' , 'INTEGER' , 'INTERVAL' , 'INTO' , 'IS' , 'ITERATE' , 'JOIN' , 'KEY' , 'KEYS' , 'KILL' , 'LABEL' , 'LEADING' , 'LEAVE' , 'LEFT' , 'LIKE' , 'LIMIT' , 'LINEAR' , 'LINES' , 'LOAD' , 'LOCALTIME' , 'LOCALTIMESTAMP' , 'LOCK' , 'LONG' , 'LONGBLOB' , 'LONGTEXT' , 'LOOP' , 'LOW_PRIORITY' , 'MATCH' , 'MEDIUMBLOB' , 'MEDIUMINT' , 'MEDIUMTEXT' , 'MIDDLEINT' , 'MINUTE_MICROSECOND' , 'MINUTE_SECOND' , 'MOD' , 'MODIFIES' , 'NATURAL' , 'NOT' , 'NO_WRITE_TO_BINLOG' , 'NULL' , 'NUMERIC' , 'ON' , 'OPTIMIZE' , 'OPTION' , 'OPTIONALLY' , 'OR' , 'ORDER' , 'OUT' , 'OUTER' , 'OUTFILE' , 'PRECISION' , 'PRIMARY' , 'PROCEDURE' , 'PURGE' , 'RAID0' , 'RANGE' , 'READ' , 'READS' , 'REAL' , 'REFERENCES' , 'REGEXP' , 'RELEASE' , 'RENAME' , 'REPEAT' , 'REPLACE' , 'REQUIRE' , 'RESTRICT' , 'RETURN' , 'REVOKE' , 'RIGHT' , 'RLIKE' , 'SCHEMA' , 'SCHEMAS' , 'SECOND_MICROSECOND' , 'SELECT' , 'SENSITIVE' , 'SEPARATOR' , 'SET' , 'SHOW' , 'SMALLINT' , 'SPATIAL' , 'SPECIFIC' , 'SQL' , 'SQLEXCEPTION' , 'SQLSTATE' , 'SQLWARNING' , 'SQL_BIG_RESULT' , 'SQL_CALC_FOUND_ROWS' , 'SQL_SMALL_RESULT' , 'SSL' , 'STARTING' , 'STRAIGHT_JOIN' , 'TABLE' , 'TERMINATED' , 'THEN' , 'TINYBLOB' , 'TINYINT' , 'TINYTEXT' , 'TO' , 'TRAILING' , 'TRIGGER' , 'TRUE' , 'UNDO' , 'UNION' , 'UNIQUE' , 'UNLOCK' , 'UNSIGNED' , 'UPDATE' , 'USAGE' , 'USE' , 'USING' , 'UTC_DATE' , 'UTC_TIME' , 'UTC_TIMESTAMP' , 'VALUES' , 'VARBINARY' , 'VARCHAR' , 'VARCHARACTER' , 'VARYING' , 'WHEN' , 'WHERE' , 'WHILE' , 'WITH' , 'WRITE' , 'X509' , 'XOR' , 'YEAR_MONTH' , 'ZEROFILL'];
	
	public static function run(Request $request)
	{
		$gii = new Gii();
		if (!empty($request->input->string('t'))) {
			$gii->tableName = $request->input->string('t');
			\Yoc::$app->redis->del(\Yoc::$app->redis->keys($gii->tableName . '*'));
			ModelPool::removeByPatten($gii->tableName);
		}
		if ($request->input->checkRequestParam('integer' , 'm' , 1)) {
			$model = 1;
		}
		if ($request->input->checkRequestParam('integer' , 'c' , 1)) {
			$c = 1;
		}
		if ($request->input->get('isUpdate') == 1) {
			$gii->isUpdate = true;
		}
		$gii->getTable($c , $model);
		return $gii->fileList;
	}
	
	private function getTable(&$m , &$c)
	{
		$tables = [];
		if (!empty($this->tableName)) {
			if (strpos(',' , $this->tableName)) {
				$data = explode(',' , $this->tableName);
				$tables = $this->getFields($data);
			} else {
				$tables = $this->getFields($this->tableName);
			}
		} else {
			$_tables = \DB::quote('show tables');
			if (!empty($_tables)) {
				$res = [];
				foreach ($_tables as $key => $val) {
					$res[] = $val["Tables_in_xl_advanced"];
				}
				$tables = $this->getFields($res);
			}
		}
		if (!empty($tables)) {
			foreach ($tables as $key => $val) {
				$data = $this->createModelFile($key , $val);
				if ($m == 1 && $c == 1) {
					$this->createCFile($data['classFileName'] , $data['fields']);
					$this->createMFile($data['classFileName'] , $data['tableName'] , $data['visible'] , $data['res'] , $data['fields']);
				} else if ($m == 1) {
					$this->createMFile($data['classFileName'] , $data['tableName'] , $data['visible'] , $data['res'] , $data['fields']);
				} else {
					$this->createCFile($data['classFileName'] , $data['fields']);
				}
			}
		}
	}
	
	private function getFields($tables)
	{
		$res = [];
		if (is_array($tables)) {
			foreach ($tables as $key => $val) {
				if (empty($val)) continue;
				$_tmp = \DB::quote('SHOW FULL FIELDS FROM ' . $val);
				if (!empty($_tmp)) {
					$res[$val] = $_tmp;
				}
			}
		} else {
			$_tmp = \DB::quote('SHOW FULL FIELDS FROM ' . $tables);
			if (!empty($_tmp)) {
				$res[$tables] = $_tmp;
			}
		}
		return $res;
	}
	
	public function createModelFile($tableName , $tables)
	{

//		if (!is_dir('model')) {
//			mkdir('model');
//		}
		
		$res = $visible = $fields = $keys = [];
		$_fields = array_column($tables , 'Field');
		foreach (['id'] as $key => $val) {
			if (!in_array($val , $_fields)) {
				throw new \Exception('必填字段' . $val . '不存在');
			}
		}
		
		foreach ($tables as $_key => $_val) {
			if ($_val['Field'] == 'id' && $_val['Extra'] == 'auto_increment') {
				$keys = $tableName;
			}
			if (in_array(strtoupper($_val['Field']) , $this->keyword)) {
				throw new \Exception('You can not use keyword "' . $_val['Field'] . '" as field at table "' . $tableName . '"');
			}
			array_push($visible , $this->createVisible($_val['Field']));
			array_push($fields , $_val);
			$res[] = $this->createSetFunc($_val['Field'] , $_val['Comment']);
		}
		
		if (empty($keys)) {
			throw new \Exception('please check table ' . $tableName . ', the table do not have primary id or id is not auto_increment');
		}
		
		$classFileName = $this->getClassName($keys);
		
		return [
			'classFileName' => $classFileName ,
			'tableName'     => $keys ,
			'visible'       => $visible ,
			'fields'        => $fields ,
			'res'           => $res ,
		];
	}
	
	private function createVisible($field)
	{
		return '
 * @property $' . $field;
	}
	
	private function createSetFunc($field , $comment)
	{
		return '
            ' . str_pad('\'' . $field . '\'' , 20 , ' ' , STR_PAD_RIGHT) . '=> \'' . (empty($comment) ? ucfirst($field) : $comment) . '\',';
	}
	
	private function getClassName($tableName)
	{
		$res = [];
		foreach (explode('_' , $tableName) as $n => $val) {
			$res[] = ucfirst($val);
		}
		return implode('' , $res) . 'Model';
	}
	
	private function createCFile($className , $fields)
	{
		$path = \Yoc::$app->controllerPath;
		$modelPath = \Yoc::$app->modelPath;
		
		$modelName = str_replace('Xl' , '' , $className);
		$_className = str_replace('Model' , '' , $className);
		$managerName = str_replace('Xl' , '' , $_className);
		
		$namespace = ltrim($path['namespace'] , '\\');
		$model_namespace = ltrim($modelPath['namespace'] , '\\');
		
		$class = '';
		$controller = $namespace . '\\' . $managerName . 'Controller';
		if (file_exists($path['path'] . '/' . $managerName . 'Controller.php')) {
			try {
				$class = new \ReflectionClass($controller);
			} catch (\Exception $e) {
				var_dump($e->getMessage());
			}
		}
		
		$html = $this->getUseContent($class , $controller);
		if (empty($html)) {
			$html .= "<?php
namespace {$namespace};

use Code;
use Exception;
use {$model_namespace}\\{$managerName};
use yoc\core\Str;
use yoc\http\Request;
use yoc\http\Response;
use app\components\ActiveController;
";
		}
		
		$html .= "
		
/**
 * Class {$managerName}Controller
 *
 * @package Controller
 */
class {$managerName}Controller extends ActiveController
{\n";
		$funcNames = [];
		$default = ['actionAdd' , 'actionUpdate' , 'actionDetail' , 'actionDelete' , 'actionList'];
		if (is_object($class)) {
			$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
			$funcNames = array_column($methods , 'name');
			if (!empty($methods)) foreach ($methods as $key => $val) {
				if ($val->class != $class->getName()) continue;
				$html .= "
	" . $val->getDocComment() . "\n";
				$content = $this->getFuncLineContent($class , $controller , $val->name) . "\n";
				if (in_array($val->name , $default)) {
					$newContent = $this->{'controller' . str_replace('action' , 'Method' , $val->name)}($fields , $managerName , $managerName);
//					print_r(array_diff_assoc(explode(PHP_EOL,$content),explode(PHP_EOL,$newContent)));
				}
				$html .= $this->getFuncLineContent($class , $controller , $val->name) . "\n";
			}
		}
		
		
		foreach ($default as $key => $val) {
			if (in_array($val , $funcNames)) continue;
			$html .= $this->{'controllerMethod' . str_replace('action' , '' , $val)}($fields , $managerName , $managerName) . "\n";
		}
		$html .= '
}';
		
		$file = $path['path'] . '/' . $managerName . 'Controller.php';
		if (file_exists($file)) {
			unlink($file);
		}
		
		file_put_contents($file , $html);
		$this->fileList[] = $managerName . 'Controller.php';
	}
	
	/**
	 * @param \ReflectionClass $object
	 * @param                  $className
	 *
	 * @return string
	 */
	public function getUseContent($object , $className)
	{
		$file = BASE_PATH . '/' . preg_replace('/\\\\/' , '/' , $className) . '.php';
		if (!file_exists($file)) {
			return '';
		}
		$content = file_get_contents(BASE_PATH . '/' . preg_replace('/\\\\/' , '/' , $className) . '.php');
		$explode = explode(PHP_EOL , $content);
		$exists = array_slice($explode , 0 , $object->getStartLine());
		$_tmp = [];
		foreach ($exists as $key => $val) {
			if (trim($val) == '/**') {
				break;
			}
			$_tmp[] = $val;
		}
		return trim(implode(PHP_EOL , $_tmp));
	}
	
	/**
	 * @param \ReflectionClass $object
	 * @param                  $className
	 * @param                  $method
	 *
	 * @return string
	 */
	public function getFuncLineContent($object , $className , $method)
	{
		$fun = $object->getMethod($method);
		$content = file_get_contents(BASE_PATH . '/' . preg_replace('/\\\\/' , '/' , $className) . '.php');
		$explode = explode(PHP_EOL , $content);
		$exists = array_slice($explode , $fun->getStartLine() - 1 , $fun->getEndLine() - $fun->getStartLine() + 1);
		return implode(PHP_EOL , $exists);
	}
	
	private function createMFile($classFileName , $tableName , $visible , $res , $fields)
	{
		
		$class = '';
		$modelPath = \Yoc::$app->modelPath;
		
		$managerName = str_replace('Xl' , '' , $classFileName);
		$managerName = str_replace('Model' , '' , $managerName);
		$namespace = ltrim($modelPath['namespace'] , '\\');
		$classFileName = ltrim($modelPath['namespace'] , '\\') . '\\' . $managerName;
		if (file_exists($modelPath['path'] . '/' . $managerName . '.php')) {
			try {
				$class = new \ReflectionClass($modelPath['namespace'] . '\\' . $managerName);
			} catch (\Exception $e) {
				var_dump($e->getMessage());
			}
		}
		
		$html = $this->getUseContent($class , $classFileName);
		if (empty($html)) {
			$html = '<?php
namespace ' . $namespace . ';

use yoc\db\ActiveRecord;';
		}
		$html .= '
		
/**
 * Class ' . $managerName . '
 * @package Inter\mysql
 *' . implode('' , $visible) . '
 */
class ' . $managerName . ' extends ActiveRecord
{';
		
		if (!empty($class)) {
			foreach ($class->getDefaultProperties() as $key => $val) {
				$html .= '
    protected $' . $key . ' = ' . (is_array($val) ? str_replace('"' , '\'' , json_encode($val)) : '\'' . $val . '\'') . ';' . "\n";
			}
		} else {
			$primary = $this->createPrimary($fields);
			if (!empty($primary)) {
				$html .= $primary . "\n";
			}
		}
		
		$html .= $this->createTableName($tableName) . "\n";
		
		$html .= $this->createRules($fields);
		
		
		$html .= '
    /**
     * @inheritdoc
     */
    public function attributes() : array
    {
        return [' . implode('' , $res) . '
        ];
    }' . "\n";
		
		
		$out = ['rules' , 'tableName' , 'attributes'];
		if (is_object($class)) {
			$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
			if (!empty($methods)) foreach ($methods as $key => $val) {
				if ($val->class != $class->getName()) continue;
				if (in_array($val->name , $out)) continue;
//				var_dump($val);
				$html .= "
	" . $val->getDocComment() . "\n";
				$html .= $this->getFuncLineContent($class , $classFileName , $val->name) . "\n";
			}
		}
		$html .= '
}';
		
		$file = rtrim($modelPath['path'] , '/') . '/' . $managerName . '.php';
		if (file_exists($file)) {
			unlink($file);
		}
		
		file_put_contents($file , $html);
		$this->fileList[] = $managerName . '.php';
	}
	
	/**
	 * 用来生成文档的
	 * 格式
	 * array(
	 *      'field' ,'字段類型' ,'是否必填' ,'字段长度' , '字段解释',
	 *      'field' ,'字段類型' ,'是否必填' ,'字段长度' , '字段解释',
	 *      'field' ,'字段類型' ,'是否必填' ,'字段长度' , '字段解释',
	 *      'field' ,'字段類型' ,'是否必填' ,'字段长度' , '字段解释',
	 *      'field' ,'字段類型' ,'是否必填' ,'字段长度' , '字段解释',
	 *      'field' ,'字段類型' ,'是否必填' ,'字段长度' , '字段解释',
	 * )
	 */
	private function createPrimary($fields)
	{
		foreach ($fields as $key => $val) {
			if ($val['Extra'] == 'auto_increment') {
				return '
	protected $primary = \'' . $val['Field'] . '\';';
			}
		}
		return '';
	}
	
	private function createTableName($field)
	{
		return '
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return \'' . $field . '\';
    }
    ';
	}
	
	private function createRules($fields)
	{
		$data = [];
		foreach ($fields as $key => $val) {
			if ($val['Extra'] == 'auto_increment') continue;
			$type = preg_replace('/\(.*?\)|\s+\w+/' , '' , $val['Type']);
			foreach ($this->type as $_key => $_val) {
				if (in_array($type , $_val)) {
					$type = $_key;
					break;
				}
			}
			$data[$type][] = $val;
		}
		
		$_field_one = '';
		$required = $this->getRequired($fields);
		if (!empty($required)) {
			$_field_one .= $required;
		}
		foreach ($data as $key => $val) {
			$field = '[\'' . implode('\', \'' , array_column($val , 'Field')) . '\']';
			if (count($val) == 1) {
				$field = '\'' . current($val)['Field'] . '\'';
			}
			$_field_one .= '
			[' . $field . ', \'' . $key . '\'],';
		}
		foreach ($data as $key => $val) {
			$length = $this->getLength($val);
			if (!empty($length)) {
				$_field_one .= $length . ',';
			}
		}
		$required = $this->getUnique($fields);
		if (!empty($required)) {
			$_field_one .= $required;
		}
		return '
	/**
	 * @return array
	 */
    public function rules(){
        return [' . $_field_one . '
        ];
    }
        ';
	}
	
	public function getRequired($val)
	{
		$data = [];
		foreach ($val as $_key => $_val) {
			if ($_val['Extra'] == 'auto_increment') continue;
			if ($_val['Key'] == 'PRI' || $this->checkIsRequired($_val) === 'true') {
				array_push($data , $_val['Field']);
			}
		}
		if (empty($data)) {
			return '';
		}
		return '
			[[\'' . implode('\', \'' , $data) . '\'], \'required\'],';
	}
	
	private function checkIsRequired($val)
	{
		return strtolower($val['Null']) == 'no' && $val['Default'] === null ? 'true' : 'false';
	}
	
	public function getLength($val)
	{
		$data = [];
		foreach ($val as $key => $_val) {
			$preg = preg_match('/\((.*?)\)/' , $_val['Type'] , $results);
			if ($preg && isset($results[1])) {
				$data[$results[1]][] = $_val['Field'];
			}
		}
		if (empty($data)) return '';
		$string = [];
		foreach ($data as $key => $_val) {
			if (count($_val) == 1) {
				$_tmp = '
			[\'' . current($_val) . '\', \'maxLength\' => ' . $key . ']';
			} else {
				$_tmp = '
			[[\'' . implode('\', \'' , $_val) . '\'], \'maxLength\' => ' . $key . ']';
			}
			$string[] = $_tmp;
		}
		return implode(',' , $string);
	}
	
	public function getUnique($fields)
	{
		$data = [];
		foreach ($fields as $_key => $_val) {
			if ($_val['Extra'] == 'auto_increment') continue;
			if (strpos($_val['Type'] , 'unique') !== false) {
				$data[] = $_val['Field'];
			}
		}
		if (empty($data)) {
			return '';
		}
		return '
			[[\'' . implode('\', \'' , $data) . '\'], \'unique\'],';
	}
	
	public function setDocuemnt($document)
	{
		$this->document = $document;
		if (empty($this->document)) {
			return $_SERVER['DOCUMENT_ROOT'] . '/model';
		}
		return $this->document;
	}
	
	public function controllerMethodAdd($fields , $className , $object = null)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new ' . $className . '();
		$results = $model->load() && !$model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}';
	}
	
	public function controllerMethodAdds($fields , $className , $object = null)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionAdd(Request $request){
		$model = new ' . $className . '();
		$model->setBatch([' . $this->getData($className , $fields) . '
		]);
		$results = $model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}';
	}
	
	private function getData($object , $fields , $request = 'post')
	{
		$html = '';
		
		$length = $this->getMaxLength($fields);
		
		foreach ($fields as $key => $val) {
			preg_match('/\d+/' , $val['Type'] , $number);
			$type = strtolower(preg_replace('/\(\d+\)/' , '' , $val['Type']));
			$first = preg_replace('/\s+\w+/' , '' , $type);
			if ($val['Field'] == 'id') continue;
			if ($type == 'timestamp') continue;
			$_field = [];
			foreach ($this->type as $_key => $value) {
				if (!in_array(strtolower($first) , $value)) continue;
				$comment = '//' . $val['Comment'];
				$_field['type'] = $_key;
				if ($type == 'date' || $type == 'datetime' || $type == 'time') {
					switch ($type) {
						case 'date':
							$_tps = '$request->input->' . $_key . '(\'' . $val['Field'] . '\', ' . $this->checkIsRequired($val) . ', date(\'Y-m-d\'))';
							break;
						case 'time':
							$_tps = '$request->input->' . $_key . '(\'' . $val['Field'] . '\', ' . $this->checkIsRequired($val) . ', date(\'H:i:s\'))';
							break;
						default:
							$_tps = '$request->input->' . $_key . '(\'' . $val['Field'] . '\', ' . $this->checkIsRequired($val) . ', date(\'Y-m-d H:i:s\'))';
					}
					$html .= '
            \'' . str_pad($val['Field'] . '\'' , $length , ' ' , STR_PAD_RIGHT) . ' => ' . str_pad($_tps . ',' , 60 , ' ' , STR_PAD_RIGHT) . $comment;
				} else {
					
					$tmp = 'null';
					if (isset($number[0])) {
						if (strpos(',' , $number[0])) {
							$tp = explode(',' , $number[0]);
							$tmp = '[' . $tp[0] . ',' . $tp[1] . ']';
							$_field['min'] = $tp[0];
							$_field['max'] = $tp[1];
						} else {
							$tmp = '[0,' . $number[0] . ']';
							$_field['max'] = $number[0];
						}
					}
					if ($type == 'int' && $number[0] == 10) {
						$_tps = '$request->input->' . $_key . '(\'' . $val['Field'] . '\', ' . ($this->checkIsRequired($val)) . ', ' . $tmp . ', time())';
					} else {
						if (empty($val['Default']) && !is_numeric($val['Default'])) {
							$_tps = '$request->input->' . $_key . '(\'' . $val['Field'] . '\', ' . ($this->checkIsRequired($val)) . ', ' . $tmp . ')';
						} else {
							$_tps = '$request->input->' . $_key . '(\'' . $val['Field'] . '\', ' . ($this->checkIsRequired($val)) . ', ' . $tmp . ',';
							if (is_numeric($val['Default'])) {
								$_tps .= $val['Default'] . ')';
							} else {
								$_tps .= '\'' . $val['Default'] . '\')';
							}
						}
					}
					$html .= '
            \'' . str_pad($val['Field'] . '\'' , $length , ' ' , STR_PAD_RIGHT) . ' => ' . str_pad($_tps . ',' , 60 , ' ' , STR_PAD_RIGHT) . $comment;
				}
				$_field['required'] = $this->checkIsRequired($val);
			}
			$this->rules[$val['Field']] = $_field;
		}
		return $html;
	}
	
	private function getMaxLength($fields)
	{
		$length = 0;
		foreach ($fields as $key => $val) {
			if (mb_strlen($val['Field'] . ' >=') > $length) $length = mb_strlen($val['Field'] . ' >=');
		}
		return $length;
	}
	
	public function controllerMethodUpdate($fields , $className , $object = null)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionUpdate(Request $request){
		$model = ' . $className . '::findOne($request->input->integer(\'id\'));
		if (empty($model)) {
			throw new \Exception(\'指定数据不存在\');
		}
		$results = $model->load() && !$model->save();
		if (!$results) {
			throw new Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}';
	}
	
	public function controllerMethodUpdates($fields , $className , $object = null)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function actionUpdate(Request $request){
		$model = ' . $className . '::findOne($request->input->integer(\'id\'));
		if (empty($model)) {
			throw new \Exception(\'指定数据不存在\');
		}
		$model->setBatch([' . $this->getData($className , $fields) . '
		]);
		$results = $model->save();
		if (!$results) {
			throw new \Exception($model->getLastError());
		}
		return Response::analysis(Code::SUCCESS, $results);
	}';
	}
	
	public function controllerMethodDetail($fields , $className , $managerName)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionDetail(Request $request){
        $check = $request->input->get(\'id\');
        if(empty($check)){
            throw new Exception(\'param id can not empty\');
        }
        $model = ' . $managerName . '::findOne($check);
        if(empty($model)){
            throw new Exception(\'Data Not Exists\');
        }
        return Response::analysis(Code::SUCCESS, $model);
    }';
	}
	
	public function controllerMethodDelete($fields , $className , $managerName)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionDelete(Request $request){
		$_key = $request->input->integer(\'id\', true);
		$pass = $request->input->password(\'password\', true);
		if(empty($this->user) || strcmp(Str::encrypt($pass), $this->user->password)){
		    throw new \Exception(\'密码错误\');
        }
		$model = ' . $managerName . '::findOne($_key);
		if (empty($model)) {
		    throw new \Exception(\'数据不存在\');
		}
        if(!$model->delete()){
            return Response::analysis(Code::ERROR, $model->getLastError());
        }
		return Response::analysis(Code::SUCCESS, $model);
    }';
	}
	
	public function controllerMethodList($fields , $className , $managerName , $object = null)
	{
		return '
    /**
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
    public function actionList(Request $request)
    {
        $pWhere = array();' . $this->getWhere($fields , $object) . '
        
        //分页处理
	    $count   = $request->input->get(\'count\', false, -1);
	    $order   = $request->input->get(\'order\', false, \'id\');
	    if(!empty($order)) {
	        $order .= $request->input->get(\'isDesc\') ? \' asc\' : \' desc\';
	    }else{
	        $order = \'id desc\';
	    }
	    
	    //列表输出
	    $model = ' . $managerName . '::where($pWhere)->orderBy($order);
	    if($count != -100){
		    $model->limit($request->input->page ,$request->input->size);
	    }
        if((int) $count === 1){
		    $count = $model->count();
	    }
		return Response::analysis(Code::SUCCESS,$model->all(),$count);
    }
    ';
	}
	
	private function getWhere($fields , $object)
	{
		$html = '';
		
		$length = $this->getMaxLength($fields);
		
		foreach ($fields as $key => $val) {
			preg_match('/\d+/' , $val['Type'] , $number);
			
			$type = strtolower(preg_replace('/\(\d+\)/' , '' , $val['Type']));
			
			$first = preg_replace('/\s+\w+/' , '' , $type);
			
			if ($val['Field'] == 'id') continue;
			if ($type == 'timestamp') continue;
			
			foreach ($this->type as $_key => $value) {
				if (!in_array(strtolower($first) , $value)) continue;
				$comment = '//' . $val['Comment'];
				if ($type == 'date' || $type == 'datetime' || $type == 'time') {
					$_tps = '$request->input->get(\'' . $val['Field'] . '\')';
					$html .= '
        $pWhere[\'' . str_pad($val['Field'] . ' <=\']' , $length , ' ' , STR_PAD_RIGHT) . ' = ' . str_pad($_tps . ';' , 60 , ' ' , STR_PAD_RIGHT) . $comment;
					$html .= '
        $pWhere[\'' . str_pad($val['Field'] . ' >=\']' , $length , ' ' , STR_PAD_RIGHT) . ' = ' . str_pad($_tps . ';' , 60 , ' ' , STR_PAD_RIGHT) . $comment;
				} else {
					
					$_tps = '$request->input->get(\'' . $val['Field'] . '\')';
					$html .= '
        $pWhere[\'' . str_pad($val['Field'] . '\']' , $length , ' ' , STR_PAD_RIGHT) . ' = ' . str_pad($_tps . ';' , 60 , ' ' , STR_PAD_RIGHT) . $comment;
				}
			}
		}
		return $html;
	}
	
	public function getFieldLength($fieldType)
	{
		preg_match('/\d+/' , $fieldType , $number);
		$tmp = 'null';
		if (isset($number[0])) {
			if (strpos(',' , $number[0])) {
				$tp = explode(',' , $number[0]);
				$tmp = '[' . $tp[0] . ',' . $tp[1] . ']';
			} else {
				$tmp = '[0,' . $number[0] . ']';
			}
		}
		return $tmp;
	}
	
	public function getUnsigned($val)
	{
		$data = [];
		foreach ($val as $_key => $_val) {
			if ($_val['Key'] == 'PRI' || $this->checkIsRequired($_val)) {
				$data[] = $_val['Field'];
			}
		}
		if (empty($data)) {
			return '';
		}
		return '
			[\'' . implode('\', \'' , $data) . '\', \'required\'],';
	}
	
	/**
	 * @param $oldString
	 * @param $newString
	 *
	 * @return array
	 * 函数体对比
	 * 逐行对比
	 */
	private function checkOfLine($fields , $oldString , $newString)
	{
		$string = [];
		$oldExplode = explode(PHP_EOL , $oldString);
		$newExplode = explode(PHP_EOL , $newString);
		
		foreach ($newExplode as $key => $val) {
			$newExplode[$key] = preg_replace('/\s+/' , '' , $val);
		}
		
		$fields = $this->loadFields($fields);
		foreach ($oldExplode as $key => $val) {
//			print_r(preg_replace('/\s+/', '', $val));
//			echo PHP_EOL;
		}

//        var_dump(implode(PHP_EOL, array_merge(array_filter($oldExplode, function ($value){
//            return trim($value) != '';
//        }), array_filter($newExplode, function ($value){
//            return trim($value) != '';
//        }))));
		
		return $oldString;
	}
	
	private function loadFields($fields)
	{
		return array_column($fields , 'Field');
	}
	
	private function getComment($fields)
	{
		$html = '';
		$length = $this->getMaxLength($fields);
		foreach ($fields as $val) {
			if ($val['Comment'] == 'id') continue;
			$type = preg_replace('/\(\d+\)/' , '' , $val['Type']);
			$start = preg_replace('/\s+\w+/' , '' , $type);
			if ($type == 'timestamp') continue;
			$html .= '
     * @param ' . str_pad($val['Field'] , $length , ' ' , STR_PAD_RIGHT) . '[' . str_pad($start . ']' , $length , ' ' , STR_PAD_RIGHT) . (empty($val['Comment']) ? '' : '[' . $val['Comment'] . ']');
		}
		return $html;
	}
}