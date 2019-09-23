<?php

namespace BITPAY\Home\Controllers;

use BITPAY\Msg;
use BITPAY\Base;

// 统计
class StatsController extends ControllerBase {
	public function initialize() {
		parent::initialize();
	}

	public function payAction(){
		$this->view->pick('stats/pay');
	}

	public function remitAction(){
		$this->view->pick('stats/remit');
	}
}