<?php
namespace app\admin\controller;
use think\Db;

class Stat extends Base {
	public function daily() {
		//平台当天的利润，商户当天的交易量，代理的当天利润，承兑商当天购买的币量，商户当天下发的币量
		//
	}

	// 使用一个时间戳来确定日期
	public function doStat() {
		$time      = time();
		$timeStart = strtotime(date('Y-m-d', $time));
		$timeEnd   = $timeStart + 86399;
		$time      = ['between', "$timeStart,$timeEnd"];
		$sqlMap    = ['status' => 1, 'addtime' => $time];
		$fee1      = getTotalInfo($sqlMap, 'merchant_user_recharge', 'fee');
		$fee2      = getTotalInfo($sqlMap, 'merchant_user_withdraw', 'fee');
		$fee3      = getTotalInfo($sqlMap, 'merchant_withdraw', 'fee');
		$sqlMap = ['status' => 4, 'ctime' => $time];
		$fee4      = getTotalInfo($sqlMap, 'order_buy', 'platform_fee');
		$fee5      = getTotalInfo($sqlMap, 'order_sell', 'fee');
		$fee6      = getTotalInfo($sqlMap, 'order_sell', 'buyer_fee');
		// 平台利润
		$profit = [];
		$profit['platform'] = $fee1 + $fee2 + $fee3 + $fee4 + $fee5 + $fee6;
		// 特殊id: 2,16,7,4,39,29,475,10,11,12
		$specialId = ['2', '16', '7', '4', '39', '29', '475', '10', '11', '12'];
		$userModel = Db::name('merchant');
		$mchIds    = $userModel->where('reg_type', 1)->whereNotIn('id', $specialId)->field('id')->select();
		$mchIds    = array_column($mchIds, 'id'); // 有效商户
		$userIds   = $userModel->where('reg_type', 2)->whereNotIn('id', $specialId)->field('id')->select();
		$userIds   = array_column($userIds, 'id'); // 有效承兑商
		$buyModel  = Db::name('order_buy');
		$sellModel = Db::name('order_sell');
		// TODO 商户当天购买币量
		$profit['mch_buy_vol'] = $buyModel->where($sqlMap)->whereIn('buy_id', $mchIds)->sum('deal_num');
		// TODO 商户当天下发币量
		$profit['mch_sell_vol'] = $sellModel->where($sqlMap)->whereIn('sell_id', $mchIds)->sum('deal_num');
		// TODO 承兑当天购买币量
		$profit['user_buy_vol'] = $sellModel->where($sqlMap)->whereNotIn('sell_id', array_merge($specialId, $mchIds))->sum('deal_num');
		// 系统卖出币量
		$profit['sys_sell_vol'] = $sellModel->where($sqlMap)->whereNotIn('sell_id', $specialId)->sum('deal_num');
		//代理商奖励总和
		$profit['agent_reward'] = getTotalInfo(['create_time' => $time], 'agent_reward', 'amount');
		//承兑商奖励总和
		$profit['user_reward'] = getTotalInfo(['create_time' => $time], 'trader_reward', 'amount');
	}
}