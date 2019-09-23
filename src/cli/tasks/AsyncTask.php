<?php

/**
 * Class AsyncTask
 * @property \BITPAY\Service $service
 * @property \Redis        $redis
 * @property \Redis        $session
 */
class AsyncTask extends \Phalcon\CLI\Task {
	/**
	 * 异步请求通知, 当订单完成后, 自动请求订单
	 */
	public function mainAction() {
		//扫描处理订单, 然后单向签名
	}
}
