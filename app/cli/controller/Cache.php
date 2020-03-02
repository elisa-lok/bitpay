<?php
namespace app\cli\controller;
class Cache extends Base {
	public function cleanAll() {
		$c = new \Redis();
		$c->flushAll();
		$c->flushDB();
	}

	public function get() {
	}
}

?>