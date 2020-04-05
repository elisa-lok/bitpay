<?php
namespace app\cli\controller;
use think\Db;

class Ad extends Base {
	public function updateAdSellPrice() {
		$usdtPriceWay = config('usdt_price_way');
		($usdtPriceWay == 0) && die('price way error');
		$sellOrderModel = Db::name('ad_sell');
		$sellOrders     = $sellOrderModel->where('state=1')->select();
		$usdtPrice      = getUsdtPrice();
		foreach ($sellOrders as $v) {
			$price = $usdtPrice * (1 + getTopAgentFeeRate($v['userid']));
			$sellOrderModel->where('id', $v['id'])->update(['price' => $price]); // 计算溢价
		}
		$msg = '【' . date('Y-m-d H:i:s') . "】 卖单加价, 更新模式: $usdtPriceWay, USDT价格:$usdtPrice \r\n";
		file_put_contents(RUNTIME_PATH . 'data/cli_updateAdSellPrice_' . date('ymd') . '.log', $msg, FILE_APPEND);
	}

	// 更新市价单价格
	public function updateAdBuyPrice() {
		$usdtPriceWay = config('usdt_price_way_buy');
		($usdtPriceWay == 0) && die('price way buy error');
		$addFee = $usdtPriceWay == 2 ? config('usdt_price_add_buy') : 0;
		// 只有支持加价模式的变动
		Db::startTrans();
		$usdtPrice = getUsdtPrice();
		$res       = Db::name('ad_buy')->where('state=1')->update(['price' => $usdtPrice + $addFee]);
		$res ? Db::commit() : Db::rollback();
		$msg = '【' . date('Y-m-d H:i:s') . '】 买单加价价格更新' . ($res ? '成功' : '失败') . ", 更新模式:$usdtPriceWay, USDT价格:$usdtPrice  \r\n";
		file_put_contents(RUNTIME_PATH . 'data/cli_updateAdBuyPrice_' . date('ymd') . '.log', $msg, FILE_APPEND);
	}

	public function downAd() {
		$remain = config('ad_down_remain_amount');//充值手续费
		//挂卖下架
		/*$sellids = Db::name('ad_sell')->field('id, amount, userid')->where('state', 1)->where('amount', 'gt', 0)->select();
		foreach ($sellids as $k => $v) {
			$total = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 7)->sum('deal_num');
			if ($v['amount'] <= $total + $remain) {
				//开始下架
				Db::name('ad_sell')->where('id', $v['id'])->setField('state', 2);
				$nowAds = Db::name('ad_sell')->where('userid', $v['userid'])->where('state', 1)->where('amount', 'gt', 0)->count();
				Db::name('merchant')->where('id', $v['userid'])->setField('ad_on_sell', $nowAds ? $nowAds : 0);
			}
		}*/
		//购买挂单下架
		$buyIds = Db::name('ad_buy')->field('id, amount, userid')->where('state', 1)->where('amount', 'gt', 0)->select();
		foreach ($buyIds as $k => $v) {
			$total = Db::name('order_sell')->where('buy_bid', $v['id'])->where('status', 'neq', 5)->sum('deal_num');
			if ($v['amount'] <= $total + $remain) {
				//开始下架
				Db::name('ad_buy')->where('id', $v['id'])->setField('state', 2);
				$nowAds = Db::name('ad_buy')->where('userid', $v['userid'])->where('state', 1)->where('amount', 'gt', 0)->count();
				Db::name('merchant')->where('id', $v['userid'])->setField('ad_on_buy', $nowAds ? $nowAds : 0);
			}
		}
	}

	// TODO 关闭余额不足的订单
	public function closeAd() {
		$orders = Db::name('ad_sell')->where(['state' => 1])->select();
		foreach ($orders as $v) {
			if ($v['remain_amount'] * $v['price'] < $v['min_limit']) {
				Db::startTrans();
				if (!Db::name('ad_sell')->where(['id' => $v['id'], 'userid' => $this->uid])->update(['state' => 3, 'finished_time' => time()])) continue;
				if (!balanceChange(FALSE, $this->uid, $v['remain_amount'], 0, -$v['remain_amount'], 0, BAL_REDEEM, $orders['orderid'], '余额不足自动关闭')) continue;
				$count = Db::name('ad_sell')->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
				Db::name('merchant')->update(['id' => $this->uid, 'ad_on_sell' => $count ? $count : 0]);
				Db::commit();
			}
		}
	}
}