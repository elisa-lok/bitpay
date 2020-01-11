<?php
namespace app\home\model;
use think\Model;
use think\request;

class WithdrawModel extends Model {
	protected $name = 'merchant_user_withdraw';

	public function getWithdraw($where, $order) {
		return $this->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getAllByWhere($where, $order) {
		return $this->where($where)->order($order)->select();
	}
}

?>