<?php

namespace BITPAY\Utils;
class Rsa {
	private $res;

	public function __construct() {
		// 默认使用 RSA2 标准
		$config    = [
			"digest_alg"       => "sha256",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];
		$this->res = openssl_pkey_new($config);
	}

	public function GenPrivate() {
		// Extract the private key from $res to $privKey
		openssl_pkey_export($this->res, $privKey);
		return $privKey;
	}

	public function GenPubic() {
		$pubKey = openssl_pkey_get_details($this->res);
		return $pubKey["key"];
	}

	public function SignCreate($unencrypted, string $pubKey) {
		if (is_object($unencrypted) || is_array($unencrypted)) {
			return FALSE;
		}
		openssl_public_encrypt($unencrypted, $encrypted, $pubKey);
		return base64_encode($encrypted);
	}

	public function SignCheck($encrypted, string $privKey) {
		openssl_private_decrypt(base64_decode($encrypted), $decrypted, $privKey);
		return $decrypted;
	}
}