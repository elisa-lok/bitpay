<?php

/**
 * Class RemitTask
 * @property \BITPAY\Service $service
 * @property \Redis        $redis
 * @property \Redis        $session
 */
class MailTask extends \Phalcon\CLI\Task {
	/**
	 * 通知
	 */
	public function mainAction() {
		//扫描处理订单, 然后单向签名

	}

	/**
	 * 发送公告
	 */
	public function sendBulletinAction(){

	}
}
