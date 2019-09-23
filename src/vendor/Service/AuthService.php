<?php

namespace BITPAY\Service;

use BITPAY\Model\AuthUserApi;
use BITPAY\Model\UserList;
use BITPAY\Msg;

/**
 * Api操作
 * Class AuthService
 * @package BITPAY\Service
 */
class AuthService extends BaseService {
	/**
	 * @param int  $uid
	 * @param bool $checkIp
	 * @return array|mixed|\Phalcon\Mvc\Model\ResultInterface|AuthUserApi|null
	 */
	public function getApiAuthByUid(int $uid, bool $checkIp = FALSE) {
		$key    = 'sig_' . $uid;
		$sigKey = $this->cache->get($key);
		if (!$sigKey) {
			$user = UserList::findFirst('state = 1 AND uid = ' . (int)$uid);
			if (!$user) {
				return Msg::ErrUserUnavailable;
			}
			$sigKey = AuthUserApi::findFirst('uid = ' . (int)$uid);
			if (!$sigKey) {
				return Msg::ErrAuth;
			}
			if ($checkIp && strpos($sigKey->ip_binding, $this->getIpAddr()) === FALSE) {
				return Msg::ErrIPExtent;
			}
			$sigKey = $sigKey->toArray();
			$this->cache->save($key, $sigKey, 1800);
		}
		return $sigKey;
	}
}