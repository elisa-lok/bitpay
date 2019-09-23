<?php

namespace BITPAY\Home\Controllers;

use BITPAY\Msg;

// 用户
class UserController extends ControllerBase {
	public function initialize() {
		parent::initialize();
	}

	public function infoAction() {
		$this->view->pick('user/info');
	}

	//修改密码
	public function passwordAction() {
		if($this->request->isPost()){

			// 校验旧密码

			// 校验新密码安全性

			// 更新密码
			$this->api(Msg::Suc);
		}
		$this->view->pick('user/password');
	}

	// 操作记录
	public function opAction() {
		if ($this->request->isPost()) {
			$page  = $this->request->getPost('page', 'int!', 0);   // 分页
			$limit = $this->request->getPost('limit', 'int!', 25); // 限制
			$data  = [];
			isset($_POST['action_type']) && $_POST['action_type'] !== '' && $data['action_type'] = $this->request->getPost('action_type', 'int');
			isset($_POST['ip_addr']) && $_POST['ip_addr'] !== '' && $data['action_type'] = $this->request->getPost('ip_addr', 'float');
			isset($_POST['ctime_bucket']) && $_POST['ctime_bucket'] !== '' && $data['ctime_bucket'] = $this->request->getPost('action_type', 'trim');
			$res = $this->s->log()->opRecord($this->uid, $page, $limit, $data);
			$this->api($res['data'], $res['count']);
		}
		//删除当前用户一个月前信息
		$this->s->log()->opRecordDel($this->uid);
		$this->view->pick('user/op');
	}

	// 代付IP设置
	public function ipAction() {

		// 修改信息
		if ($this->request->isPost()) {
			var_dump($this->request->getPost('state'));
			var_dump($this->request->getPost('ip_addr'));die;
			// 修改IP, 验证邮箱密码
			$this->ipCheckMailCode();

			// 验证OTP密码

			// 进行修改
		}
	}

	public function captchaAction() {
		!$this->request->isPost() && $this->api(Msg::ErrNotFound);
		header('HTTP/2.0 500' );
		$this->api(Msg::ErrFailure);
		$key = 'chg_ip_'. $this->uid;
		$this->redis->get($key) && $this->api(Msg::Suc);
		$code = $this->s->captcha()->genCode(true);
		$this->api($this->s->mail()->sendCaptcha($code));

	}

	public function checkCaptcha() {

	}



	//校验代码
	private function getCaptcha($pre) {
		$key     = 'vCode_' . $pre;
		$timeout = 900;
		$captcha = mb_substr(str_shuffle('0123456789'), 0, 6);
		$this->cache->save($key, $captcha, $timeout);
		return $captcha;
	}

}