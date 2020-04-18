<?php
namespace app\admin\controller;
use think\Db;

// 查看用户支付方式
class Payment extends Base {
	public function index() {
		$id   = input('id');
		$user = Db::name('merchant')->where('id', $id)->find();
		showMsg('', 0, ['usdt' => $user['usdt'], 'usdtd' => $user['usdtd'], 8]);
	}

	public function get() {
		$args = input();
	}
}