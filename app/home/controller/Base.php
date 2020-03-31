<?php
namespace app\home\controller;
use app\home\model\BaseModel;
use app\home\model\LogModel;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;

class Base extends Controller {
	public $uid;

	public function _initialize() {
		header('Content-Type: text/html; charset=utf-8');
		!sql_check() && $this->error('您提交的参数非法,系统已记录您的本次操作！');
		$model = new BaseModel();
		$cate  = $model->getAllCate();
		//强制设置谷歌验证码
		$request = Request::instance();
		$action  = $request->action();
		if (session('uid')) {
			$this->uid = session('uid');
			//$user      = Db::name('merchant')->where('id', session('uid'))->find();
			//if(!in_array($action, array('loginout', 'merchantset', 'login','jiancega','register')) && empty($user['ga'])){
			//header('Location: /merchant/merchantSet');die;
			// }
		}
		isset($_GET['invite']) && session('invite', $_GET['invite']);
		$controller = $request->controller();
		if ($controller != 'Login' && session('uid') && !in_array($action, ['pay', 'pay_a'])) {
			$logId = session('logid');
			if (!$logId) {
				header('Location: /home/login/loginout');
				die;
			}
			$m           = new LogModel();
			$where['id'] = $logId;
			$log         = $m->getLog($where);
			if (!empty($log)) {
				if ((time() - $log['update_time']) >= 1800) {
					$m->updateOne(['id' => $logId, 'update_time' => time(), 'online' => 0]);
					header('Location: /home/login/loginout');
					die;
				} else {
					$m->updateOne(['id' => $logId, 'update_time' => time()]);
				}
			} else {
				header('Location: /home/login/loginout');
				die;
			}
		}
		$this->setConfig();
		$this->assign('cate', $cate);
		$c = request()->controller();
		$a = request()->action();
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

	protected function rollbackAndShow($msg, $code){
		Db::rollback();
		showMsg($msg, $code);
	}



	public function getAdvNo(){
		$txId  = (int)(microtime(true) * 1000);
		$ad = Db::name('ad_sell')->where(['ad_no' => $txId])->find();
		return empty($ad) ? $txId : $this->getAdvNo();
	}

/*	protected function getAdvNo() {
		$code = '';
		for ($i = 1; $i <= 5; $i++) {
			$code .= chr(rand(97, 122));
		}
		$adv_no  = $code . time();
		$advsell = Db::name('ad_sell')->where(['ad_no' => $adv_no])->find();
		return empty($advsell) ? $adv_no : $this->getAdvNo();
	}*/
}