<?php

namespace BITPAY\Service;

/**
 * 管理员操作记录
 * Class AdminLogService
 * @package BITPAY\Service
 */
class AdminLogService extends BaseService {

	public function createOpLog($admId, $admName, $uriPath, $desc){

	}


	public function createSignInLog($admId, $admName, $uriPath){
		$this->getIpAddr();

	}
}