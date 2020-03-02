<?php
namespace app\admin\model;
use think\Model;

class MessageModel extends Model {
	protected $name       = 'question';
	protected $createTime = 'addtime';

	public function getMessageByWhere($map, $nowPage, $limits) {
		$join = [['__MERCHANT__ b', 'b.id=a.merchant_id', 'LEFT'],];
		return $this->field('a.*, b.mobile')->alias('a')->join($join)->where($map)->page($nowPage, $limits)->order('a.id desc')->select();
	}

	public function getAllCount($map) {
		return $this->where($map)->count();
	}

	public function getOneMessage($id) {
		return $this->where('id', $id)->find();
	}

	public function editMessage($param) {
		try {
			$result = $this->allowField(TRUE)->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '回复成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 删除问题
	 * @param $id
	 */
	public function delMessage($id) {
		try {
			$where['id'] = ['in', $id];
			$this->where($where)->delete();
			return ['code' => 1, 'data' => '', 'msg' => '删除成功'];
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}

?>