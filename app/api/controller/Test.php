<?php
namespace app\api\controller;
class Test extends Base {
	public function index() {
		$a = parse_url('https://qr.alipay.com/fkx16846wg7ln4bi7d4c0ae?t=1584840836666&p=1233');
		parse_str($a['query'], $arr);
		$a = preg_replace('/t=\d{13}/', 't={MICROTIME}', 'https://qr.alipay.com/fkx16846wg7ln4bi7d4c0ae?t=1584840836666&p=1233');
		var_dump($a);
		die;
	}

	public function t(){
		var_dump(date('Y-m-d H:i:s',strtotime('2020-06-01T23:40:28.381Z')));
	}

	public function phpinfo(){
		phpinfo();
	}
}