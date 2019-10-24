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
			$orderInfo['status'] == $args['status'] && showMsg('操作成功'); //状态未改变
			$updateArr = ['status' => $args['status']];
			if ($args['timeout']) {
				//计算延长时间
				$updateArr['ltime']         = ((time() - $orderInfo['ctime']) / 60) + 61;//延长60分钟, 预留多一分钟
				$updateArr['finished_time'] = 0;
			}
			Db::startTrans();
			$res1 = Db::name('order_buy')->where('id=' . $edit)->update($updateArr); // 更新订单
			// 判断剩余额度
			$res2 = $res3 = 1;
			// 重建订单信息
			if ($args['refactor']) {
				!in_array($orderInfo['status'], ['5','9']) && showMsg('该状态不能重建订单', 0);
				//在余额里面进行扣钱
				$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
				$res2     = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setDec('usdt', $realAmt);
				$res3     = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdtd', $realAmt);
			}
			if($res1 && $res2 && $res3){
				Db::commit();
				showMsg('操作成功', 1);
			} else{
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