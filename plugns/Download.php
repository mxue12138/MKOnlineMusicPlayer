<?php

namespace Mxue;

class Download
{
    public $source;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function echojson ($downpath, $msg = '')
    {
        if ($downpath) {
            return json_encode(array(
                'code' => 1,
                'url' => $downpath,
                'msg' => '歌曲url获取成功'
            ));
        } else {
            return json_encode(array(
                'code' => 0,
                'url' => '',
                'msg' => $msg
            ));
        }
    }

    public function curl ($url, $destination)
    {
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

    public function download ($url, $name, $artist)
    {
        if (!$url) {
            return $this->echojson(null, '歌曲url有误');
        } else if (!$name) {
            return $this->echojson(null, '歌曲名称有误');
        }

        $url = urldecode($url);
        $artist = $artist ? ' - '.$artist : '';
        $source = $this->source;

        $protocol = $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
        $filepath = dirname(dirname(__FILE__)).'/temp/'.$source.'/'.$name.$artist.'.mp3';
        $downpath = $protocol.$_SERVER['HTTP_HOST'].'/temp/'.$source.'/'.$name.$artist.'.mp3';
        
        if (file_exists($filepath)) {
            return $this->echojson($downpath);
        } else {
            $curl_status = $this->curl($url, $filepath);
            if ($curl_status) {
                return $this->echojson($downpath);
            } else {
                return $this->echojson(null, '歌曲url获取失败');
            }
        }
    }
}