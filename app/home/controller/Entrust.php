<?php
namespace app\home\controller;
class Entrust extends Base {
	public function _initialize() {
		parent::_initialize();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
	}

	// 新建订单
	public function new() {
	}
}

?>