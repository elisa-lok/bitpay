<?php

namespace BITPAY\Utils;

class IP {
	/**
	 *  登录时获取用户ip ，地区
	 */
	public static function getTaobaoLocation($ipAddr = NULL): string {
		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . ($ipAddr ?? $_SERVER['REMOTE_ADDR']);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_ENCODING, 'text');
		$res = curl_exec($ch);
		curl_close($ch);

		$ipInfo = json_decode($res, TRUE);
		return (string)$ipInfo['code'] == '1' ? 'Unknown' : $ipInfo['data']['country'] . $ipInfo['data']['region'] . $ipInfo['data']['city'];
	}

	public static function realLocation($ip_addr = NULL) {
		$url = 'http://ip-api.com/json/' . ($ip_addr ?? $_SERVER['REMOTE_ADDR']) . '?lang=zh-CN';
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_ENCODING, 'text');
		$res = curl_exec($ch);
		curl_close($ch);
		$ipInfo = json_decode($res, TRUE);
		return isset($ipInfo['city']) ? $ipInfo['country'] . $ipInfo['city'] : 'Unknown';
	}

	public static function externalIpAddress(){
		$url = 'http://ip-api.com/json';
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_ENCODING, 'text');
		$res = curl_exec($ch);
		curl_close($ch);
		$ipInfo = json_decode($res, TRUE);
		return isset($ipInfo['query']) ?? '127.0.0.1';
	}

	/**
	 * get server external ip location
	 * @return string
	 */
	public static function externalIpLocation(){
		$url = 'http://ip-api.com/json';
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_ENCODING, 'text');
		$res = curl_exec($ch);
		curl_close($ch);
		$ipInfo = json_decode($res, TRUE);
		return isset($ipInfo['country']) ? $ipInfo['country'] . $ipInfo['city'] : 'Unknown';
	}
}
