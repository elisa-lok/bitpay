<?php

namespace BITPAY\Api\Lib\Service;

use BITPAY\Api\Common\Msg;

/**
 * Class BaseService
 * @package BITPAY\Service
 * @property \Redis                         $redis
 * @property \Phalcon\Session\Adapter\Redis $session
 * @property \Phalcon\Config                $config
 * @property \Phalcon\Config                $this->config
 * @property \Phalcon\Db\AdapterInterface   $dbm
 * @property \Phalcon\Db\AdapterInterface   $dbs
 * @property \Phalcon\Cache\Backend\Redis   $cache
 * @property \BITPAY\Api\Common\Msg           $msg
 */
class BaseService extends \Phalcon\Mvc\User\Component {
	/**
	 * 使用curl的get方法
	 * @param string $url
	 * @param int    $timeout
	 * @return mixed
	 */
	public function curlGet($url, $conv = FALSE, $SSL = 0, $timeout = 30) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSL); // 让CURL支持HTTPS访问
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_ENCODING, 'text');
		$res = curl_exec($ch);
		$res = $conv ? mb_convert_encoding($res, 'utf-8', 'GBK,UTF-8,ASCII') : $res;
		curl_close($ch);
		return $res;
	}

	/**
	 * 输出信息并退出程序
	 * @param mixed $data
	 * @param bool  $urlDecodeFlag
	 */
	public function api($data = Msg::Suc, $urlDecodeFlag = FALSE) {
		$data === TRUE && $data = Msg::Suc;
		!$data && $data = Msg::ErrFailure;
		$res = isset($data['statusCode'], $data['code'], $data['msg']) ? ['code' => $data['code'], 'msg' => $data['msg'], 'data' => ''] : [
			'code' => 0,
			'msg'  => 'success',
			'data' => $data
		];
		header('Content-type:application/json;charset=utf-8');
		isset($data['statusCode']) && header('HTTP/2.0 ' . $data['statusCode']);
		echo $urlDecodeFlag ? urldecode(json_encode($res)) : json_encode($res);
		die;
	}

	/**
	 * 使用curl的post方法
	 * @param      $url
	 * @param      $params
	 * @param bool $use_http_build_query
	 * @param int  $SSL
	 * @return mixed
	 */
	public function curlPost($url, $params, $use_http_build_query = TRUE, $SSL = 0) {
		$use_http_build_query && $params = http_build_query($params);

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_POST, 1);
		curl_setopt($curlHandle, CURLOPT_URL, $url);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $SSL); // 让CURL支持HTTPS访问
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($curlHandle);
		curl_close($curlHandle);

		return $result;
	}

	/**
	 * 字符串转化成SQL语句或者数组
	 * @param string $str
	 * @param bool   $toSqlIn
	 * @return array|bool
	 */
	public function strToArray($str = '', $toSqlIn = FALSE) {
		$str = str_replace([" ", "　", "\t", "\n", "\r"], '', $str);
		$res = preg_match('/^\d(\,[\d]+)+$/', $str) ? ($toSqlIn ? " IN ('" . implode("','", array_filter(explode(',', $str))) . "') " : array_filter(explode(',', $str))) : FALSE;
		return $toSqlIn && preg_match('/^\d+$/', $str) ? " = '$str'" : $res;
	}

	/**
	 * @param array|string $ids
	 * @return string
	 */
	public function arrayToSqlIn($ids) {
		return is_array($ids) ? " IN ('" . implode("','", array_unique($ids)) . "') " : " = '" . addslashes($ids) . "'";
	}

	/**
	 * 密码加密
	 * @param        $password
	 * @param string $salt
	 * @return string
	 */
	public function hash($password, $salt = '') {
		return md5(md5(trim($password) . $salt) . $salt);
	}
}
