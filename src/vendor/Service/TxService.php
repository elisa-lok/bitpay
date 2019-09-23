<?php

namespace BITPAY\Service;

use BITPAY\Model\TxPay;

class TxService extends BaseService {
	public function createTxId($uid) {
		return (int)(microtime(TRUE) * 1000) . (int)$uid . rand(100, 999);
	}

	public function getUserTxList($page = 0, $limit = 25, array $data = []) {
		$sql = $this->txSqlCondition($data);

		$offset   = $page - 1 < 0 ? 0 : $page - 1;
		$limit    = $limit < 10 ? 10 : (int)$limit;
		$limitSql = " LIMIT $offset, $limit ";

		$res['data']  = TxPay::find($sql . $limitSql)->toArray();
		$res['count'] = TxPay::count($sql);
		return $res;
	}

	public function getSubUserTxList($uids, $page = 0, $limit = 25, array $data = []) {

	}

	public function txSqlCondition($data, $orderBy = ' ctime DESC ') {
		$sql = [];
		isset($data['tx_out_no']) && $data['tx_out_no'] != '' && $sql[] = " tx_no = '" . $data['tx_out_no'] . "' "; //只允许字母与数字
		isset($data['tx_no']) && $data['tx_no'] != '' && $sql[] = " tx_no = '" . $data['tx_no'] . "'";              //只允许字母与数字
		isset($data['acc_id']) && $data['acc_id'] && $sql[] = ' acc_id ' . $this->arrayToSqlIn($data['acc_id']);                // 用户ID
		isset($data['state']) && $data['state'] != '' && $sql[] = ' state = ' . (int)$data['state'] . ' ';
		isset($data['ctime_bucket']) && $data['ctime_bucket'] != '' && $sql[] = $this->timeSQL('ctime', $data['ctime_bucket']);
		isset($data['etime_bucket']) && $data['etime_bucket'] != '' && $sql[] = $this->timeSQL('etime', $data['etime_bucket']);
		isset($data['mtime_bucket']) && $data['mtime_bucket'] != '' && $sql[] = $this->timeSQL('mtime', $data['mtime_bucket']);
		return implode(' AND ', $sql) .' ORDER BY '. $orderBy;
	}


}