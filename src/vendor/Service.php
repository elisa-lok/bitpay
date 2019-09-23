<?php

namespace BITPAY;

use BITPAY\Service\TxService;

/**
 * Class Services
 * @package BITPAY\Service
 */
class Service {
	/**
	 * @return \BITPAY\Service\AdminService()
	 */
	public function admin() {
		return new Service\AdminService();
	}

	/**
	 * @return Service\AuthService
	 */
	public function auth(){
		return new Service\AuthService();
	}

	/**
	 * 添加如下语句就可以在IDE显示提示
	 * @return \BITPAY\Service\UserService()
	 */
	public function user() {
		return new Service\UserService();
	}

	public function adminLog() {
		return new Service\AdminLogService();
	}

	public function userLog() {
		return new Service\UserLogService();
	}

	/**
	 * @property \BITPAY\Service\BankService()
	 * @return Service\BankService
	 */
	public function bank() {
		return new Service\BankService();
	}

	/**
	 * @property \BITPAY\Service\CaptchaService()
	 * @return Service\CaptchaService()
	 */
	public function captcha() {
		return new Service\CaptchaService();
	}

	/**
	 * @property \BITPAY\Service\MailService()
	 * @return Service\MailService
	 */
	public function mail() {
		return new Service\MailService();
	}

	/**
	 * @property \BITPAY\Service\SmsService()
	 * @return Service\SmsService
	 */
	public function sms() {
		return new Service\SmsService();
	}

	/**
	 * @property \BITPAY\Service\UserSignService()
	 * @return Service\UserSignService
	 */
	public function sign() {
		return new Service\UserSignService();
	}

	/**
	 * @property \BITPAY\Service\LogService()
	 * @return Service\LogService
	 */
	public function log() {
		return new Service\LogService();
	}

	/**
	 * @property \BITPAY\Service\OrderService()
	 * @return Service\OrderService
	 */
	public function order() {
		return new Service\OrderService();
	}

	/**
	 * @return Service\BalanceService
	 */
	public function balance() {
		return new Service\BalanceService();
	}

	/**
	 * @return Service\ChannelService
	 */
	public function channel() {
		return new Service\ChannelService();
	}

	public function tx(){
		return new TxService();
	}
}