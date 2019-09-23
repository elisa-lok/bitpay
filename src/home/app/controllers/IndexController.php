<?php

namespace BITPAY\Home\Controllers;

use BITPAY\Msg;

/**
 * 首頁
 * Class IndexController
 * @package XBANK\Home\Controllers
 */
class IndexController extends ControllerBase {

	public function indexAction() {
		$this->isAgent ? $this->view->pick('index/agent_index') : $this->view->pick('index/index');
	}

	public function welcomeAction() {
		$this->isAgent ? $this->view->pick('index/agent_welcome') : $this->view->pick('index/welcome');
	}

	public function testAction(){
		if($this->request->isPut()){
			$data = [
				'username' => 'zhangsan',
				'age' => 18,
				'gender' => 'male'
			];
			$res = ['url' => '/index/test', 'method' => 'post', 'formData'=>$data];
			$this->api($res);
		}

		if($this->request->isPost()){
			$this->api(Msg::Suc);
		}
		$this->view->pick('index/form');

	}
}