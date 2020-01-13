<?php
namespace app\admin\model;
use think\db;
use think\Model;

class MerchantModel extends Model {
	protected $name       = 'merchant';
	protected $createTime = 'addtime';

	public function getMerchantByWhere($map, $nowPage, $limits) {
		return $this->where($map)->page($nowPage, $limits)->order('id DESC')->select();
	}

	public function getUserByParam($param, $field) {
		return $this->where($field, $param)->find();
	}

	public function getAllCount($map) {
		return $this->where($map)->count();
	}

	public function getOneByWhere($param, $field) {
		return $this->where($field, $param)->find();
	}

	public function editMerchant($param) {
		try {
			$result = $this->allowField(TRUE)->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】编辑用户:' . $param['id'] . '失败', 0);
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】编辑用户:' . $param['id'] . '成功', 1);
				return ['code' => 1, 'data' => '', 'msg' => '操作成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	public function delMerchant($id) {
		try {
			$this->where('id', $id)->delete();
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除用户:' . $id . '失败', 0);
			return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
		} catch (PDOException $e) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除用户:' . $id . '失败', 0);
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	public function getAllCountUsdt($map) {
		return Db::name('coin_log')->where($map)->count();
	}

	public function getUsdtByWhere($map, $nowPage, $limits) {
		$join = [
			['__ADMIN__ b', 'b.id=a.admin_id', 'LEFT'],
		];
		return Db::name('coin_log')->field('a.*, b.username')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountAgent($map) {
		return Db::name('agent_reward')->alias('a')->where($map)->count();
	}

	public function getAllCountTrader($map) {
		return Db::name('trader_reward')->alias('a')->where($map)->count();
	}

	public function getRewardByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.uid', 'LEFT'],
			['__MERCHANT__ c', 'c.id=a.duid', 'LEFT'],
		];
		return Db::name('agent_reward')->field('a.*, b.name, c.name as downname')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getTraderRewardByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.uid', 'LEFT'],
			['__ORDER_BUY__ c', 'c.id=a.orderid', 'LEFT'],
		];
		return Db::name('trader_reward')->field('a.*, b.name, c.order_no, c.deal_amount')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountTr($map) {
		return Db::name('merchant_recharge')->alias('a')->where($map)->count();
	}

	public function getTraderRechargeByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.merchant_id', 'LEFT'],
		];
		return Db::name('merchant_recharge')->field('a.*, b.name')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountAd($map) {
		return Db::name('ad_sell')->alias('a')->where($map)->count();
	}

	public function getAdByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.userid', 'LEFT'],
		];
		return Db::name('ad_sell')->field('a.*, b.name')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountAdBuy($map) {
		return Db::name('ad_buy')->alias('a')->where($map)->count();
	}

	public function getAdBuyByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.userid', 'LEFT'],
		];
		return Db::name('ad_buy')->field('a.*, b.name')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountOrder($map) {
		return Db::name('order_buy')->alias('a')->where($map)->count();
	}

	public function getOrderByWhere($map, $nowPage, $limits) {
		$join = [['__MERCHANT__ b', 'b.id=a.buy_id', 'LEFT'], ['__MERCHANT__ c', 'c.id=a.sell_id', 'LEFT'], ['__AD_SELL__ d', 'd.id=a.sell_sid', 'LEFT'],];
		return Db::name('order_buy')->field('a.*, b.name, c.name as trader, d.ad_no')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountOrderBuy($map) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.buy_id', 'LEFT'],
			['__MERCHANT__ c', 'c.id=a.sell_id', 'LEFT'],
			['__AD_BUY__ d', 'd.id=a.buy_bid', 'LEFT'],
		];
		return Db::name('order_sell')->alias('a')->join($join)->where($map)->count();
	}

	public function getOrderBuyByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.buy_id', 'LEFT'],
			['__MERCHANT__ c', 'c.id=a.sell_id', 'LEFT'],
			['__AD_BUY__ d', 'd.id=a.buy_bid', 'LEFT'],
		];
		return Db::name('order_sell')->field('a.*, b.name, c.name as trader, d.ad_no')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCountStatistics() {
		return Db::name('statistics')->count();
	}

	public function getStatistics($map, $nowPage, $limits) {
		return Db::name('statistics')->where($map)->page($nowPage, $limits)->order('id DESC')->select();
	}

	public function getMerchantStatistics($map, $nowPage, $limits, $order = ['id' => 'desc']) {
		// $join = [
		// 	// ['__MERCHANT_RECHARGE__ b', 'b.merchant_id=a.id', 'LEFT']
		// 	['__MERCHANT_WITHDRAW__ b', 'b.merchant_id=a.id', 'LEFT']
		// ];
		return $this->field('id, reg_type, name, mobile, usdt+usdtd as usdtt, order_sell_usdt_amount')->where($map)->page($nowPage, $limits)->order($order)->select();
		//所属部门，名称，手机号，usdt活动数量，usdt冻结数量，总数量，充值数量，提币数量，在售挂单数，挂买数，出售成功次数，求购成功次数，总出售usdt数量，总求购usdt数量
		//return $this->field('id, reg_type, name, mobile, usdt, usdtd, usdt+usdtd as usdtt, recharge_amount, withdraw_amount, ad_on_sell, ad_on_buy, order_sell_success_num, order_buy_success_num, order_sell_usdt_amount, order_buy_usdt_amount')
		//	->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order($order)->select();
	}

	public function recharge() {
		return $this->hasMany(ChongBiModel::class, 'merchant_id', 'id');
	}

	public function orderSell() {
		return $this->hasMany(\app\home\model\OrderBuyModel::class, 'buy_id', 'id');
	}
}

?>