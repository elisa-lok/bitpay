<?php
namespace app\home\controller;
use app\home\model\BaseModel;
use think\Cache;
use think\Controller;
use think\Db;

class Base extends Controller {
	public $uid;

	public function _initialize() {
		header('Content-Type: text/html; charset=utf-8');
		!sql_check() && $this->error('您提交的参数非法,系统已记录您的本次操作！');
		$model = new BaseModel();
		//强制设置谷歌验证码
		$c         = request()->controller();
		$a         = request()->action();
		$this->uid = session('uid');
		if (!$this->uid && !in_array($c, ['Login', 'Index', 'Auto'])) {
			header('Location: /home/login/loginout');
			die;
		}
		isset($_GET['invite']) && session('invite', $_GET['invite']);
		$nowIp = getIp();
		// 如果IP发生变化, 需要重新记录
		if ($this->uid && (session('ip') != $nowIp)) {
			// 记录新ip
			session('ip', $nowIp);
			$user = session('user');
			// 记录新的IP地址
			Db::name('login_log')->insert(['merchant_id' => $this->uid, 'login_time' => time(), 'update_time' => time(), 'online' => 1, 'ip' => $nowIp]);
			writeMerchantLog($this->uid, session('username', $user['name']), '用户【' . $user['name'] . '】登录成功', 1);
		}
		$this->setConfig();
		$this->assign('cate', $model->getAllCate());
		$this->assign('url', '/' . $c . '/' . $a);
	}

	protected function setConfig() {
		$config = cache('db_config_data');
		if (!$config) {
			$config = api('Config/lists');
			cache('db_config_data', $config);
		}
		config($config);
	}

	protected function rollbackAndMsg($msg, $cacheKey = NULL) {
		$cacheKey && Cache::rm($cacheKey);
		Db::rollback();
		$this->error($msg);
		die;
	}

	protected function rollbackAndShow($msg, $code) {
		Db::rollback();
		showMsg($msg, $code);
	}

	protected function getAdvNo() {
		$txId = (int)(microtime(TRUE) * 1000);
		$ad   = Db::name('ad_sell')->where(['ad_no' => $txId])->find();
		return empty($ad) ? $txId : $this->getAdvNo();
	}
}