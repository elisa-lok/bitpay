<?php
namespace app\home\controller;
use app\home\model\BaseModel;
use app\home\model\LogModel;
use think\Controller;
use think\Request;

class Base extends Controller {
	public $uid;

	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
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
		if ($controller != 'Login' && session('uid') && $action != 'pay') {
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
		$this->assign('cate', $cate);
		$c = request()->controller();
		$a = request()->action();
		$this->assign('url', '/' . $c . '/' . $a);
	}

	private function setConfig() {
		$config = cache('db_config_data');
		if (!$config) {
			$config = api('Config/lists');
			cache('db_config_data', $config);
		}
		config($config);
	}
}