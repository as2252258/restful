<?php

error_reporting(E_ALL ^ E_NOTICE);

define('BASE_PATH' , __DIR__);
define('VENDOR_PATH' , __DIR__ . '/vendor');
define('YOC_PATH' , __DIR__ . '/vendor/yoc');
define('RUNTIME_PATH' , __DIR__ . '/runtime');
define('TMP_PATH' , RUNTIME_PATH . '/tmp');
define('FILE_CACHE_DIR' , RUNTIME_PATH . '/cache');
define('LOGS_PATH' , RUNTIME_PATH . '/logs/');
define('TMP_TOKEN' , 'TMP_TOKEN');
define('DEBUG' , true);
define('ERROR_LEVEL_ERROR' , 'error');
define('ERROR_LEVEL_EXCEPTION' , 'exception');
define('ERROR_LEVEL_SHUT_DOWN' , 'shutdown');
define('ERROR_LEVEL_TASK' , 'task');
define('STATUS_SUCCESS' , 0);
define('CONTROLLER_PATH' , 'task_log');
define('ACCOUNT_MATCH' , '/^[a-zA-Z0-9@\.]{1,40}$/');
define('EMAIL_MATCH' , '/[a-zA-Z\d\.]+@[a-zA-Z\d]+(\.[a-zA-Z\d]+)+/');
define('PHONE_MATCH' , '/^1[35789]\d{9}$/');
define('TIME_MATCH' , '/\d{4}\-\d{2}\-\d{2}(\s+\d{2}:\d{2}:\d{2}){0,}/');
define('SOCKET_PATH' , RUNTIME_PATH . '/socket/');
define('SYSTEM_CACHE' , 'unknown_system_message_');
define('FRIEND_CACHE' , 'unknown_friend_message_');
define('GROUP_CACHE' , 'unknown_group_message_');
define('FD_LIST' , 'FD_LIST_');
define('USER_FD' , 'FD_ONLY_');
define('LOG_HISTORY' , 'log_history_');

require(__DIR__ . '/vendor/Yoc.php');
//require(__DIR__ . '/vendor/Connect.php');
require(__DIR__ . '/vendor/Reboot.php');
require(__DIR__ . '/vendor/autoload.php');

$config = require_once(__DIR__ . '/config/web.php');

function server_stop($config)
{
	$socket = BASE_PATH . '/runtime/socket/socket.sock';
	if (file_exists($socket)) {
		$pathId = file_get_contents($socket);
		if (!empty($pathId) && is_numeric($pathId)) {
			if (process_exists($config['serverName'])) {
				shell_exec("kill $pathId");
			}
			unlink($socket);
		}
		echo 'shutdown success' . PHP_EOL;
	} else {
		if (process_exists($config['serverName'])) {
			shell_exec("kill $pathId");
		}
		echo 'unknown error server not exists' . PHP_EOL;
	}
}

function server_start($config)
{
	$socket = BASE_PATH . '/runtime/socket/socket.sock';
	if (!file_exists($socket)) {
		$server = new \yoc\base\Application($config);
		return $server->start();
	}
	if (!process_exists($config['serverName'])) {
		$server = new \yoc\base\Application($config);
		return $server->start();
	}
	$pathId = file_get_contents($socket);
	if (!empty($pathId) && is_numeric($pathId)) {
		echo 'server is exists, can you want restart ?' . PHP_EOL;
		echo 'please [ yes | no ] : ';
		fscanf(STDIN , "%s\n" , $number); // 从 STDIN 读取数字
		if (strtolower($number) === 'yes') {
			server_stop($config);
			sleep(1.5);
		} else {
			exit;
		}
	}
	$server = new \yoc\base\Application($config);
	return $server->start();
}

if (!isset($argv[1])) {
	server_start($config);
} else {
	if ($argv[1] == 'stop') {
		server_stop($config);
	} else {
		if ($argv[1] == 'reload') {
			server_stop($config);
			echo 'starting ...' . PHP_EOL;
			sleep(1);
			server_start($config);
		} else {
			server_start($config);
		}
	}
}