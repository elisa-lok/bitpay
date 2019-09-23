<?php

namespace BITPAY\Api\Lib\Service;

class SysService extends BaseService {
	// 检查是否黑名单IP
	public function checkBlockedIp($ip) {
		return TRUE;
	}
}