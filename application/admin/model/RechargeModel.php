<?php
namespace app\admin\model;
use think\Model;

class RechargeModel extends Model {
	protected $name       = 'merchant_user_recharge';
	protected $createTime = 'addtime';

	public function getRechargeByWhere($map, $Nowpage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.merchant_id', 'LEFT'],
			['__MERCHANT_USER_ADDRESS__ c', 'a.to_address=c.address', 'LEFT']
		];
		return $this->field('a.*, b.name, c.username')->alias('a')->join($join)->where($map)->page($Nowpage, $limits)->order('a.id desc')->select();
	}

	public function getAllCount($map) {
		return $this->where($map)->count();
	}

	public function getOneByWhere($param, $field) {
		return $this->where($field, $param)->find();
	}

	public function editWithdraw($param) {
		try {
			$result = $this->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '操作成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}

?>