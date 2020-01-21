<?php
namespace app\home\controller;
use app\home\model\MerchantModel;
use app\home\model\OrderModel;
use think\Cache;
use think\Db;
use think\exception\DbException;

class Order extends Base {
	public function _initialize() {
		parent::_initialize();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
	}

	// 承兑商释放币
	public function deal() {//放行usdt
		if (request()->isPost()) {
			!$this->uid && $this->error('请登录操作');
			$id               = input('post.id');
			$orderModel       = new OrderModel();
			$mchModel         = new MerchantModel();
			$where['id']      = $id;
			$where['sell_id'] = $this->uid;
			$orderInfo        = $orderModel->where($where)->find();
			empty($orderInfo) && $this->error('订单不存在');
			($orderInfo['status'] == 5) && $this->error('订单已经被取消');
			($orderInfo['status'] == 6) && $this->error('订单申诉中，无法释放');
			//20190830修改,不打款,也可以确认
			$unpay = ($orderInfo['status'] == 0) ? 1 : 0;
			// $this->error('此订单对方已经拍下还未付款');
			($orderInfo['status'] >= 3) && $this->error('此订单已处理,无需再次处理');
			$seller = $mchModel->where('id', $this->uid)->find();
			$buyer  = $mchModel->where('id', $orderInfo['buy_id'])->find();
			($seller['usdtd'] < $orderInfo['deal_num']) && $this->error('您的冻结不足，交易失败');
			// 锁定操作 代码执行完成前不可继续操作 60秒后可再次点击操作
			Cache::has($id) && $this->error('操作频繁,请稍后重试');
			Cache::set($id, TRUE, 60);

			//盘口费率
			$pkFeeRate = $buyer['merchant_pk_fee'];
			$pkFeeRate = $pkFeeRate ? $pkFeeRate : 0;
			$totalFee  = $orderInfo['deal_num'] * $pkFeeRate / 100;
			//承兑商卖单奖励
			$sellerGet         = $seller['trader_trader_get'];
			$sellerGet         = $sellerGet ? $sellerGet : 0;
			$sellerAwardMoney  = $sellerGet * $orderInfo['deal_num'] / 100;
			$sellerParentMoney = $buyerParentMoney = $buyerAgentExist = 0;
			// 承兑商代理
			$firstParent      = $secondParent = $thirdParent = NULL;
			$sellerFirstMoney = $sellerSecondMoney = $sellerThirdMoney = 0;
			if ($seller['pid'] > 0) {
				// 一级
				$firstParent      = $mchModel->where('id', $seller['pid'])->find();
				$sellerFirstMoney = ($firstParent && $firstParent['agent_check'] == 1) ? $orderInfo['deal_num'] * (float)$firstParent['trader_parent_get'] / 100 : $sellerFirstMoney;
				$sellerFirstMoney < 0 && $this->error('配置异常, 请联系管理员,错误码:231');
				// 二级
				if ($firstParent && $firstParent['pid'] > 0) {
					$secondParent      = $mchModel->where('id', $firstParent['pid'])->find();
					$sellerSecondMoney = ($secondParent && $secondParent['agent_check'] == 1) ? ($orderInfo['deal_num'] * ($secondParent['trader_parent_get'] - $firstParent['trader_parent_get']) / 100) : $sellerSecondMoney;
					$sellerSecondMoney < 0 && $this->error('配置异常, 请联系管理员,错误码:232');
				}
				// 三级
				if ($secondParent && $secondParent['pid']) {
					$thirdParent      = $mchModel->where('id', $secondParent['pid'])->find();
					$sellerThirdMoney = ($thirdParent && $thirdParent['agent_check'] == 1) ? ($orderInfo['deal_num'] * ($thirdParent['trader_parent_get'] - $secondParent['trader_parent_get']) / 100) : $sellerThirdMoney;
					$sellerThirdMoney < 0 && $this->error('配置异常, 请联系管理员,错误码:233');
				}
				$sellerParentMoney = $sellerFirstMoney + $sellerSecondMoney + $sellerThirdMoney;
			}
			// 买家代理, 商户
			if ($buyer['pid']) {
				$buyerParent    = $mchModel->where('id', $buyer['pid'])->find();
				$buyerParentGet = $buyerParent['enable_new_get'] == 0 ? $buyerParent['trader_merchant_parent_get'] : $buyer['trader_merchant_parent_get_new'];
				if ($buyerParent['agent_check'] == 1 && $buyerParentGet > 0) {
					//商户代理利润
					$buyerAgentExist  = 1;
					$buyerParentGet   = $buyerParentGet ? $buyerParentGet : 0;
					$buyerParentMoney = $buyerParentGet * $orderInfo['deal_num'] / 100;
					($sellerParentMoney + $buyerParentMoney + $sellerAwardMoney > $totalFee) && $this->error('配置异常, 请联系管理员,错误码:234');
				}
			}
			//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
			$sum = $orderInfo['deal_num'] - $totalFee;
			//实际到账金额
			$platformMoney = $totalFee - $sellerParentMoney - $buyerParentMoney - $sellerAwardMoney;
			$platformMoney < 0 && $this->error('配置异常, 请联系管理员,错误码:235');
			Db::startTrans();
			try {
				// 订单更新
				$updateCondition = $unpay == 1 ? ['status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $platformMoney] : ['status' => 4, 'finished_time' => time(), 'platform_fee' => $platformMoney];
				!(Db::name('order_buy')->where('id', $id)->update($updateCondition)) && $this->rollbackAndMsg('订单更新失败', $id);
				// 卖家减去冻结
				!balanceChange(FALSE, $orderInfo['sell_id'], 0, 0, -$orderInfo['deal_num'], 0, BAL_SOLD, $orderInfo['id']) && $this->rollbackAndMsg('冻结余额不足,错误码:10001', $id);
				// 卖家增加数据, 增加平均交易时间:秒average , 以及交易单数transact
				!$mchModel->where('id', $orderInfo['sell_id'])->update(['transact' => Db::raw('transact+1')]) && $this->rollbackAndMsg('卖家信息操作失败,错误码:10002', $id);
				// 卖家卖单奖励
				if ($sellerAwardMoney > 0) {
					!balanceChange(FALSE, $orderInfo['sell_id'], $sellerAwardMoney, 0, 0, 0, BAL_COMMISSION, $orderInfo['id']) && $this->rollbackAndMsg('订单操作失败,,错误码:10003', $id);
					!(Db::name('trader_reward')->insert(['uid' => $orderInfo['sell_id'], 'orderid' => $orderInfo['id'], 'amount' => $sellerAwardMoney, 'type' => 0, 'create_time' => time()])) && $this->rollbackAndMsg('订单操作失败,错误码:10004', $id);
				}
				//卖家代理利润
				if ($sellerFirstMoney > 0) {
					// 一级
					$rsArr = agentReward($firstParent['id'], $orderInfo['sell_id'], $sellerFirstMoney, 3, $id);
					!$rsArr[0] && $this->rollbackAndMsg('订单操作失败,错误码:10005', $id);
					!$rsArr[1] && $this->rollbackAndMsg('订单操作失败,错误码:10006', $id);
					// 二级
					if ($sellerSecondMoney > 0) {
						$rsArr = agentReward($secondParent['id'], $orderInfo['sell_id'], $sellerSecondMoney, 3, $id);
						!$rsArr[0] && $this->rollbackAndMsg('订单操作失败,错误码:10007', $id);
						!$rsArr[1] && $this->rollbackAndMsg('订单操作失败,错误码:10008', $id);
					}
					// 三级
					if ($sellerThirdMoney > 0) {
						$rsArr = agentReward($thirdParent['id'], $orderInfo['sell_id'], $sellerThirdMoney, 3, $id);
						!$rsArr[0] && $this->rollbackAndMsg('订单操作失败,错误码:10009', $id);
						!$rsArr[1] && $this->rollbackAndMsg('订单操作失败,错误码:10010', $id);
					}
				}
				// 买家加币
				!balanceChange(FALSE, $orderInfo['buy_id'], $sum, 0, 0, 0, BAL_BOUGHT, $orderInfo['id']) && $this->rollbackAndMsg('订单操作失败,错误码:10011', $id);
				!$mchModel->where('id', $orderInfo['buy_id'])->update(['transact' => Db::raw('transact+1')]) && $this->rollbackAndMsg('订单操作失败,错误码:10012', $id);
				// 买家代理
				if ($buyerParentMoney > 0 && $buyerAgentExist) {
					$rsArr = agentReward($buyer['pid'], $orderInfo['buy_id'], $buyerParentMoney, 4, $id);//4
					!$rsArr[0] && $this->rollbackAndMsg('订单操作失败,错误码:10013', $id);
					!$rsArr[1] && $this->rollbackAndMsg('订单操作失败,错误码:10014', $id);
				}
				// 平台利润
				if ($platformMoney > 0) {
					$rsArr = agentReward(-1, 0, $platformMoney, 5, $id);//5
					!$rsArr[1] && $this->rollbackAndMsg('订单操作失败,错误码:10015', $id);
				}
				// 提交事务
				Db::commit();
				financeLog($orderInfo['buy_id'], $sum, '买入USDT_f1', 0, session('user.name'));//添加日志
				if ($sellerAwardMoney > 0) {
					financeLog($orderInfo['sell_id'], $sellerAwardMoney, '承兑商卖单奖励_f1', 0, session('user.name'));//添加日志
				}
				getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $sum, $orderInfo['deal_num']);
				//请求回调接口
				$data = ['amount' => $orderInfo['deal_num'], 'rmb' => $orderInfo['deal_amount'], 'orderid' => $orderInfo['orderid'], 'appid' => $buyer['appid'], 'status' => 1];
				askNotify($data, $orderInfo['notify_url'], $buyer['key']);
				Cache::rm($id);
				$this->success('交易成功');
			} catch (DbException $e) {
				$this->rollbackAndMsg('交易失败，参考信息：' . $e->getMessage(), $id);
			}
		}
	}

