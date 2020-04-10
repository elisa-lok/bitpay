<?php
namespace app\admin\controller;
use think\Db;

class Stat extends Base {
	public function index() {
		// 之前30天的数据
		$res = Db::name('stat_daily')->where('id != '.date('Ymd'))->order('id DESC')->limit(30)->select();
		// 今天币量合并之前数据
		$res = array_merge([$this->doStat(time())], $res);
		$this->assign('data', $res); //当前页
		return $this->fetch();
	}

	public function todo(){
		$d = (int)input('d');
		$d = $d < 1 ? 30 : $d;
		$time = time()-86400;
		$db = Db::name('stat_daily');
		for($i=$d;$i>0;$i--){
			$date = $this->doStat($time);
			$isExist = $db->where('id', $date['id'])->find();
			$isExist ? $db->update($date) : $db->insert($date);
			$time -= 86400;
		}
		die('All Done!!');
	}

	// 使用一个时间戳来确定日期
	private function doStat(int $time) {
		//平台当天的利润，商户当天的交易量，代理的当天利润，承兑商当天购买的币量，商户当天下发的币量
		$timeStart = strtotime(date('Y-m-d', $time));
		$timeEnd   = $timeStart + 86399;
		$timeSql      = ['between', "$timeStart,$timeEnd"];
		$sqlMap    = ['status' => 1, 'addtime' => $timeSql];
		$fee1      = getTotalInfo($sqlMap, 'merchant_user_recharge', 'fee');
		$fee2      = getTotalInfo($sqlMap, 'merchant_user_withdraw', 'fee');
		$fee3      = getTotalInfo($sqlMap, 'merchant_withdraw', 'fee');
		$sqlMap = ['status' => 4, 'ctime' => $timeSql];
		$fee4      = getTotalInfo($sqlMap, 'order_buy', 'platform_fee');
		$fee5      = getTotalInfo($sqlMap, 'order_sell', 'fee');
		$fee6      = getTotalInfo($sqlMap, 'order_sell', 'buyer_fee');
		// 平台利润
		$profit = [];
		$profit ['id'] = date('Ymd', $time);
		$profit ['ctime'] = time();
		$profit['platform_profit'] = $fee1 + $fee2 + $fee3 + $fee4 + $fee5 + $fee6;
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
		$profit['user_buy_vol'] = $sellModel->where($sqlMap)->whereNotIn('sell_id', array_merge($specialId, $userIds))->sum('deal_num');
		// 系统卖出币量
		$profit['sys_sell_vol'] = $sellModel->where($sqlMap)->whereIn('sell_id', $specialId)->sum('deal_num');
		//代理商奖励总和
		$profit['agent_reward'] = getTotalInfo(['create_time' => $timeSql], 'agent_reward', 'amount');
		//承兑商奖励总和
		$profit['user_reward'] = getTotalInfo(['create_time' => $timeSql], 'trader_reward', 'amount');
		// 成功率统计
		$profit['succ_rate'] = 0;
		$statusArr = Db::name('order_buy')->where(['ctime' =>$timeSql])->field('status,COUNT(1) AS `num`')->group('status')->select();
		if($statusArr){
			// // 0,1,4,5,6,9
			$statusArr = array_column($statusArr,'num', 'status');
			$profit['succ_rate'] = isset($statusArr[4]) ? ((float)(100*(float)$statusArr[4] / (float)array_sum($statusArr))) : 0 ;
		}
		return $profit;
	}
}