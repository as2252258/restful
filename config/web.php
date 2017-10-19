<?php

return [
	'serverName'     => 'restful server' ,
	'modelPath'      => [
		'path'      => BASE_PATH . '/app/model' ,
		'namespace' => 'app\\model'
	] ,
	'controllerPath' => [
		'path'      => BASE_PATH . '/app/controller' ,
		'namespace' => 'app\\controller'
	] ,
	'components'     => [
		'error'      => [
			'class' => 'yoc\exception\ErrorHandler' ,
		] ,
		'auth'       => [
			'class' => 'yoc\core\Authorize' ,
		] ,
		'fileCache'  => [
			'class' => 'yoc\cache\FileCache' ,
		] ,
		'db'         => [
			'class'        => 'yoc\db\Connect' ,
			'cds'          => 'mysql:dbname=xl_advanced;host=127.0.0.1' ,
			'username'     => 'root' ,
			'password'     => 'xl.2005113426' ,
			'prefix'       => 'xl_' ,
			'slaveConfigs' => [
				['mysql:dbname=xl_advanced;host=10.165.11.173' , 'mysql@slave.163.com' , 'mysql@slave.163.com']
			]
		] ,
		'redis'      => [
			'class'    => 'yoc\cache\Redis' ,
			'database' => 0 ,
			'port'     => 62001 ,
			'auth'     => '64e549f1d967a1e92493ff48ec74aa90' ,
			'host'     => '127.0.0.1'
		] ,
		'memcache'   => [
			'class'    => 'yoc\cache\Memcache' ,
			'database' => 0 ,
			'port'     => 62001 ,
			'auth'     => '64e549f1d967a1e92493ff48ec74aa90' ,
			'host'     => '127.0.0.1'
		] ,
		'qiniu'      => [
			'class'      => 'yoc\core\Qiniu' ,
			'bucket'     => 'xlimages' ,
			'pathUrl'    => 'xlimages.qiniudn.com' ,
			'access_key' => 'tXApHbkKJgwSXY4JyrosJBLlq299TJAWZfhgDiLT' ,
			'secret_key' => '6j0yi2GgCGCXQnrNKNOjrRs8FjAxCIC3ENuIuj9G'
		] ,
		'email'      => [
			'class' => 'yoc\core\Email' ,
			'host'  => 'smtpdm.aliyun.com' ,
			'name'  => 'tianyueserver@email.dlibli.com' ,
			'pass'  => 'AS123456xl' ,
			'nick'  => 'tianyueserver' ,
		] ,
		'response'   => [
			'class'      => 'yoc\http\Response' ,
			'statusCode' => 200 ,
			'format'     => 'json' ,
			'isHttp'     => false ,
		] ,
		'socket'     => require_once BASE_PATH . '/config/socket.php' ,
		'urlManager' => [
			'class'        => 'yoc\core\UrlManager' ,
			'enableSuffix' => false ,
			'suffix'       => 'html' ,
			'rules'        => [
				'detail/<id:\d+>'               => 'user/detail' ,
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>' ,
			]
		]
	]
];