	/**
	 * 商家释放
	 */
	public function deal_merchant() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登录操作');
			$id               = input('post.id');
			$mchModel         = new MerchantModel();
			$where['id']      = $id;
			$where['sell_id'] = $this->uid;
			$orderInfo        = Db::name('order_sell')->where($where)->find();
			(empty($orderInfo)) && $this->error('订单不存在');
			($orderInfo['status'] == 5) && $this->error('订单已经被取消');
			($orderInfo['status'] == 6) && $this->error('订单申诉中，无法释放');
			($orderInfo['status'] == 0) && $this->error('此订单对方已经拍下还未付款');
			($orderInfo['status'] >= 3) && $this->error('此订单已经释放无需再次释放');
			$seller = $mchModel->getUserByParam($this->uid, 'id');
			$buyer  = $mchModel->getUserByParam($orderInfo['buy_id'], 'id');
			($seller['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) && $this->error('您的冻结不足，交易失败');
			$fee  = config('usdt_buy_trader_fee');
			$fee  = $fee ? $fee : 0;
			$sfee = $orderInfo['deal_num'] * $fee / 100;
			$mum  = $orderInfo['deal_num'] - $sfee;
			Db::startTrans();
			try {
				$rs1      = balanceChange(FALSE, $orderInfo['sell_id'], 0, 0, -$orderInfo['deal_num'], $orderInfo['fee'], BAL_SOLD, $orderInfo['id']);
				$rs2      = Db::name('order_sell')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'buyer_fee' => $sfee]);
				$rs3      = balanceChange(FALSE, $orderInfo['buy_id'], $mum, 0, 0, 0, BAL_BOUGHT, $orderInfo['id']);
				$rs4      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('transact_buy', 1);
				$total    = Db::name('order_sell')->field('sum(dktime-ctime) as total')->where('buy_id', $orderInfo['buy_id'])->where('status', 4)->select();
				$tt       = $total[0]['total'];
				$transact = Db::name('merchant')->where('id', $orderInfo['buy_id'])->value('transact_buy');
				$rs5      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->update(['averge_buy' => intval($tt / $transact)]);
				if ($rs1 && $rs2 && $rs3 && $rs4) {
					// 提交事务
					Db::commit();
					financeLog($orderInfo['buy_id'], $mum, '买入USDT_f2', 0, session('user.name'));//添加日志
					getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num'] + $orderInfo['fee'], session('user.name'));
					$this->success('交易成功');
				} else {
					// 回滚事务
					Db::rollback();
					$this->error('交易失败');
				}
			} catch (DbException $e) {
				// 回滚事务
				Db::rollback();
				$this->error('交易失败，参考信息：' . $e->getMessage());
			}
		}
	}
}

?>