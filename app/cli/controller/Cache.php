<?php
namespace app\cli\controller;
class Cache extends Base {
	public function clean() {
		$c = new \Redis();
		$c->flushAll();
		$c->flushDB();
	}

	public function get() {
	}
}

?>