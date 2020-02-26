<?php
namespace app\admin\controller;
use app\admin\model\ConfigModel;
use think\Db;

class Config extends Base {
	/**
	 * 获取配置参数
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function index() {
		$list   = Db::name('config')->select();
		$config = [];
		foreach ($list as $k => $v) {
			$config[trim($v['name'])] = $v['value'];
		}
		$this->assign('config', $config);
		return $this->fetch();
	}

	/**
	 * 批量保存配置
	 * @param $config
	 */
	public function save($config) {
		$cfgModel = new ConfigModel();
		if ($config && is_array($config)) {
			foreach ($config as $name => $value) {
				$cfgModel->where(['name' => $name])->find() ? $cfgModel->allowField(TRUE)->where(['name' => $name])->setField('value', $value) : $cfgModel->insert(['name' => $name, 'value' => $value]);
			}
		}
		writelog($this->uid, $this->username, '用户【' . $this->username . '】更改设置:成功', 1);
		cache('db_config_data', NULL);
		$this->success('保存成功！');
	}
}
