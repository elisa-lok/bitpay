<?php
namespace app\home\controller;
use app\home\model\LogModel;
use app\home\model\MerchantModel;
use think\db;

class Login extends Base {
	public function register() {
		if (request()->isPost()) {
			$reg_type = input('post.reg_type');
			!$reg_type && $this->error('请选择注册类型');
			$name          = input('post.username');
			$idCard        = input('post.idcard');
			$mobile        = input('post.mobile');
			$password      = input('post.password');
			$paypassword   = input('post.paypassword');
			$repassword    = input('post.password_confirmation');
			$repaypassword = input('post.paypassword_confirmation');
			$nickname      = input('post.nickname');
			//$smscode = input('post.smscode');
			if (empty($nickname) || strlen($nickname) > 20) {
				$this->error('请填写正确的昵称');
			}
			if (!checkName($name)) {
				$this->error('请输入您的正确姓名');
			}
			/*if(empty($smscode)){
				$this->error('请填写短信验证码');
			}*/
			$model  = new MerchantModel();
			$idfind = $model->getOneByParam($idCard, 'idcard');
			if (!$idfind) {
				$this->error('注册失败：身份证号码已注册');
			}
			$nameind = $model->getOneByParam($name, 'name');
			if (!$nameind) {
				$this->error('注册失败：姓名已注册');
			}
			$mobileFind = $model->getOneByParam($mobile, 'mobile');
			if (!$mobileFind) {
				$this->error('注册失败：手机号已注册');
			}
			if (empty($idCard)) {
				$this->error('请填写身份证号');
			}
			if (empty($mobile)) {
				$this->error('请填写手机号');
			}
			if (empty($password)) {
				$this->error('请填写登录密码');
			}
			if ($password != $repassword) {
				$this->error('登录确认密码错误');
			}
			if ($paypassword != $repaypassword) {
				$this->error('交易确认密码错误');
			}
			$invite_code = input('post.invite_code');
			$pid         = 0;
			if (!$invite_code) {
				$invite_code = session('invite');
			}
			if (empty($invite_code) && config('reg_invite_on') == 1) {
				$this->error('注册失败：邀请码必填');
			}
			if ($invite_code) {
				$puser = Db::name('merchant')->where('invite', $invite_code)->find();
				if ($puser) {
					$pid = $puser['id'];
				}
			}
			if ($pid == 0 && config('reg_invite_on') == 1) {
				$this->error('注册失败：邀请码填写错误');
			}
			/*$files = request()->file('image');
			$imgarr = array();
			foreach($files as $file){
				// 移动到框架应用根目录/public/uploads/ 目录下
				$info = $file->validate(['size'=>3145728,'ext'=>'jpg,png,jpeg,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads/idcard');
				if($info){
					$imgarr[] = $info->getSaveName();
				}else{
					// 上传失败获取错误信息
					$this->error($file->getError());
				}
			}
			if(count($imgarr) != 2){
				$this->error('请同时上传身份证正面和反面照片');
			}

			$idCard_zheng = $imgarr[0];
			$idCard_fan = $imgarr[1];


			if(empty($idCard_zheng) || empty($idCard_fan)){
				$this->error('请上传照片');
			}*/
			while (TRUE) {
				$appid = generate_password();
				if ($model->getOneByParam($appid, 'appid')) {
					break;
				}
			}
			// if ($smscode != session($mobile.'_code')) {
			//     $this->error('短信验证码错误!');
			// }
			$key                  = md5(time() . $mobile);
			$param['name']        = $name;
			$param['nickname']    = $nickname;
			$param['mobile']      = $mobile;
			$param['password']    = md5($password);
			$param['paypassword'] = md5($paypassword);
			$param['pid']         = $pid;
			$param['idcard']      = $idCard;
			//$param['idcard_zheng'] = $imgarr[0];
			//$param['idcard_fan'] = $imgarr[1];
			$param['appid']    = $appid;
			$param['key']      = $key;
			$param['addtime']  = time();
			$param['reg_type'] = $reg_type;
			$return            = $model->insertOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg'], '/login.html');
			} else {
				$this->error($return['msg']);
			}
		}
		if (!session('uid')) {
			return $this->fetch();
		} else {
			header('Location: /merchant/index');
			die;
		}
	}

	public function jiancega($username) {
		if (empty($username)) {
			$this->error(0);
		}
		$user = Db::name('merchant')->where('mobile|name', $username)->find();
		if (!$user) {
			$this->error(0);
		}
		if (empty($user['ga'])) {
			$this->error(0);
		}
		$arr         = explode('|', $user['ga']);
		$ga_is_login = $arr[1];
		if (!$ga_is_login) {
			$this->error(0);
		}
		$this->success(1);
	}

	public function login() {
		if (request()->isPost()) {
			$username = input('post.username');
			$password = input('post.password');
			$verify   = input('post.verify');
			$device   = input('post.device');
			if (!captcha_check($verify)) {
				$this->error('图片验证码错误');
			}
			$ga     = input('post.goole');
			$model  = new MerchantModel();
			$return = $model->login($username, $password);
			if ($return['code'] != 1) {
				$this->error($return['msg']);
			} else {
				$user = $return['data'];
				if ($user['ga']) {
					$ga_n = new \com\GoogleAuthenticator();
					$arr  = explode('|', $user['ga']);
					// 存储的信息为谷歌密钥
					$secret = $arr[0];
					// 存储的登录状态为1需要验证，0不需要验证
					$ga_is_login = $arr[1];
					// 判断是否需要验证
					if ($ga_is_login) {
						if (!$ga) {
							$this->error('请输入谷歌验证码！');
						}
						// 判断登录有无验证码
						$aa = $ga_n->verifyCode($secret, $ga, 1);
						if (!$aa) {
							$this->error('谷歌验证码错误！');
						}
					}
				}
				$model = new LogModel();
				$flag  = $model->insertOne(['merchant_id' => $return['data']['id'], 'login_time' => time(), 'update_time' => time(), 'online' => 1]);
				if ($device) {
					session('device', $device);
					Db::name('merchant')->where(['id' => $return['data']['id']])->update(['device' => $device]);
				}
				if ($flag['code'] > 0) {
					session('logid', $flag['code']);
					session('uid', $return['data']['id']);
					session('user', $return['data']);
					writeMerchantlog($return['data']['id'], $username, '用户【' . $username . '】登录成功', 1);
					$this->success($return['msg']);
				} else {
					$this->error($flag['msg']);
				}
			}
		}
		if (!session('uid')) {
			return $this->fetch();
		} else {
			header('Location: /merchant/index');
			die;
		}
	}

	public function findpwd() {
		if (request()->isPost()) {
			$username   = input('post.username');
			$verify     = input('post.verify');
			$goole      = input('post.goole');
			$password   = input('post.password');
			$repassword = input('post.repassword');
			if (empty($username)) {
				$this->error('请填写姓名或手机号');
			}
			if (empty($verify)) {
				$this->error('请填写图片验证码');
			}
			if (empty($goole)) {
				$this->error('请填写谷歌验证码');
			}
			if (!captcha_check($verify)) {
				$this->error('图片验证码错误');
			}
			if (empty($password)) {
				$this->error('请填写新密码');
			}
			if ($password != $repassword) {
				$this->error('确认密码错误');
			}
			//谷歌验证
			$merchant = Db::name('merchant')->where('name|mobile', $username)->find();
			if (empty($merchant)) {
				$this->error('姓名或手机号错误');
			}
			if (empty($merchant['ga'])) {
				$this->error('你未设置谷歌验证');
			}
			$ga_n = new \com\GoogleAuthenticator();
			$arr  = explode('|', $merchant['ga']);
			// 存储的信息为谷歌密钥
			$secret = $arr[0];
			$aa     = $ga_n->verifyCode($secret, $goole, 1);
			if (!$aa) {
				$this->error('谷歌验证码错误！');
			}
			if (Db::name('merchant')->where('id', $merchant['id'])->update(['password' => md5($password)])) {
				$this->success('修改成功', url('home/login/login'));
			} else {
				$this->error('修改失败，请稍后再试');
			}
		}
		if (!session('uid')) {
			return $this->fetch();
		} else {
			header('Location: /merchant/index');
			die;
		}
	}

	public function verify() {
		$config           = [
			// 验证码字体大小
			'fontSize' => 30,
			// 验证码位数
			'length'   => 4,
			// 关闭验证码杂点
			'useNoise' => FALSE,
			// 'bg' =>  array(100,100,100),
		];
		$captcha          = new \think\captcha\Captcha($config);
		$captcha->codeSet = '2345689';
		return $captcha->entry();
	}

	public function loginout() {
		session('logid', NULL);
		session('uid', NULL);
		session('user', NULL);
		$this->redirect('/');
	}

	public function sendPhoneCode() {
		if (request()->isPost()) {
			$mobile     = input('post.mobile');
			$model      = new MerchantModel();
			$mobileFind = $model->getOneByParam($mobile, 'mobile');
			if (!$mobileFind) {
				$this->error('注册失败：手机号已注册');
			}
			if (session($mobile . '_code')) {
				if (time() - session($mobile . '_time') < 120) { //秒,2分钟
					$this->error('获取验证码间隔小于2分钟,请稍后再试.');
				}
			}
			if (preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[\d]{9}$|^18[\d]{9}$/', $mobile)) {
				$code    = rand(100000, 999999);
				$content = '您的验证码为:' . $code . '.验证码有效时间为2分钟.如不是本人操作,请忽略.';
				// $result = 2;
				$result = sendSms($mobile, $content);
				if ($result > 0) {
					session($mobile . '_code', $code); //验证码超时
					session($mobile . '_time', time());
					$this->success('手机验证码已发送,请注意查收.');
				} else {
					$this->error('短信验证码发送失败,请稍后再试!');
				}
			} else {
				$this->error('手机号码输入错误!请检查后重新输入!');
			}
		}
	}

	public function sendPhoneCodeModify() {
		if (request()->isPost()) {
			$mobile     = input('post.mobile');
			$model      = new MerchantModel();
			$mobileFind = $model->getOneByParam($mobile, 'mobile');
			if ($mobileFind) {
				$this->error('账户不存在!');
			}
			if (session($mobile . '_mcode')) {
				if (time() - session($mobile . '_time') < 120) { //秒,2分钟
					$this->error('获取验证码间隔小于2分钟,请稍后再试.');
				}
			}
			if (preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[\d]{9}$|^18[\d]{9}$/', $mobile)) {
				$code    = rand(100000, 999999);
				$content = '您的验证码为:' . $code . '.验证码有效时间为2分钟.如不是本人操作,请忽略.';
				// $result = 2;
				$result = sendSms($mobile, $content);
				if ($result > 0) {
					session($mobile . '_mcode', $code); //验证码超时
					session($mobile . '_time', time());
					$this->success('手机验证码已发送,请注意查收.');
				} else {
					$this->error('短信验证码发送失败,请稍后再试!');
				}
			} else {
				$this->error('手机号码输入错误!请检查后重新输入!');
			}
		}
	}
}

?>