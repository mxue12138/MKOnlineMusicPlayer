<?php

namespace Download;

class Download {
	public function getParam($key, $default = '') {
	  return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default)) : $default);
	}

	public function resJson ($data) {
		header('Content-Type:application/json; charset=utf-8');
		die(json_encode($data));
	}

	public function resStatus ($downpath) {
		if ($downpath) {
			$this->resJson(array(
				'code' => 1,
				'data' => array(
					'url' => $downpath
				),
				'msg' => '歌曲url获取成功'
			));
		} else {
			$this->resJson(array(
				'code' => 0,
				'data' => array(
					'url' => ''
				),
				'msg' => '歌曲url获取失败'
			));
		}
	}

	public function curl ($url, $destination) {
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
			$file = fopen(iconv('UTF-8', 'GBK', $destination), 'w+');
			fputs($file, $data);
			fclose($file);
			return true;
		} else {
			return false;
		}
	}

	public function main () {
		if (!$this->getParam('url')) {
			die('歌曲url有误');
		} else if (!$this->getParam('name')) {
			die('歌曲名称有误');
		} else if (!$this->getParam('source')) {
			die('歌曲类型有误');
		}

		$url = urldecode($this->getParam('url'));
		$artist = $this->getParam('artist') ? ' - '.$this->getParam('artist') : '';
		$source = $this->getParam('source');

		$extension = '.'.pathinfo(parse_url($url)['path'])['extension'];
		$filename = $this->getParam('name');
		$filepath = './temp/'.$source.'/'.$filename.$artist.$extension;
		$downpath = $filepath;

		if (file_exists($filepath)) {
			$this->resStatus($downpath);
		} else {
			$curl_status = $this->curl($url, $filepath);
			if ($curl_status) {
				$this->resStatus($downpath);
			} else {
				$this->resStatus(null);
			}
		}
	}
}

(new Download())->main();