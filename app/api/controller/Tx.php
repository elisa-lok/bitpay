<?php
namespace app\api\controller;
use think\Db;

class Tx {
	public function getNotify($id){
		!preg_match('/^\w{6,10}$/', $id) && showMsg('参数错误');
		$user = Db::name('merchant')->where('vcode', $id)->find();
		$args = input('.post');
		!$user && showMsg('用户不存在');
		$order  = Db::name('order_buy')->where('sell_id', $user['id'])->whereIn('status' , '0,1')->find();
		!$order && showMsg('订单不存在');

	}

	private function logNotify(){
		file_put_contents(RUNTIME_PATH . 'data/get_notify_' . date('ymd') . '.log', '【' . date('Y-m-d H:i:s', time()) . '】【URL】' . $url . '【返回】' . $return . ',【请求】' . json_encode($data, 320) . PHP_EOL, FILE_APPEND);
		file_put_contents(RUNTIME_PATH . 'data/notify_' . date('ymd') . '.log', '【' . date('Y-m-d H:i:s', time()) . '】【URL】' . $url . '【返回】' . $return . ',【请求】' . json_encode($data, 320) . PHP_EOL, FILE_APPEND);
	}

	public function logIp(){
		Db::name('tx_ip')->insert(['ip' => getIp()]);
	}
}