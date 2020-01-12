<?php
namespace app\home\model;
use app\admin\model\ChongBiModel;
use PDOException;
use think\Db;
use think\Model;
use think\request;

class MerchantModel extends Model {
	protected $name = 'merchant';

	public function getOneByParam($param, $field) {
		$find = $this->where($field, $param)->find();
		if (!empty($find)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	public function getUserByParam($param, $field) {
		return $this->where($field, $param)->find();
	}

	public function insertOne($param) {
		try {
			$result = $this->allowField(TRUE)->save($param);
			if (FALSE === $result) {
				return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '恭喜你，注册成功'];
			}
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	/**
	 * [updateOne 编辑用户]
	 * @author [Max] [1004991278@qq.com]
	 */
	public function updateOne($param) {
		try {
			$result = $this->allowField(TRUE)->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '信息修改成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	public function login($username, $password) {
		$find = $this->where('mobile|name|nickname', $username)->find();
		if (empty($find)) {
			return ['code' => -1, 'msg' => '用户不存在'];
		}
		if ($find['reg_check'] == 0) {
			return ['code' => -1, 'msg' => '用户待审核'];
		}
		if ($find['reg_check'] == 2) {
			return ['code' => -1, 'msg' => '用户未审核通过'];
		}
		if ($find['password'] != md5($password)) {
			return ['code' => -1, 'msg' => '登陆密码错误'];
		}
		return ['code' => 1, 'msg' => '恭喜你，登陆成功', 'data' => $find];
	}

	public function getMerchant($where, $order) {
		return $this->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getApiLog($where, $order) {
		$join = [
			['__MERCHANT__ b', 'a.duid=b.id', 'LEFT'],
		];
		return Db::name('merchant_apilog')->alias('a')->field('a.*, b.name')->join($join)->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getAgentReward($where, $order) {
		$join = [
			['__MERCHANT__ b', 'a.duid=b.id', 'LEFT'],
		];
		return Db::name('agent_reward')->alias('a')->field('a.*, b.name')->join($join)->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getTraderReward($where, $order) {
		$join = [
			['__ORDER_BUY__ b', 'a.orderid=b.id', 'LEFT'],
		];
		return Db::name('trader_reward')->alias('a')->field('a.*, b.order_no, b.deal_amount')->join($join)->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getLoginLog($where, $order) {
		return Db::name('merchant_log')->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getMerchantStatistics($map, $order = ['id' => 'desc']) {
		return $this->field('id, reg_type, name, mobile, usdt+usdtd as usdtt, order_sell_usdt_amount')->where($map)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function recharge() {
		return $this->hasMany(ChongBiModel::class, 'merchant_id', 'id');
	}

	public function orderSell() {
		return $this->hasMany(OrderBuyModel::class, 'buy_id', 'id');
	}
}

?>