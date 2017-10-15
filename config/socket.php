<?php

define('LOG_PATH' , BASE_PATH . '/runtime/server.log');

file_put_contents(LOG_PATH , '');

return [
	'class'    => 'yoc\base\Server' ,
	'port'     => 8001 ,
	'httpPort' => 8000 ,
	'host'     => '127.0.0.1' ,
	'httpHost' => '127.0.0.1' ,
//	'process'  => ['yoc\\core\\WebSocket' , 'process'] ,
	'callback' => [
		'start'       => 'yoc\\core\\WebSocket' ,
		'workerStart' => 'yoc\\core\\WebSocket' ,
		'open'        => 'yoc\\core\\WebSocket' ,
		'message'     => 'yoc\\core\\WebSocket' ,
		'task'        => 'yoc\\core\\WebSocket' ,
		'finish'      => 'yoc\\core\\WebSocket' ,
		'close'       => 'yoc\\core\\WebSocket' ,
	] ,
	'setting'  => [
		'worker_num'               => 8 ,                  //设置启动的worker进程数量。swoole采用固定worker进程的模式
		'reactor_num'              => 8 ,
		'backlog'                  => 1000 ,                   //此参数将决定最多同时有多少个待accept的连接。
		'max_request'              => 40000 ,              //此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
		'max_conn'                 => 40000 ,
		'open_eof_check'           => false ,           //打开buffer
		'package_eof'              => "\r\n\r\n" ,        //设置EOF
		'dispatch_mode'            => 1 ,
		'daemonize'                => 1 ,                   //是否以守护进程模式启动
		'open_cpu_affinity'        => 1 ,           //启用CPU亲和设置
		'open_tcp_nodelay'         => 1 ,            //启用tcp_nodelay
		'tcp_defer_accept'         => 1 ,            //此参数设定一个秒数，当客户端连接连接到服务器时，在约定秒数内并不会触发accept，直到有数据发送，或者超时时才会触发。
		'task_worker_num'          => 400 ,
		'enable_port_reuse'        => true ,
		'log_file'                 => LOG_PATH ,
		'discard_timeout_request'  => false ,
		'open_mqtt_protocol'       => true ,
		'task_ipc_mode'            => 2 ,
		'task_max_request'         => 100000 , //设置task进程的最大任务数。
		'message_queue_key'        => 0x72000120 ,
		'tcp_fastopen'             => true ,
		'pid_file'                 => __DIR__ . '/../runtime/server.pid' ,
//		'enable_reuse_port'       => true,
//    'user'            => 'root',
//    'group'           => 'root',
		'heartbeat_idle_time'      => 600 ,
		'heartbeat_check_interval' => 50 ,
		'package_max_length'       => 3096000 ,
		//'ssl_cert_file'   => '/data/web/www.yemaoka.com.crt',
		//'ssl_key_file'    => $key_dir.'/ssl.key',
	]
];