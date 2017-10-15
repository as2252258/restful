<?php

class Reboot
{
	/**
	 * @var resource
	 */
	protected $inotify;
	protected $pid;
	
	protected $reloadFileTypes = ['.php' => true];
	protected $watchFiles = [];
	protected $afterNSeconds = 1;
	
	/**
	 * 正在reload
	 */
	protected $reloading = false;
	
	protected $events;
	
	/**
	 * 根目录
	 *
	 * @var array
	 */
	protected $rootDirs = [];
	
	function putLog($log)
	{
//		echo $log . PHP_EOL;
		/*        $_log = "[".date('Y-m-d H:i:s')."]\t".$log."\n";
				$file = WEB_ROOT.'/runtime/reload/reload_'.date('Y_m_d').'.txt';
				if(!is_dir(WEB_ROOT.'/runtime/reload')) {
					mkdir(WEB_ROOT.'/runtime/reload');
				}
				if(!file_exists($file)){
					touch($file);
				}
				file_put_contents($file,$_log.'[pid:'.$this->pid.']',FILE_APPEND);*/
	}
	
	/**
	 * @param $serverPid
	 *
	 * @throws Exception
	 */
	function __construct($serverPid)
	{
		$this->pid = $serverPid;
		if (posix_kill($serverPid, 0) === false) {
			throw new Exception("Process#$serverPid not found.");
		}
		
		$this->inotify = inotify_init();
		$this->events = IN_MODIFY | IN_DELETE | IN_CREATE | IN_MOVE;
		
		swoole_event_add($this->inotify, function ($ifd) {
			$events = inotify_read($this->inotify);
			if (!$events) {
				return;
			}
			foreach ($events as $ev) {
				if ($ev['mask'] == IN_IGNORED) {
					continue;
				} else if ($ev['mask'] == IN_CREATE or $ev['mask'] == IN_DELETE or $ev['mask'] == IN_MODIFY or $ev['mask'] == IN_MOVED_TO or $ev['mask'] == IN_MOVED_FROM) {
					$fileType = strstr($ev['name'], '.');
					//非重启类型
					if (!isset($this->reloadFileTypes[$fileType])) {
						continue;
					}
				}
				
				//正在reload，不再接受任何事件，冻结1秒
				if (!$this->reloading) {
					$this->putLog("after 1 seconds reload the server");
					//有事件发生了，进行重启
					swoole_timer_after($this->afterNSeconds * 1000, [$this, 'reload']);
					$this->reloading = true;
				}
			}
		});
	}
	
	function reload()
	{
		$this->putLog("reloading");
		//向主进程发送信号
		posix_kill($this->pid, SIGUSR1);
		//清理所有监听
		$this->clearWatch();
		//重新监听
		foreach ($this->rootDirs as $root) {
			$this->watch($root);
		}
		//继续进行reload
		$this->reloading = false;
	}
	
	/**
	 * 添加文件类型
	 *
	 * @param $type
	 */
	function addFileType($type)
	{
		$type = trim($type, '.');
		$this->reloadFileTypes['.' . $type] = true;
	}
	
	/**
	 * 添加事件
	 *
	 * @param $inotifyEvent
	 */
	function addEvent($inotifyEvent)
	{
		$this->events |= $inotifyEvent;
	}
	
	/**
	 * 清理所有inotify监听
	 */
	function clearWatch()
	{
		foreach ($this->watchFiles as $wd) {
			// inotify_rm_watch($this->inotify, $wd);
		}
		$this->watchFiles = [];
	}
	
	/**
	 * @param      $dir
	 * @param bool $root
	 *
	 * @return bool
	 * @throws Exception
	 */
	function watch($dir, $root = true)
	{
		//目录不存在
		if (!is_dir($dir)) {
			throw new Exception("[$dir] is not a directory.");
		}
		//避免重复监听
		if (isset($this->watchFiles[$dir])) {
			return false;
		}
		//根目录
		if ($root) {
			$this->rootDirs[] = $dir;
		}
		
		$wd = @inotify_add_watch($this->inotify, $dir, $this->events);
		$this->watchFiles[$dir] = $wd;
		
		$files = scandir($dir);
		foreach ($files as $f) {
			if ($f == '.' or $f == '..' or $f == 'runtime' or preg_match('/\.txt/', $f) or preg_match('/\.sql/', $f) or preg_match('/\.log/', $f)) {
				continue;
			}
			$path = $dir . '/' . $f;
			//递归目录
			if (is_dir($path)) {
				$this->watch($path, false);
			}
			//检测文件类型
			$fileType = strstr($f, '.');
			if (isset($this->reloadFileTypes[$fileType])) {
				$wd = @inotify_add_watch($this->inotify, $path, $this->events);
				$this->watchFiles[$path] = $wd;
			}
		}
		return true;
	}
	
	function run()
	{
		swoole_event_wait();
	}
}