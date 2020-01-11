<?php
namespace app\home\model;
use PDOException;
use think\db;
use think\Model;

class LogModel extends Model {
	protected $name               = 'login_log';
	protected $autoWriteTimestamp = FALSE;

	public function getLog($where) {
		return $this->where($where)->find();
	}

	public function insertOne($param) {
		try {
			$result = Db::name($this->name)->insertGetId($param);
			if ($result) {
				return ['code' => $result, 'data' => '', 'msg' => ''];
			} else {
				return ['code' => -1, 'data' => '', 'msg' => '数据修改失败'];
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
				return ['code' => 1, 'data' => '', 'msg' => '修改成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}

?>