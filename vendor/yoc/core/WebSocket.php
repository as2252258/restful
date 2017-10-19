<?php
/**
 * Created by PhpStorm.
 * User: whwyy
 * Date: 2017/9/15 0015
 * Time: 1:45
 */

namespace yoc\core;

use app\logic\UserLogic;
use app\model\User;
use yoc\base\Objects;


/**
 * Class WebSocket
 *
 * @package yoc\server
 * ],
 */
class WebSocket extends Objects
{
	/**
	 * @param \swoole_websocket_server $server
	 * @param \swoole_http_request     $request
	 */
	public function onOpen(\swoole_websocket_server $server , \swoole_http_request $request)
	{
		try {
			$get = get_object_vars($request);
			if (!isset($get['get']) || !isset($get['get']['auth'])) {
				throw new \Exception('Auth Error');
			}
			
			$code = base64_decode($get['get']['auth']);
			if (empty($code)) {
				throw new \Exception('Not Find Auth Token');
			}
			
			$explode = explode('&' , $code);
			if (count($explode) < 2) {
				throw new \Exception('Auth Token Num Error');
			}
			$user = User::findOne($explode[2]);
			$data = ['token' => $explode[0] , 'time' => $explode[1] , 'user' => $explode[2] , 'source' => $explode[3]];
			if (!\Yoc::$app->auth->check($data , $user)) {
				throw new \Exception('Auth Check Fail');
			}
			
			$server->push($request->fd , $request->fd);
			$server->protect($request->fd , true);
			
			\Yoc::$app->asyncTask(UserLogic::className() , 'connect' , [$user , $request->fd]);
		} catch (\Exception $exception) {
			$server->push($request->fd , $exception->getMessage() . ' line ' . $exception->getLine() . ' file ' . $exception->getFile());
			$server->close($request->fd);
		}
	}
	
	/**
	 * @param \swoole_server $serv
	 */
	public function onStart(\swoole_server $serv)
	{
		if (!is_dir(TMP_PATH)) {
			mkdir(TMP_PATH);
		}
		echo 'start success' . PHP_EOL;
		file_put_contents(SOCKET_PATH . '/socket.sock' , $serv->master_pid);
		$redis = \Yoc::getRedis();
		$redis->del($redis->keys(FD_LIST . '*'));
		$redis->del($redis->keys(USER_FD . '*'));
		swoole_set_process_name(\Yoc::$app->serverName);
	}
	
	/**
	 * @param \swoole_server $request
	 * @param                $workeer_id
	 */
	public function onWorkerStart(\swoole_server $request , $workeer_id)
	{
		// TODO: Implement onWorkerStart() method.
		if ($workeer_id < 8) {
			\Yoc::$app->db->beginConnectPool();
			$kit = new \Reboot($request->master_pid);
			$kit->watch(BASE_PATH);
			$kit->addFileType('.php');
			$kit->run();
			swoole_set_process_name('work server');
		} else {
			swoole_set_process_name('task server');
		}
	}
	
	/**
	 * @param \swoole_server $serv
	 * @param                $task_id
	 * @param                $from_id
	 * @param                $data
	 */
	public function onTask(\swoole_server $serv , $task_id , $from_id , $data)
	{
		$time = microtime(true);
		try {
			if (is_string($data)) {
				$data = json_decode($data , true);
			}
			if (array_key_exists('class' , $data) && !empty($data['class'])) {
				$reflect = new \ReflectionClass($data['class']);
				if (!$reflect->hasMethod($data['name'][0])) {
					throw new \Exception('method ' . $data['name'][0] . ' not exists');
				}
				$method = $reflect->getMethod($data['name'][0]);
				$method->invokeArgs($reflect->newInstance() , $data['name'][1] ?? []);
			} else {
				if (!empty($data) && is_numeric($data)) {
					$serv->finish($data);
				} else {
					if (!array_key_exists('userId' , $data)) {
						throw new \Exception('接收用户不能为空');
					};
					if ($serv->exist($data['userId'])) {
						\Yoc::$server->push($data['userId'] , json_encode($data));
					}
				}
			}
			$endTime = microtime(true);
			$finish = json_encode(['taskId' => $task_id , 'status' => 'success' , 'data' => ArrayAccess::toArray($data) , 'info' => '' , 'runTime' => [
				'startTime' => $time ,
				'endTime'   => $endTime ,
				'runTime'   => $endTime - $time
			]]);
		} catch (\Exception $exception) {
			$endTime = microtime(true);
			\Yoc::getError()->setLogger($exception , ERROR_LEVEL_TASK);
			$message = "info : " . $exception->getMessage() . " on line " . $exception->getLine() . " at file " . $exception->getFile();
			$finish = json_encode(['taskId' => $task_id , 'status' => 'error' , 'data' => ArrayAccess::toArray($data) , 'info' => $message , 'runTime' => [
				'startTime' => $time ,
				'endTime'   => $endTime ,
				'runTime'   => $endTime - $time
			]]);
		}
		$serv->finish($finish);
	}
	
	/**
	 * @param \swoole_server $serv
	 * @param                $fd
	 */
	public function onClose(\swoole_server $serv , $fd)
	{
		$userId = \Yoc::getRedis()->get(USER_FD . $fd);
		\Yoc::getRedis()->del(USER_FD . $fd);
		if (empty($userId)) {
			return;
		}
		\Yoc::$app->asyncTask(UserLogic::className() , 'close' , [$fd]);
	}
	
	/**
	 * @param \swoole_websocket_server $server
	 * @param                          $frame
	 */
	public function onMessage(\swoole_websocket_server $server , $frame)
	{
		try {
			$server->push($frame->fd , $frame->fd);
			$data = json_decode($frame->data , true);
			if (!is_null($data) && !is_numeric($data)) {
				if (!isset($data['event'])) {
					return;
				}
				$explode = explode('/' , $data['event']);
				if (count($explode) != 2) {
					return;
				}
				$action = $this->stanceRoute($explode[1]);
				$controller = $this->stanceRoute($explode[0]);
				$create = $this->createController($controller , null);
				if (!method_exists($create , $action)) {
					return;
				}
				unset($data['event']);
				$controller->$action();
			}
//			\Yoc::pushUser('zb' , 1 , ['blob'=>$frame->data]);
		} catch (\Exception $e) {
			\Yoc::getError()->setLogger($e);
			$server->push($frame->fd , $e->getMessage());
		}
	}
	
	/**
	 * @param string $string
	 *
	 * @return string
	 * 路由重建
	 */
	private function stanceRoute(string $string)
	{
		$string = strtolower($string);
		if (strpos($string , '-') !== false) {
			$explode = explode('-' , $string);
			$string = '';
			foreach ($explode as $key => $val) {
				$string .= ucfirst($val);
			}
			$string = lcfirst($string);
		}
		return $string;
	}
	
	/**
	 * @param \swoole_server $serv
	 * @param                $task_id
	 * @param                $data
	 */
	public function onFinish(\swoole_server $serv , $task_id , $data)
	{
		\Yoc::getRedis()->rPush('task_' . date('Y_m_d') , $data);
	}
	
	/**
	 * @param \swoole_process $process
	 */
	public function process(\swoole_process $process)
	{
//		swoole_timer_tick()
	}
}