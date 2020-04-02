<?php
use app\home\model\BankModel;
use app\home\model\MerchantModel;
use app\home\model\WxModel;
use app\home\model\YsfModel;
use app\home\model\ZfbModel;
use com\GoogleAuthenticator;
use think\Db;

// 仅仅使用做备份方法功能
class bak {
	public function Scurl($url, $data = []) {
		//使用crul模拟
		$ch = curl_init();
		//禁用https
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		//允许请求以文件流的形式返回
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch); //执行发送
		curl_close($ch);
		return $result;
	}
	public function BackArr($key) {
		$bankArr = [
			'工商银行' => 'ICBC',
			'农业银行' => 'ABC',
			'中国银行' => 'BOC',
			'建设银行' => 'CCB',
			'招商银行' => 'CMB',
			'浦发银行' => 'SPDB',
			'广发银行' => 'GDB',
			'兴业银行' => 'CIB',
			'北京银行' => 'BCCB',
			'交通银行' => 'COMM',
			'平安银行' => 'SPABANK',
			'光大银行' => 'CEB',
			'中信银行' => 'CNCB',
			'民生银行' => 'CMBC',
			'华夏银行' => 'HXB',
			'上海银行' => 'BOS',
			'邮政储蓄' => 'PSBC',
		];
		return $bankArr[$key] ?? showMsg('暂不支持该银行');
	}
	public function delBank() {
		$id = input('param.id');
		$m  = new BankModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => $this->uid]);
		return json($rs);
	}

	public function delZfb() {
		$id = input('param.id');
		$m  = new ZfbModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => $this->uid]);
		return json($rs);
	}

	public function delWx() {
		$id = input('param.id');
		$m  = new WxModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => $this->uid]);
		return json($rs);
	}

	public function delYsf() {
		$id = input('param.id');
		$m  = new YsfModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => $this->uid]);
		return json($rs);
	}

	public function doaccount() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$c_bank                     = input('post.c_bank');
			$c_bank_detail              = input('post.c_bank_detail');
			$c_bank_card                = input('post.c_bank_card');
			$c_bank_card_again          = input('post.c_bank_card_again');
			$id                         = input('post.id');
			$m                          = new BankModel();
			$param['c_bank']            = $c_bank;
			$param['c_bank_detail']     = $c_bank_detail;
			$param['c_bank_card']       = $c_bank_card;
			$param['c_bank_card_again'] = $c_bank_card_again;
			$param['merchant_id']       = $this->uid;
			$param['name']              = input('post.name');
			$param['truename']          = input('post.truename');
			$user                       = Db::name('merchant')->where('id', $this->uid)->find();
			$ga                         = explode('|', $user['ga']);
			if (isset($ga[4]) && $ga[4]) {
				$code = input('post.ga');
				!$code && $this->error('请输入谷歌验证码');
				$google = new GoogleAuthenticator();
				!$google->verifyCode($ga['0'], $code, 1) && $this->error('谷歌验证码错误！');
			}
			if ($id) {
				$param['id'] = $id;
				$rs          = $m->updateOne($param);
			} else {
				$rs = $m->insertOne($param);
			}
			($rs['code'] == 1) ? $this->success($rs['msg']) : $this->error($rs['msg']);
			// TODO ?????????? 为什么没往下写?
			$param['id']            = $this->uid;
			$param['c_bank']        = $c_bank;
			$param['c_bank_detail'] = $c_bank_detail;
			$param['c_bank_card']   = $c_bank_card;
			$param['name']          = $name = input('post.name');
			(empty($name) || !checkName($name)) && $this->error('请填写真实姓名');
			($c_bank_card_again != $c_bank_card && !empty($c_bank_card)) && $this->error('确认银行卡卡号错误！');
			(strlen($c_bank_card) < 16 || strlen($c_bank_card) > 22) && $this->error('请输入正确的银行卡号');
			(!$c_bank) && $this->error('请输入开户银行');
			(!$c_bank_detail) && $this->error('请输入开户支行');
			$param['id']            = $this->uid;
			$param['c_bank']        = $c_bank;
			$param['c_bank_detail'] = $c_bank_detail;
			$param['c_bank_card']   = $c_bank_card;
			$param['name']          = $name;
			$model                  = new MerchantModel();
			$return                 = $model->updateOne($param);
			($return['code'] == 1) ? $this->success($return['msg']) : $this->error($return['msg']);
		}
	}

	public function doalipay() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$user = Db::name('merchant')->where('id', $this->uid)->find();
			$ga   = explode('|', $user['ga']);
			if (isset($ga[4]) && $ga[4]) {
				$code = input('post.ga');
				!$code && $this->error('请输入谷歌验证码');
				$google = new GoogleAuthenticator();
				!$google->verifyCode($ga['0'], $code, 1) && $this->error('谷歌验证码错误！');
			}
			$name = input('post.name');
			(empty($name) || !checkName($name)) && $this->error('请填写真实姓名');
			$alipay_account = input('post.alipay_account');
			(!$alipay_account) && $this->error('请输入支付宝账户');
			$file = request()->file('avatar');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_alipay_img'] = $info->getSaveName();
				} else {
					$param['c_alipay_img'] = '';
					// $this->error('请上传支付宝收款码：' . $file->getError());
				}
			} else {
				$lastImg = input('post.last_alipay_img');
				if (empty($lastImg)) {
					$param['c_alipay_img'] = '';
					//$this->error('请上传支付宝收款码');
				} else {
					$param['c_alipay_img'] = $lastImg;
				}
			}
			$param['id']               = $this->uid;
			$param['c_alipay_account'] = $alipay_account;
			$param['name']             = $name;
			$model                     = new MerchantModel();
			$return                    = $model->updateOne($param);
			($return['code'] == 1) ? $this->success($return['msg']) : $this->error($return['msg']);
		}
	}

	public function dowechat() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$user = Db::name('merchant')->where('id', $this->uid)->find();
			$ga   = explode('|', $user['ga']);
			if (isset($ga[4]) && $ga[4]) {
				$code = input('post.ga');
				!$code && $this->error('请输入谷歌验证码');
				$google = new GoogleAuthenticator();
				!$google->verifyCode($ga['0'], $code, 1) && $this->error('谷歌验证码错误！');
			}
			$name = input('post.name');
			(empty($name) || !checkName($name)) && $this->error('请填写真实姓名');
			$file = request()->file('avatar2');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_wechat_img'] = $info->getSaveName();
				} else {
					$param['c_wechat_img'] = '';
					// $this->error('请上传微信收款码：' . $file->getError());
				}
			} else {
				$lastImg = input('post.last_wechat_img');
				if (empty($lastImg)) {
					$param['c_wechat_img'] = '';
					// $this->error('请上传微信收款码');
				} else {
					$param['c_wechat_img'] = $lastImg;
				}
			}
			$wxAccount = input('post.wechat_account');
			(!$wxAccount) && $this->error('请输入微信账户');
			$param['id']               = $this->uid;
			$param['c_wechat_account'] = $wxAccount;
			$param['name']             = $name;
			$model                     = new MerchantModel();
			$return                    = $model->updateOne($param);
			($return['code'] == 1) ? $this->success($return['msg']) : $this->error($return['msg']);
		}
	}

	public function doalipaynew() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$user = Db::name('merchant')->where('id', $this->uid)->find();
			$ga   = explode('|', $user['ga']);
			if (isset($ga[4]) && $ga[4]) {
				$code = input('post.ga');
				!$code && $this->error('请输入谷歌验证码');
				$google = new GoogleAuthenticator();
				!$google->verifyCode($ga['0'], $code, 1) && $this->error('谷歌验证码错误！');
			}
			$trueName       = input('post.zfbtruename');
			$name           = input('post.zfbname');
			$alipay_account = input('post.alipay_account');
			$alipay_id      = input('post.alipay_id');
			empty($trueName) && $this->error('请填写真实姓名');
			empty($name) && $this->error('请填写标识名称');
			empty($alipay_id) && $this->error('请输入支付宝ID');
			// if(!$alipay_account){
			// $this->error('请输入支付宝账户');
			// }
			$file = request()->file('avatar');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_bank_detail'] = $info->getSaveName();
				} else {
					$param['c_bank_detail'] = '';
					// $this->error('请上传支付宝收款码：' . $file->getError());
				}
			} else {
				$lastImg = input('post.last_alipay_img');
				if (empty($lastImg)) {
					$param['c_bank_detail'] = '';
					// $this->error('请上传支付宝收款码');
				} else {
					$param['c_bank_detail'] = $lastImg;
				}
			}
			$param['merchant_id'] = $this->uid;
			$param['c_bank']      = $alipay_account;
			$param['truename']    = $trueName;
			$param['name']        = $name;
			$param['alipay_id']   = trim($alipay_id);
			$model                = new ZfbModel();
			$return               = $model->insertOne($param);
			($return['code'] == 1) ? $this->success($return['msg']) : $this->error($return['msg']);
		}
	}

	public function dowechatnew() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$trueName  = input('post.wxtruename');
			$name      = input('post.wxname');
			$wxAccount = input('post.wechat_account');
			(empty($trueName)) && $this->error('请填写真实姓名');
			(empty($name)) && $this->error('请填写标识名称');
			(!$wxAccount) && $this->error('请输入微信账户');
			$file = request()->file('avatar2');
			if ($file) {
				$info                   = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				$param['c_bank_detail'] = $info ? $info->getSaveName() : '';
			} else {
				$lastImg = input('post.last_wechat_img');
				// $this->error('请上传微信收款码');
				$param['c_bank_detail'] = empty($lastImg) ? '' : $lastImg;
			}
			$param['merchant_id'] = $this->uid;
			$param['c_bank']      = $wxAccount;
			$param['truename']    = $trueName;
			$param['name']        = $name;
			$model                = new WxModel();
			$return               = $model->insertOne($param);
			($return['code'] == 1) ? $this->success($return['msg']) : $this->error($return['msg']);
		}
	}

	public function doysfnew() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$trueName = input('post.ysftruename');
			$name     = input('post.ysfname');
			(empty($trueName)) && $this->error('请填写真实姓名');
			(empty($name)) && $this->error('请填写标识名称');
			$file = request()->file('avatar2');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_bank_detail'] = $info->getSaveName();
				} else {
					$this->error('请上传已释放收款码：' . $file->getError());
				}
			} else {
				$lastImg = input('post.ysfimg');
				(empty($lastImg)) && $this->error('请上传微信收款码');
				$param['c_bank_detail'] = $lastImg;
			}
			$param['merchant_id'] = $this->uid;
			// $param['c_bank'] = $wxAccount;
			$param['truename'] = $trueName;
			$param['name']     = $name;
			$model             = new YsfModel();
			$return            = $model->insertOne($param);
			($return['code'] == 1) ? $this->success($return['msg']) : $this->error($return['msg']);
		}
	}

}