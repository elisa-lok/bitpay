<?php

/**
 * Class MainTask
 * @property \BITPAY\Service $service
 * @property \Redis        $redis
 * @property \Redis        $session
 */
class MainTask extends \Phalcon\CLI\Task {
	public function mainAction() {
		$this->console->handle(['task' => 'main', 'action' => 'provincestats']);
	}

	public function provinceStatsAction() {
		echo "qeqwrqweqwe";
	}
}
