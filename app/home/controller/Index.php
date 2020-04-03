<?php
namespace app\home\controller;
use app\home\model\IndexModel;

class Index extends Base {
	public function index() {
		$this->assign('uid', session('uid'));
		return $this->fetch('index');
	}
}
