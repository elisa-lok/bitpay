<?php
namespace app\api\controller;
use think\Controller;

class Base extends Controller {
	public function _initialize() {
		$config = cache('db_config_data');
		if (!$config) {
			$config = load_config();
			cache('db_config_data', $config);
		}
		config($config);
	}

	protected function suc($data) {
		/* 返回状态，200 成功，500失败 */
		die(json_encode(['status' => 1, 'data' => $data,], 320));
	}

	protected function err($message) {
		/* 返回状态，200 成功，500失败 */
		die(json_encode(['status' => 0, 'err' => $message,], 320));
	}

	protected function check_code(int $len = 5, string $char = '') {
		$c       = '0123456789';
		$char    = $char == '' ? $c : $char;
		$charLen = strlen($char);
		$str     = '';
		for ($i = 0; $i < $len; $i++) {
			$str .= $char[rand(0, $charLen - 1)];
		}
		return $str;
	}
}