<?php

namespace BITPAY\Utils;

class Validate {
	/**
	 * 微信用用户名通用
	 * @param $str
	 * @return int
	 */
	public function username($str) {
		return preg_match('/^[\w\-]{5,15}$/i', $str);
	}

	/**
	 * Chinese身份证验证
	 * @param $str
	 * @return false|int
	 */
	public function idCard($str) {
		return preg_match('/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $str);
	}

	/**
	 * 支付宝校验
	 * @param $str
	 * @return bool
	 */
	public static function alipay($str) {
		return self::phone($str) || self::email($str);
	}

	/**
	 * 中国地区手机校验
	 * @param $str
	 * @return int
	 */
	public static function phone($str) {
		return preg_match('/^1[3456789]\d{9}$/', $str);
	}

	/**
	 * 邮箱检验
	 * @param $str
	 * @return int
	 */
	public static function email($str) {
		return preg_match('/^[a-z0-9]+([._-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/', strtolower($str));
		//		return preg_match('/^[a-z]([._-]{0,1}[a-z]+)+@[0-9a-z]+(-{0,1}[a-z]+)+.[a-z]{2,8}$/i', strtolower($str));
	}

	/**
	 * @param $url
	 * @return mixed
	 */
	public static function url($url){
		return (bool)filter_var($url, FILTER_VALIDATE_URL);
	}

	/**
	 * @param $ipAddr
	 * @return mixed
	 */
	public static function ip($ipAddr){
		return (bool)filter_var($ipAddr, FILTER_VALIDATE_IP);
	}

	/**
	 * 匹配QQ
	 * @param $str
	 * @return int
	 */
	public function qq($str) {
		return preg_match('/^\d{5,12}$/', $str);
	}

	/**
	 * 银行卡16或19位
	 * @param $str
	 * @return int
	 */
	public function bankNum($str) {
		return preg_match('/^\d{16}|\d{19}$/', $str);
	}

	/**
	 * @param $str
	 * @return int
	 */
	public function secAnswer($str) {
		//防sql注入
		return preg_match('/[\'\*\,\(\)\=]|select|update|insert/', $str);
	}

	public function passwordLen(string $pwd) {
		$len = mb_strlen($pwd);
		return $len < 8 || $len > 16;
	}

	public function passwdStrength(string $pwd) {
		//密码长度
		$len = mb_strlen($pwd, 'UTF-8');
		if ($len < 8 || $len > 16) {
			return FALSE;
		}
		//密码字符

		//密码类型, 2种以上
		// '[\w!@#$%^&*()+-=\[\]\;\',./{}|:"<>?]';
	}

	/**
	 * 密码校验
	 * @param string $pwd
	 * @return bool
	 */
	public static function passwdValid(string $pwd) {
		if (preg_match('/[^\w!@#$%\^&\*()+-=\[\]\;\',\.\/\{}|:"<>?]/', $pwd)) {
			return false;
		}
		//密码种类数
		return (bool)preg_match('/^(?![A-Z]+$)(?![\d]+$)(?![a-z]+$)(?![!@#$%^&\*()+-=\[\]\;\',\.\/\{}|:"<>?]+$)\\S{8,16}$/', $pwd);
	}
}