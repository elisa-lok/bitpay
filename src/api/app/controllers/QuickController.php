<?php

namespace BITPAY\Api\Controllers;

use BITPAY\Base;
use BITPAY\Msg;

// 快捷支付

/**
 * TODO 快捷支付分三种类型, 1.绑卡支付, 2.直接支付, 3.银联跳转
 * Class QuickController
 * @package BITPAY\Api\Controllers
 */
class QuickController extends ControllerBase {

	public function create(){
		var_dump(__DIR__,__FILE__,__LINE__);
		die;
	}

	private function checkParams(){

	}
}