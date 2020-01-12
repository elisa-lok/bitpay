<?php
namespace app\admin\model;
use think\Model;

class ConfigModel extends Model {
	protected $name = 'config';

	//获取配置所有信息
	public function getAllConfig() {
		return $this->select();
	}

	//保存信息
	public function SaveConfig($map, $value) {
		try {
			$result = $this->allowField(TRUE)->where($map)->setField('value', $value);
			if (FALSE === $result) {
				return ['code' => -1, 'msg' => $this->getError()];
			} else {
				return ['code' => 1, 'msg' => '保存成功'];
			}
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}