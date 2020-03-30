<?php
namespace app\cli\controller;
use think\db;

// 统计
class Stat extends Base {
	// 利润
	public function profit() {
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

}

?>