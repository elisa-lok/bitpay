<?php

namespace BITPAY\Api\Controllers;

use BITPAY\Api\Common\Msg;
use BITPAY\Api\Lib\Pay;
use BITPAY\Utils\QRcode;

class TestController extends ControllerBase {
	//-------------------------------------------------------------------------------------------------------------------------
	//账户余额信息
	public function index() {
		QRcode::png('sdfsdf');
		die;
	}

	// 校验订单号
	public function checkTxNo(string $txNo) {
		return preg_match('/[0-9a-zA-Z]/', $txNo) !== FALSE;
	}
}