<?php

namespace BITPAY\Home\Controllers;

use BITPAY\Msg;
use BITPAY\Utils\Validate;

/**
 * 登錄
 * Class SignController
 * @package BITPAY\Home\Controllers
 */
class SignController extends ControllerBase {
	public function initialize() {
		parent::initialize();
	}

	public function indexAction(){
		$this->view->pick('sign/index');
	}

	//获取邮箱验证码
	public function getSignUpMailCaptchaAction() {
		!$this->request->isPost() && $this->api(Msg::ErrNotFound);
		$email   = $this->request->get('email', ['email', 'lower']);
		$captcha = $this->request->get('captcha', ['trim', 'lower']); //图形验证码
		!$this->s->captcha()->checkPicCaptcha($captcha) && $this->api(Msg::ErrCaptchaInvalid);
		$code = $this->s->captcha()->getMailCaptcha($email);
		$this->s->mail()->signUpCaptcha($email, $code) ? $this->api(Msg::Suc) : $this->api(Msg::ErrCaptchaSend);
	}


	//-----------------------------------------登入-----------------------------------------

	//TODO 登录  [type] 代理类型 4=>普通商户 5=>代理商户
	public function inAction() {
		$this->checkPicCaptcha();
		$username = $this->request->getPost('username', 'email');
		$psw = $this->request->getPost('password', 'trim');
		$psw = $this->request->getPost('password', 'trim');
		$rawData = $this->getRawData();
		!$rawData && $this->api(Msg::ErrFailure);
		$this->uid && $this->api(Msg::Suc);
		$sign['username'] = addslashes($rawData->username);
		$sign['password'] = trim($rawData->password);
		$sign['ip']       = isset($rawData->ip) ? $rawData->ip : 0;
		//$sign['otp']      = (string)$rawData->otp;
		//$captcha          = $rawData->captcha;
		//!$this->checkPicCaptcha($captcha) && $this->api(Msg::ErrCaptchaInvalid); //校验验证码
		(!$sign['username'] || !$sign['password']) && $this->api(Msg::ErrAccAuth);
		return $this->api($this->s->sign()->in($sign));
	}

	//-----------------------------------------重置密码-----------------------------------------

	/**
	 * 重置密码
	 */
	public function resetAction() {
		$rawData = $this->getRawData();
		!$rawData && $this->api(Msg::ErrFailure);
		$user['uid']      = $this->uid;
		$user['password'] = $rawData->password;
		$user['newPass']  = $rawData->new_pass;
		$user['ip']       = isset($rawData->ip) ? $rawData->ip : 0;
		return $this->api($this->s->sign()->updateLoginPassword($user));
	}

	//-----------------------------------------登出-----------------------------------------
	//注销用户登录
	public function outAction() {
		$this->s->sign()->out() ? $this->api(Msg::Suc) : $this->api(Msg::ErrFailure);
	}

	//-----------------------------------------通用部分-----------------------------------------

	//获取验证码
	public function getPicCaptchaAction() {
		$this->s->captcha()->getPic();
	}

	public function getPicCaptchaBase64Action() {
		echo $this->s->captcha()->getPicBase64();
		die;
	}

	//TODO 检查验证码是否正确
	private function checkPicCaptcha() {
		!$this->request->isPost() && $this->api(Msg::ErrNotFound);
		$captcha = $this->request->getPost('captcha', 'alphanum');
		!$this->s->captcha()->checkPicCaptcha($captcha) && $this->api(Msg::ErrCaptchaInvalid);
	}

	//检查邮件的验证码
	private function checkEmailCaptcha() {
		$email        = $this->request->getPost('email', ['email', 'lower']);
		$emailCaptcha = $this->request->getPost('email_captcha', ['alphanum', 'lower']); //email验证码
		!Validate::email($email) && $this->api(Msg::ErrInvalidEmail); //检查email格式
		!$this->s->captcha()->checkMailCaptcha($email, $emailCaptcha) && $this->api(Msg::ErrCaptchaInvalid);
	}
}