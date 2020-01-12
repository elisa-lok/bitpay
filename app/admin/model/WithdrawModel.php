<?php
namespace app\admin\model;
use think\Model;

class WithdrawModel extends Model {
	protected $name       = 'merchant_user_withdraw';
	protected $createTime = 'addtime';
	protected $updateTime = 'endtime';

	public function getWithdrawByWhere($map, $nowPage, $limits) {
		$join = [
			['__MERCHANT__ b', 'b.id=a.merchant_id', 'LEFT'],
		];
		return $this->field('a.*, b.name')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
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