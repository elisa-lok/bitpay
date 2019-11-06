<?php

namespace app\admin\controller;

use think\Db;

class Order extends Base {
	public function buy($edit) {
		$orderInfoModel = Db::name('order_buy')->where('id=' . $edit);
		$orderInfo      = $orderInfoModel->find();
		!$orderInfo && $this->error('订单不存在');
		if (request()->isPost()) {
			$args = input('post.');

			$src_status = $orderInfo['status'];

			($orderInfo['status'] == $args['status']) && ($orderInfo['deal_amount'] == $args['deal_amount']) && showMsg('操作成功'); //状态未改变
			$updateArr = ['status' => $args['status']];
			if($args['deal_amount'] != $orderInfo['deal_amount']){
				$updateArr['deal_amount'] = $args['deal_amount'];
				$updateArr['deal_num'] = number_format($args['deal_amount'] / $orderInfo['deal_price'], 8,'.','');
			}
			if ($args['timeout']) {
				//计算延长时间
				$updateArr['ltime']         = ((time() - $orderInfo['ctime']) / 60) + 61;//延长60分钟, 预留多一分钟
				$updateArr['finished_time'] = 0;
			}
			Db::startTrans();
			$res1 = Db::name('order_buy')->where('id=' . $edit)->update($updateArr); // 更新订单
			// 判断剩余额度
			$res2 = $res3 = $res4 = $res5 = $res6 = $res7 = 1;
			// 重建订单信息
			if ($args['refactor']) {
				!in_array($orderInfo['status'], ['5', '9']) && showMsg('该状态不能重建订单', 0);
				//在余额里面进行扣钱
				$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
				$res2    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setDec('usdt', $realAmt);
				$res3    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdtd', $realAmt);
			}
			//  判断取消订单
            if ($args['status'] == 5 && ($src_status == 0 || $src_status == 1)){
                // 减少挂卖单交易量和增加挂卖单剩余量
                $real_number = $orderInfo['deal_num'] + $orderInfo['fee'];
                $res4    = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->setDec('trading_volume', $real_number);
                $res5    = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->setInc('remain_amount', $real_number);
                // 获取挂单
                $sell = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->find();
                if ($sell['state'] == 2){
                    // 如果挂单已下架 回滚余额
                    $res6 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $real_number);
                    $res7 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $real_number);
                }
                // 减少用户冻结余额和增加用户余额
                //$res2    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdt', $orderInfo['deal_num']);
                //$res3    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdtd', $orderInfo['deal_num']);
            }
			if ($res1 && $res2 && $res3 && $res4 && $res5 && $res6 && $res7) {
				Db::commit();
				showMsg('操作成功', 1);
			} else {
				Db::rollback();
				showMsg('操作失败', 0);
			}
		}
		$this->assign('data', $orderInfo);
		return $this->fetch('order/buy_edit');
	}

	// todo 卖单编辑
	public function sell($edit) {
		$orderInfo = Db::name('order_sell')->where('id=' . $edit)->find();
		!$orderInfo && $this->error('订单不存在');
	}
}