<?php

namespace BITPAY\Api\Common;

class Validate {
	public static function isIPv4($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? TRUE : FALSE;
	}

	public static function isIPv6($ip) {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? TRUE : FALSE;
	}

	public static function isMail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) ? TRUE : FALSE;
	}

	public static function isInt($str) {
		return filter_var($str,FILTER_VALIDATE_INT) ? TRUE : FALSE;
	}

	public static function isAlphaNum($str){

	}

	public static function checkTxEBank($data){
		$args = [
			'uid' => 	FILTER_SANITIZE_NUMBER_INT,
			'tx_out_no' => '',
			'amt' => '',
			'bank_code' => '',
			'return_url' => '',
			'notify_url' => '',
			'timestamp' => '',
			'ip' => '',
			'sig_type' => '',
			'sig' => '',
		];
		return filter_var_array($data, $args);
	}

	public static function checkTxQuick($data){

	}

	public static function checkTxScan($data){

	}

	public static function checkTxWap($data){

	}

	public static function checkTxRemit($data){

	}

	public static function checkTxQuery($data){

	}
}