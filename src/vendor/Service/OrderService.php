<?php

namespace BITPAY\Service;

use BITPAY\Model\TxPay;
use BITPAY\Model\TxPayHis;
use BITPAY\Model\TxRemit;
use BITPAY\Model\TxRemitHis;

class OrderService extends BaseService {
	//TODO 订单计数条件
	public function orderSql($data) {
		$sql = 'uid = ' . (int)$data['uid'];
		isset($data['txRid']) && $data['txRid'] && $sql .= ' AND tx_rid = ' . (int)$data['txRid'];
		isset($data['txOid']) && $data['txOid'] && $sql .= ' AND tx_oid = ' . (int)$data['txOid'];
		isset($data['prodId']) && $data['prodId'] > '0' && $sql .= ' AND prod_id = ' . (int)$data['prodId'];
		isset($data['chanCode']) && $data['chanCode'] && $chanCode = $data['chanCode'];
		isset($chanCode) && $sql .= " AND chan_code = '$chanCode'";
		isset($data['state']) && $data['state'] && $sql .= ' AND state ' . $this->arrayToSqlIn($data['state']);
		isset($data['isFrozen']) && $data['isFrozen'] >= '0' && $sql .= ' AND frozen_state = ' . (int)$data['frozen_state'];
		isset($data['settleState']) && $data['settleState'] && $sql .= ' AND settle_state ' . $this->arrayToSqlIn($data['settleState']);
		isset($data['startStime']) && $data['startStime'] && $sql .= ' AND ctime >= ' . (int)$data['startStime'];
		isset($data['startEtime']) && $data['startEtime'] && $sql .= ' AND ctime <= ' . (int)$data['startEtime'];
		isset($data['endStime']) && $data['endStime'] && $sql .= ' AND etime >= ' . (int)$data['endStime'];
		isset($data['endEtime']) && $data['endEtime'] && $sql .= ' AND etime <= ' . (int)$data['endEtime'];
		return $sql;
	}
	//-----------------------------------------TODO 处理中-----------------------------------------
	//TODO 处理中订单数量（充值）
	public function orderQuantity($data) {
		return TxPay::count(['conditions' => $this->orderSql($data)]);
	}

	//TODO 处理中订单数量（代付）
	public function orderQuantityRemit($data) {
		return TxRemit::count(['conditions' => $this->orderSql($data)]);
	}

	//TODO 处理中订单列表（充值）
	public function orderProcessing($order) {
		$conditions = 'state = 0 AND uid = ' . (int)$order['uid'];
		$order['txOid'] && $conditions .= ' AND tx_oid = ' . (int)$order['txOid'];
		$order['prodId'] && $conditions .= ' AND prod_id = ' . (int)$order['prodId'];
		$order['stime'] && $order['etime'] && $conditions .= ' AND ctime >= ' . (int)$order['stime'] . ' AND ctime <= ' . (int)$order['etime'];
		$columns = 'tx_oid,uid,amt_total,fee_total,amt_rec,ctime,etime,state,settle_time,settle_cycle,settle_state,memo,prod_id';
		return TxPay::find(['conditions' => $conditions, 'columns' => $columns, 'offset' => $order['page']*15-15, 'limit' => 15, 'order' => 'ctime DESC'])->toArray();
	}

	//TODO 处理中订单列表（代付）
	public function orderProcessingRemit($order) {
		$conditions = 'state = 0 AND uid = ' . (int)$order['uid'];
		$order['txRid'] && $conditions .= ' AND tx_rid = ' . (int)$order['txRid'];
		$order['chanCode'] && $conditions .= ' AND chan_code = "' . $order['chanCode'] . '"';
		$order['stime'] && $order['etime'] && $conditions .= ' AND ctime >= ' . (int)$order['stime'] . ' AND ctime <= ' . (int)$order['etime'];
		$columns = 'tx_rid,uid,amt_total,fee_total,amt_rec,chan_code,ctime,etime,state,memo';
		return TxRemit::find(['conditions' => $conditions, 'columns' => $columns, 'offset' => $order['page']*15-15, 'limit' => 15, 'order' => 'ctime DESC'])->toArray();
	}

