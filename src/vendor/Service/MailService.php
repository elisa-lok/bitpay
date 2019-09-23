<?php

namespace BITPAY\Service;

use BITPAY\Template\Mail;
use BITPAY\Utils\IP;

/**
 * Class MailService
 * @package BITPAY\Service
 */
class MailService extends BaseService {
	//Todo 发送注册邮件验证码
	public function signUpCaptcha($mailAddr, $captcha) {
		$body = sprintf(Mail::SignUpCaptchaTemp['body'], $captcha);
		return $this->sendMail($mailAddr, Mail::SignUpCaptchaTemp['subject'], $body);
	}

	//Todo 注册成功通知
	public function signUpSucc($mailAddr) {
		$ip         = $this->getIpAddr();
		$ipLocation = IP::realLocation($ip);
		$body       = sprintf(Mail::SignUpApproved['body'], $captcha);
	}

	//todo 登录成功通知
	public function signInNotify($mailAddr) {
		$ip         = $this->getIpAddr();
		$ipLocation = IP::realLocation($ip);
		$body       = sprintf(Mail::SignInNotify['body'], $mailAddr, $this->getDate(), $ip, $ipLocation);
		return $this->sendMail($mailAddr, Mail::SignInNotify['subject'], $body);
	}

	//todo 审核通过
	public function approved($uid, $email, $apiKey, $otpKey) {

	}


	//TODO 发送登录密码错误邮件
	//TODO 发送重设密码验证码
	public function resetCaptcha($mailAddr, $captcha, $username) {
		$body = sprintf(Mail::AccResetCaptcha['body'], $username, $this->getDate(), $this->getIpAddr(), IP::realLocation($this->getIpAddr()), $captcha);
		return $this->sendMail($mailAddr, Mail::AccResetCaptcha['subject'], $body);
	}

	/**
	 * @param string $mailAddr
	 * @param string $captcha
	 * @return bool
	 * @throws \BITPAY\Utils\Exception
	 */
	public function sendCaptcha(string $mailAddr, string $captcha){
		$body = sprintf(Mail::Captcha['body'], $captcha);
		$this->sendMail($mailAddr, Mail::Captcha['subject'],$body);
		return true;
	}

	//TODO 发送OTP验证器的密码

	//TODO 发送余额减少变动通知

	// TODO 日营收通知
	//TODO 周营收通知

	//TODO 月营收通知

	/**
	 * 发送email
	 * @param $receiverAddr
	 * @param $subject
	 * @param $body
	 * @return bool
	 * @throws \BITPAY\Utils\Exception
	 */
	private function sendMail($receiverAddr, $subject, $body) {
		$mail            = new \BITPAY\Utils\PHPMailer();
		$mail->SMTPDebug = 0; //debug模式, 0,1,2
		$mail->IsSMTP(); // send via SMTP
		//$mail->IsHTML(); // send as   HTML
		$mail->CharSet  = 'UTF-8'; // 这里指定字符集
		$mail->Encoding = 'base64';
		$mail->SMTPAuth = TRUE; // turn on SMTP authentication

		$mail->Host       = $this->config->mail->host; //SMTP服务器，如smtp.163.com
		$mail->Port       = $this->config->mail->port; //SMTP服务器端口
		$mail->SMTPSecure = $this->config->mail->secure;
		$mail->Username   = $mail->From = $this->config->mail->acc; //SMTP服务器的用户邮箱
		$mail->Password   = $this->config->mail->pass; //SMTP服务器的用户密码
		$mail->FromName   = $this->config->mail->accName; // 发件人
		$mail->AddAddress($receiverAddr); // 收件人

		$mail->Subject = $this->config->mail->prefix . $subject; // 邮件主题
		$mail->Body    = $body; //邮件内容
		$mail->AltBody = strip_tags($body);
		return $mail->Send();
	}

	/**
	 * @return string
	 */
	private function getDate() {
		return date('Y-m-d H:i:s', time());
	}
}