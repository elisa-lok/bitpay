<?php
namespace app\home\controller;
use app\common\model\Usdt;
use think\Cache;
use think\db;
use think\Exception;

// (PHP_SAPI != 'cli') && die('error');
class Auto extends Base {
	/**
	 * 定时刷新该方法
	 * 定时获取充值记录，并给对应商户增加usdt
	 */
	public function autoErc($block = '') {//ERC代币检测到账,1分钟检测一次
		// $accounts = Db::name('address')->where(array('status'=>1,'type'=>'eth'))->field('uid,address')->select();
		$time     = time();
		$confirms = config('usdt_confirms');     //确认次数
		$feeMy    = config('agent_recharge_fee');//充值手续费
		empty($confirms) && die('请设置确认数');
		$wei      = 1e6;
		$addr     = config('usdtaddr');//usdt合约地址
		$getBlock = 'http://api.etherscan.io/api?module=proxy&action=eth_blockNumber&apikey=YourApiKeyToken';
		$blockNum = file_get_contents($getBlock);
		$blockNum = json_decode($blockNum, TRUE);
		$blockNum = explode('0x', $blockNum['result'])[1];
		if ($block) {
			$lastBlock = $block;
			$fromBlock = $block;
		} else {
			$lastBlock = hexdec($blockNum);
			$fromBlock = $lastBlock - 10;
		}
		$url    = 'http://api.etherscan.io/api?module=account&action=txlist&address=' . $addr . '&startblock=' . $fromBlock . '&endblock=' . $lastBlock . '&sort=asc&apikey=ERXIYCNF6PP3ZNQAWICHJ6N5W7P212AHZI';
		$return = file_get_contents($url);
		$return = json_decode($return, TRUE);
		if ($return['message'] == 'OK') {
			foreach ($return['result'] as $v2) {
				if (strlen($v2['input']) == 138) {
					$datalist = explode('0x', $v2['input'])[1];
					$account  = substr($datalist, 32, 40);
					$account  = '0x' . $account;
					$account  = strtolower($account);
					$amount   = substr($datalist, -26);
					$num      = hexdec($amount) / $wei;
					if ($num >= 1) { //1usdt以上才入账
						$userAdd = Db::name('address')->where(['status' => 1, 'type' => 'eth', 'address' => $account])->field('uid,address')->find();
						if ($v2['txreceipt_status'] == '1' && $userAdd) {
							$fee = Db::name('merchant')->where('id', $userAdd['uid'])->value('user_recharge_fee');
							$pid = Db::name('merchant')->where('id', $userAdd['uid'])->value('pid');
							try {
								$sfee = 0;
								if ($fee) {
									$sfee = $num * $fee / 100;
								}
								if ($v2['confirmations'] < $confirms) {
									//待确认
									if ($res = Db::name('merchant_recharge')->where(['txid' => $v2['hash']])->find()) {
										Db::name('merchant_recharge')->update(['id' => $res['id'], 'confirmations' => $v2['confirmations'], 'addtime' => $time]);
									} else {
										Db::name('merchant_recharge')->insert([
											'merchant_id'   => $userAdd['uid'],
											'from_address'  => $v2['from'],
											'to_address'    => $account,
											'coinname'      => 'usdt',
											'txid'          => $v2['hash'],
											'num'           => $num,
											'mum'           => $num - $sfee,
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
								$valid    = $v2['confirmations'];
								if ($res = Db::name('merchant_recharge')->where(['txid' => $v2['hash']])->find()) {
									if ($res['status'] != 1 && $valid) {
										$rs1 = balanceChange(FALSE, $userAdd['uid'], $num - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值1');
										$rs2 = Db::name('merchant_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 1, 'confirmations' => $v2['confirmations']]);
										//增加充值数量统计，不算手续费
										Db::name('merchant')->where(['id' => $userAdd['uid']])->setInc('recharge_amount', $num);
										financeLog($userAdd['uid'], ($num - $sfee), 'USDT充值到账_1', 0, '系统自动');//添加日志
										if ($pid && $sfee && $feeMy) {
											$feeMy = round($feeMy * $sfee / 100, 8);
											$rsArr = agentReward($pid, $userAdd['uid'], $feeMy, 2);
										}
									}
									if (!$valid && $res['status'] != 5) {
										$rs1 = TRUE;
										$rs2 = Db::name('merchant_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 5, 'confirmations' => $v2['confirmations']]);
									}
								} else {
									if ($valid) {
										$rs1 = balanceChange(FALSE, $userAdd['uid'], $num - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充币2');
										$rs2 = Db::name('merchant_recharge')->insert([
											'merchant_id'   => $userAdd['uid'],
											'from_address'  => $v2['from'],
											'to_address'    => $account,
											'coinname'      => 'usdt',
											'txid'          => $v2['hash'],
											'num'           => $num,
											'mum'           => $num - $sfee,
											'addtime'       => $time,
											'status'        => 1,
											'fee'           => $sfee,
											'confirmations' => $v2['confirmations']
										]);
										//增加充值数量统计，不算手续费
										Db::name('merchant')->where(['id' => $userAdd['uid']])->setInc('recharge_amount', $num);
										// financeLog($userAdd['uid'],($num - $sfee),'USDT充值_1',0);//添加日志
										if ($pid && $sfee && $feeMy) {
											$feeMy = round($feeMy * $sfee / 100, 8);
											$rsArr = agentReward($pid, $userAdd['uid'], $feeMy, 2);
										}
									} else {
										$rs1 = TRUE;
										$rs2 = Db::name('merchant_recharge')->insert([
											'merchant_id'   => $userAdd['uid'],
											'from_address'  => $v2['from'],
											'to_address'    => $account,
											'coinname'      => 'usdt',
											'txid'          => $v2['hash'],
											'num'           => $num,
											'mum'           => $num - $sfee,
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
								file_put_contents(RUNTIME_PATH . "data/traderzrdebug.txt", " - " . $v2['hash'] . "|" . date('Y-m-d H:i:s', $time) . "|" . $e->getMessage() . " + " . PHP_EOL, FILE_APPEND);
								Db::rollback();
							}
						}
					}
				}
			}
		} else {
			echo $return['message'];
		}
	}

	public function autoEth() {                  //盘口提币
		$time     = time();
		$confirms = config('usdt_confirms');     //充值手续费
		$feeMy    = config('agent_recharge_fee');//充值手续费
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
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值3');
							$rs2 = Db::name('merchant_user_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 1, 'confirmations' => $v2['confirmations']]);
							//增加充值数量统计，不算手续费
							Db::name('merchant')->where(['id' => $v['merchant_id']])->setInc('recharge_amount', $v2['amount']);
							financeLog($v['merchant_id'], ($v2['amount'] - $sfee), '盘口提币到账_1', 0, '系统自动');//添加日志
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
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值4');
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
							financeLog($v['merchant_id'], ($v2['amount'] - $sfee), 'USDT充值_1', 0, '系统自动');//添加日志
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

	public function autoEthTrader() {       //omni入账检测
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
		//$fee = config('user_recharge_fee')->value('value');//充值手续费
		$confirms = config('usdt_confirms');  //充值手续费
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
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值5');
							$rs2 = Db::name('merchant_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 1, 'confirmations' => $v2['confirmations']]);
							//增加充值数量统计，不算手续费
							Db::name('merchant')->where(['id' => $v['merchant_id']])->setInc('recharge_amount', $v2['amount']);
							financeLog($v['merchant_id'], ($v2['amount'] - $sfee), 'USDT充值_1', 0, '系统自动');//添加日志
						}
						if (!$valid && $res['status'] != 5) {
							$rs1 = TRUE;
							$rs2 = Db::name('merchant_recharge')->update(['id' => $res['id'], 'addtime' => $time, 'status' => 5, 'confirmations' => $v2['confirmations']]);
						}
					} else {
						if ($valid) {
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值6');
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
							financeLog($v['merchant_id'], ($v2['amount'] - $sfee), 'USDT充值_1', 0, '系统自动');//添加日志
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

	public function coverusdt() {//OMNI汇总USDT
		$list = Db::name('merchant')->where(['usdtb' => ['neq', NULL]])->select();
		if ($list) {
			$model = new Usdt();
			foreach ($list as $k => $v) {
				$usdt = $model->index('getbalance', $v['usdtb'], $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
				if ($usdt['code'] == 1 && $usdt['data'] >= 50) {//只有大于50才做汇总
					$return = $model->index('cover', $v['usdtb'], $usdt['data'], $index = NULL, $count = NULL, $skip = NULL);
					if ($return['code'] == 0) {
						$msg = '汇总失败(用户:' . $v['mobile'] . ',地址:' . $v['usdtb'] . '):' . $return['msg'];
					} else {
						$msg = '汇总成功(用户:' . $v['mobile'] . ',地址:' . $v['usdtb'] . ',数量:' . $usdt['data'] . '):' . $return['data'];
					}
					file_put_contents(RUNTIME_PATH . "data/usdtcover.txt", " - " . $msg . "|" . date('Y-m-d H:i:s') . " + " . PHP_EOL, FILE_APPEND);
				}
			}
		}
	}

	// 卖单倒计时
	public function sellCountDown() {
		$list = Db::name('order_buy')->where( time() . "-ctime > ltime*60 AND status=0 ")->whereOr( 'status=1 AND dktime < '. (time() - 3600))->select();
		!$list &&  die('无数据');
		foreach ($list as $key => $vv) {
			// 锁定操作 代码执行完成前不可继续操作
			if (Cache::has($vv['id'])) continue;
			Cache::set($vv['id'], TRUE, 60);
			Db::startTrans();
			$memo = $vv['status'] == 1 ? '恶意点付款' : '自动支付超时';
			$orderInfo = Db::name('order_buy')->where(['id' => $vv['id']])->find();
			//$seller = Db::name('merchant')->where(array('id'=>$vv['sell_id']))->find();
			$buyer = Db::name('merchant')->where(['id' => $vv['buy_id']])->find();
			$rs1     = Db::name('order_buy')->update(['status' => 5, 'id' => $vv['id']]);
			$realAmt = $orderInfo['deal_num'] + $orderInfo['fee'];
			// 回滚挂单,  增加剩余量, 减少交易量
			$rs2 = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->update(['remain_amount' => Db::raw('remain_amount + ' . $realAmt), 'trading_volume' => Db::raw('trading_volume -' . $realAmt)]);
			// 获取挂单
			$sellInfo = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->find();
			$rs3      = $rs4 = 1;
			if ($sellInfo['state'] == 3) {
				// 如果挂单已下架 回滚余额
				$rs3 = balanceChange(FALSE, $orderInfo['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $orderInfo['id'], $memo);
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
			$rs2       = balanceChange(FALSE, $orderInfo['sell_id'], $realAmt, 0, -$realAmt, 0, BAL_REDEEM, $orderInfo['id'], "支付超时,自动下架");
			if ($rs1 && $rs2) {
				Db::commit();
			} else {
				Db::rollback();
				$msg = '【' . date('Y-m-d H:i:s') . '】 订单' . $vv['id'] . '回滚失败, 买家ID: ' . $vv['buy_id'] . ' , 卖家ID: ' . $vv['sell_id'] . ", 失败步骤: $rs1,$rs2";
				file_put_contents(RUNTIME_PATH . 'data/cli_buyCountDown_' . date('ymd') . '.log', $msg, FILE_APPEND);
			}
		}
	}

	public function statistics() {
		//平台利润，所有平台的手续费,用户充值手续费+用户提币手续费+商户提币手续费+场外交易商户手续(不计算为0)+场外交易平台利润+承兑商求购商户手续费+承兑商求购承兑商手续费
		$feeMap            = ['status' => 1];
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
		$adBuySum   = Db::name('ad_buy')->where($adMap)->count();
		$adBuyTotal = Db::name('ad_buy')->where($adMap)->sum('amount');
		$adBuyIds   = Db::name('ad_buy')->where($adMap)->column('id');
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
				if (!balanceChange(FALSE, $this->uid, $v['remain_amount'], 0, -$v['remain_amount'], 0, BAL_REDEEM, $orders['orderid'], '余额不足,自动下架')) continue;
				$count = Db::name('ad_sell')->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
				Db::name('merchant')->update(['id' => $this->uid, 'ad_on_sell' => $count ? $count : 0]);
				Db::commit();
			}
		}
	}
}

?>