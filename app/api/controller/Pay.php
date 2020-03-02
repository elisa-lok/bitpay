<?php
namespace app\api\controller;
use think\Db;

header('Content-Type: text/html; charset=utf-8');

class Pay extends Base {
	public function index() {
		$id    = input('get.id');
		$appId = input('get.appid');
		$type  = input('get.type');
		$order = Db::name('order_buy')->where('id', $id)->find();
		(empty($order)) && $this->error('订单参数错误1');
		$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();
		(empty($merchant)) && $this->error('订单参数错误2');
		($merchant['appid'] != $appId) && $this->error('请求路径appid错误');
		$this->assign('remaintime', $order['ltime'] * 60 + $order['ctime'] - time());
		$bankId     = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method');
		$alipayId   = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method2');//5
		$wxpayId    = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method3');//4
		$unionpayId = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method4');//2
		$this->assign('id', $id);
		$this->assign('order', $order);
		$this->assign('appid', $appId);
		$this->assign('money', round($order['deal_amount'], 2));
		$this->assign('amount', $order['deal_num']);
		$this->assign('no', $order['order_no']);
		$merchant = Db::name('merchant')->where('id', $order['sell_id'])->find();
		$bank     = [];
		$payArr   = [];
		// 防封域名
		$domain = Db::name('sys_domain')->where('state', 1)->field('domain')->select();
		$domain = array_column($domain, 'domain');
		shuffle($domain);
		if ($type == 'bank' && $bankId > 0) {
			$bank                    = Db::name('merchant_bankcard')->where('id', $bankId)->find();
			$merchant['c_bank_card'] = $bank['c_bank_card'];
			$merchant['name']        = $bank['truename'];
			$merchant['bank']        = $bank['c_bank'] . $bank['c_bank_detail'];
			$payArr[]                = 'bank';
		}
		if ($type == 'alipay' && $alipayId > 0) {
			$alipay = Db::name('merchant_zfb')->where('id', $alipayId)->find();
			//empty($alipay['alipay_id']) && $this->error('appid不存在');
			//$url = 'https://api.uomg.com/api/long2dwz';
			//$longUrl = 'alipays://platformapi/startapp?appId=20000116&actionType=toAccount&goBack=NO&memo='. $order['check_code'].'&userId=' . $alipay['alipay_id'];
			/*固定码*/ //$longUrl = 'alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={"s": "money","u":"' . $alipay['alipay_id'] . '","a":"' . $order['deal_amount'] . '","m":"' . $order['check_code'] . '"}';
			/*转账码*/ //$longUrl ='https://ds.alipay.com/?from=mobilecodec&scheme='.urlencode('alipays://platformapi/startapp?appId=20000200&actionType=toAccount&account=&amount=&userId=' . $alipay['alipay_id'] . '&memo=' . $order['check_code'] .'');
			// 防封域名
			//$redirectUrl = $_SERVER['REQUEST_SCHEME'] . '://' . ($domain[0] ? $domain[0] : $_SERVER['SERVER_NAME']) . '/go/url/' . base64_encode($longUrl);
			/*$merchant['c_alipay_img'] = $longUrl;
			//$merchant['c_alipay_img'] = $redirectUrl;
			$merchant['alipay_name'] = $alipay['truename'];
			$merchant['alipay_acc'] = $alipay['c_bank'];
			$payArr[] .= 'zfb';*/
			$merchant['zfb']          = $alipay['c_bank_card'];
			$merchant['name']         = $alipay['truename'];
			$merchant['c_alipay_img'] = $alipay['c_bank_detail'];
			$merchant['alipay_name']  = $alipay['truename'];
			$merchant['alipay_acc']   = $alipay['c_bank'];
			$payArr[]                 .= 'zfb';
		}
		if ($type == 'wxpay' && $wxpayId > 0) {
			$wx                       = Db::name('merchant_wx')->where('id', $wxpayId)->find();
			$merchant['wx']           = $wx['c_bank_card'];
			$merchant['name']         = $wx['truename'];
			$merchant['c_wechat_img'] = $wx['c_bank_detail'];
			$merchant['wxpay_name']   = $wx['truename'];
			$merchant['wxpay_acc']    = $wx['c_bank'];
			$payArr[]                 .= 'wx';
		}
		if ($type == 'unionpay' && $unionpayId > 0) {
			$unionpay                  = Db::name('merchant_ysf')->where('id', $unionpayId)->find();
			$merchant['ysf']           = $unionpay['c_bank_card'];
			$merchant['name']          = $unionpay['truename'];
			$merchant['c_ysf_img']     = $unionpay['c_bank_detail'];
			$merchant['unionpay_name'] = $unionpay['truename'];
			$merchant['unionpay_acc']  = $unionpay['c_bank'];
			$payArr[]                  .= 'ysf';
		}
		$this->assign('payarr', $payArr);
		//$this->assign('logUrl', $longUrl);
		$this->assign('merchant', $merchant);
		//平均确认时间
		if (!$merchant['transact']) {
			$min    = 0;
			$second = 0;
		} else {
			$total   = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $order['sell_id'])->where('status', 4)->select();
			$average = intval($total[0]['total'] / $merchant['transact']);
			$min     = intval(floor($average / 60));
			$second  = $average % 60;
		}
		Db::name('tx_ip')->insert(['ip' => getIp()]);
		$this->assign('domain', ($domain[0] ? $domain[0] : $_SERVER['SERVER_NAME']));
		$this->assign('min', $min);
		$this->assign('second', $second);
		return $this->fetch('pay');
	}
}