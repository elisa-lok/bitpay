<?php
namespace app\api\controller;
use think\Db;

header('Content-Type: text/html; charset=utf-8');

class Pay extends Base {
	public function index($type, $id, $appId) {
		$payType = ['wxpay' => 'wx', 'alipay' => 'zfb', 'unionpay' => 'ysf', 'bank' => 'bankcard'];
		!isset($payType[$type]) && $this->error('支付方式不存在');
		$order = Db::name('order_buy')->where('id', $id)->find();
		(empty($order)) && $this->error('订单参数错误1');
		$buyer = Db::name('merchant')->where('id', $order['buy_id'])->find();
		(empty($buyer)) && $this->error('订单参数错误2');
		($buyer['appid'] != $appId) && $this->error('订单参数错误3');
		$methodType = ['wxpay' => 'pay_method3', 'alipay' => 'pay_method2', 'unionpay' => 'pay_method4', 'bank' => 'pay_method'];
		$payId      = Db::name('ad_sell')->where(['id' => $order['sell_sid']])->value($methodType[$type]);
		!$payId && $this->error('支付方式不存在, 请重新生成的订单');
		$order['pay_type']    = $type;
		$order['deal_amount'] = round($order['deal_amount'], 2);
		$order['end_time']    = $order['ltime'] * 60 + $order['ctime'];
		$order['remain_time'] = $order['ltime'] * 60 + $order['ctime'] - time();
		$this->assign('id', $id);
		$this->assign('order', $order);
		$this->assign('appid', $appId);
		$payDetail           = Db::name('merchant_' . $payType[$type])->where('id', $payId)->find();
		$payDetail['qrcode'] = StrToMicroTime($payDetail['qrcode'], true);
		/*固定码*/ //$longUrl = 'alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={"s": "money","u":"' . $alipay['alipay_id'] . '","a":"' . $order['deal_amount'] . '","m":"' . $order['check_code'] . '"}';
		/*转账码*/ //$longUrl ='https://ds.alipay.com/?from=mobilecodec&scheme='.urlencode('alipays://platformapi/startapp?appId=20000200&actionType=toAccount&account=&amount=&userId=' . $alipay['alipay_id'] . '&memo=' . $order['check_code'] .'');
		/*好友码*/
		$domain = Db::name('sys_domain')->where('state', 1)->field('scheme,domain')->select();
		shuffle($domain);
		$domain = $domain[0]['scheme'].'://'.$domain[0]['domain'];
		if ($type == 'alipay' && preg_match('/^20\d{14}$/', $payDetail['c_bank_card']) && (preg_match('/^1[3456789]\d{9}$/', $payDetail['c_bank']) || preg_match('/^\w+((.\w+)|(-\w+))@[A-Za-z0-9]+((.|-)[A-Za-z0-9]+).[A-Za-z0-9]+$/', $payDetail['c_bank']))) {
			$payDetail['qrcode'] = $domain . '/s/' . AesEncrypt(time().'|'.$payDetail['c_bank_card'] . '|' . $payDetail['c_bank'] . '|||0');
		}
		$this->assign('payDetail', $payDetail);
		Db::name('tx_ip')->insert(['ip' => getIp()]);
		return $this->fetch('transfer');
	}

	public function checkOutTime() {
		$id    = input('post.id');
		$order = Db::name('order_buy')->where('id', $id)->find();
		!$order && $this->error('no order');
		$remainTime = $order['ltime'] * 60 + $order['ctime'] - time();
		if ($order['status'] == 0 && $remainTime < 1) {
			Db::startTrans();
			$rs1 = Db::name('order_buy')->where('id', $id)->update(['status' => 5, 'dktime' => time(), 'finished_time' => time(), 'desc' => '订单超时']);
			$rs2 = $this->abortTx($order);
			($rs1 && $rs2) ? Db::commit() : Db::rollback();
		}
		($order['status'] == 5) && $this->success('ok');
		($remainTime < 0 || $order['status'] != 0) ? $this->success('ok') : $this->error('no');
	}

	public function confirm() {
		$id    = input('post.id');
		$appid = input('post.appid');
		$order = Db::name('order_buy')->where('id', $id)->find();
		empty($order) && $this->error('订单参数错误1');
		$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();
		empty($merchant) && $this->error('订单参数错误2');
		($merchant['appid'] != $appid) && $this->error('appid错误');
		($order['status'] == 5) && $this->error('此订单已取消');
		($order['status'] >= 1) && $this->success($order['return_url']);
		$rs = Db::name('order_buy')->where('id', $id)->update(['status' => 1, 'dktime' => time(), 'desc' => '付款方点付款']);
		if ($rs) {
			// $mobile = Db::name('merchant')->where('id', $order['sell_id'])->value('mobile');
			// if (!empty($mobile)) {
			// 	$sendText = Db::name('config')->where('name', 'send_message_content')->value('value');
			// 	if ($sendText) {
			// 		$replaceTpl = ['{usdt}', '{cny}', '{tx_id}', '{check_code}', '{pay_way}'];
			// 		$replaceStr = [round($order['deal_num'], 2), round($order['deal_amount'], 2), $order['orderid'], $order['check_code'], PayWayTxt[$order['pay_way']]];
			// 		$content    = str_replace($replaceTpl, $replaceStr, $sendText);
			// 		sendSms($mobile, $content);
			// 	}
			// }
			$this->success($order['return_url']);
		}
		$this->error('确认失败，请稍后再试');
	}

	public function cancel() {
		$id         = (int)input('post.id');
		$appid      = input('post.appid');
		$reason     = input('post.reason');
		$orderModel = Db::name('order_buy');
		$order      = $orderModel->where('id', $id)->find();
		!$order && $this->error('订单不存在');
		$user = Db::name('merchant')->where('id', $order['buy_id'])->find();
		(!$user || $user['appid']) != $appid && $this->error('订单错误');
		($order['status'] > 0) && $this->success($order['return_url']);
		Db::startTrans();
		$rs1 = Db::name('order_buy')->where('id', $id)->update(['status' => 5, 'dktime' => time(), 'finished_time' => time(), 'desc' => $reason]);
		// 查找订单是否已经下架
		$rs2 = $this->abortTx($order);
		if ($rs1 && $rs2) {
			Db::commit();
			$this->success($order['return_url']);
		} else {
			Db::rollback();
			$this->error('处理失败');
		}
	}

	private function abortTx(&$order) {
		$realAmt   = $order['deal_num'] + $order['fee'];
		$sellOrder = Db::name('ad_sell')->where('id', $order['sell_sid'])->lock()->find();
		if ($sellOrder['state'] == 3) { // 1进行中,2暂停
			return balanceChange(false, $order['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $order['id'], '页面支付超时');
			// return Db::name('merchant')->where('id', $order['sell_sid'])->update(['usdt' => Db::raw('`usdt` + ' . $realAmt), 'usdtd' => Db::raw('`usdtd` - ' . $realAmt)]);
		}
		return Db::name('ad_sell')->where('id', $order['sell_sid'])->setInc('remain_amount', $realAmt);
	}
}