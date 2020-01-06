<?php

namespace app\admin\controller;

use app\admin\model\MerchantModel;
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
			if ($args['deal_amount'] != $orderInfo['deal_amount']) {
				$updateArr['deal_amount'] = $args['deal_amount'];
				$updateArr['deal_num']    = number_format($args['deal_amount'] / $orderInfo['deal_price'], 8, '.', '');
			}
			if ($args['timeout']) {
				//计算延长时间
				$updateArr['ltime']         = ((time() - $orderInfo['ctime']) / 60) + 61;//延长60分钟, 预留多一分钟
				$updateArr['finished_time'] = 0;
			}


			Cache::has($orderInfo['id']) && showMsg('操作频繁', 0);
			Cache::set($orderInfo['id'], TRUE, 30);

			Db::startTrans();
			$res1 = Db::name('order_buy')->where('id=' . $edit)->update($updateArr); // 更新订单
			// 判断剩余额度
			$res2 = $res3 = $res4 = $res5 = $res6 = $res7 = 1;
			// 重建订单信息
			if ($args['refactor']) {
				!in_array($orderInfo['status'], ['5', '9']) && showMsg('该状态不能重建订单', 0);
				//在余额里面进行扣钱
				$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
				$res2    = balanceChange(false, $orderInfo['sell_id'], -$orderInfo['deal_num'], $orderInfo['fee'], $orderInfo['deal_num'], $orderInfo['fee'], BAL_SYS, $orderInfo['id'], "重建订单");
				//$res2    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setDec('usdt', $realAmt);
				//$res3    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdtd', $realAmt);
			}
			// 判断完成
			if (($args['status'] == 4) && ($src_status == 0 || $src_status == 1)) {
				// 放行扣承兑商冻结和增加商户余额
				$model2 = new MerchantModel();

				$merchant    = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
				$buymerchant = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
				if ($merchant['usdtd'] < $orderInfo['deal_num']) showMsg('您的冻结不足，释放失败', 0);

				$nopay = ($orderInfo['status'] == 0) ? 1 : 0;
				$sfee  = 0;
				$mum   = $orderInfo['deal_num'] - $sfee;
				//盘口费率
				$pkfee = $buymerchant['merchant_pk_fee'];
				$pkfee = $pkfee ? $pkfee : 0;
				$pkdec = $orderInfo['deal_num'] * $pkfee / 100;
				//平台利润
				$platformGet   = config('trader_platform_get');
				$platformGet   = $platformGet ? $platformGet : 0;
				$platformMoney = $platformGet * $orderInfo['deal_num'] / 100;
				//承兑商卖单奖励
				$traderGet         = $merchant['trader_trader_get'];
				$traderGet         = $traderGet ? $traderGet : 0;
				$traderMoney       = $traderGet * $orderInfo['deal_num'] / 100;
				$traderParentMoney = $traderMParentMoney = $tpexist = $mpexist = 0;
				if ($merchant['pid']) {
					$traderP = $model2->getUserByParam($merchant['pid'], 'id');
					if ($traderP['agent_check'] == 1 && $traderP['trader_parent_get']) {
						//承兑商代理利润
						$tpexist           = 1;
						$traderParentGet   = $traderP['trader_parent_get'];
						$traderParentGet   = $traderParentGet ? $traderParentGet : 0;
						$traderParentMoney = $traderParentGet * $orderInfo['deal_num'] / 100;
					}
				}
				if ($buymerchant['pid']) {
					$buymerchantP = $model2->getUserByParam($buymerchant['pid'], 'id');
					$buymerchantP['enable_new_get'] == 0 ?
						$traderMParentGet = $buymerchantP['trader_merchant_parent_get'] :
						$traderMParentGet = $buymerchant['trader_merchant_parent_get_new'];
					if ($buymerchantP['agent_check'] == 1 && $traderMParentGet) {
						//商户代理利润
						$mpexist = 1;
						//$traderMParentGet   = $buymerchantP['trader_merchant_parent_get'];
						$traderMParentGet   = $traderMParentGet ? $traderMParentGet : 0;
						$traderMParentMoney = $traderMParentGet * $orderInfo['deal_num'] / 100;
					}
					/*
					if ($buymerchantP['agent_check'] == 1 && $buymerchantP['trader_merchant_parent_get']) {
						//商户代理利润
						$mpexist            = 1;
						$traderMParentGet   = $buymerchantP['trader_merchant_parent_get'];
						$traderMParentGet   = $traderMParentGet ? $traderMParentGet : 0;
						$traderMParentMoney = $traderMParentGet * $orderInfo['deal_num'] / 100;
					}
					*/
				}
				//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
				$moneyArr           = getMoneyByLevel($pkdec, $platformMoney, $traderParentMoney, $traderMParentMoney, $traderMoney);
				$mum                = $mum - $pkdec;
				$platformMoney      = $moneyArr[0];
				$traderParentMoney  = $moneyArr[1];
				$traderMParentMoney = $moneyArr[2];
				$traderMoney        = $moneyArr[3];
				Db::startTrans();
				try {
					$rs1 = balanceChange(TRUE, $orderInfo['sell_id'], 0, 0, -$orderInfo['deal_num'], 0, BAL_SOLD, $orderInfo['id'], "编辑修改状态->完成订单");
					//$rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num']);
					//20190830修改
					if ($nopay == 1) {
						$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $moneyArr[0]]);
					} else {
						$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0]]);
					}
					// $rs2 = Db::name('order_buy')->update(['id'=>$orderInfo['id'], 'status'=>4, 'finished_time'=>time(), 'platform_fee'=>$moneyArr[0]]);
					//$rs3      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
					$rs3      = balanceChange(false, $orderInfo['buy_id'], $mum, 0, 0, 0, BAL_BOUGHT, $orderInfo['id'], "编辑修改状态->完成订单");
					$rs4      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('transact', 1);
					$total    = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderInfo['sell_id'])->where('status', 4)->select();
					$tt       = $total[0]['total'];
					$transact = Db::name('merchant')->where('id', $orderInfo['sell_id'])->value('transact');
					$rs5      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->update(['averge' => intval($tt / $transact)]);
					//承兑商卖单奖励
					$rs6 = $rs7 = $rs8 = $rs9 = $rs10 = $rs11 = $res3 = TRUE;
					if ($traderMoney > 0) {
						$rs6 = balanceChange(false, $orderInfo['sell_id'], $traderMoney, 0, 0, 0, BAL_COMMISSION, $orderInfo['id'], "编辑修改状态->完成订单");
						//$rs6 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $traderMoney);
						$rs7 = Db::name('trader_reward')->insert(['uid' => $orderInfo['sell_id'], 'orderid' => $orderInfo['id'], 'amount' => $traderMoney, 'type' => 0, 'create_time' => time()]);
					}
					//承兑商代理利润
					if ($traderParentMoney > 0 && $tpexist) {
						$rsArr = agentReward($merchant['pid'], $orderInfo['sell_id'], $traderParentMoney, 3);//3
						$rs8   = $rsArr[0];
						$rs9   = $rsArr[1];
					}
					//商户代理利润
					if ($traderMParentMoney > 0 && $mpexist) {
						$rsArr = agentReward($buymerchant['pid'], $orderInfo['buy_id'], $traderMParentMoney, 4);//4
						$rs10  = $rsArr[0];
						$rs11  = $rsArr[1];
					}
					// 平台利润
					if ($platformMoney > 0) {
						$rsArr = agentReward(-1, 0, $platformMoney, 5);//5
						$res3  = $rsArr[1];
					}
					if ($rs1 && $rs2 && $rs3 && $rs4 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11 && $res3) {
						// 提交事务
						Db::commit();
						financelog($orderInfo['buy_id'], $mum, '买入USDT_f1', 0, session('username'));//添加日志
						if ($traderMoney > 0) {
							financelog($orderInfo['sell_id'], $traderMoney, '承兑商卖单奖励_f1', 0, session('username'));//添加日志
						}

						getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num']);
						//请求回调接口
						$data['amount']  = $orderInfo['deal_num'];
						$data['rmb']     = $orderInfo['deal_amount'];
						$data['orderid'] = $orderInfo['orderid'];
						$data['appid']   = $buymerchant['appid'];
						$data['status']  = 1;
						askNotify($data, $orderInfo['notify_url'], $buymerchant['key']);
						showMsg('操作成功', 1);
					} else {
						// 回滚事务
						Db::rollback();
						showMsg('释放失败,请稍后再试!', 0);
					}
				} catch (\think\Exception\DbException $e) {
					// 回滚事务
					Db::rollback();
					showMsg('释放失败，参考信息：' . $e->getMessage(), 0);
				}
			}
			// 判断取消
			if ($args['status'] == 5 && ($src_status == 0 || $src_status == 1)) {
				// 减少挂卖单交易量和增加挂卖单剩余量
				$real_number = $orderInfo['deal_num'] + $orderInfo['fee'];
				$res4        = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->setDec('trading_volume', $real_number);
				$res5        = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->setInc('remain_amount', $real_number);
				// 获取挂单
				$sell = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->find();
				if ($sell['state'] == 2) {
					// 如果挂单已下架 回滚余额
					$res6 = balanceChange(TRUE, $orderInfo['sell_id'], $orderInfo['deal_num'], $orderInfo['fee'], -$orderInfo['deal_num'], $orderInfo['fee'], BAL_CANCEL, $orderInfo['id'], "编辑修改状态->取消订单");

					//$res6 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $real_number);
					//$res7 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $real_number);
				}
				// 减少用户冻结余额和增加用户余额
				//$res2    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdt', $orderInfo['deal_num']);
				//$res3    = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdtd', $orderInfo['deal_num']);
			}
			// 判断付款&放行
			if ($src_status == 5 && ($args['status'] == 0 || $args['status'] == 1)) {
				// 关闭状态下改待付款或待放行
				$real_number = $orderInfo['deal_num'] + $orderInfo['fee'];
				// 如果挂单已下架则不允许修改
				$sellInfo = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->find();
				($sellInfo['state'] == 2) && showMsg('原挂单已下架，不允许修改订单状态。', 0);
				// 剩余数量不足
				$sellInfo['remain_amount'] < $real_number && showMsg('挂单剩余数量不足，无法修改。', 0);
				/*
				if ($sellinfo['remain_amount'] < $real_number) {
					// 如果账户余额不足则不操作
					$sellUser = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
					$sellUser['usdt'] < $real_number && showMsg('账户可用余额不足，无法修改。', 0);
					// 如果余额足够 则直接扣余额
					!balanceChange(TRUE, $orderInfo['sell_id'], -$real_number, 0, $real_number, 0, BAL_SYS, $orderInfo['id'], "挂单剩余数量不足") && showMsg('扣除余额失败', 0);
				}
				*/
				// 需重新扣除剩余数量
				$res5 = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->setDec('remain_amount', $real_number);
				$res4 = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid']])->setInc('trading_volume', $real_number);
			}
			if ($res1 && $res2 && $res3 && $res4 && $res5 && $res6 && $res7) {
				Db::commit();
				Cache::rm($orderInfo['id']);
				showMsg('操作成功', 1);
			} else {
				Db::rollback();
				Cache::rm($orderInfo['id']);
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
			$args = input('post.');

			$src_status = $orderInfo['status'];

			($orderInfo['status'] == $args['status']) && ($orderInfo['deal_amount'] == $args['deal_amount']) && showMsg('操作成功'); //状态未改变
			$updateArr = ['status' => $args['status']];

			if ($args['deal_amount'] != $orderInfo['deal_amount']) {
				$updateArr['deal_amount'] = $args['deal_amount'];
				$updateArr['deal_num']    = number_format($args['deal_amount'] / $orderInfo['deal_price'], 8, '.', '');
			}
			if ($args['timeout']) {
				//计算延长时间
				$updateArr['ltime']         = ((time() - $orderInfo['ctime']) / 60) + 61;//延长60分钟, 预留多一分钟
				$updateArr['finished_time'] = 0;
			}
			Db::startTrans();
			$res1 = Db::name('order_sell')->where('id=' . $edit)->update($updateArr); // 更新订单
			$res2 = $res3 = TRUE;
			// 判断完成
			if (($args['status'] == 4) && ($src_status == 0 || $src_status == 1)) {
				$merchant = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
				if ($merchant['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) {
					$this->error('您的冻结不足，释放失败');
				}
				$fee  = config('usdt_buy_trader_fee');
				$fee  = $fee ? $fee : 0;
				$sfee = $orderInfo['deal_num'] * $fee / 100;
				$mum  = $orderInfo['deal_num'] - $sfee;
				try {
					$real_number = number_format($orderInfo['deal_num'] + $orderInfo['fee'], 8, '.', '');
					// 减少商户冻结
					$rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $real_number);
					// 更新完成时间
					$rs2 = Db::name('order_sell')->update(['id' => $orderInfo['id'], 'finished_time' => time(), 'buyer_fee' => $sfee]);
					// 增加买家余额
					$rs3 = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
					// 增加买家求购成功次数
					$rs4 = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('transact_buy', 1);
					// 查询平均打款时间
					$total    = Db::name('order_sell')->field('sum(dktime-ctime) as total')->where('buy_id', $orderInfo['buy_id'])->where('status', 4)->select();
					$tt       = $total[0]['total'];
					$transact = Db::name('merchant')->where('id', $orderInfo['buy_id'])->value('transact_buy');
					$rs5      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->update(['averge_buy' => intval($tt / $transact)]);
					if ($res1 && $rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
						// 提交事务
						Db::commit();
						financelog($orderInfo['buy_id'], $mum, '买入USDT_f2', 0, session('username'));//添加日志
						financelog($orderInfo['sell_id'], $real_number, '卖出USDT_f2', 1, session('username'));//添加日志
						getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $real_number, session('username'));
						showMsg('操作成功', 1);
					} else {
						// 回滚事务
						Db::rollback();
						showMsg('操作失败', 0);
					}

				} catch (\think\Exception\DbException $e) {
					// 回滚事务
					Db::rollback();
					showMsg('操作失败,参考信息:' . $e->getMessage(), 0);
				}
			} else {
				// 判断取消
				if (($args['status'] == 5) && ($src_status == 0 || $src_status == 1)) {
					$real_number = number_format($orderInfo['deal_num'] + $orderInfo['fee'], 8, '.', '');
					$res2        = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $real_number);
					$res3        = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $real_number);
				}
				if ($res1 && $res2 && $res3) {
					Db::commit();
					showMsg('操作成功', 1);
				} else {
					Db::rollback();
					showMsg('操作失败', 0);
				}
			}
		}
		$this->assign('data', $orderInfo);
		return $this->fetch('order/sell_edit');
	}
}