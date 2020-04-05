<?php
namespace app\api\controller;
use app\home\model\MerchantModel;
use think\Db;
use think\exception\DbException;

class Notify {
	// {"title":"支付宝支付","time":"2020-01-19 11:55:52","money":"0.01","encrypt":"0","content":"成功收款0.01元。享免费提现等更多专属服务，点击查看","deviceid":"ffffffff-8e14-9cf5-0000-0000625e6f1c","type":"alipay"}
	// 自动收款
	public function send($id) {
		$this->logNotify();
		!preg_match('/^\w{6,10}$/', $id) && $this->rollbackShowMsg('参数错误', $id);
		$args = json_decode(file_get_contents('php://input'), TRUE);
		($args['title'] != '支付宝支付' || $args['type'] != 'alipay' || strlen($args['deviceid'] < 20)) && die;
		!$args && $this->rollbackShowMsg('参数错误', $id);
		$seller = Db::name('merchant')->where('vcode', $id)->find();
		!$seller && $this->rollbackShowMsg('用户不存在', $id);
		$orderInfo = Db::name('order_buy')->where('sell_id', $seller['id'])->whereIn('status', '0,1')->find();
		!$orderInfo && $this->rollbackShowMsg('订单不存在');
		empty($args['money']) && $this->rollbackShowMsg('金额错误');
		($args['money'] != $orderInfo['deal_amount']) && $this->rollbackShowMsg('金额不匹配', $id);
		// 开始处理订单
		$mchModel = new MerchantModel();
		empty($orderInfo) && $this->rollbackShowMsg('订单不存在', $id);
		($orderInfo['status'] == 5) && $this->rollbackShowMsg('订单已经被取消', $id);
		($orderInfo['status'] == 6) && $this->rollbackShowMsg('订单申诉中，无法释放', $id);
		//20190830修改,不打款,也可以确认
		$unpay = ($orderInfo['status'] == 0) ? 1 : 0;
		($orderInfo['status'] >= 3) && $this->rollbackShowMsg('此订单已处理,无需再次处理', $id);
		$buyer = $mchModel->where('id', $orderInfo['buy_id'])->find();
		($seller['usdtd'] < $orderInfo['deal_num']) && $this->rollbackShowMsg('您的冻结不足，交易失败', $id);
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
			$sellerFirstMoney < 0 && $this->rollbackShowMsg('配置异常, 请联系管理员,错误码:231', $id);
			// 二级
			if ($firstParent && $firstParent['pid'] > 0) {
				$secondParent      = $mchModel->where('id', $firstParent['pid'])->find();
				$sellerSecondMoney = ($secondParent && $secondParent['agent_check'] == 1) ? ($orderInfo['deal_num'] * ($secondParent['trader_parent_get'] - $firstParent['trader_parent_get']) / 100) : $sellerSecondMoney;
				$sellerSecondMoney < 0 && $this->rollbackShowMsg('配置异常, 请联系管理员,错误码:232', $id);
			}
			// 三级
			if ($secondParent && $secondParent['pid']) {
				$thirdParent      = $mchModel->where('id', $secondParent['pid'])->find();
				$sellerThirdMoney = ($thirdParent && $thirdParent['agent_check'] == 1) ? ($orderInfo['deal_num'] * ($thirdParent['trader_parent_get'] - $secondParent['trader_parent_get']) / 100) : $sellerThirdMoney;
				$sellerThirdMoney < 0 && $this->rollbackShowMsg('配置异常, 请联系管理员,错误码:233', $id);
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
				($sellerParentMoney + $buyerParentMoney + $sellerAwardMoney > $totalFee) && $this->rollbackShowMsg('配置异常, 请联系管理员,错误码:234', $id);
			}
		}
		//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
		$sum = $orderInfo['deal_num'] - $totalFee;
		//实际到账金额
		$platformMoney = $totalFee - $sellerParentMoney - $buyerParentMoney - $sellerAwardMoney;
		$platformMoney < 0 && $this->rollbackShowMsg('配置异常, 请联系管理员,错误码:235', $id);
		Db::startTrans();
		try {
			// 订单更新
			$updateCondition = $unpay == 1 ? ['status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $platformMoney] : ['status' => 4, 'finished_time' => time(), 'platform_fee' => $platformMoney];
			!(Db::name('order_buy')->where('id', $orderInfo['id'])->update($updateCondition)) && $this->rollbackShowMsg('订单更新失败', $id, TRUE);
			// 卖家减去冻结
			!balanceChange(FALSE, $orderInfo['sell_id'], 0, 0, -$orderInfo['deal_num'], 0, BAL_SOLD, $orderInfo['id'],'自动完成1') && $this->rollbackShowMsg('冻结余额不足,错误码:10001', $id, TRUE);
			// 卖家增加数据, 增加平均交易时间:秒average , 以及交易单数transact
			!$mchModel->where('id', $orderInfo['sell_id'])->update(['transact' => Db::raw('transact+1')]) && $this->rollbackShowMsg('卖家信息操作失败,错误码:10002', $id, TRUE);
			// 卖家卖单奖励
			if ($sellerAwardMoney > 0) {
				!balanceChange(FALSE, $orderInfo['sell_id'], $sellerAwardMoney, 0, 0, 0, BAL_COMMISSION, $orderInfo['id'],'自动完成2') && $this->rollbackShowMsg('订单操作失败,,错误码:10003', $id, TRUE);
				!(Db::name('trader_reward')->insert(['uid' => $orderInfo['sell_id'], 'orderid' => $orderInfo['id'], 'amount' => $sellerAwardMoney, 'type' => 0, 'create_time' => time()])) && $this->rollbackShowMsg('订单操作失败,错误码:10004', $id, TRUE);
			}
			//卖家代理利润
			if ($sellerFirstMoney > 0) {
				// 一级
				$rsArr = agentReward($firstParent['id'], $orderInfo['sell_id'], $sellerFirstMoney, 3, $id, TRUE);
				!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10005', $id, TRUE);
				!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10006', $id, TRUE);
				// 二级
				if ($sellerSecondMoney > 0) {
					$rsArr = agentReward($secondParent['id'], $orderInfo['sell_id'], $sellerSecondMoney, 3, $id, TRUE);
					!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10007', $id, TRUE);
					!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10008', $id, TRUE);
				}
				// 三级
				if ($sellerThirdMoney > 0) {
					$rsArr = agentReward($thirdParent['id'], $orderInfo['sell_id'], $sellerThirdMoney, 3, $id, TRUE);
					!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10009', $id, TRUE);
					!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10010', $id, TRUE);
				}
			}
			// 买家加币
			!balanceChange(FALSE, $orderInfo['buy_id'], $sum, 0, 0, 0, BAL_BOUGHT, $orderInfo['id'],'自动完成3') && $this->rollbackShowMsg('订单操作失败,错误码:10011', $id, TRUE);
			!$mchModel->where('id', $orderInfo['buy_id'])->update(['transact' => Db::raw('transact+1')]) && $this->rollbackShowMsg('订单操作失败,错误码:10012', $id, TRUE);
			// 买家代理
			if ($buyerParentMoney > 0 && $buyerAgentExist) {
				$rsArr = agentReward($buyer['pid'], $orderInfo['buy_id'], $buyerParentMoney, 4, $id, TRUE);//4
				!$rsArr[0] && $this->rollbackShowMsg('订单操作失败,错误码:10013', $id, TRUE);
				!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10014', $id, TRUE);
			}
			// 平台利润
			if ($platformMoney > 0) {
				$rsArr = agentReward(-1, 0, $platformMoney, 5, $id, TRUE);//5
				!$rsArr[1] && $this->rollbackShowMsg('订单操作失败,错误码:10015', $id, TRUE);
			}
			// 提交事务
			Db::commit();
			financeLog($orderInfo['buy_id'], $sum, '买入USDT_f1', 0, session('user.name')); //添加日志
			if ($sellerAwardMoney > 0) {
				financeLog($orderInfo['sell_id'], $sellerAwardMoney, '承兑商卖单奖励_f1', 0, session('user.name'));//添加日志
			}
			getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $sum, $orderInfo['deal_num']);
			//请求回调接口
			$data = ['amount' => $orderInfo['deal_num'], 'rmb' => $orderInfo['deal_amount'], 'orderid' => $orderInfo['orderid'], 'appid' => $buyer['appid'], 'status' => 1];
			askNotify($data, $orderInfo['notify_url'], $buyer['key']);
			showMsg('交易成功', 1);
		} catch (DbException $e) {
			$this->rollbackShowMsg('交易失败，参考信息：' . $e->getMessage(), $id, TRUE);
		}
	}

	private function logNotify() {
		$rawContent = file_get_contents('php://input');
		$rawArr     = json_decode($rawContent, TRUE);
		$now        = date('Y-m-d H:i:s');
		$notifyPath = RUNTIME_PATH . 'data/get_notify_' . date('ymd') . '.log';
		// 异步回调通知形式
		if ($_POST) {
			$str = "【" . $now . "】 【FORM】\n" . json_encode($_POST, 320);
			file_put_contents($notifyPath, $str, FILE_APPEND);
		} elseif ($rawArr) {
			$str = "【" . $now . "】 【JSON】: $rawContent" . PHP_EOL;
			file_put_contents($notifyPath, $str, FILE_APPEND);
		}
	}

	private function rollbackShowMsg($msg, $vcode, $isRollback = FALSE) {
		$isRollback && Db::rollback();
		$notifyPath = RUNTIME_PATH . 'data/get_notify_err_' . date('ymd') . '.log';
		$str        = '【' . date('Y-m-d H:i:s') . "】$vcode : $msg" . PHP_EOL;
		file_put_contents($notifyPath, $str, FILE_APPEND);
		die;
		showMsg($msg, 0);
	}
}