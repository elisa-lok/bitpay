<?php

namespace BITPAY\Api\Controllers;

// 账户
class BaseController extends ControllerBase {
	//-------------------------------------------------------------------------------------------------------------------------
	//账户余额信息
	public function standard() {
		echo time();die;
	}

	public function timestamp(){
		echo date(DATE_RFC3339, time());
		die;
	}

	public function limit(){

	}
}