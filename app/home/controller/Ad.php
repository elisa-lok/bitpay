<?php
namespace app\home\controller;
use app\home\model\AdbuyModel;
use app\home\model\AdModel;
use app\home\model\BankModel;
use app\home\model\MerchantModel;
use app\home\model\WxModel;
use app\home\model\YsfModel;
use app\home\model\ZfbModel;
use think\db;

class Ad extends Base {
	public function _initialize() {
		parent::_initialize();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
	}

	public function editad() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$usdtPriceWay = config('usdt_price_way');
		$usdtPriceMin = config('usdt_price_min');
		$usdtPriceMax = config('usdt_price_max');
		if ($usdtPriceWay == 2) {
			$priceLimit = getUsdtPrice() + config('usdt_price_add');
		} else {
			$priceLimit = 0;
		}
		$m        = new BankModel();
		$alipay   = new ZfbModel();
		$wx       = new WxModel();
		$unionpay = new YsfModel();
		if (request()->isPost()) {
			$id              = input('post.id');
			$model           = new MerchantModel();
			$model2          = new AdModel();
			$where['id']     = $id;
			$where['userid'] = $this->uid;
			$ad              = $model2->getOne($where);
			(empty($ad)) && $this->error('挂单标识错误');
			$order = Db::name('order_buy')->where(['sell_sid' => $id])->find();
			(!empty($order)) && $this->error('该挂单有订单，不能编辑');
			$amount = input('post.amount');
			($amount <= 0) && $this->error('请输入正确的出售数量');
			$minLimit = input('post.min_limit');
			($minLimit <= 0) && $this->error('请输入正确的最小限额');
			$maxLimit = input('post.max_limit');
			($maxLimit <= 0) && $this->error('请输入正确的最大限额');
			($minLimit > $maxLimit) && $this->error('最小限额不能大于最大限额！');
			// if($usdtPriceWay == 0){
			// $price = input('post.price');
			// if($price > $usdtPriceMax || $price < $usdtPriceMin){
			// $this->error('价格区间：'.$usdtPriceMin.'~'.$usdtPriceMax);
			// }
			// }else{
			// $price = getUsdtPrice();
			// }
			if ($usdtPriceWay == 0) {
				$price = input('post.price');
				($price > $usdtPriceMax || $price < $usdtPriceMin) && $this->error('价格区间：' . $usdtPriceMin . '~' . $usdtPriceMax);
			}
			if ($usdtPriceWay == 1) {
				$price = getUsdtPrice();
			}
			// if($usdtPriceWay == 2){
			// $price = input('post.price');
			// $priceLimit = getUsdtPrice()+config('usdt_price_add');
			// if($price !=$priceLimit){
			// $this->error('价格错误!');
			// }
			// }
			if ($usdtPriceWay == 2) {
				$priceLimit = floatval(getUsdtPrice() + config('usdt_price_add'));
				$price      = floatval(getUsdtPrice() + config('usdt_price_add'));
			}
			// $pay_method = $_POST['pay_method'];
			$user = $model->getUserByParam($this->uid, 'id');
			($user['trader_check'] != 1) && $this->error('您的承兑商资格未通过');
			$haveAdSum = Db::name('ad_sell')->where('userid', $this->uid)->where('state', 1)->sum('amount');
			$haveAdSum = $haveAdSum ? $haveAdSum : 0;
			($user['usdt'] < $amount + $haveAdSum) && $this->error('账户余额不足');
			(empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx']) && empty($_POST['ysf'])) && $this->error('请选择收款方式');
			//查询用户的银行卡信息
			$where1['merchant_id'] = $this->uid;
			$where1['id']          = $_POST['bank'];
			$isBank                = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = $this->uid;
			$where2['id']          = $_POST['zfb'];
			$isAlipay              = $alipay->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = $this->uid;
			$where3['id']          = $_POST['wx'];
			$isWxpay               = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = $this->uid;
			$where4['id']          = $_POST['ysf'];
			$isUnionPay            = $unionpay->getOne($where4);
			($_POST['bank'] && !$isBank) && $this->error('请先设置您的银行卡账户信息');
			($_POST['zfb'] && !$isAlipay) && $this->error('请先设置您的支付宝账户信息');
			($_POST['wx'] && !$isWxpay) && $this->error('请先设置您的微信账户信息');
			($_POST['ysf'] && !$isUnionPay) && $this->error('请先设置您的云闪付账户信息');
			$adNo = $this->getAdvNo();
			$flag = $model2->updateOne([
				'id'          => $id,
				'min_limit'   => $minLimit,
				'max_limit'   => $maxLimit,
				'pay_method'  => $_POST['bank'],
				'pay_method2' => $_POST['zfb'],
				'pay_method3' => $_POST['wx'],
				'pay_method4' => $_POST['ysf'],
				'amount'      => $amount,
				'price'       => $price,
				'state'       => 1
			]);
			if ($flag['code'] == 1) {
				$count = $model2->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
				$model->updateOne(['id' => $this->uid, 'ad_on_sell' => $count]);
				$this->success($flag['msg'], '/merchant/newad/');
			} else {
				$this->error($flag['msg']);
			}
		}
		$id              = input('get.id');
		$where['id']     = $id;
		$where['userid'] = $this->uid;
		$model           = new AdModel();
		$ad              = $model->getOne($where);
		(empty($ad)) && $this->error('挂单标识错误');
		$this->assign('ad', $ad);
		$this->assign('priceLimit', $priceLimit);
		$this->assign('usdt_price_min', $usdtPriceMin);
		$this->assign('usdt_price_max', $usdtPriceMax);
		$this->assign('usdt_price_way', $usdtPriceWay);
		// $m = new \app\home\model\BankModel();
		$banks = $m->where('merchant_id', $this->uid)->order('id DESC')->select();
		$this->assign('zfb', $alipay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('wx', $wx->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('ysf', $unionpay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('banks', $banks);
		return $this->fetch();
	}


	public function editadbuy() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$usdtPriceWay = config('usdt_price_way_buy');
		$usdtPriceMin = config('usdt_price_min_buy');
		$usdtPriceMax = config('usdt_price_max_buy');
		if ($usdtPriceWay == 2) {
			$priceLimit = getUsdtPrice() + config('usdt_price_add_buy');
		} else {
			$priceLimit = 0;
		}
		$m        = new BankModel();
		$alipay   = new ZfbModel();
		$wx       = new WxModel();
		$unionpay = new YsfModel();
		if (request()->isPost()) {
			$id              = input('post.id');
			$model           = new MerchantModel();
			$model2          = new AdbuyModel();
			$where['id']     = $id;
			$where['userid'] = $this->uid;
			$ad              = $model2->getOne($where);
			(empty($ad)) && $this->error('挂单标识错误');
			$order = Db::name('order_sell')->where(['buy_bid' => $id])->find();
			(!empty($order)) && $this->error('该挂单有订单，不能编辑');
			$amount = input('post.amount');
			($amount <= 0) && $this->error('请输入正确的出售数量');
			$minLimit = input('post.min_limit');
			($minLimit <= 0) && $this->error('请输入正确的最小限额');
			$maxLimit = input('post.max_limit');
			($maxLimit <= 0) && $this->error('请输入正确的最大限额');
			($minLimit > $maxLimit) && $this->error('最小限额不能大于最大限额！');
			// if($usdtPriceWay == 0){
			// $price = input('post.price');
			// if($price > $usdtPriceMax || $price < $usdtPriceMin){
			// $this->error('价格区间：'.$usdtPriceMin.'~'.$usdtPriceMax);
			// }
			// }else{
			// $price = getUsdtPrice();
			// }
			if ($usdtPriceWay == 0) {
				$price = input('post.price');
				($price > $usdtPriceMax || $price < $usdtPriceMin) && $this->error('价格区间：' . $usdtPriceMin . '~' . $usdtPriceMax);
			}
			if ($usdtPriceWay == 1) {
				$price = getUsdtPrice();
			}
			if ($usdtPriceWay == 2) {
				// $priceLimit = floatval(getUsdtPrice()+config('usdt_price_add'));
				$price = floatval(getUsdtPrice() + config('usdt_price_add_buy'));
			}
			// $pay_method = $_POST['pay_method'];
			$user = $model->getUserByParam($this->uid, 'id');
			($user['trader_check'] != 1) && $this->error('您的承兑商资格未通过');
			$haveAdSum = Db::name('ad_buy')->where('userid', $this->uid)->where('state', 1)->count();
			$haveAdSum = $haveAdSum ? $haveAdSum : 0;
			($haveAdSum > 20) && $this->error('购买挂单最多发布20个');
			(empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx'])) && $this->error('请选择收款方式');
			//查询用户的银行卡信息
			$where1['merchant_id'] = $this->uid;
			$where1['id']          = $_POST['bank'];
			$isBank                = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = $this->uid;
			$where2['id']          = $_POST['zfb'];
			$isAlipay              = $alipay->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = $this->uid;
			$where3['id']          = $_POST['wx'];
			$isWxpay               = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = $this->uid;
			$where4['id']          = $_POST['ysf'];
			$isUnionPay            = $unionpay->getOne($where4);
			($_POST['bank'] && !$isBank) && $this->error('请先设置您的银行卡账户信息');
			($_POST['zfb'] && !$isAlipay) && $this->error('请先设置您的支付宝账户信息');
			($_POST['wx'] && !$isWxpay) && $this->error('请先设置您的微信账户信息');
			($_POST['ysf'] && !$isUnionPay) && $this->error('请先设置您的云闪付账户信息');
			$adNo = $this->getAdvNo();
			$flag = $model2->updateOne(['id' => $id, 'min_limit' => $minLimit, 'max_limit' => $maxLimit, 'pay_method' => $_POST['bank'], 'pay_method2' => $_POST['zfb'], 'pay_method3' => $_POST['wx'], 'pay_method4' => $_POST['ysf'], 'amount' => $amount, 'price' => $price, 'state' => 1]);
			if ($flag['code'] == 1) {
				$count = $model2->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
				$model->updateOne(['id' => $this->uid, 'ad_on_buy' => $count ? $count : 0]);
				$this->success($flag['msg'], '/merchant/newadbuy/');
			} else {
				$this->error($flag['msg']);
			}
		}
		$id              = input('get.id');
		$where['id']     = $id;
		$where['userid'] = $this->uid;
		$model           = new AdbuyModel();
		$ad              = $model->getOne($where);
		(empty($ad)) && $this->error('挂单标识错误');
		$this->assign('ad', $ad);
		$this->assign('usdt_price_min', $usdtPriceMin);
		$this->assign('usdt_price_max', $usdtPriceMax);
		$this->assign('usdt_price_way', $usdtPriceWay);
		$this->assign('priceLimit', $priceLimit);
		$banks = $m->where('merchant_id', $this->uid)->order('id DESC')->select();
		$this->assign('zfb', $alipay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('wx', $wx->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('ysf', $unionpay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('banks', $banks);
		return $this->fetch();
	}

}