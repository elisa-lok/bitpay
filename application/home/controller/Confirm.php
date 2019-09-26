<?php

namespace app\home\controller;

use app\home\model\MerchantModel;
use app\home\model\OrderModel;
use think\Db;

class Confirm extends Base {
	/**
	 * 短信处理页面
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function Confirm() {
		$data = input('get.');
		$data['tx'] == '' && exit("404");
		$where['order_no'] = $data['tx'];
		$order_info        = Db::name("order_buy")->where($where)->find();
		$sellWhere['id']   = $order_info['sell_id'];
		$sell_info         = Db::name("ad_sell")->where($sellWhere)->find();
		$this->assign('order_info', $order_info);
		$this->assign('sell_info', $sell_info);
		return $this->fetch();
	}

	/**
	 * 订单确认
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function OrderConfirmation() {
		if (request()->isPost()) {
			$param             = input('post.');
			$where['order_no'] = $param['no'];
			$order             = Db::name('order_buy')->where($where)->find();
			$sellWhere         = $order['sell_id'];
			$sell_user         = Db::name('merchant')->where($sellWhere)->find();
			empty($param['trade_code']) && $this->error("请输入备注码");
			empty($param['secure_psw']) && $this->error("请输入交易密码");
			if ($order['check_code'] != $param['trade_code']) {
				$this->error('你的备注码不正确！！！ 请核对...');
			}
			if ($sell_user['paypassword'] != md5($param['secure_psw'])) {
				$this->error('你的交易密码不正确！！！ 请核对...');
			}
			if ($order['check_code'] == $param['trade_code'] && $sell_user['paypassword'] == md5($param['secure_psw'])) {
				$id               = $order['id'];
				$model            = new OrderModel();
				$model2           = new MerchantModel();
				$where['id']      = $id;
				$where['sell_id'] = $order['sell_id'];
				$orderinfo        = $model->getOne($where);
				empty($orderinfo) && $this->error('订单不存在');
				($orderinfo['status'] == 5) && $this->error('订单已经被取消');
				($orderinfo['status'] == 6) && $this->error('订单申诉中，无法确认');
				//20190830修改,不打款,也可以确认
				$nopay = ($orderinfo['status'] == 0) ? 1 : 0;//20190830修改
				// $this->error('此订单对方已经拍下还未付款');
				($orderinfo['status'] >= 3) && $this->error('此订单已经确认无需再次确认');
				$merchant    = $model2->getUserByParam($order['sell_id'], 'id');
				$buymerchant = $model2->getUserByParam($orderinfo['buy_id'], 'id');
				($merchant['usdtd'] < $orderinfo['deal_num']) && $this->error('您的冻结不足，确认失败');

				$sfee = 0;
				$mum  = $orderinfo['deal_num'] - $sfee;
				//盘口费率
				$pkfee = $buymerchant['merchant_pk_fee'];
				$pkfee = $pkfee ? $pkfee : 0;
				$pkdec = $orderinfo['deal_num'] * $pkfee / 100;
				//平台利润
				$platformGet   = config('trader_platform_get');
				$platformGet   = $platformGet ? $platformGet : 0;
				$platformMoney = $platformGet * $orderinfo['deal_num'] / 100;
				//承兑商卖单奖励
				$traderGet         = $merchant['trader_trader_get'];
				$traderGet         = $traderGet ? $traderGet : 0;
				$traderMoney       = $traderGet * $orderinfo['deal_num'] / 100;
				$traderParentMoney = $traderMParentMoney = $tpexist = $mpexist = 0;
				if ($merchant['pid']) {
					$traderP = $model2->getUserByParam($merchant['pid'], 'id');
					if ($traderP['agent_check'] == 1 && $traderP['trader_parent_get']) {
						//承兑商代理利润
						$tpexist           = 1;
						$traderParentGet   = $traderP['trader_parent_get'];
						$traderParentGet   = $traderParentGet ? $traderParentGet : 0;
						$traderParentMoney = $traderParentGet * $orderinfo['deal_num'] / 100;
					}
				}
				if ($buymerchant['pid']) {
					$buymerchantP = $model2->getUserByParam($buymerchant['pid'], 'id');
					if ($buymerchantP['agent_check'] == 1 && $buymerchantP['trader_merchant_parent_get']) {
						//商户代理利润
						$mpexist            = 1;
						$traderMParentGet   = $buymerchantP['trader_merchant_parent_get'];
						$traderMParentGet   = $traderMParentGet ? $traderMParentGet : 0;
						$traderMParentMoney = $traderMParentGet * $orderinfo['deal_num'] / 100;
					}
				}
				//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
				$moneyArr           = getMoneyByLevel($pkdec, $platformMoney, $traderParentMoney, $traderMParentMoney, $traderMoney);
				$mum                = $mum - $pkdec;
				$traderParentMoney  = $moneyArr[1];
				$traderMParentMoney = $moneyArr[2];
				$traderMoney        = $moneyArr[3];
				Db::startTrans();
				try {
					$rs1 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setDec('usdtd', $orderinfo['deal_num']);
					//20190830修改
					if ($nopay == 1) {
						$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $moneyArr[0]]);
					} else {
						$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0]]);
					}
					// $rs2 = Db::table('think_order_buy')->update(['id'=>$orderinfo['id'], 'status'=>4, 'finished_time'=>time(), 'platform_fee'=>$moneyArr[0]]);
					$rs3      = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->setInc('usdt', $mum);
					$rs4      = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('transact', 1);
					$total    = Db::table('think_order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderinfo['sell_id'])->where('status', 4)->select();
					$tt       = $total[0]['total'];
					$transact = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->value('transact');
					$rs5      = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->update(['averge' => intval($tt / $transact)]);
					//承兑商卖单奖励
					$rs6 = $rs7 = $rs8 = $rs9 = $rs10 = $rs11 = TRUE;
					if ($traderMoney > 0) {
						$rs6 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('usdt', $traderMoney);
						$rs7 = Db::table('think_trader_reward')->insert(['uid' => $orderinfo['sell_id'], 'orderid' => $orderinfo['id'], 'amount' => $traderMoney, 'type' => 0, 'create_time' => time()]);
					}
					//承兑商代理利润
					if ($traderParentMoney > 0 && $tpexist) {
						$rsarr = agentReward($merchant['pid'], $orderinfo['sell_id'], $traderParentMoney, 3);//3
						$rs8   = $rsarr[0];
						$rs9   = $rsarr[1];
					}
					//商户代理利润
					if ($traderMParentMoney > 0 && $mpexist) {
						$rsarr = agentReward($buymerchant['pid'], $orderinfo['buy_id'], $traderMParentMoney, 4);//4
						$rs10  = $rsarr[0];
						$rs11  = $rsarr[1];
					}
					if ($rs1 && $rs2 && $rs3 && $rs4 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11) {
						// 提交事务
						Db::commit();
						financelog($orderinfo['buy_id'], $mum, '买入USDT_f1', 0, $order['buy_username']);//添加日志
						if ($traderMoney > 0) {
							financelog($orderinfo['sell_id'], $traderMoney, '承兑商卖单奖励_f1', 0, $order['buy_username']);//添加日志
						}

						getStatisticsOfOrder($orderinfo['buy_id'], $orderinfo['sell_id'], $mum, $orderinfo['deal_num']);
						//请求回调接口
						$data['amount']  = $orderinfo['deal_num'];
						$data['rmb']     = $orderinfo['deal_amount'];
						$data['orderid'] = $orderinfo['orderid'];
						$data['appid']   = $buymerchant['appid'];
						$data['status']  = 1;
						askNotify($data, $orderinfo['notify_url'], $buymerchant['key']);
						$this->success('确认成功');
					} else {
						// 回滚事务
						Db::rollback();
						$this->error('确认失败,请稍后再试!');
					}
				} catch (\think\Exception\DbException $e) {
					// 回滚事务
					Db::rollback();
					$this->error('确认失败，参考信息：' . $e->getMessage());
				}
			}
		}

	}

	/**
	 * 订单申诉
	 * @throws \think\Exception
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 * @throws \think\exception\PDOException
	 */
	public function disputed() {
		$param             = input('post.');
		$where['order_no'] = $param['no'];
		$order             = Db::name('order_buy')->where($where)->find();
		$sellWhere         = $order['sell_id'];
		$sell_user         = Db::name('merchant')->where($sellWhere)->find();
		empty($param['amt']) && $this->error("请输入你的实际收到的金额");
		empty($param['trade_code']) && $this->error("请输入备注码");
		empty($param['secure_psw']) && $this->error("请输入交易密码");
		$data['actual_amount'] = $param['amt'];
		if ($order['check_code'] != $param['trade_code']) {
			$this->error('你的备注码不正确！！！ 请核对...');
		}
		if ($sell_user['paypassword'] != md5($param['secure_psw'])) {
			$this->error('你的交易密码不正确！！！ 请核对...');
		}
		if ($order['check_code'] == $param['trade_code'] && $sell_user['paypassword'] == md5($param['secure_psw'])) {
			Db::name('order_buy')->where('order_no', $param['no'])->update($data);
			$this->success("已提交");
		}
	}
}
