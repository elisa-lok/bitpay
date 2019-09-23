<?php

namespace BITPAY\Utils;
class Rand {
	public static function randString(int $len = 16, string $char = '') {
		$c       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-={}[]:";\'<>?,./~`';
		$char    = $char == '' ? $c : $char;
		$charLen = strlen($char);
		$str     = '';
		for ($i = 0; $i < $len; $i++) {
			$str .= $char[rand(0, $charLen - 1)];
		}
		return $str;
	}
}