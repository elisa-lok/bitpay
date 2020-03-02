<?php
namespace app\admin\model;
use think\Model;

class AddressModel extends Model {
	protected $name       = 'merchant_user_address';
	protected $createTime = 'addtime';

	public function getAddressByWhere($map, $nowPage, $limits) {
		$join = [['__MERCHANT__ b', 'b.id=a.merchant_id', 'LEFT'],];
		return $this->field('a.*, b.mobile')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCount($map) {
		return $this->where($map)->count();
	}

	public function getAddressByUsername($username) {
		return $this->where('username', $username)->column('address');
	}
}

?>