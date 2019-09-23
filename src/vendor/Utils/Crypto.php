<?php

namespace BITPAY\Utils;

class Crypto {
	//***************************************** AES加密部分 *****************************************
	private static $defaultIv     = 'ZV`7<5X]/2brS@sz'; // 16位
	private static $defaultKey    = 'e{&[^<wpliI$AgKs:>Ft(.~g]1eR-]VO'; //32位
	private static $defaultMethod = 'aes-256-cbc';

	/**
	 *
	 * @param string $unencrypted
	 * @param string $key
	 * @param string $iv
	 * @param string $method //偏移量
	 * @return bool
	 */
	public static function AesEncrypt(string $unencrypted, string $key = '', string $iv = '', string $method = 'aes-256-cbc') {
		$len = strlen($key);
		($len != 16 && $len != 24 && $len != 32) && $key = self::$defaultKey;
		!in_array($method, openssl_get_cipher_methods()) && $method = self::$defaultMethod;
		strlen($iv) != 16 && $iv = self::$defaultIv;
		return base64_encode(openssl_encrypt($unencrypted, $method, $key, OPENSSL_RAW_DATA, $iv));
	}

	/**
	 * @param string $encrypted
	 * @param string $key
	 * @param string $iv
	 * @param string $method
	 * @return bool|string
	 */
	public static function AesDecrypt(string $encrypted, string $key = '', string $iv = '', string $method = 'aes-256-cbc') {
		$len = strlen($key);
		($len != 16 && $len != 24 && $len != 32) && $key = self::$defaultKey;
		!in_array($method, openssl_get_cipher_methods()) && $method = self::$defaultMethod;
		strlen($iv) != 16 && $iv = self::$defaultIv;
		return openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);
	}

	// /**
	//  * @param string $string 需要加密的字符串
	//  * @param string $key    密钥
	//  * @return string
	//  */
	// private function AesEncrypt($string, $key) {
	// 	return base64_encode(openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA));
	// }
	//
	// /**
	//  * @param string $string 需要解密的字符串
	//  * @param string $key    密钥
	//  * @return string
	//  */
	// private function AesDecrypt($string, $key) {
	// 	return openssl_decrypt(base64_decode($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
	// }
}