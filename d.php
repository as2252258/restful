<?php

//if (!extension_loaded('swoole')) {
//	die('extension swoole not load');
//}

//class b
//{
//	public function a()
//	{
//		while (true) {
//			echo 'ps' . PHP_EOL;
//			sleep(1);
//		}
//	}
//}
//
//$process = new swoole_process(['b', 'a']);
//$process->start();

//$array = ['a' => '' , 'v' => ''];
//var_dump(array_flip(array_keys($array)));


//class a{
//	const B = 'a';
//}
//
//class B extends a{
//
//}
//
//$class = new ReflectionClass('a');


//var_dump(strchr(5464.55555 , '.'));
//var_dump(sprintf('%.6f' , '.456786754'));
//$s = file_get_contents(__DIR__ . '/app/controller/SetController.php');
//print_r(explode(PHP_EOL , $s));


function curl_push($url , $type = 'get' , $data = [])
{
	$_data = paramEncode($data);
	if ($type == 'get') {
		$url .= '?' . http_build_query($_data);
	}
	$ch = curl_init();
	curl_setopt($ch , CURLOPT_URL , $url);
//	curl_setopt($ch , CURLOPT_HEADER , true);
	curl_setopt($ch , CURLOPT_NOBODY , false);
	curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);//返回内容
	curl_setopt($ch , CURLOPT_FOLLOWLOCATION , true);// 跟踪重定向
	curl_setopt($ch , CURLOPT_TIMEOUT , 5);// 超时设置
//	curl_setopt($ch , CURLOPT_HTTPHEADER , $_tmp);
	curl_setopt($ch , CURLOPT_ENCODING , 'gzip,deflate');
	switch (strtolower($type)) {
		case 'post':
			curl_setopt($ch , CURLOPT_POST , 1);
			curl_setopt($ch , CURLOPT_POSTFIELDS , http_build_query($_data));
			break;
		case 'delete':
			curl_setopt($ch , CURLOPT_CUSTOMREQUEST , 'DELETE');
			curl_setopt($ch , CURLOPT_POSTFIELDS , http_build_query($_data));
			break;
		case 'put':
			curl_setopt($ch , CURLOPT_CUSTOMREQUEST , 'PUT');
			curl_setopt($ch , CURLOPT_POSTFIELDS , http_build_query($_data));
			break;
		default:
			curl_setopt($ch , CURLOPT_CUSTOMREQUEST , 'GET');
	}
	$output = curl_exec($ch);
	if ($output === false) {
		return false;
	}
	return $output;
}

/**
 * @param        $arr
 * @param string $pushType
 *
 * @return array|string
 * 将请求参数进行编码
 */
function paramEncode($arr , $pushType = 'post')
{
	if (!is_array($arr)) {
		return [];
	}
	$_tmp = [];
	foreach ($arr as $Key => $val) {
		$_tmp[$Key] = $val;
	}
	
	return ($pushType == 'post' ? $_tmp : http_build_query($_tmp));
}

function httpcopy($url , $file = "" , $timeout = 60)
{
	$file = empty($file) ? pathinfo($url , PATHINFO_BASENAME) : $file;
	$dir = pathinfo($file , PATHINFO_DIRNAME);
	!is_dir($dir) && @mkdir($dir , 0755 , true);
	$url = str_replace(" " , "%20" , $url);
	
	$ch = curl_init();
	curl_setopt($ch , CURLOPT_URL , $url);
	curl_setopt($ch , CURLOPT_TIMEOUT , $timeout);
	curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
	$temp = curl_exec($ch);
	if (@file_put_contents($file , $temp) && !curl_error($ch)) {
		return $file;
	} else {
		return false;
	}
}


$tmp = 'tmp';
if (!is_dir($tmp)) {
	mkdir($tmp);
}

$urlArray = [];
$url = 'https://www.pianyissl.com';
//getUrlContent('https://www.pianyissl.com' , $urlArray);
function getUrlContent($url , &$urlArray)
{
	if (in_array($url , $urlArray)) {
		return;
	}
	if (strpos($url , 'https://www.pianyissl.com') === false) {
		return;
	}
	array_push($urlArray , $url);
	$content = curl_push($url);
	if ($content !== false) {
		
		getCssAllContents($content);
		getJsAllContents($content);
		geImgAllContents($content);
		
		$url = parse_url($url);
		if (empty($url['path'])) {
			$url['path'] = 'first_home';
		} else {
			$explode = explode('/' , ltrim(rtrim($url['path'] , '/') , '/'));
			$string = 'tmp/';
			foreach ($explode as $key => $val) {
				if (!is_dir($string . $val)) {
					mkdir($string . $val);
				}
				$string .= $val . '/';
			}
		}
		if (!empty($url['query'])) {
			file_put_contents('tmp/' . ltrim($url['path'] , '/') . '/' . $url['query'] . '.html' , $content);
		} else {
			file_put_contents('tmp/' . ltrim($url['path'] , '/') . '.html' , $content);
		}
		$urls = getUrls($content);
		foreach ($urls as $key => $val) {
			if (empty($val)) continue;
			if ($val == '#') continue;
			if ($val == '/') continue;
			if ($val == 'javascript:void(0);') continue;
			if (preg_match('/^#.*/' , $val)) continue;
			if (preg_match('/^\//' , $val)) {
				getUrlContent('https://www.pianyissl.com/' . ltrim($val , '/') , $urlArray);
			} else {
				getUrlContent($val , $urlArray);
			}
		}
	}
}

