<?php

namespace BITPAY\Service;

use BITPAY\Model\BalUser;
use BITPAY\Model\BalUserLog;
use BITPAY\Model\TxPay;
use BITPAY\Model\TxPayHis;
use BITPAY\Model\TxRemitHis;

class BalanceService extends BaseService {

	// 流水日志
	public function getBalLog(int $page, int $limit,array $data){
		$sql = $this->sqlBalLogConditions($data);
		$offset   = $page - 1 < 0 ? 0 : $page - 1;
		$limit    = $limit < 10 ? 10 : (int)$limit;
		$limitSql = " LIMIT $offset, $limit ";

		$res['data']  = TxPay::find($sql . $limitSql)->toArray();
	}

	// todo 获取用户余额信息, 暂时不做币种处理
	public function getBal($uid) {
		return BalUser::find('uid '. $this->arrayToSqlIn($uid))->toArray();
	}


	private function sqlBalLogConditions($data){
		$sql = [];
		isset($data['uid']) && $data['uid'] && $sql[] = ' uid ' . $this->arrayToSqlIn($data['uid']);                // 用户ID
		isset($data['tx_out_no']) && $data['tx_out_no'] != '' && $sql[] = " tx_no = '" . $data['tx_out_no'] . "' "; //只允许字母与数字
		isset($data['tx_no']) && $data['tx_no'] != '' && $sql[] = " tx_no = '" . $data['tx_no'] . "'";              //只允许字母与数字
		isset($data['state']) && $data['state'] != '' && $sql[] = ' state = ' . (int)$data['state'] . ' ';
		isset($data['ctime_bucket']) && $data['ctime_bucket'] != '' && $sql[] = $this->timeSQL('ctime', $data['ctime_bucket']);
		isset($data['etime_bucket']) && $data['etime_bucket'] != '' && $sql[] = $this->timeSQL('etime', $data['etime_bucket']);
		isset($data['mtime_bucket']) && $data['mtime_bucket'] != '' && $sql[] = $this->timeSQL('mtime', $data['mtime_bucket']);
		return implode(' AND ', $sql) .' ORDER BY '. $orderBy;
	}

}