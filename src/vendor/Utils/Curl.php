<?php

namespace BITPAY\Utils;

/**
 * Class Curl
 * @package BITPAY\Utils
 */
class Curl {
	/**
	 * @param string $url
	 * @param bool   $ssl
	 * @param int    $timeout
	 * @return bool|string
	 */
	public static function Get(string $url, bool $ssl = FALSE, int $timeout = 30) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl); // 让CURL支持HTTPS访问
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

	/**
	 * @param string $url
	 * @param array  $params
	 * @param bool   $ssl
	 * @param bool   $use_http_build_query
	 * @param int    $timeout
	 * @return bool|string
	 */
	public function Post(string $url, array $params, bool $ssl = FALSE, bool $use_http_build_query = TRUE, int $timeout = 30) {
		$use_http_build_query && $params = http_build_query($params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl); // 让CURL支持HTTPS访问
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
}