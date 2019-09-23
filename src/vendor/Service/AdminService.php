<?php

namespace BITPAY\Service;

use BITPAY\Msg;
use BITPAY\Model\AdmList;

class AdminService extends BaseService {
	/**
	 * 用户登录
	 * @param $username
	 * @param $password
	 * @return mixed
	 */
	public function signIn($username, $password) {
		$user = AdmList::findFirst("adm_name = '$username'");
		if (!$user) {
			return Msg::ErrUserNotFound;
		}
		if ($user->state != 1) {
			return Msg::ErrUserNotFound;
		}
		//TODO 校验密码
		if ($this->hash($password, $user->salt) != $user->password) {
			return Msg::ErrAccAuth;
		}
		$admin             = [];
		$admin['id']       = $user->adm_id;
		$admin['realname'] = $user->realname;
		return $admin;
	}

	public function signOut() {
		return $this->session->destroy($this->session->getId());
	}
}