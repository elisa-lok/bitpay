<?php

namespace BITPAY\Service;

use BITPAY\Msg;
use BITPAY\Model\UserList;
use BITPAY\Model\LogUserOp;
use BITPAY\Model\AuthUserOtp;

/**
 * 所有有关登录信息都在这里处理, 包括用户修改密码
 * Class SignService
 * @package BITPAY\Service
 */
class UserSignService extends BaseService {
	var $signTimeOut = 86400;
	var $sidPre      = 'sid_';
	//-----------------------------------------登录-----------------------------------------

	//TODO 用户登录, 只允许一个用户
	public function in($sign) {
		$username = $sign['username'];
		$user     = UserList::findFirst("username = '$username'");
		if (!$user) {
			return Msg::ErrUserNotFound;
		}
		//if (!$this->checkGoogleAuth(AuthUserOtp::findFirst(['conditions' => 'uid = ' . (int)$user->uid, 'columns' => 'crypto_key'])->crypto_key, (string)$sign['otp'])) {
		//	return Msg::ErrGoogleAuth;
		//}
		if ($user->state != 1) {
			return Msg::ErrAccState;
		}
		//TODO 校验密码
		if ($this->hash($sign['password'], $user->salt) != $user->password) {
			return Msg::ErrAccAuth;
		}
		//TODO 记录登录信息
		$log              = new LogUserOp();
		$log->uid         = (int)$user->uid;
		$log->action_type = 0;
		$log->action_desc = 'Unknown';
		$log->ctime       = time();
		$log->ip_addr     = isset($sign['ip']) ? $sign['ip'] : 0;
		if (!$log->create()) {
			return Msg::ErrFailure;
		}
		$this->session->regenerateId();
		$accInfo             = [];
		$accInfo['uid']      = $user->uid;
		$accInfo['userType'] = $user->user_type;
		$accInfo['username'] = $user->username;
		$accInfo['email']    = $user->email;
		$accInfo['phone']    = $user->phone;
		$this->session->set('uid', $accInfo['uid']);
		$this->cache->save($this->sidPre . $user->uid, $accInfo, $this->signTimeOut);
		return $accInfo;
	}

	//-----------------------------------------登出-----------------------------------------

	//TODO 注销
	public function out() {
		//销毁缓存
		return $this->session->destroy($this->session->getId());
	}

	public function clear($uid) {
		$this->cache->delete($this->sidPre . $uid);
	}

	//TODO 修改登录密码
	public function updateLoginPassword($user) {
		$data = UserList::findFirst('uid = ' . (int)$user['uid']);
		if (!$data) {
			return Msg::ErrUserNotFound;
		}
		if ($this->hash($user['password'], $data->salt) != $data->password) {
			return Msg::ErrPassword;
		}
		$data->password = $this->hash($user['newPass'], $data->salt);
		if (!$data->update()) {
			return Msg::ErrFailure;
		}
		return $this->merchantsWEB($user['uid'], 2, '', $user['ip']);
	}

	//----------------------------------------- 检查CSRF -----------------------------------------

	public function setCSRF($forceRenew = FALSE) {
		!isset($_COOKIE['csrf']) && setcookie('csrf', md5(microtime()));
	}

	//-----------------------------------------获取账户信息-----------------------------------------

	/**
	 * 获取账户信息
	 * @return mixed
	 */
	public function getAccInfo() {
		//根据session获取uid
		$sid = $this->session->getId();
		$uid = $this->session->get('uid');
		if (!$uid) {
			$this->session->destroy($sid);
			return FALSE;
		}
		return $this->cache->get($this->sidPre . $uid);
	}
}