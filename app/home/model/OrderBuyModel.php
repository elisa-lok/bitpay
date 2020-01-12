<?php
namespace app\home\model;
use think\Model;

class OrderBuyModel extends Model {
	protected $name = 'order_buy';

	public function getAllByWhere($where, $order) {
		return $this->where($where)->order($order)->select();
	}
}