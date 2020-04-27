<?php
namespace app\admin\controller;
// 查看用户支付方式
use think\Db;

class Payment extends Base {
	public function index() {
		$tag    = input('tag');
		$payWay = input('pay_way');
		$payWay = $payWay === NULL ? 1 : (int)$payWay;
		$data = [];
		if ($tag !== NULL) {
			$payWayTbl = [0 => 'merchant_bankcard', 1 => 'merchant_zfb', 2 => 'merchant_wx', 3 => 'merchant_ysf'];
			$data = Db::name($payWayTbl[$payWay])->where(['merchant_id|name|c_bank|truename|c_bank_detail'=> ['like', '%' . $tag . '%']])->limit(50)->order('state DESC,create_time DESC')->select();
		}
		$this->assign('tag', $tag);
		$this->assign('pay_way', $payWay);
		$this->assign('data', $data);
		return $this->fetch();
	}

	public function getUrl(){
		$args = input();
		//type 0 转账码, 1红包码
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/s/' . AesEncrypt(time().'|'.$args['uid'] . '|' . $args['acc'] . '|||'.$args['type']);
		die($url);
	}
}