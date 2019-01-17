<?php

function getParam($key, $default = '') {
	return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default)) : $default);
}

function resJson ($data) {
	header('Content-Type:application/json; charset=utf-8');
	die(json_encode($data));
}

function resStatus ($downpath = null) {
	if ($downpath) {
		resJson(array(
			'code' => 1,
			'data' => array(
				'url' => $downpath
			),
			'msg' => '歌曲url获取成功'
		));
	} else {
		resJson(array(
			'code' => 0,
			'data' => array(
				'url' => ''
			),
			'msg' => '歌曲url获取失败'
		));
	}
}

function curl ($url, $destination) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1');
	$data = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($status == 403 || $status == 404 || $status == 502 || $status == 503) {
		return false;
	} elseif ($data) {
		curl_close($curl);
		$file = fopen($destination, 'w+');
		fputs($file, $data);
		fclose($file);
		return true;
	} else {
		return false;
	}
}

function main () {
	if (!getParam('url')) {
		die('歌曲url有误');
	} else if (!getParam('name')) {
		die('歌曲名称有误');
	} else if (!getParam('source')) {
		die('歌曲类型有误');
	}

	$url = urldecode(getParam('url'));
	$artist = getParam('artist') ? ' - '.getParam('artist') : '';
	$source = getParam('source');

	// $extension = '.'.pathinfo(parse_url($url)['path'])['extension'];
	$filename = getParam('name');
	// $filepath = './temp/'.$source.'/'.$filename.$artist.'$extension';
	$filepath = './temp/'.$source.'/'.$filename.$artist.'.mp3';
	$downpath = $filepath;

	if (file_exists($filepath)) {
		resStatus($downpath);
	} else {
		$curl_status = curl($url, $filepath);
		if ($curl_status) {
			resStatus($downpath);
		} else {
			resStatus();
		}
	}
}

main();