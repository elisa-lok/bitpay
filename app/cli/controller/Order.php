<?php
namespace app\cli\controller;
use think\Cache;
use think\Db;

class Order extends Base {
	// 卖单倒计时
	public function sellCountDown() {
		$list = Db::name('order_buy')->where("" . time() . "-ctime>ltime*60 and status=0 ")->select();
		if (!$list) {
			return;
		}
		foreach ($list as $key => $vv) {
			// 锁定操作 代码执行完成前不可继续操作
			if (Cache::has($vv['id'])) continue;
			Cache::set($vv['id'], TRUE, 60);
			Db::startTrans();
			$orderInfo = Db::name('order_buy')->where(['id' => $vv['id']])->find();
			//$seller = Db::name('merchant')->where(array('id'=>$vv['sell_id']))->find();
			$buyer = Db::name('merchant')->where(['id' => $vv['buy_id']])->find();
			//$table = "movesay_".'usdt'."_log";
			$rs1     = Db::name('order_buy')->update(['status' => 5, 'id' => $vv['id']]);
			$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
			// 回滚挂单,  增加剩余量, 减少交易量
			$rs2 = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->update(['remain_amount' => Db::raw('remain_amount + ' . $realAmt), 'trading_volume' => Db::raw('trading_volume -' . $realAmt)]);
			// 获取挂单
			$sellInfo = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->find();
			$rs3      = $rs4 = 1;
			if ($sellInfo['state'] == 3) {
				// 如果挂单已下架 回滚余额
				$rs3 = balanceChange(FALSE, $orderInfo['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $orderInfo['id'], "支付超时->自动下架");
			}
			if ($rs1 && $rs2 && $rs3) {
				Db::commit();
				//请求回调接口,失败
				$data['amount']  = $orderInfo['deal_num'];
				$data['orderid'] = $orderInfo['orderid'];
				$data['appid']   = $buyer['appid'];
				$data['status']  = 0;
				askNotify($data, $orderInfo['notify_url'], $buyer['key']);
			} else {
				Db::rollback();
				$msg = '【' . date('Y-m-d H:i:s') . '】 订单' . $vv['id'] . '回滚失败, 买家ID: ' . $vv['buy_id'] . ' , 卖家ID: ' . $vv['sell_id'] . ", 失败步骤: $rs1,$rs2,$rs3";
				file_put_contents(RUNTIME_PATH . 'data/cli_sellCountDown_' . date('ymd') . '.log', $msg, FILE_APPEND);
			}
		}
	}

	public function buyCountDown() {
		$list = Db::name('order_sell')->where("" . time() . "-ctime>ltime*60 and status=0 ")->select();
		if (!$list) {
			return;
		}
		foreach ($list as $key => $vv) {
			Db::startTrans();
			$orderInfo = Db::name('order_sell')->where(['id' => $vv['id']])->find();
			$rs1       = Db::name('order_sell')->update(['status' => 5, 'id' => $vv['id']]);
			$realAmt   = $orderInfo['deal_num'] + $orderInfo['fee'];
			$rs2       = balanceChange(FALSE, $orderInfo['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $orderInfo['id'], "支付超时->自动下架->buy");
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