<?php

namespace BITPAY\Service;

use BITPAY\Model\PayAccPack;
use BITPAY\Model\PayBankList;
use BITPAY\Model\TxPayHis;
use BITPAY\Model\UserBankList;
use BITPAY\Model\PayChan;
use BITPAY\Model\PayChanProd;
use BITPAY\Model\UserRate;
use BITPAY\Msg;

class BankService extends BaseService {
	public function getBankList() {
		return PayBankList::find(['conditions' => 'state = 1', 'columns' => 'bank_code,bank_name'])->toArray();
	}

	/**
	 * 银行卡操作  必须是代理商才能添加银行卡！！！
	 * @param $bankCode
	 * @return array|bool
	 */
	public function operationBankCard($bankCode) {
		$bank = $bankCode['id'] === 0 ? new UserBankList() : UserBankList::findFirst('id = ' . $bankCode['id']);
		if ($bank && $bank->save($bankCode)) {
			return TRUE;
		}
		return Msg::ErrFailure;
	}

	/**
	 * 银行卡信息（代理商）
	 * @param int $uid 用户ID
	 * @return mixed
	 */
	public function getBankCardInfo(int $uid) {
		return UserBankList::find('uid = ' . (int)$uid)->toArray();
	}

	/**
	 * 删除银行卡
	 * @param int $id  id
	 * @param int $uid 用户ID
	 * @return boolean
	 */
	public function deleteBankCard(int $id, int $uid) {
		return UserBankList::findFirst('id = ' . (int)$id . ' AND uid = ' . (int)$uid)->delete();
	}

	/**
	 * 修改默认银行卡
	 * @param int $id
	 * @param int $uid
	 * @return array|bool
	 */
	public function editDefaultBankCard(int $id, int $uid) {
		$deBank = UserBankList::findFirst('id = ' . (int)$id . ' AND uid = ' . (int)$uid);
		if (!$deBank) {
			return Msg::ErrBankCardNotFound;
		}
		if ($deBank->is_default != 1) {
			$default = UserBankList::findFirst('is_default = 1 AND uid = ' . (int)$uid);
			if ($default) {
				$default->is_default = 0;
				$default->update();
			}
			$deBank->is_default = 1;
			return $deBank->update();
		}
		return TRUE;
	}

	/**
	 * 网关
	 * @return array
	 */
	public function getChanName() {
		return PayChan::find(['conditions' => 'state = 1', 'columns' => 'chan_code,chan_name'])->toArray();
	}

	/**
	 * 通道
	 * @return array
	 */
	public function getChanProdName() {
		return PayChanProd::find(['conditions' => 'state = 1', 'columns' => 'prod_id,prod_name'])->toArray();
	}

	/**
	 * 通道费率
	 * @param int $uid
	 * @return mixed
	 */
	public function chanProdRate(int $uid){
		$rate['list'] = UserRate::find('uid = '.(int)$uid)->toArray();
		$rate['name'] = $rate['list'] ? PayAccPack::find('pack_id '.$this->arrayToSqlIn(array_column($rate['list'],'pack_id')))->toArray() : 0;
		return $rate;
	}
	/**
	 * 通道分析
	 * @param int $uid
	 * @return array
	 */
	public function chanProdAnalyze(int $uid){
		return TxPayHis::find(['conditions' => 'uid = '.(int)$uid, 'columns' => 'amt_total,amt_rec,fee_total,prod_id,state'])->toArray();
	}
}