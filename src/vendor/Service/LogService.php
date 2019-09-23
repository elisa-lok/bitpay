<?php

namespace BITPAY\Service;

use BITPAY\Model\BalUserLog;
use BITPAY\Model\LogUserOp;
use BITPAY\Model\TxPayHis;
use BITPAY\Model\TxRemitHis;

class LogService extends BaseService {
	/**
	 * 操作记录
	 * @param int   $uid
	 * @param int   $page
	 * @param int   $limit
	 * @param array $data
	 * @return array
	 */
	public function opRecord(int $uid, int $page, int $limit, array $data) {
		$offset   = $page - 1 < 0 ? 0 : $page - 1;
		$limit    = $limit < 10 ? 10 : (int)$limit;
		$sql  = 'uid = ' . (int)$uid;
		$res['data'] =  LogUserOp::find(['conditions' => $sql, 'columns' => 'uid,act_type,act_desc,FROM_UNIXTIME(ctime) AS ctime,ip_addr', 'offset' => $offset, 'limit' => $limit, 'order' => 'ctime DESC'])->toArray();
		$res['count'] = LogUserOp::count(['conditions' => $sql,'columns' => 1 ]);
		return $res ;
	}

	// 删除30天前的IP记录
	public function opRecordDel(int $uid) {
		$time = mktime(0,0,0) - 2592000; // 86400 * 30, 30天前0:0:0
		return LogUserOp::find('uid = ' . (int)$uid . ' AND ctime < ' . $time)->delete();
	}


	/**
	 * 登录记录数量
	 * @param int $uid
	 * @param     $stime
	 * @return mixed
	 */
	public function loginRecordNum(int $uid, $stime) {
		$sql = 'action_type = 0 AND uid = ' . (int)$uid;
		$stime && $sql .= ' AND ctime >= ' . $stime;
		return LogUserOp::count(['conditions' => $sql, 'columns' => '0']);
	}

	/**
	 * 资金记录
	 * @param $record
	 * @return array
	 */
	public function moneyRecord($record) {
		$sql = 'uid = ' . (int)$record['uid'];
		isset($record['txId']) && $record['txId'] > 0 && $sql .= ' AND tx_id = ' . (int)$record['txId'];
		$record['ctime'] && $record['etime'] && $sql .= ' AND ctime >= ' . (int)$record['ctime'] . ' AND ctime <= ' . (int)$record['etime'];
		$record['balType'] >= '0' && $sql .= ' AND bal_type = ' . (int)$record['balType'];
		return BalUserLog::find(['conditions' => $sql, 'offset' => $record['page'] * 15 - 15, 'limit' => 15, 'order' => 'bal_log_id desc'])->toArray();
	}

	/**
	 * 资金记录数量
	 * @param int $uid
	 * @param     $stime
	 * @param     $etime
	 * @param     $type
	 * @return mixed
	 */
	public function moneyRecordNum(int $uid, $stime, $etime, $type) {
		$sql = 'uid = ' . (int)$uid;
		$stime && $etime && $sql .= ' AND ctime >= ' . (int)$stime . ' AND ctime <= ' . (int)$etime;
		$type >= '0' && $sql .= ' AND bal_type = ' . (int)$type;
		return BalUserLog::count(['conditions' => $sql, 'column' => '0']);
	}

	/**
	 * 结算记录数量
	 * @param $settle
	 * @return mixed
	 */
	public function settleLogListQuantity($settle) {
		$sql = 'uid = ' . (int)$settle['uid'];
		$settle['settleState'] >= '0' && $sql .= ' AND settle_state = ' . (int)$settle['settleState'];
		$settle['settleCycle'] >= '0' && $sql .= ' AND settle_cycle = ' . (int)$settle['settleCycle'];
		$settle['stime'] && $settle['etime'] && $sql .= ' AND settle_time >= ' . (int)$settle['stime'] . ' AND settle_time <= ' . (int)$settle['etime'];
		return TxPayHis::count(['conditions' => $sql, 'columns' => '0']);
	}

	/**
	 * 结算记录列表
	 * @param $settle
	 * @return mixed
	 */
	public function settleLogList($settle) {
		$sql = 'uid = ' . (int)$settle['uid'];
		$settle['settleState'] >= '0' && $sql .= ' AND settle_state = ' . (int)$settle['settleState'];
		$settle['settleCycle'] >= '0' && $sql .= ' AND settle_cycle = ' . (int)$settle['settleCycle'];
		$settle['stime'] && $settle['etime'] && $sql .= ' AND settle_time >= ' . (int)$settle['stime'] . ' AND settle_time <= ' . (int)$settle['etime'];
		return TxPayHis::find([
								  'conditions' => $sql,
								  'offset'     => $settle['page'] * 15 - 15,
								  'limit'      => 15,
								  'columns'    => 'prod_id,amt_total,amt_rec,fee_total,settle_state,settle_cycle,settle_time',
								  'order'      => 'settle_time desc'
							  ])->toArray();
	}

	/**
	 * 代付记录数量
	 * @param $remit
	 * @return mixed
	 */
	public function remitLogListQuantity($remit) {
		$sql = 'uid = ' . (int)$remit['uid'];
		$remit['state'] >= '0' && $sql .= ' AND state = ' . (int)$remit['state'];
		$remit['stime'] && $remit['etime'] && $sql .= ' AND ctime >= ' . (int)$remit['stime'] . ' AND ctime <= ' . (int)$remit['etime'];
		return TxRemitHis::count(['conditions' => $sql, 'columns' => '0']);
	}

	/**
	 * 代付记录
	 * @param $remit
	 * @return array
	 */
	public function remitLogList($remit) {
		$sql = 'uid = ' . (int)$remit['uid'];
		$remit['state'] >= '0' && $sql .= ' AND state = ' . (int)$remit['state'];
		$remit['stime'] && $remit['etime'] && $sql .= ' AND ctime >= ' . (int)$remit['stime'] . ' AND ctime <= ' . (int)$remit['etime'];
		return TxRemitHis::find(
			[
									'conditions' => $sql,
									'offset'     => $remit['page'] * 15 - 15,
									'limit'      => 15,
									'columns'    => 'tx_rid,amt_total,amt_rec,fee_total,state,bank_code,bank_branch,real_name,ctime',
									'order'      => 'ctime desc'
								])->toArray();
	}
}