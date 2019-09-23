<?php

namespace BITPAY\Service;

use BITPAY\Msg;

/**
 * Class CaptchaService
 * @package BITPAY\Service
 */
class SmsService extends BaseService {


	/**
	 * 发送验证码
	 * @param $phone
	 * @return array|bool
	 */
	public function sendCaptcha($phone) {
		if (!$this->config->send_captcha) {
			return Msg::ErrRequestsTooFrequency;
		}
		//todo 判断手机验证码是否存在 如果存在，就提示操作频繁
		//        $this->redis->delete(self::SMS_TIMEOUT.$phone);die;
		if ($this->redis->get(self::SMS_TIMEOUT . $phone)) {
			return Msg::ErrRequestsTooFrequency;
		}
		$ip      = self::IpAddr . $_SERVER['REMOTE_ADDR'];
		$ipCount = (int)$this->redis->get($ip);
		if ($ipCount > 10) {
			return Msg::ErrRequestsTooFrequency;
		}
		if (strlen($_SERVER['HTTP_USER_AGENT']) < 10) {
			return Msg::ErrRequestsTooFrequency;
		}
		$phoneCount = $this->cookies->get(self::PhoneCookies)->getValue();
		if ($phoneCount === NULL) {
			$this->cookies->set(self::PhoneCookies, 1, time() + 3600);
		} elseif ($phoneCount > 2) {
			return Msg::ErrRequestsTooFrequency;
		} else {
			$this->cookies->set(self::PhoneCookies, ++$phoneCount, time() + 7200);
			if (!$this->cookies->get(self::PhoneCookies)->getValue()) {
				return Msg::ErrRequestsTooFrequency;
			}
		}

		$captcha = mb_substr(str_shuffle('0123456789'), 0, 6);
		// 发送验证码

		$url    = $this->config->submail->url;
		$params = 'appid=' . $this->config->submail->appid . '&to=' . $phone . '&content=【BITPAY】您的短信验证码：' . $captcha . '，请在3分钟内输入,切勿泄露他人。&signature=' . $this->config->submail->signature;
		$res    = $this->curlPost($url, $params, FALSE);
		$res    = json_decode($res, TRUE);
		if ($res['status' != 'success']) {
			return FALSE;
		}

		$this->redis->setex($ip, 7200, ++$ipCount);
		return $this->redis->setex(self::SMS_TIMEOUT . $phone, 180, $captcha);
	}

	/**
	 * 检查验证码
	 * @param $phone
	 * @param $captcha
	 * @return array|bool
	 */
	public function checkCaptcha($phone, $captcha) {

		$code = (int)$this->redis->get(self::SMS_TIMEOUT . $phone);
		//获取redis 和 cookie
		$ipErrorNumber    = (int)$this->redis->get(self::IpErrorNumber . $_SERVER['REMOTE_ADDR']) ?? 0;
		$phoneErrorNumber = (int)$this->redis->get('phone_verification' . $phone) ?? 0;
		//如果ip错误次数大于或者等于3就跳出

		if ($ipErrorNumber >= self::ErrCaptchaTimes || $phoneErrorNumber >= self::ErrCaptchaTimes) {
			return Msg::ErrVerificationErr;
		}
		//ip错误次数累加
		if (!$code || $captcha != $code) {
			$this->redis->setex('phone_verification' . $phone, 900, ++$phoneErrorNumber);
			$this->redis->setex(self::IpErrorNumber . $_SERVER['REMOTE_ADDR'], 900, ++$ipErrorNumber);
			return Msg::ErrCaptchaInvalid;
		}
		return TRUE;
	}

	public function sendOrderInfo() {

	}

	/**
	 * 发送验证短信
	 * @param $phoneNum
	 * @param $text
	 */
	private function sendSms($phoneNum, $text) {

	}

	/**
	 * 发送推广短信
	 * @param $phoneNum
	 * @param $text
	 */
	private function sendPromoSms($phoneNum, $text) {

	}
}