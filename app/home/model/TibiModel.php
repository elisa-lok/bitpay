<?php
namespace app\home\model;
use think\db;
use think\Exception\DbException;
use think\Model;
use think\request;

class TibiModel extends Model {
	protected $name = 'merchant_withdraw';

	public function getWithdraw($where, $order) {
		return $this->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getAllByWhere($where, $order) {
		return $this->where($where)->order($order)->select();
	}

	public function cancel($id) {
		$find = $this->where('id', $id)->find();
		if ($find['status'] != 0) {
			return ['code' => 0, 'data' => '', 'msg' => '撤销失败：订单状态错误'];
		}
		Db::startTrans();
		try {
			$rs1 = Db::name('merchant')->where('id', $find['merchant_id'])->setInc('usdt', $find['num']); //0
			$rs3 = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);//0
			$rs2 = $this->where('id', $id)->update(['status' => 3]);
			if ($rs1 && $rs2 && $rs3) {
				// 提交事务
				Db::commit();
				return ['code' => 1, 'data' => '', 'msg' => '撤销成功'];
			} else {
				// 回滚事务
				Db::rollback();
				return ['code' => 0, 'data' => '', 'msg' => '撤销失败'];
			}
		} catch (DbException $e) {
			// 回滚事务
			Db::rollback();
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}

?>