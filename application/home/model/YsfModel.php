<?php
namespace app\home\model;
use PDOException;
use think\Model;
use think\request;

class YsfModel extends Model {
	protected $name = 'merchant_ysf';
	// 开启自动写入时间戳
	protected $autoWriteTimestamp = TRUE;

	public function getBank($where, $order) {
		return $this->where($where)->order($order)->paginate(20, FALSE, ['query' => Request::instance()->param()]);
	}

	public function getOne($where) {
		return $this->where($where)->find();
	}

	public function insertOne($param) {
		try {
			$result = $this->allowField(TRUE)->save($param);
			if (FALSE === $result) {
				return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '恭喜你，新增成功'];
			}
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	public function updateOne($param) {
		try {
			$result = $this->allowField(TRUE)->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '修改成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	public function delOne($where) {
		try {
			$result = $this->where($where)->delete();
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}

?>