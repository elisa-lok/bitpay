<?php

namespace BITPAY\Api\Lib\Service;

use BITPAY\Api\Common\Base;
use BITPAY\Api\Common\Msg;
use BITPAY\Api\Model\AccList;
use BITPAY\Api\Model\AuthAccApi;

class AccountService extends BaseService {
	const AccPrefix = 'acc_';

	public function getAccInfo(int $accId) {
		$accInfo = $this->cache->get(self::AccPrefix . $accId);
		if (!$accInfo) {
			// 获取基础信息
			$sql     = 'acc_id = ' . (int)$accId . ' AND acc_type = ' . Base::AccTypeMerchant;
			$accInfo = AccList::findFirst([$accId, 'columns' => 'acc_id, s_acc_id, ss_acc_id,sss_acc_id,state,settle_rate,settle_cycle,adm_id']);
			!$accInfo && $this->api(Msg::ErrNotFoundAcc);
			$accInfo->state != Base::AccStateNormal && $this->api(Msg::ErrAccUnavailable);
			// 获取key信息
			$accApiAuth = AuthAccApi::findFirst($accId);
			!$accApiAuth && $this->api(Msg::ErrNotFoundAcc);
			$accInfo               = $accInfo->toArray();
			$accInfo['ip_switch']  = $accApiAuth->ip_switch;
			$accInfo['ip_binding'] = $accApiAuth->ip_binding;
			$accInfo['sig_type']   = $accApiAuth->sig_type;
			$accInfo['sig']        = $accApiAuth->sig;
			$this->cache->save(self::AccPrefix . $accId, $accInfo);
		}
		return $accInfo;
	}

	public function checkIp(string $ip, array $accInfo) {
		return strpos($accInfo['ip'], $ip) === FALSE ? FALSE : TRUE;
	}

	public function checkTxCount() {

	}
}