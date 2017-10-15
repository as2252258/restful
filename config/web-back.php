<?php

return [
	'serverName'     => 'restful server',
	'modelPath'      => [
		'path'      => BASE_PATH . '/app/model',
		'namespace' => 'app\\model'
	],
	'controllerPath' => [
		'path'      => BASE_PATH . '/app/controller',
		'namespace' => 'app\\controller'
	],
	'components'     => [
		'error'     => [
			'class' => 'yoc\exception\ErrorHandler',
		],
		'auth'      => [
			'class' => 'yoc\core\Authorize',
		],
		'fileCache' => [
			'class' => 'yoc\cache\FileCache',
		],
		'db'        => [
			'class'        => 'yoc\db\Connect',
			'cds'          => 'mysql:dbname=xl_advanced;host=127.0.0.1',
			'username'     => '',
			'password'     => '',
			'prefix'       => 'xl_',
			'slaveConfigs' => [
				['mysql:dbname=xl_advanced;host=', '', '']
			]
		],
		'redis'     => [
			'class'    => 'yoc\cache\Redis',
			'database' => 0,
			'port'     => 6391,
			'auth'     => '',
			'host'     => '127.0.0.1'
		],
		'memcache'     => [
			'class'    => 'yoc\cache\Memcache',
			'database' => 0,
			'port'     => 6391,
			'auth'     => '',
			'host'     => '127.0.0.1'
		],
		'qiniu'     => [
			'class'      => 'yoc\core\Qiniu',
			'bucket'     => '',
			'pathUrl'    => '',
			'access_key' => '',
			'secret_key' => ''
		],
		'email'     => [
			'class' => 'yoc\core\Email',
			'host'  => '',
			'name'  => '',
			'pass'  => '',
			'nick'  => '',
		],
		'response'  => [
			'class'      => 'yoc\http\Response',
			'statusCode' => 200,
			'format'     => 'json',
			'isHttp'     => false,
		],
		'socket'    => require_once BASE_PATH . '/config/socket.php'
	]
];