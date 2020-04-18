<?php
namespace app\admin\controller;
use app\admin\model\MerchantModel;
use think\Cache;
use think\Db;
use think\Exception\DbException;

class Order extends Base {
	public function buy($edit) {
		$orderInfoModel = Db::name('order_buy')->where('id=' . $edit);
		$orderInfo      = $orderInfoModel->lock(TRUE)->find();
		!$orderInfo && $this->error('订单不存在');
		if (request()->isPost()) {
			$args       = input('post.');
			$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
			$orderState = $orderInfo['status'];
			($args['status'] == 4) && ($args['status'] == $orderState) && showMsg('状态一致, 无需修改', 0);
			($orderState == $args['status']) && ($orderInfo['deal_amount'] == $args['deal_amount']) && showMsg('操作成功', 1); //状态未改变
			$updateArr = ['status' => $args['status']];
			if ($args['deal_amount'] != $orderInfo['deal_amount']) {
				$updateArr['deal_amount'] = $args['deal_amount'];
				$updateArr['deal_num']    = number_format($args['deal_amount'] / $orderInfo['deal_price'], 8, '.', '');
			}
			if ($args['timeout'] || $args['status'] == 0 || $args['status'] == 1) {
				//计算延长时间
				$updateArr['ltime']         = ((time() - $orderInfo['ctime']) / 60) + 15;//延长15分钟
				$updateArr['finished_time'] = 0;
			}
			$updateArr['desc'] = '管理员操作订单';
			// 判断状态
			Cache::has($edit) && showMsg('操作频繁', 0);
			Cache::set($edit, TRUE, 5);
			Db::startTrans();
			!Db::name('order_buy')->where('id', $edit)->update($updateArr) && $this->rollbackShowMsg('订单更新失败', $edit); // 更新订单
			// 判断剩余额度
			$res2 = $res3 = $res4 = $res5 = $res6 = $res7 = 1;
			// 重建订单信息
			if ($args['refactor']) {
				!in_array($orderState, ['5', '9']) && $this->rollbackShowMsg('该状态不能重建订单', $edit);
				//在余额里面进行扣钱
				!balanceChange(FALSE, $orderInfo['sell_id'], -$orderInfo['deal_num'], $orderInfo['fee'], $orderInfo['deal_num'], $orderInfo['fee'], BAL_SYS, $edit, "重建订单") && $this->rollbackShowMsg('余额更新失败: code:220', $edit);
			}
			$adSellModel = Db::name('ad_sell');
			$msg         = '';
			// 已关闭 => 已完成
			if ($args['status'] == 4 && ($orderState == 5 || $orderState == 9)) {
				// 判断原订单是否存在
				$adSellInfo = $adSellModel->where('id', $orderInfo['sell_sid'])->find();
				if ($adSellInfo['state'] < 3) {
					// 原挂单还在, 修改原本挂单
					!$adSellModel->where('id', $orderInfo['sell_sid'])->update(['remain_amount' => Db::raw('remain_amount - ' . $realAmt), 'trading_volume' => Db::raw('trading_volume + ' . $realAmt)]) && $this->rollbackAndMsg('修改交易单失败', $edit);
				} else {
					// 原挂单已经不存在, 寻找新的订单
					$adSellInfo = $adSellModel->where(['userid' => $orderInfo['sell_id'], 'state' => ['lt', 3]])->lock(TRUE)->find();
					// 如果新订单存在, 并且金额大于订单
					if ($adSellInfo && $adSellInfo['remain_amount'] >= $realAmt) {
						!$orderInfoModel->update(['sell_sid' => $adSellInfo['id']]) && $this->rollbackAndMsg('修改成新原始挂单失败', $edit);
						!$adSellModel->where('id', $adSellInfo['id'])->update(['remain_amount' => Db::raw('remain_amount - ' . $realAmt), 'trading_volume' => Db::raw('trading_volume + ' . $realAmt)]) && $this->rollbackAndMsg('修改新挂单失败', $edit);
						$msg = '已变更原订单为: ' . $adSellInfo['id'] . ', 并操作成功';
					} else {
						// 判断用户是否足够扣
						$userInfo = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
						if ($userInfo['usdt'] < $realAmt) {
							$msg = $adSellInfo ? '卖家余额为负, 有新挂卖余量不足, 已从用户余额扣除' : '卖家余额为负, 无新挂卖, 请告知充值';
						}
						!balanceChange(FALSE, $orderInfo['sell_id'], -$orderInfo['deal_num'], 0, $orderInfo['deal_num'], 0, BAL_SYS, $edit, '系统处理关闭=>完成') && $this->rollbackShowMsg('系统处理关闭=>完成,处理失败', $edit);
					}
				}
				$orderState = 1; // 更改状态, 以便后面进行完成处理过程
			}
			// 未付或已付 => 已完成
			if (($args['status'] == 4) && ($orderState == 0 || $orderState == 1)) {
				// 放行扣承兑商冻结和增加商户余额
				$mchModel = new MerchantModel();
				$seller   = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
				$buyer    = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
				($seller['usdtd'] < $orderInfo['deal_num']) && $this->rollbackShowMsg('您的冻结不足，交易失败', $edit);
				$unpay = ($orderState == 0) ? 1 : 0;
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
					$sellerFirstMoney < 0 && $this->rollbackAndMsg('配置异常, 请联系管理员,错误码:231', $edit);
					// 二级
					if ($firstParent && $firstParent['pid'] > 0) {
						$secondParent      = $mchModel->where('id', $firstParent['pid'])->find();
						$sellerSecondMoney = ($secondParent && $secondParent['agent_check'] == 1) ? ($orderInfo['deal_num'] * ($secondParent['trader_parent_get'] - $firstParent['trader_parent_get']) / 100) : $sellerSecondMoney;
						$sellerSecondMoney < 0 && $this->rollbackAndMsg('配置异常, 请联系管理员,错误码:232', $edit);
					}
					// 三级
					if ($secondParent && $secondParent['pid']) {
						$thirdParent      = $mchModel->where('id', $secondParent['pid'])->find();
						$sellerThirdMoney = ($thirdParent && $thirdParent['agent_check'] == 1) ? ($orderInfo['deal_num'] * ($thirdParent['trader_parent_get'] - $secondParent['trader_parent_get']) / 100) : $sellerThirdMoney;
						$sellerThirdMoney < 0 && $this->rollbackAndMsg('配置异常, 请联系管理员,错误码:233', $edit);
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
						($sellerParentMoney + $buyerParentMoney + $sellerAwardMoney > $totalFee) && $this->rollbackAndMsg('配置异常, 请联系管理员,错误码:234', $edit);
					}
				}
				//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
				$sum = $orderInfo['deal_num'] - $totalFee;
				//实际到账金额
				$platformMoney = $totalFee - $sellerParentMoney - $buyerParentMoney - $sellerAwardMoney;
				$platformMoney < 0 && $this->rollbackAndMsg('配置异常, 请联系管理员,错误码:235', $edit);
				try {
					// 订单更新
					$updateCondition         = $unpay == 1 ? ['status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $platformMoney] : ['status' => 4, 'finished_time' => time(), 'platform_fee' => $platformMoney];
					$updateCondition['desc'] = '系统更新订单';
					!(Db::name('order_buy')->where('id', $edit)->update($updateCondition)) && $this->rollbackShowMsg('订单更新失败', $edit);
					// 卖家减去冻结
					!balanceChange(FALSE, $orderInfo['sell_id'], 0, 0, -$orderInfo['deal_num'], 0, BAL_SOLD, $edit, '管理员买单编辑01') && $this->rollbackShowMsg('冻结余额不足,错误码:10001', $edit);
					// 卖家增加数据, 增加平均交易时间:秒average , 以及交易单数transact
					!$mchModel->where('id', $orderInfo['sell_id'])->update(['transact' => Db::raw('transact+1')]) && $this->rollbackShowMsg('卖家信息操作失败,错误码:10002', $edit);
					// 卖家卖单奖励
					if ($sellerAwardMoney > 0) {
						!balanceChange(FALSE, $orderInfo['sell_id'], $sellerAwardMoney, 0, 0, 0, BAL_COMMISSION, $edit, '管理员买单编辑02') && $this->rollbackShowMsg('订单操作失败,,错误码:10003', $edit);
						!(Db::name('trader_reward')->insert(['uid' => $orderInfo['sell_id'], 'orderid' => $edit, 'amount' => $sellerAwardMoney, 'type' => 0, 'create_time' => time()])) && $this->rollbackShowMsg('订单操作失败,错误码:10004', $edit);
					}
					//卖家代理利润
					if ($sellerFirstMoney > 0) {
						// 一级
						$rsArr = agentReward($firstParent['id'], $orderInfo['sell_id'], $sellerFirstMoney, 3, $edit);
						!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10005', $edit);
						!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10006', $edit);
						// 二级
						if ($sellerSecondMoney > 0) {
							$rsArr = agentReward($secondParent['id'], $orderInfo['sell_id'], $sellerSecondMoney, 3, $edit);
							!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10007', $edit);
							!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10008', $edit);
						}
						// 三级
						if ($sellerThirdMoney > 0) {
							$rsArr = agentReward($thirdParent['id'], $orderInfo['sell_id'], $sellerThirdMoney, 3, $edit);
							!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10009', $edit);
							!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10010', $edit);
						}
					}
					// 买家加币
					!balanceChange(FALSE, $orderInfo['buy_id'], $sum, 0, 0, 0, BAL_BOUGHT, $edit, '管理员买单编辑03') && $this->rollbackShowMsg('订单操作失败,错误码:10011', $edit);
					!$mchModel->where('id', $orderInfo['buy_id'])->update(['transact' => Db::raw('transact+1')]) && $this->rollbackShowMsg('订单操作失败,错误码:10012', $edit);
					// 买家代理
					if ($buyerParentMoney > 0 && $buyerAgentExist) {
						$rsArr = agentReward($buyer['pid'], $orderInfo['buy_id'], $buyerParentMoney, 4, $edit);//4
						!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10013', $edit);
						!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10014', $edit);
					}
					// 平台利润
					if ($platformMoney > 0) {
						$rsArr = agentReward(-1, 0, $platformMoney, 5, $edit);//5
						!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10015', $edit);
					}
					// 提交事务
					Db::commit();
					getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $sum, $orderInfo['deal_num']);
					//请求回调接口
					$data = ['amount' => $orderInfo['deal_num'], 'rmb' => $orderInfo['deal_amount'], 'orderid' => $orderInfo['orderid'], 'appid' => $buyer['appid'], 'status' => 1];
					askNotify($data, $orderInfo['notify_url'], $buyer['key']);
					showMsg(($msg ? $msg : '操作成功'), 1);
				} catch (DbException $e) {
					$this->rollbackShowMsg('交易失败，error：' . $e->getMessage(), $edit);
				}
			}
			// 待付,已付=>关闭
			if ($args['status'] == 5 && ($orderState == 0 || $orderState == 1)) {
				// 获取挂单
				$sell = $adSellModel->where('id', $orderInfo['sell_sid'])->find();
				if ($sell['state'] == 3) {
					// 如果挂单已下架 回滚余额
					$res6 = balanceChange(TRUE, $orderInfo['sell_id'], $orderInfo['deal_num'], $orderInfo['fee'], -$orderInfo['deal_num'], $orderInfo['fee'], BAL_CANCEL, $edit, "编辑状态-取消订单");
				} else {
					// 减少挂卖单交易量和增加挂卖单剩余量
					$res4 = $adSellModel->where(['id' => $orderInfo['sell_sid']])->update(['remain_amount' => Db::raw('remain_amount + ' . $realAmt), 'trading_volume' => Db::raw('trading_volume - ' . $realAmt), 'finished_time' => time()]);
				}
			}
			// 关闭 => 待付,已付
			if (($orderState == 5 || $orderState == 9) && ($args['status'] == 0 || $args['status'] == 1)) {
				// 如果挂单已下架则不允许修改
				$sellInfo = $adSellModel->where('id', $orderInfo['sell_sid'])->find();
				($sellInfo['state'] == 3) && showMsg('原挂单已下架，不允许修改订单状态。', 0);
				// 剩余数量不足
				$sellInfo['remain_amount'] < $realAmt && showMsg('挂单剩余数量不足，无法修改。', 0);
				// 需重新扣除剩余数量
				$res4 = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->update(['remain_amount' => Db::raw('remain_amount - ' . $realAmt), 'trading_volume' => Db::raw('trading_volume + ' . $realAmt)]);
			}
			if ($res2 && $res3 && $res4 && $res5 && $res6 && $res7) {
				Db::commit();
				Cache::rm($edit);
				showMsg('操作成功', 1);
			} else {
				Db::rollback();
				Cache::rm($edit);
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
		if (request()->isPost()) {
			$args       = input('post.');
			$orderState = $orderInfo['status'];
			($orderState == $args['status']) && ($orderInfo['deal_amount'] == $args['deal_amount']) && showMsg('操作成功'); //状态未改变
			$updateArr = ['status' => $args['status']];
			if ($args['deal_amount'] != $orderInfo['deal_amount']) {
				$updateArr['deal_amount'] = $args['deal_amount'];
				$updateArr['deal_num']    = number_format($args['deal_amount'] / $orderInfo['deal_price'], 8, '.', '');
			}
			if ($args['timeout'] || $args['status'] == 0 || $args['status'] == 1) {
				//计算延长时间
				$updateArr['ltime']         = ((time() - $orderInfo['ctime']) / 60) + 15;//延长15分钟, 预留多一分钟
				$updateArr['finished_time'] = 0;
			}
			Db::startTrans();
			!Db::name('order_sell')->where('id=' . $edit)->update($updateArr) && $this->rollbackShowMsg('更新卖家信息失败');
			// 判断完成
			if (($args['status'] == 4) && ($orderState == 0 || $orderState == 1)) {
				$merchant = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
				($merchant['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) && $this->error('您的冻结不足，交易失败');
				$fee  = config('usdt_buy_trader_fee');
				$fee  = $fee ? $fee : 0;
				$sfee = $orderInfo['deal_num'] * $fee / 100;
				$mum  = $orderInfo['deal_num'] - $sfee;
				try {
					$realAmt = number_format($orderInfo['deal_num'] + $orderInfo['fee'], 8, '.', '');
					// 减少商户冻结
					!Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $realAmt) && $this->rollbackShowMsg('减少商户冻结失败');
					// 更新完成时间
					!Db::name('order_sell')->update(['id' => $edit, 'finished_time' => time(), 'buyer_fee' => $sfee]) && $this->rollbackShowMsg('更新时间失败');
					// 增加买家余额
					!balanceChange(FALSE, $orderInfo['buy_id'], $mum, 0, 0, 0, BAL_BOUGHT, $edit, '买入') && $this->rollbackShowMsg('修改买家余额失败');;
					// 增加买家求购成功次数
					!Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('transact_buy', 1) && $this->rollbackShowMsg('更新买家求购次数失败');
					// 提交事务
					Db::commit();
					getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $realAmt, $this->username);
					showMsg('操作成功', 1);
				} catch (DbException $e) {
					// 回滚事务
					Db::rollback();
					showMsg('操作失败,参考信息:' . $e->getMessage(), 0);
				}
			} else {
				// 判断取消
				if (($args['status'] == 5) && ($orderState == 0 || $orderState == 1)) {
					$realAmt = number_format($orderInfo['deal_num'] + $orderInfo['fee'], 8, '.', '');
					!balanceChange(FALSE, $orderInfo['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_SYS, $edit, '系统编辑订单') && $this->rollbackShowMsg('更新卖家金额失败');
				}
				Db::commit();
				showMsg('操作成功', 1);
			}
		}
		$this->assign('data', $orderInfo);
		return $this->fetch('order/sell_edit');
	}
}