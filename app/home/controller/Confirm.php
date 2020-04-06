<?php
namespace app\home\controller;
use app\home\model\MerchantModel;
use app\home\model\OrderModel;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;

class Confirm extends Base {
	/**
	 * 短信处理页面
	 * @return mixed
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function Confirm() {
		$data = input('get.');
		$data['tx'] == '' && exit("404");
		$where['order_no'] = $data['tx'];
		$order_info        = Db::name('order_buy')->where($where)->find();
		$sellWhere['id']   = $order_info['sell_id'];
		$sell_info         = Db::name("ad_sell")->where($sellWhere)->find();
		$this->assign('order_info', $order_info);
		$this->assign('sell_info', $sell_info);
		return $this->fetch();
	}

	/**
	 * 订单确认
	 * @throws Exception
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 */
	public function OrderConfirmation() {
		if (request()->isPost()) {
			$param             = input('post.');
			$where['order_no'] = $param['no'];
			$order             = Db::name('order_buy')->where($where)->find();
			($order['sell_id'] != $this->uid) && $this->error('无权操作订单');
			$sellWhere = $order['sell_id'];
			$sell_user = Db::name('merchant')->where($sellWhere)->find();
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
				$orderInfo        = $model->getOne($where);
				empty($orderInfo) && $this->error('订单不存在');
				($orderInfo['status'] == 5) && $this->error('订单已经被取消');
				($orderInfo['status'] == 6) && $this->error('订单申诉中，无法确认');
				//20190830修改,不打款,也可以确认
				$nopay = ($orderInfo['status'] == 0) ? 1 : 0;//20190830修改
				// $this->error('此订单对方已经拍下还未付款');
				($orderInfo['status'] >= 3) && $this->error('此订单已经确认无需再次确认');
				$merchant    = $model2->getUserByParam($order['sell_id'], 'id');
				$buymerchant = $model2->getUserByParam($orderInfo['buy_id'], 'id');
				($merchant['usdtd'] < $orderInfo['deal_num']) && $this->error('您的冻结不足，确认失败');
				$sfee = 0;
				$mum  = $orderInfo['deal_num'] - $sfee;
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
					if ($buymerchantP['agent_check'] == 1 && $buymerchantP['trader_merchant_parent_get']) {
						//商户代理利润
						$mpexist            = 1;
						$traderMParentGet   = $buymerchantP['trader_merchant_parent_get'];
						$traderMParentGet   = $traderMParentGet ? $traderMParentGet : 0;
						$traderMParentMoney = $traderMParentGet * $orderInfo['deal_num'] / 100;
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
					$rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num']);
					//20190830修改
					if ($nopay == 1) {
						$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $moneyArr[0], 'desc' => '用户操作1']);
					} else {
						$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0], 'desc' => '用户操作2']);
					}
					// $rs2 = Db::name('order_buy')->update(['id'=>$orderInfo['id'], 'status'=>4, 'finished_time'=>time(), 'platform_fee'=>$moneyArr[0]]);
					$rs3      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
					$rs4      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('transact', 1);
					$total    = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderInfo['sell_id'])->where('status', 4)->select();
					$tt       = $total[0]['total'];
					$transact = Db::name('merchant')->where('id', $orderInfo['sell_id'])->value('transact');
					$rs5      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->update(['averge' => intval($tt / $transact)]);
					//承兑商卖单奖励
					$rs6 = $rs7 = $rs8 = $rs9 = $rs10 = $rs11 = TRUE;
					if ($traderMoney > 0) {
						$rs6 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $traderMoney);
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
					if ($rs1 && $rs2 && $rs3 && $rs4 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11) {
						// 提交事务
						Db::commit();
						financeLog($orderInfo['buy_id'], $mum, '买入USDT_f1', 0, $order['buy_username']);//添加日志
						if ($traderMoney > 0) {
							financeLog($orderInfo['sell_id'], $traderMoney, '承兑商卖单奖励_f1', 0, $order['buy_username']);//添加日志
						}
						getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num']);
						//请求回调接口
						$data['amount']  = $orderInfo['deal_num'];
						$data['rmb']     = $orderInfo['deal_amount'];
						$data['orderid'] = $orderInfo['orderid'];
						$data['appid']   = $buymerchant['appid'];
						$data['status']  = 1;
						askNotify($data, $orderInfo['notify_url'], $buymerchant['key']);
						$this->success('确认成功');
					} else {
						// 回滚事务
						Db::rollback();
						$this->error('确认失败,请稍后再试!');
					}
				} catch (DbException $e) {
					// 回滚事务
					Db::rollback();
					$this->error('确认失败，参考信息：' . $e->getMessage());
				}
			}
		}
	}

	/**
	 * 订单申诉
	 * @throws Exception
	 * @throws DataNotFoundException
	 * @throws ModelNotFoundException
	 * @throws DbException
	 * @throws PDOException
	 */
	public function disputed() {
		$param             = input('post.');
		$where['order_no'] = $param['no'];
		$order             = Db::name('order_buy')->where($where)->find();
		$sell_user         = Db::name('merchant')->where('id', $order['sell_id'])->find();
		empty($param['amt']) && $this->error("请输入你的实际收到的金额");
		empty($param['trade_code']) && $this->error("请输入备注码");
		empty($param['secure_psw']) && $this->error("请输入交易密码");
		$data['actual_amount'] = $param['amt'];
		$data['desc']          = '用户申诉';
		if ($order['check_code'] != $param['trade_code']) $this->error('你的备注码不正确！！！ 请核对...');
		if ($sell_user['paypassword'] != md5($param['secure_psw'])) $this->error('你的交易密码不正确！！！ 请核对...');
		if ($order['check_code'] == $param['trade_code'] && $sell_user['paypassword'] == md5($param['secure_psw'])) {
			Db::name('order_buy')->where('order_no', $param['no'])->update($data);
			$this->success('已提交');
		}
	}
}
