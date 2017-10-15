<?php

namespace contrab;


use app\model\Process;

class Contrab
{
	
	/**
	 * @param \swoole_process $process
	 * 定时器
	 */
	public function check(\swoole_process $process)
	{
		$contrab = $this;
		swoole_timer_tick(60 * 1000, function ($timer_id, $param) use ($process, $contrab) {
			$time = strtotime(date('Y-m-d H:i'));
			$list = Process::where(['status' => 1, 'runTime <> 0 and runTime > ' . $time])->all();
			if ($list->isEmpty()) {
				swoole_timer_clear($timer_id);
				$process->exit(0);
			}
			foreach ($list as $key => $val) {
				if (!empty($val->runTime) && $val->runTime != $time) continue;
				if ($val->type == 1) {
					$contrab->shell_exec($val);
				} else if ($val->type == 2) {
					if (!file_exists($val->command)) {
						continue;
					}
					$process->exec($val->execFilePath, $val->command);
				} else {
					$contrab->curl_push($val);
				}
				if ($val->onlyOne == 1) {
					$val->status = 0;
				}
				$val->runNum += 1;
				$val->modifyTime = time();
				$val->save();
			}
		});
	}
	
	/**
	 * @param Process $model
	 */
	private function shell_exec(Process $model)
	{
		shell_exec($model->command);
	}
	
	/**
	 * @param Process $model
	 */
	private function curl_push(Process $model)
	{
		swoole_async_dns_lookup($model->host, function ($host, $ip) use ($model) {
			if ($model->isHttps) {
				$cli = new \swoole_http_client($ip, 443, true);
			} else {
				$cli = new \swoole_http_client($ip, 80);
			}
			if (!$cli->isConnected()) {
				return;
			}
			$callback = $model->callback ?? function (\swoole_http_client $client) {
					$body = $client->body;
					echo 'end';
				};
			if ($model->method == 'get') {
				$cli->get($model->command, $callback);
			} else {
				$cli->post($model->command, $model->param, $callback);
			}
		});
	}
}