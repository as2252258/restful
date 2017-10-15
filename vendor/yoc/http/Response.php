<?php

namespace yoc\http;

use Code;
use Exception;
use swoole_http_response;
use Yoc;
use yoc\base\Components;
use yoc\Db\Collection;

/**
 * Class Response
 */
class Response extends Components
{
	/** @var swoole_http_response $response */
	public $response = null;
	public $statusCode = 200;
	public $format = 'json';
	public $isHttp = false;
	public $startTime;
	
	/**
	 * @param $data
	 * @param $recv
	 * @param $type
	 * @param $allot
	 */
	private static function allot($data , &$recv , $type , &$allot , $default = null)
	{
		$_type = gettype($data);
		if (is_object($_type)) {
			$data = self::toArray($data);
		}
		if ($_type !== $type) {
			$allot = $data;
			$recv = $default;
		} else {
			$recv = $data;
		}
	}
	
	/**
	 * @param $message
	 *
	 * @return array
	 */
	protected static function toArray($message)
	{
		$param = $message;
		if (is_object($message)) {
			if ($message instanceof yoc\db\ActiveRecord) {
				$param = $message->toArray();
			} else if ($message instanceof Collection) {
				$param = $message->toArray();
			} else {
				$param = get_object_vars($message);
			}
		}
		return $param;
	}
	
	/**
	 * @param $value
	 *
	 * @return $this
	 * @throws Exception
	 * 设置返回数据类型
	 */
	public function setFormat($value)
	{
		if (!in_array($value , ['json' , 'xml' , 'text'])) {
			throw new Exception('Response Error: Unknown Response Type');
		}
		$this->format = $value;
		return $this;
	}
	
	/**
	 * @param       $code
	 * @param       $message
	 * @param array $param
	 * @param int   $total
	 * @param array $exPageInfo
	 */
	public function send($code , $message = '' , $param = [] , $total = 0 , $exPageInfo = [])
	{
		try {
			if (!$this->isConnect()) {
				throw new Exception('http client is finished');
			}
			$responseData = self::analysis($code , $message , $param , $total , $exPageInfo);
			if (!is_numeric($responseData['code'])) {
				$responseData['code'] = Code::SUCCESS;
			}
			list($format , $data) = $this->getFormatData($responseData);
			$this->response->gzip(4);
			$this->response->status($this->statusCode);
			$this->response->header('Content-Type' , $format);
			$this->response->header('Access-Control-Allow-Origin' , '*');
			$this->response->header("Access-Control-Allow-Headers" , "logintime,token,userid");
			$this->response->header('Run-Time' , $runTime = $this->runTime());
			if ($format == 'xml') {
				$this->response->sendfile($data->saveXml());
			} else {
				$this->response->end($data);
			}
			$timer = BASE_PATH . '/runtime/timer';
			if (!is_dir($timer)) {
				mkdir($timer);
			}
			$fileName = $timer . '/time_' . date('Y_m_d') . '.text';
			file_put_contents($fileName , ',' . json_encode([
					'time' => $runTime ,
					'url'  => Yoc::getRequest()->getUrlPath()
				]) , FILE_APPEND);
			echo '[' . $runTime . ']   ' . Yoc::getRequest()->getUrlPath();
			echo PHP_EOL;
		} catch (\Exception $ex) {
			Yoc::getError()->setLogger($ex , Code::ERROR_LEVEL_EXCEPTION);
		}
	}
	
	/**
	 * @return mixed
	 */
	public function isConnect()
	{
		if (!$this->response instanceof swoole_http_response) {
			return false;
		}
		return Yoc::$server->connection_info($this->response->fd);
	}
	
	/**
	 * @param        $code
	 * @param string $message
	 * @param array  $param
	 * @param int    $total
	 * @param array  $exPageInfo
	 *
	 * @return array
	 */
	public static function analysis()
	{
		$args = func_get_args();
		$data = ['code' => -100 , 'message' => '' , 'total' => 0 , 'exPageInfo' => [] , 'results' => '' , 'trance' => []];
		foreach ($args as $key => $val) {
			if (is_integer($val)) {
				if ($data['code'] < -1) {
					$data['code'] = $val;
				} else {
					$data['total'] = $val;
				}
			} else if (is_string($val)) {
				if (intval($val) == $val && strlen(floatval($val)) == strlen($val)) {
					$data['total'] = (int) $val;
				} else {
					if ($key === 0 && !is_numeric($val)) {
						$data['results'] = $val;
					} else {
						if (empty($data['message'])) {
							$data['message'] = $val;
						} else {
							$data['results'] = $val;
						}
					}
				}
			} else if (is_array($val) || is_object($val)) {
				if ($data['results'] === '') {
					$data['results'] = self::toArray($val);
				} else {
					$data['exPageInfo'] = self::toArray($val);
				}
			}
		}
		if (empty($data['message']) && $data['code'] != 0 && $data['code'] != -100) {
			if (isset(Code::$INFO[$data['code']])) {
				$data['message'] = Code::$INFO[$data['code']];
			} else {
				$data['message'] = Code::$INFO[Code::SYSTEM_ERROR];
			}
		}
		if ($data['code'] == -100) $data['code'] = 0;
		return json_encode($data , JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * @param $data
	 *
	 * @return array
	 */
	private function getFormatData($data)
	{
		if ($this->format == 'xml') {
			$_tmp[] = 'application/xml';
			$_tmp[] = $this->createXml($data);
		} else if ($this->format == 'text') {
			$_tmp[] = 'application/text';
			$_tmp[] = json_encode($data);
		} else {
			$_tmp[] = 'application/json';
			$_tmp[] = json_encode($data , JSON_UNESCAPED_UNICODE);
		}
		return $_tmp;
	}
	
	private function createXml(array $data)
	{
		$dom = new \DOMDocument("1.0" , "utf-8");//声明版本和编码
		$dom->formatOutput = true;//格式化输出
		$root = $dom->createElement("xml");
		$dom->appendChild($root);
		$fieldArr = array_keys($data);
		foreach ($data as $result) {
			$link = $dom->createElement("link");
			$root->appendChild($link);
			for ($i = 0 ; $i < $data ; $i++) {
				$node[$i] = $dom->createElement($fieldArr[$i]);
				$node[$i]->appendChild($dom->createTextNode($result[$fieldArr[$i]]));
				$link->appendChild($node[$i]);
			}
		}
		return $dom;
	}
	
	/**
	 * @return string
	 */
	private function runTime()
	{
		return sprintf('%.6f' , microtime(true) - $this->startTime);
	}
}