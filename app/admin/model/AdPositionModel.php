<?php
namespace app\admin\model;
use think\Model;

class AdPositionModel extends Model {
	protected $name = 'ad_position';
	// 开启自动写入时间戳
	protected $autoWriteTimestamp = TRUE;

	/**
	 * [getAll 根据条件获取全部数据]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function getAll($nowpage, $limits) {
		return $this->page($nowpage, $limits)->order('id asc')->select();
	}

	/**
	 * 插入信息
	 * @param $param
	 */
	public function insertAdPosition($param) {
		try {
			$result = $this->validate('AdPositionValidate')->allowField(TRUE)->save($param);
			if (FALSE === $result) {
				return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '添加挂单位成功'];
			}
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 编辑信息
	 * @param $param
	 */
	public function editAdPosition($param) {
		try {
			$result = $this->validate('AdPositionValidate')->allowField(TRUE)->save($param, ['id' => $param['id']]);
			if (FALSE === $result) {
				return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'data' => '', 'msg' => '编辑挂单位成功'];
			}
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}

	/**
	 * 根据id获取一条信息
	 * @param $id
	 */
	public function getOne($id) {
		return $this->where('id', $id)->find();
	}

	/**
	 * [getAll 获取全部挂单位]
	 * @author [] [864491238@qq.com]
	 */
	public function getAllPosition() {
		return $this->order('id asc')->select();
	}

	/**
	 * 删除信息
	 * @param $id
	 */
	public function delAdPosition($id) {
		try {
			$this->where('id', $id)->delete();
			return ['code' => 1, 'data' => '', 'msg' => '删除挂单位成功'];
		} catch (PDOException $e) {
			return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}