function getCssAllContents($content)
{
	$preg = preg_match_all('/<link.*?href=[\'|\"](.*?)[\'|\"].*?\/>/' , $content , $results);
	if (!$preg || !isset($results[1])) {
		return;
	}
	foreach ($results[1] as $key => $value) {
		if (empty($value)) continue;
		if (preg_match('/\w+\.\w+\.\w+/' , $value)) {
			continue;
		}
		$explode = explode('/' , ltrim(rtrim($value , '/') , '/'));
		$string = 'tmp/';
		foreach ($explode as $_key => $_val) {
			$_val = trim($_val , '/');
			if (empty($_val)) continue;
			if (strpos($_val , '.css') !== false) continue;
			if (!is_dir($string . $_val)) {
				mkdir($string . $_val);
			}
			$string .= $_val . '/';
		}
		file_put_contents('tmp/' . ltrim($value , '/') , file_get_contents('https://www.pianyissl.com' . $value) , FILE_APPEND);
	}
}

function getJsAllContents($content)
{
	
	$preg = preg_match_all('/<script.*?src=[\'|\"](.*?)[\'|\"].*?>/' , $content , $results);
	if (!$preg || !isset($results[1])) {
		return;
	}
	foreach ($results[1] as $key => $val) {
		if (empty($val)) continue;
		if (preg_match('/\w+\.\w+\.\w+/' , $val)) {
			continue;
		}
		$explode = explode('/' , ltrim(rtrim($val , '/') , '/'));
		$string = 'tmp/';
		foreach ($explode as $_key => $_val) {
			$_val = trim($_val , '/');
			if (empty($_val)) continue;
			if (strpos($_val , '.js') !== false) continue;
			if (!is_dir($string . $_val)) {
				mkdir($string . $_val);
			}
			$string .= $_val . '/';
		}
		file_put_contents('tmp/' . ltrim($val , '/') , file_get_contents('https://www.pianyissl.com' . $val) , FILE_APPEND);
	}
}

function geImgAllContents($content)
{
	
	$preg = preg_match_all('/<img.*?src=[\'|\"](.*?)[\'|\"].*?\/>/' , $content , $results);
	if (!$preg || !isset($results[1])) {
		return;
	}
	foreach ($results[1] as $key => $val) {
		if (!preg_match('/[^\/]/' , $val)) {
			continue;
		}
		$explode = explode('/' , ltrim(rtrim($val , '/') , '/'));
		$string = 'tmp/';
		foreach ($explode as $_key => $_val) {
			$_val = trim($_val , '/');
			if (!is_dir($string . $_val)) {
				mkdir($string . $_val);
			}
			$string .= $_val . '/';
		}
		httpcopy('https://www.pianyissl.com/' . $val , $val);
	}
}

function getUrls($content)
{
	$preg = preg_match_all('/<a.*?href=[\'|\"](.*?)[\'|\"].*?>/' , $content , $results);
	if ($preg && isset($results[1])) {
		return $results[1];
	}
	return [];
}

//
//function dds()
//{
//	var_dump(func_get_args());
//}
//
//dds('12' , '32' , '12');


function StringRand(int $length = 20)
{
	$string = '';
	if ($length < 1) $length = 20;
	$STRING = 'abcdefghijklmnopqrstuvwxyz';
	$NUMBER = '01234567890';
	$default = $STRING . strtoupper($STRING) . $NUMBER;
	$default = str_split($default);
	for ($i = 0 ; $i < $length ; $i++) {
		$string .= $default[array_rand($default)];
	}
	return (string) $string;
}

$tmp = [];
$max = 100000;
for ($i = 0 ; $i < $max ; $i++) {
	$str = StringRand();
	if (in_array($str , $tmp)) {
		while (true) {
			$str = StringRand();
			if (in_array($str , $tmp)) {
				continue;
			}
			array_push($tmp , $str);
			break;
		}
	} else {
		array_push($tmp , $str);
	}
	var_dump($i . ':' . $str);
}

file_put_contents('code.txt' , '[\'' . implode('\',\'' , $tmp) . '\']');