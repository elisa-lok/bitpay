<?php
namespace app\cli\controller;
use app\common\model\Usdt;
use think\Db;
use think\Exception;

class Blockchain extends Base {
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
										$rs1 = balanceChange(FALSE, $userAdd['uid'], $num - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
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
										$rs1 = balanceChange(FALSE, $userAdd['uid'], $num - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充币');
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
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
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
							$rs1 = balanceChange(FALSE, $v['merchant_id'], $v2['amount'] - $sfee, 0, 0, 0, BAL_RECHARGE, '', '钱包充值');
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
}