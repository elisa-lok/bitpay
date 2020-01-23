<?php
namespace app\home\controller;
use app\common\model\Usdt;
use think\console\Input;
use think\console\Output;
use think\db;
use think\Exception;

class Cron extends Base {
	protected function configure() {
		$this->setName('Cron')->setDescription('Here is the remark ');
	}

	protected function execute(Input $input, Output $output) {
		$output->writeln(date('Y/m/d H:i:s') . " Crontab job Start.");
		$this->usdtin();//充值
		$this->order(); //订单上下架
		$output->writeln(date('Y/m/d H:i:s') . " Crontab job End.");
	}

	protected function usdtin() {//充值
		$cnt = 0;
		while (TRUE) {
			$cnt++;
			$this->autoEth();      //充值1
			$this->autoEthTrader();//充值2
			sleep(60);
			if ($cnt > 7893748927349701) break;
		}
	}

	protected function order() {//订单上下架
		$cnt = 0;
		while (TRUE) {
			$cnt++;
			$this->sellCountDown();//充值1
			$this->buyCountDown(); //充值2
			$this->statistics();   //充值2
			sleep(30);
			if ($cnt > 7893748927349701) break;
		}
	}

	private function autoEth() {
		$time     = time();
		$confirms = Db::name('config')->where('name', 'usdt_confirms')->value('value');     //充值手续费
		$feeMy    = Db::name('config')->where('name', 'agent_recharge_fee')->value('value');//充值手续费
		if (empty($confirms)) {
			exit('请设置确认数');
		}
		$model   = new Usdt();
		$address = Db::name('merchant_user_address')->field('merchant_id, username, addtime, address')->select();
		$count   = 1000;
		$skip    = 0;
		foreach ($address as $k => $v) {
			if (empty($v['address'])) {
				continue;
			}
			$record = $model->index('transactionslist', $v['address'], $money = NULL, $index = NULL, $count, $skip);
			if (empty($record['data']) || $record['data'] == 'false') {
				continue;
			}
			$record_data = json_decode($record['data'], TRUE);
			$fee         = Db::name('merchant')->where('id', $v['merchant_id'])->value('user_recharge_fee');
			$pid         = Db::name('merchant')->where('id', $v['merchant_id'])->value('pid');
			foreach ($record_data as $k2 => $v2) {
				if ($v2['referenceaddress'] != $v['address']) {
					continue;
				}
				try {
					$sfee = 0;
					if ($fee) {
						$sfee = $v2['amount'] * $fee / 100;
					}
					if ($v2['confirmations'] < $confirms) {
						//待确认
						if ($res = Db::name('merchant_user_recharge')->where(['txid' => $v2['txid']])->find()) {
							Db::name('merchant_user_recharge')->update(['id' => $res['id'], 'confirmations' => $v2['confirmations'], 'addtime' => $time]);
						} else {
							Db::name('merchant_user_recharge')->insert([
								'merchant_id'   => $v['merchant_id'],
								'from_address'  => $v2['sendingaddress'],
								'to_address'    => $v['address'],
								'coinname'      => 'usdt',
								'txid'          => $v2['txid'],
								'num'           => $v2['amount'],
								'mum'           => $v2['amount'] - $sfee,
								'addtime'       => $time,
								'status'        => 0,
								'fee'           => $sfee,
								'confirmations' => $v2['confirmations']
							]);
						}
						continue;
					}
					Db::startTrans();
					$rsArr    = [];
					$rsArr[0] = 1;
					$rsArr[1] = 1;
					$valid    = $v2['valid'];
					if ($res = Db::name('merchant_user_recharge')->where(['txid' => $v2['txid']])->find()) {
						if ($res['status'] != 1 && $valid) {
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
							$rs2 = Db::name('merchant_user_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 1, 'confirmations' => $v2['confirmations']]);
							//增加充值数量统计，不算手续费
							Db::name('merchant')->where(['id' => $v['merchant_id']])->setInc('recharge_amount', $v2['amount']);
							if ($pid && $sfee && $feeMy) {
								$feeMy = round($feeMy * $sfee / 100, 8);
								$rsArr = agentReward($pid, $v['merchant_id'], $feeMy, 2);
							}
						}
						if (!$valid && $res['status'] != 5) {
							$rs1 = TRUE;
							$rs2 = Db::name('merchant_user_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 5, 'confirmations' => $v2['confirmations']]);
						}
					} else {
						if ($valid) {
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
							$rs2 = Db::name('merchant_user_recharge')->insert([
								'merchant_id'   => $v['merchant_id'],
								'from_address'  => $v2['sendingaddress'],
								'to_address'    => $v['address'],
								'coinname'      => 'usdt',
								'txid'          => $v2['txid'],
								'num'           => $v2['amount'],
								'mum'           => $v2['amount'] - $sfee,
								'addtime'       => $time,
								'status'        => 1,
								'fee'           => $sfee,
								'confirmations' => $v2['confirmations']
							]);
							//增加充值数量统计，不算手续费
							Db::name('merchant')->where(['id' => $v['merchant_id']])->setInc('recharge_amount', $v2['amount']);
							if ($pid && $sfee && $feeMy) {
								$feeMy = round($feeMy * $sfee / 100, 8);
								$rsArr = agentReward($pid, $v['merchant_id'], $feeMy, 2);
							}
						} else {
							$rs1 = TRUE;
							$rs2 = Db::name('merchant_user_recharge')->insert([
								'merchant_id'   => $v['merchant_id'],
								'from_address'  => $v2['sendingaddress'],
								'to_address'    => $v['address'],
								'coinname'      => 'usdt',
								'txid'          => $v2['txid'],
								'num'           => $v2['amount'],
								'mum'           => $v2['amount'] - $sfee,
								'addtime'       => $time,
								'status'        => 5,
								'fee'           => $sfee,
								'confirmations' => $v2['confirmations']
							]);
						}
					}
					if ($rs1 && $rs2) {
						Db::commit();
					} else {
						Db::rollback();
						throw new Exception('write databses fail');
					}
				} catch (Exception $e) {
					file_put_contents(RUNTIME_PATH . "data/zrdebug.txt", " - " . $v2['txid'] . "|" . date('Y-m-d H:i:s', $time) . "|" . $e->getMessage() . " + " . PHP_EOL, FILE_APPEND);
					Db::rollback();
				}
			}
		}
	}

	private function autoEthTrader() {
		/*
		 {
		 "txid" : "hash",                 // (string) the hex-encoded hash of the transaction
		 "sendingaddress" : "address",    // (string) the Bitcoin address of the sender
		 "referenceaddress" : "address",  // (string) a Bitcoin address used as reference (if any)
		 "ismine" : true|false,           // (boolean) whether the transaction involves an address in the wallet
		 "confirmations" : nnnnnnnnnn,    // (number) the number of transaction confirmations
		 "fee" : "n.nnnnnnnn",            // (string) the transaction fee in bitcoins
		 "blocktime" : nnnnnnnnnn,        // (number) the timestamp of the block that contains the transaction
		 "valid" : true|false,            // (boolean) whether the transaction is valid
		 "positioninblock" : n,           // (number) the position (index) of the transaction within the block
		 "version" : n,                   // (number) the transaction version
		 "type_int" : n,                  // (number) the transaction type as number
		 "type" : "type",                 // (string) the transaction type as string
		 [...]                            // (mixed) other transaction type specific properties
		 }
		 */
		$time = time();
		//$fee = Db::name('config')->where('name', 'user_recharge_fee')->value('value');//充值手续费
		$confirms = Db::name('config')->where('name', 'usdt_confirms')->value('value');//充值手续费
		if (empty($confirms)) {
			exit('请设置确认数');
		}
		$model   = new Usdt();
		$address = Db::name('merchant')->field('id as merchant_id, usdtb as address, trader_recharge_fee')->where('usdtb', 'not null')->where('trader_check', 1)->select();
		$count   = 1000;
		$skip    = 0;
		foreach ($address as $k => $v) {
			if (empty($v['address'])) {
				continue;
			}
			$record = $model->index('transactionslist', $v['address'], $money = NULL, $index = NULL, $count, $skip);
			if (empty($record['data']) || $record['data'] == 'false') {
				continue;
			}
			$record_data = json_decode($record['data'], TRUE);
			$fee         = $v['trader_recharge_fee'];
			foreach ($record_data as $k2 => $v2) {
				if ($v2['referenceaddress'] != $v['address']) {
					continue;
				}
				try {
					$sfee = 0;
					if ($fee) {
						$sfee = $v2['amount'] * $fee / 100;
					}
					if ($v2['confirmations'] < $confirms) {
						//待确认
						if ($res = Db::name('merchant_recharge')->where(['txid' => $v2['txid']])->find()) {
							Db::name('merchant_recharge')->update(['id' => $res['id'], 'confirmations' => $v2['confirmations'], 'addtime' => $time]);
						} else {
							Db::name('merchant_recharge')->insert([
								'merchant_id'   => $v['merchant_id'],
								'from_address'  => $v2['sendingaddress'],
								'to_address'    => $v['address'],
								'coinname'      => 'usdt',
								'txid'          => $v2['txid'],
								'num'           => $v2['amount'],
								'mum'           => $v2['amount'] - $sfee,
								'addtime'       => $time,
								'status'        => 0,
								'fee'           => $sfee,
								'confirmations' => $v2['confirmations']
							]);
						}
						continue;
					}
					Db::startTrans();
					$valid = $v2['valid'];
					if ($res = Db::name('merchant_recharge')->where(['txid' => $v2['txid']])->find()) {
						if ($res['status'] != 1 && $valid) {
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
							$rs2 = Db::name('merchant_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 1, 'confirmations' => $v2['confirmations']]);
							//增加充值数量统计，不算手续费
							Db::name('merchant')->where(['id' => $v['merchant_id']])->setInc('recharge_amount', $v2['amount']);
						}
						if (!$valid && $res['status'] != 5) {
							$rs1 = TRUE;
							$rs2 = Db::name('merchant_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 5, 'confirmations' => $v2['confirmations']]);
						}
					} else {
						if ($valid) {
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
							$rs2 = Db::name('merchant_recharge')->insert([
								'merchant_id'   => $v['merchant_id'],
								'from_address'  => $v2['sendingaddress'],
								'to_address'    => $v['address'],
								'coinname'      => 'usdt',
								'txid'          => $v2['txid'],
								'num'           => $v2['amount'],
								'mum'           => $v2['amount'] - $sfee,
								'addtime'       => $time,
								'status'        => 1,
								'fee'           => $sfee,
								'confirmations' => $v2['confirmations']
							]);
							//增加充值数量统计，不算手续费
							Db::name('merchant')->where(['id' => $v['merchant_id']])->setInc('recharge_amount', $v2['amount']);
						} else {
							$rs1 = TRUE;
							$rs2 = Db::name('merchant_recharge')->insert([
								'merchant_id'   => $v['merchant_id'],
								'from_address'  => $v2['sendingaddress'],
								'to_address'    => $v['address'],
								'coinname'      => 'usdt',
								'txid'          => $v2['txid'],
								'num'           => $v2['amount'],
								'mum'           => $v2['amount'] - $sfee,
								'addtime'       => $time,
								'status'        => 5,
								'fee'           => $sfee,
								'confirmations' => $v2['confirmations']
							]);
						}
					}
					if ($rs1 && $rs2) {
						Db::commit();
					} else {
						Db::rollback();
						throw new Exception('write databses fail');
					}
				} catch (Exception $e) {
					file_put_contents(RUNTIME_PATH . "data/traderzrdebug.txt", " - " . $v2['txid'] . "|" . date('Y-m-d H:i:s', $time) . "|" . $e->getMessage() . " + " . PHP_EOL, FILE_APPEND);
					Db::rollback();
				}
			}
		}
	}

	private function sellCountDown() {
		$list = Db::name('order_buy')->where("" . time() . "-ctime>ltime*60 and status=0 ")->select();
		if (!$list) {
			return;
		}
		foreach ($list as $key => $vv) {
			Db::startTrans();
			$orderInfo = [];
			$orderInfo = Db::name('order_buy')->where(['id' => $vv['id']])->find();
			//$seller = Db::name('merchant')->where(array('id'=>$vv['sell_id']))->find();
			$buymerchant = Db::name('merchant')->where(['id' => $vv['buy_id']])->find();
			//$table = "movesay_".'usdt'."_log";
			$rs1     = Db::name('order_buy')->update(['status' => 5, 'id' => $vv['id']]);
			$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
			$rs2     = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setDec('usdt' . 'd', $realAmt);
			$rs3     = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdt', $realAmt);
			if ($rs1 && $rs2 && $rs3) {
				Db::commit();
				//请求回调接口,失败
				$data['amount']  = $orderInfo['deal_num'];
				$data['orderid'] = $orderInfo['orderid'];
				$data['appid']   = $buymerchant['appid'];
				$data['status']  = 0;
				askNotify($data, $orderInfo['notify_url'], $buymerchant['key']);
			} else {
				Db::rollback();
				$msg = '【' . date('Y-m-d H:i:s') . '】 订单' . $vv['id'] . '回滚失败, 买家ID: ' . $vv['buy_id'] . ' , 卖家ID: ' . $vv['sell_id'] . ", 失败步骤: $rs1,$rs2,$rs3";
				file_put_contents(RUNTIME_PATH . 'data/cli_sellCountDown_' . date('ymd') . '.log', $msg, FILE_APPEND);
			}
		}
	}

	private function buyCountDown() {
		$list = Db::name('order_sell')->where("" . time() . "-ctime > ltime*60 and status=0 ")->select();
		if (!$list) {
			return;
		}
		foreach ($list as $key => $vv) {
			Db::startTrans();
			$orderInfo   = [];
			$orderInfo   = Db::name('order_sell')->where(['id' => $vv['id']])->find();
			$buymerchant = Db::name('merchant')->where(['id' => $vv['buy_id']])->find();
			$rs1     = Db::name('order_sell')->update(['status' => 5, 'id' => $vv['id']]);
			$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
			$rs2     = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setDec('usdt' . 'd', $realAmt);
			$rs3     = Db::name('merchant')->where(['id' => $orderInfo['sell_id']])->setInc('usdt', $realAmt);
			if ($rs1 && $rs2 && $rs3) {
				Db::commit();
				$data['amount']  = $orderInfo['deal_num'];
				$data['orderid'] = $orderInfo['orderid'];
				$data['appid']   = $buymerchant['appid'];
				$data['status']  = 0;
				askNotify($data, $orderInfo['notify_url'], $buymerchant['key']);
			} else {
				Db::rollback();
				$msg = '【' . date('Y-m-d H:i:s') . '】 订单' . $vv['id'] . '回滚失败, 买家ID: ' . $vv['buy_id'] . ' , 卖家ID: ' . $vv['sell_id'] . ", 失败步骤: $rs1,$rs2,$rs3";
				file_put_contents(RUNTIME_PATH . 'data/cli_buyCountDown_' . date('ymd') . '.log', $msg, FILE_APPEND);
			}
		}
	}

	private function statistics() {
		//平台利润，所有平台的手续费,用户充值手续费+用户提币手续费+商户提币手续费+场外交易商户手续(不计算为0)+场外交易平台利润+承兑商求购商户手续费+承兑商求购承兑商手续费
		$feeMap['status']  = 1;
		$fee1              = getTotalInfo($feeMap, 'merchant_user_recharge', 'fee');
		$fee2              = getTotalInfo($feeMap, 'merchant_user_withdraw', 'fee');
		$fee3              = getTotalInfo($feeMap, 'merchant_withdraw', 'fee');
		$feeMap3['status'] = 4;
		$fee4              = getTotalInfo($feeMap3, 'order_buy', 'platform_fee');
		$fee5              = getTotalInfo($feeMap3, 'order_sell', 'fee');
		$fee6              = getTotalInfo($feeMap3, 'order_sell', 'buyer_fee');
		$feePlatform       = $fee1 + $fee2 + $fee3 + $fee4 + $fee5 + $fee6;
		//代理商奖励总和
		$feeAgent = getTotalInfo(TRUE, 'agent_reward', 'amount');
		//承兑商奖励总和
		$feeTrader = getTotalInfo(TRUE, 'trader_reward', 'amount');
		//平台现存usdt总数量，所有会员类型账户的冻结加活动
		$usdtSum1 = getTotalInfo(TRUE, 'merchant', 'usdt');
		$usdtSum2 = getTotalInfo(TRUE, 'merchant', 'usdtd');
		$usdtSum  = $usdtSum1 + $usdtSum2;
		//总充值数量，用户充值+交易充值
		$recharge    = getTotalInfo($feeMap, 'merchant_user_recharge', 'num');
		$recharge2   = getTotalInfo($feeMap, 'merchant_recharge', 'num');
		$rechargeSum = $recharge + $recharge2;
		//总提币数量，
		$merchantTi  = getTotalInfo($feeMap, 'merchant_withdraw', 'mum');
		$userTi      = getTotalInfo($feeMap, 'merchant_user_withdraw', 'mum');
		$withdrawSum = $merchantTi + $userTi;
		//现存挂单出售笔数，承兑商发布出售的单子，不含下架的，不含数量低于0的
		$adMap['state']  = 1;
		$adMap['amount'] = ['gt', 0];
		$adSellSum       = Db::name('ad_sell')->where($adMap)->count();
		$adTotal         = Db::name('ad_sell')->where($adMap)->sum('amount');
		$adIds           = Db::name('ad_sell')->where($adMap)->column('id');
		$dealNums        = Db::name('order_buy')->where('sell_sid', 'in', $adIds)->where('status', 'neq', 5)->where('status', 'neq', 9)->sum('deal_num');
		//现存挂单出售总USDT，计算所有挂卖的剩余数量
		$orderSellSum = $adTotal - $dealNums;
		//求购笔数，承兑商挂买数量
		$adBuySum     = Db::name('ad_buy')->where($adMap)->count();
		$adBuyTotal   = Db::name('ad_buy')->where($adMap)->sum('amount');
		$adBuyIds     = Db::name('ad_buy')->where($adMap)->column('id');
		$dealBuyNum = Db::name('order_sell')->where('buy_bid', 'in', $adBuyIds)->where('status', 'neq', 5)->sum('deal_num');
		//求购总数量，计算所有挂买的剩余数量
		$orderBuySum = $adBuyTotal - $dealBuyNum;
		$rs          = Db::name('statistics')->insert([
			'platform_profit'      => $feePlatform,
			'agent_reward'         => $feeAgent,
			'trader_reward'        => $feeTrader,
			'platform_usdt_amount' => $usdtSum,
			'recharge_total'       => $rechargeSum,
			'withdraw_total'       => $withdrawSum,
			'ad_sell_on_total'     => $adSellSum,
			'order_sell_amount'    => $orderSellSum,
			'ad_buy_on_total'      => $adBuySum,
			'order_buy_amount'     => $orderBuySum,
			'create_time'          => time()
		]);
		return $rs ? '更新统计表think_statistics成功' : '更新统计表think_statistics失败';
	}

	private function downad() {
		$remain = Db::name('config')->where('name', 'ad_down_remain_amount')->value('value');//充值手续费
		//挂卖下架
		$sellids = Db::name('ad_sell')->field('id, amount, userid')->where('state', 1)->where('amount', 'gt', 0)->select();
		foreach ($sellids as $k => $v) {
			$total = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 7)->sum('deal_num');
			if ($v['amount'] <= $total + $remain) {
				//开始下架
				Db::name('ad_sell')->where('id', $v['id'])->setField('state', 2);
				$nowAds = Db::name('ad_sell')->where('userid', $v['userid'])->where('state', 1)->where('amount', 'gt', 0)->count();
				Db::name('merchant')->where('id', $v['userid'])->setField('ad_on_sell', $nowAds ? $nowAds : 0);
			}
		}
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
}