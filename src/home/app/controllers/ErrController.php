<?php

namespace BITPAY\Home\Controllers;

use BITPAY\Msg;
use BITPAY\Base;

// 统计
class ErrController extends ControllerBase {
	public function initialize() {
		parent::initialize();
	}

	public function notFoundAction(){
		if($this->request->isPost()){
			$this->api(Msg::ErrNotFound);
		}
		header('HTTP/2.0 404 Not Found');
		$this->view->pick('index/404');
	}
}