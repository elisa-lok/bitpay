<?php
namespace app\admin\controller;
use app\admin\model\Node;
use com\Auth;
use think\Cache;
use think\Controller;
use think\Db;

class Base extends Controller {
	public $uid;
	public $username;

	public function _initialize() {
		if (!session('adminuid') || !session('username')) {
			$this->redirect('login/index');
		}
		$this->uid      = session('adminuid');
		$this->username = session('username');
		$auth           = new Auth();
		$module         = strtolower(request()->module());
		$controller     = strtolower(request()->controller());
		$action         = strtolower(request()->action());
		$url            = $module . "/" . $controller . "/" . $action;
		//跳过检测以及主页权限
		if ($this->uid != 1) {
			if (!in_array($url, ['admin/index/index', 'admin/index/indexpage', 'admin/upload/upload', 'admin/index/uploadface', 'admin/merchant/index', 'admin/merchant/orderlistbuy'])) {
				// $this->error('抱歉，您没有操作权限','admin/index/index');
				(!$auth->check($url, $this->uid)) && die('抱歉，您没有操作权限!');
			}
		}
		$node = new Node();
		$this->assign([
			'username' => $this->username,
			'portrait' => session('portrait'),
			'rolename' => session('rolename'),
			'menu'     => $node->getMenu(session('rule'))
		]);
		$this->setConfig();
		if (config('web_site_close') == 0 && $this->uid != 1) {
			$this->error('站点已经关闭，请稍后访问~');
		}
		if (config('admin_allow_ip') && $this->uid != 1) {
				(in_array(request()->ip(), explode('#', config('admin_allow_ip'))))&&$this->error('403:禁止访问');
		}
	}

	private function setConfig() {
		$config = cache('db_config_data');
		if (!$config) {
			$config = api('Config/lists');
			cache('db_config_data', $config);
		}
		config($config);
	}

	protected function rollbackAndMsg($msg, $cacheKey) {
		$cacheKey && Cache::rm($cacheKey);
		Db::rollback();
		$this->error($msg);
		die;
	}

	protected function rollbackShowMsg($msg, $cacheKey) {
		$cacheKey && Cache::rm($cacheKey);
		Db::rollback();
		showMsg($msg, 0);
		die;
	}
}
