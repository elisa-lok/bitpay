<?php
namespace app\admin\model;
use think\Model;

class AddresssModel extends Model {
	protected $name       = 'address';
	protected $createTime = 'addtime';

	public function getAddressByWhere($map, $Nowpage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.uid', 'LEFT'],
		];
		return $this->field('a.*, b.mobile')->alias('a')->join($join)->where($map)->page($Nowpage, $limits)->order('a.id desc')->select();
	}

	public function getAllCount($map) {
		return $this->where($map)->count();
	}

	public function getAddressByUsername($username) {
		return $this->where('username', $username)->column('address');
	}
}

?>