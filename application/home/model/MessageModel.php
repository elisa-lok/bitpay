<?php
namespace app\home\model;
use think\Model;

class MessageModel extends Model {
	protected $name = 'question';

	//protected $createTime = 'addtime';
	public function getQuestion($where, $order) {
		return $this->where($where)->order($order)->select();
	}

	/**
	 * 插入信息
	 * @param $param
	 */
	public function insertQuestion($param) {
		try {
			$result = $this->save($param);
			if (FALSE === $result) {
				return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '添加成功'];
			}
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}

?>