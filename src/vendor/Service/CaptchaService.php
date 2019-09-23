<?php

namespace BITPAY\Service;

use BITPAY\Base;

class CaptchaService extends BaseService {
	const CaptchaKey = 'captchaKey';

	/**
	 * 生成一个对应位数的验证码
	 * @param bool $num 只生成数字
	 * @param int  $length
	 * @return string
	 */
	public function genCode($num = FALSE, $length = 6) {
		$code    = '';
		$charset = $num ? '0123456789' : 'abcdefhjkmnprstuvwxyz23456789';
		$cnt     = mb_strlen($charset, 'UTF-8');
		for ($i = 0; $i < $length; $i++) {
			$code .= mb_substr($charset, mt_rand(0, $cnt - 1), 1, 'UTF-8');
		}
		return $code;
	}

	public function getMailCaptcha($mailAddr) {
		$code = $this->genCode(TRUE);
		$this->redis->setex(Base::MailCaptchaPre . $mailAddr, Base::MailCaptchaExpire, $code);
		return $code;
	}

	public function checkMailCaptcha($mailAddr, $captcha) {
		return $this->redis->get(Base::MailCaptchaPre . $mailAddr) == $captcha;
	}

	/**
	 * 获取一个图片验证码的base64
	 */
	public function getPicBase64() {
		$captcha = new \BITPAY\Utils\Captcha();
		$captcha->build();
		$this->redis->setex(Base::PicCaptchaKey . $this->session->getId(), Base::PicCaptchaExpire, $captcha->getCode());
		return $captcha->base64();
	}

	public function getPic() {
		$captcha = new \BITPAY\Utils\Captcha();
		$captcha->build();
		$this->redis->setex(Base::PicCaptchaKey . $this->session->getId(), Base::PicCaptchaExpire, $captcha->getCode());
		$captcha->output();
	}

	public function checkPicCaptcha($captcha) {
		return !empty($captcha) ? $this->redis->get(Base::PicCaptchaKey . $this->session->getId()) == $captcha : FALSE;
	}

	public function check($captcha) {
		$code = $this->session->get(self::CaptchaKey);
		return strtolower($captcha) == $code ? TRUE : FALSE;
	}
}