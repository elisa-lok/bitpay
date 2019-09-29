<?php

namespace BITPAY\Home\Controllers;

// https://api.huobi.pro/market/tickers 获取火币所有ticket



class TxController extends ControllerBase {
	//-------------------------------------------------------------------------------------------------------------------------
	public function initialize() {
		parent::initialize();
	}
	//-----------------------------------------TODO 处理中-----------------------------------------
	// 处理中订单
	public function payingAction() {
		if ($this->request->isPost()) {
			$page                   = $this->request->getPost('page', 'int', 0);   // 分页
			$limit                  = $this->request->getPost('limit', 'int', 25); // 限制
			$params['acc_id']          = $this->accId;
			$params['prod_id']      = $this->request->getPost('prod_id', 'int');   // 订单产品
			$params['state']        = $this->request->getPost('state', 'int');     // 订单状态
			$params['ctime_bucket'] = $this->request->getPost('ctime_bucket', 'trim'); //创建时间
			$params['etime_bucket'] = $this->request->getPost('etime_bucket', 'trim'); //完成时间

			$res = $this->s->tx()->getUserTxList($page, $limit, $params);
			$this->api($res['data'], $res['count']);
		}
		$this->view->pick('tx/paying');
	}

	// 处理完成订单
	public function paidAction() {
		if ($this->request->isPost()) {
			$this->api();
		}
		$this->view->pick('tx/paid');
	}

	// 代付
	public function remittingAction() {
		if ($this->request->isPost()) {
			$this->api();
		}
		$this->view->pick('tx/remitting');
	}

	public function remittedAction() {
		if ($this->request->isPost()) {
			$this->api();
		}
		$this->view->pick('tx/remitted');
	}
}