<?php

namespace BITPAY\Api\Lib;

/**
 * Class Service
 * @package BITPAY\Api
 */
class Service {
	/**
	 * @return \BITPAY\Api\Lib\Service\AccountService();
	 */
	public function account() {
		return new \BITPAY\Api\Lib\Service\AccountService();
	}

	/**
	 * @return Service\BalanceService
	 */
	public function balance() {
		return new \BITPAY\Api\Lib\Service\BalanceService();
	}

	/**
	 * @return Service\TxService
	 */
	public function tx() {
		return new \BITPAY\Api\Lib\Service\TxService();
	}

	/**
	 * @return \BITPAY\Api\Lib\Service\SysService();
	 */
	public function sys() {
		return new \BITPAY\Api\Lib\Service\SysService();
	}
}