<?php

namespace BITPAY\Home\Controllers;

use BITPAY\Base;
use BITPAY\Msg;

/**
 * Class BankController
 * @package BITPAY\Home\Controllers
 */
class FundController extends ControllerBase {
	public function initialize() {
		parent::initialize();
	}

	// 资金余额以及流水记录
	public function flowAction(){
		$page = $this->request->getPost('page', 'int', 0);
		$limit = $this->request->getPost('limit', 'int', 25);
		$balanceSvc = $this->s->balance()->getBalLog();
		$balanceSvc->getFunds($this->uid);



		$this->view->pick('fund/flow');
	}

	// 每日对账单
	public function statementAction(){
		$this->view->pick('fund/statement');
	}
}