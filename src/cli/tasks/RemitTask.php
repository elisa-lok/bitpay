<?php

/**
 * Class RemitTask
 * @property \BITPAY\Service $service
 * @property \Redis        $redis
 * @property \Redis        $session
 */
class RemitTask extends \Phalcon\CLI\Task {
	/**
	 * 代付通知
	 */
	public function mainAction() {
		//扫描处理订单, 然后单向签名
	}
}
