<?php

if (!function_exists('log_history_key')) {
	
	/**
	 * @param $userId
	 *
	 * @return string
	 */
	function log_history_key($userId)
	{
		return LOG_HISTORY . $userId;
	}
}


if (!function_exists('token_temp_key')) {
	
	/**
	 * @param $userId
	 * @param $type
	 *
	 * @return string
	 */
	function token_temp_key($userId)
	{
		$source = 'browser';
		$user = Yoc::getRequest()->input;
		if (empty($user->getHeader('source'))) {
			$source = $user->getHeader('source');
		}
		return TMP_TOKEN . '_' . strtoupper($source) . '_' . $userId;
	}
}


if (!function_exists('process_exists')) {
	
	/**
	 * @param string $server_name
	 *
	 * @return bool
	 */
	function process_exists($server_name = 'im server')
	{
		$cmd = 'ps axu|grep "' . $server_name . '"|grep -v "grep"|wc -l';
		$ret = shell_exec("$cmd");
		return (bool) rtrim($ret , "\r\n");
	}
}

if (!function_exists('socket')) {
	
	/**
	 * @return \swoole_websocket_server
	 */
	function socket()
	{
		return Yoc::$server;
	}
}

if (!function_exists('redis')) {
	
	/**
	 * @return \Redis
	 */
	function redis()
	{
		return Yoc::$app->redis;
	}
}

if (!function_exists('obs')) {
	
	function obs(){
		return 'ssssssss';
	}
}