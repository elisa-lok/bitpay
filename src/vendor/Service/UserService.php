<?php

namespace BITPAY\Service;

use BITPAY\Model\AuthUserApi;
use BITPAY\Model\AuthUserOtp;
use BITPAY\Model\UserList;
use BITPAY\Msg;

/**
 * 用户服务
 */
class UserService extends BaseService {
	//TODO 修改用户信息
	public function editUserInfo($info) {
		$us = UserList::findFirst('uid = ' . (int)$info['uid']);
		if (!$us) {
			return Msg::ErrUserNotFound;
		}
		$info['phone'] && $us->phone = $info['phone'];
		$info['email'] && $us->email = $info['email'];
		if (!$us->update()) {
			return FALSE;
		}
		return $this->merchantsWEB($info['uid'], 1, $info['acDesc'], $info['ip']);
	}

	//TODO 重置提现和支付密码 （只能代理商）
	public function withdrawalPassword($with) {
		//otp 验证
		if (!$this->checkOtpAuth(AuthUserOtp::findFirst(['conditions' => 'uid = ' . (int)$with['uid'], 'columns' => 'crypto_key'])->crypto_key, (string)$with['otp'])) {
			return Msg::ErrOTPAuth;
		}
		$user = UserList::findFirst('user_type = 1 AND uid = ' . (int)$with['uid']);
		if (!$user) {
			return Msg::ErrUserNotFound;
		}
		if ($user->wi_password != $this->hash($with['password'], $user->salt)) {
			return Msg::ErrAccAuth;
		}
		$user->wi_password = $this->hash($with['newPass'], $user->salt);
		if (!$user->update()) {
			return FALSE;
		}
		return $this->merchantsWEB($with['uid'], 2, '', $with['ip']);
	}
}