	//TODO 处理中--详情（充值）
	public function inHandOrderDetails(int $uid, int $txOid) {
		return TxPay::find('uid = ' . (int)$uid . ' AND tx_oid = ' . (int)$txOid)->toArray();
	}

	//TODO 处理中--详情（代付）
	public function inHandOrderDetailsRemit(int $uid, int $txRid) {
		return TxRemit::find('uid = ' . (int)$uid . ' AND tx_rid = ' . (int)$txRid)->toArray();
	}
	//-----------------------------------------TODO 已处理-----------------------------------------
	//TODO 历史订单数量（充值）
	public function hisOrderQuantity($data) {
		return TxPayHis::count(['conditions' => $this->orderSql($data)]);
	}

	//TODO 历史订单数量（代付）
	public function hisOrderQuantityRemit($data) {
		return TxRemitHis::count(['conditions' => $this->orderSql($data)]);
	}

	//TODO 历史订单列表（充值）
	public function hisOrderList($order) {
		$conditions = 'uid = ' . (int)$order['uid'];
		$order['txOid'] && $conditions .= ' AND tx_oid = ' . (int)$order['txOid'];
		$order['prodId'] && $conditions .= ' AND prod_id = ' . (int)$order['prodId'];
		$order['frozen_state'] >= '0' && $conditions .= ' AND frozen_state = ' . (int)$order['isFrozen'];
		$order['startStime'] && $order['startEtime'] && $conditions .= ' AND ctime >= ' . (int)$order['startStime'] . ' AND ctime <= ' . (int)$order['startEtime'];
		$order['endStime'] && $order['endEtime'] && $conditions .= ' AND etime >= ' . (int)$order['endStime'] . ' AND etime <= ' . (int)$order['endEtime'];
		$columns = 'tx_oid,uid,amt_total,fee_total,amt_rec,ctime,etime,state,settle_time,settle_cycle,settle_state,memo,prod_id';
		return TxPayHis::find(['conditions' => $conditions, 'columns' => $columns, 'offset' => $order['page']*15-15, 'limit' => 15, 'order' => 'ctime DESC'])->toArray();
	}

	//TODO 历史订单列表（代付）
	public function hisOrderListRemit($order) {
		$conditions = 'uid = ' . (int)$order['uid'];
		$order['txRid'] && $conditions .= ' AND tx_rid = ' . (int)$order['txRid'];
		$order['chanCode'] && $conditions .= ' AND chan_code = ' . (int)$order['chanCode'];
		$order['startStime'] && $order['startEtime'] && $conditions .= ' AND ctime >= ' . (int)$order['startStime'] . ' AND ctime <= ' . (int)$order['startEtime'];
		$order['endStime'] && $order['endEtime'] && $conditions .= ' AND etime >= ' . (int)$order['endStime'] . ' AND etime <= ' . (int)$order['endEtime'];
		$columns = 'tx_rid,uid,amt_total,fee_total,amt_rec,chan_code,ctime,etime,state,memo';
		return TxRemitHis::find(['conditions' => $conditions, 'columns' => $columns, 'offset' => $order['page']*15-15, 'limit' => 15, 'order' => 'ctime DESC'])->toArray();
	}

	//TODO 历史--详情（充值）
	public function hisOrderDetails(int $uid, int $txOId) {
		return TxPayHis::find('uid = ' . (int)$uid . ' AND tx_oid = ' . (int)$txOId)->toArray();
	}

	//TODO 历史--详情（代付）
	public function hisOrderDetailsRemit(int $uid, int $txRId) {
		return TxRemitHis::find('uid = ' . (int)$uid . ' AND tx_rid = ' . (int)$txRId)->toArray();
	}
}