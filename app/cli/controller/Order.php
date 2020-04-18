<?php
namespace app\cli\controller;
use think\Cache;
use think\Db;

class Order extends Base {
	public function _initialize() {
		parent::_initialize();
	}

	// 卖单倒计时
	public function sellCountDown() {
		$orderBuyModel = Db::name('order_buy');
		$list          = $orderBuyModel->where(time() . "-ctime > ltime*60 AND status=0 ")->whereOr('status=1 AND dktime < ' . (time() - config('paid_expire')))->select();
		!$list && die('无数据');
		foreach ($list as $key => $vv) {
			// 锁定操作 代码执行完成前不可继续操作
			if (Cache::has($vv['id'])) continue;
			Cache::set($vv['id'], TRUE, 60);
			Db::startTrans();
			$memo      = $vv['status'] == 1 ? '恶意点付款' : '自动支付超时';
			$orderInfo = $orderBuyModel->where(['id' => $vv['id']])->find();
			//$seller = Db::name('merchant')->where(array('id'=>$vv['sell_id']))->find();
			$buyer   = Db::name('merchant')->where(['id' => $vv['buy_id']])->find();
			$rs1     = $orderBuyModel->update(['status' => 5, 'id' => $vv['id'], 'desc' => $memo]);
			$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
			// 回滚挂单,  增加剩余量, 减少交易量
			$adSellModel = Db::name('ad_sell');
			$rs2         = $adSellModel->where('id', $orderInfo['sell_sid'])->update(['remain_amount' => Db::raw('remain_amount + ' . $realAmt), 'trading_volume' => Db::raw('trading_volume -' . $realAmt)]);
			// 获取挂单
			$sellInfo = $adSellModel->where('id', $orderInfo['sell_sid'])->find();
			$rs3      = 1;
			if ($sellInfo['state'] == 3) {
				// 如果挂单已下架 回滚余额
				$rs3 = balanceChange(FALSE, $orderInfo['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $orderInfo['id'], $memo);
			}
			if ($rs1 && $rs2 && $rs3) {
				Db::commit();
				//请求回调接口,失败, TODO 不回调
				// askNotify(['amount' => $orderInfo['deal_num'], 'orderid' =>$orderInfo['orderid'],'appid' => $buyer['appid'], 'status'=> 0], $orderInfo['notify_url'], $buyer['key']);
			} else {
				Db::rollback();
				$msg = '【' . date('Y-m-d H:i:s') . '】 订单' . $vv['id'] . '回滚失败, 买家ID: ' . $vv['buy_id'] . ' , 卖家ID: ' . $vv['sell_id'] . ", 失败步骤: $rs1,$rs2,$rs3\r\n";
				file_put_contents(RUNTIME_PATH . 'data/cli_sellCountDown_' . date('ymd') . '.log', $msg, FILE_APPEND);
			}
		}
	}

	public function buyCountDown() {
		$orderSellModel = Db::name('order_sell');
		$list           = $orderSellModel->where(time() . "-ctime>ltime*60 AND status=0 ")->select();
		!$list && die('无数据');
		foreach ($list as $key => $vv) {
			Db::startTrans();
			$rs1     = $orderSellModel->update(['status' => 5, 'id' => $vv['id']]);
			$realAmt = $vv['deal_num'] + $vv['fee'];
			$rs2     = balanceChange(FALSE, $vv['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $vv['id'], "买单自动支付超时");
			if ($rs1 && $rs2) {
				Db::commit();
			} else {
				Db::rollback();
				$msg = '【' . date('Y-m-d H:i:s') . '】 订单' . $vv['id'] . '回滚失败, 买家ID: ' . $vv['buy_id'] . ' , 卖家ID: ' . $vv['sell_id'] . ", 失败步骤: $rs1,$rs2";
				file_put_contents(RUNTIME_PATH . 'data/cli_buyCountDown_' . date('ymd') . '.log', $msg, FILE_APPEND);
			}
		}
	}
}

?>