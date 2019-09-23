<?php

namespace BITPAY\Service;

use BITPAY\Model\PayAcc;
use BITPAY\Model\PayAccPack;
use BITPAY\Model\PayChan;
use BITPAY\Model\PayChanProd;
use BITPAY\Msg;

class ChannelService extends BaseService {
	// **************************** 网关 ****************************
	public function chanList($state = NULL) {
		$sql = $state ? ' state = ' . (int)$state : '';
		return PayChan::find($sql)->toArray();
	}

	public function chanGetById(int $id) {
		$res = PayChan::findFirst('chan_id = ' . $id);
		return $res ? $res->toArray() : $res;
	}

	public function chanEdit(int $chanId, array $chanData) {
		$chanCode = PayChan::findFirst("chan_code = '" . $chanData['chan_code'] . "'");
		if ($chanCode && (($chanId == 0 && $chanCode->chan_code == $chanData['chan_code']) || ($chanId > 0 && $chanCode->chan_id != $chanId))) {
			return Msg::ErrExistChanCode;
		}
		$res = $chanId == 0 ? new PayChan() : PayChan::findFirst('chan_id = ' . $chanId);
		return $res ? $res->save($chanData) : $res;
	}

	public function chanDel(int $chanId) {
		$res = PayChan::findFirst('chan_id = ' . (int)$chanId);
		return $res ? $res->delete() : $res;
	}

	// **************************** 账户通道 ****************************
	public function accList($state = NULL, $withAll = FALSE) {
		$sql = $state ? ' state = ' . (int)$state : ' state < 2';
		$sql = $withAll ? $sql : [$sql, 'columns' => 'acc_id,acc_name,chan_code,settle_cycle,app_id,prod_id,daily_quota,weekly_quota,monthly_quota,state,rest_quota,quota,balance,frozen,rate,minimum,maximum'];
		return PayAcc::find($sql)->toArray();
	}

	public function accGetById(int $accId) {
		$res = PayAcc::findFirst((int)$accId);
		return $res ? $res->toArray() : FALSE;
	}

	public function accEdit(int $accId, array $accData) {
		$res = $accId == 0 ? new PayAcc() : PayAcc::findFirst('acc_id = ' . $accId);
		return $res ? $res->save($accData) : $res;
	}

	public function accDel(int $accId) {
		$res = PayAcc::findFirst('acc_id = ' . $accId);
		return $res ? $res->save(['state' => 2]) : $res;
	}

	// **************************** 支付产品 ****************************
	public function prodList($state = NULL) {
		$sql = $state ? ' state = ' . (int)$state : '';
		return PayChanProd::find($sql)->toArray();
	}

	public function prodEdit($prodId, array $prodData) {
		$res = !$prodId ? new PayChanProd() : PayChanProd::findFirst('prod_id = ' . $prodId);
		return $res ? $res->save($prodData) : $res;
	}

	public function prodDel(int $prodId) {
		$res = PayChanProd::findFirst('prod_id = ' . $prodId);
		return $res ? $res->delete() : $res;
	}


	// **************************** 账户通道组 ****************************
	public function packList($state = NULL) {
		$sql = $state ? ' state = ' . (int)$state : '';
		return PayAccPack::find($sql)->toArray();
	}

	public function packGetById(int $packId) {
		$res = PayAccPack::findFirst($packId);
		return $res ? $res->toArray() : FALSE;
	}

	public function packEdit(int $packId, array $packData) {
		//查找是否存在对应的accid, 过滤错误数据
		$accounts = PayAcc::find('acc_id ' . $this->arrayToSqlIn($packData['acc_ids']))->toArray();
		if (!$accounts) {
			return Msg::ErrInvalidArgument;
		}
		$packData['acc_ids'] = implode(',', array_column($accounts, 'acc_id'));
		$res                = $packId == 0 ? new PayChan() : PayChan::findFirst('pack_id = ' . $packId);
		return $res ? $res->save($packData) : $res;
	}

	public function packDel(int $packId) {
		$res = PayAccPack::findFirst('pack_id = ' . $packId);
		return $res ? $res->delete() : $res;
	}
}