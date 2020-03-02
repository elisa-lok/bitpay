<?php
namespace app\admin\model;
use think\Model;

class ConfigModel extends Model {
	protected $name = 'config';

	//保存信息
	public function SaveConfig($map, $value) {
		try {
			$result = $this->allowField(TRUE)->where($map)->setField('value', $value);
			var_dump($result, $map, $value);
			return !$result ? ['code' => -1, 'msg' => $this->getError()] : ['code' => 1, 'msg' => '保存成功'];
		} catch (PDOException $e) {
			return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
		}
	}
}