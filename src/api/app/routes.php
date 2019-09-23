<?php

use \Phalcon\Mvc\Micro\Collection;

$app->notFound(function () {
	header('HTTP/2.0 404 Not Found');
	header('Content-type:application/json;charset=utf-8');
	echo json_encode(['code' => 404, 'msg' => 'page not found', 'data' => '']);
	die;
});

// 网银支付
$ebank = new Collection();
$ebank->setHandler('BITPAY\Api\Controllers\EBankController', true);
$ebank->post('/pay/ebank/create','create');
$app->mount($ebank);

// 快捷支付
$quick = new Collection();
$quick->setHandler('BITPAY\Api\Controllers\QuickController', true);
$quick->post('/pay/quick/create','create');
$app->mount($quick);

// wap支付
$wap = new Collection();
$wap->setHandler('BITPAY\Api\Controllers\WapController', true);
$wap->post('/pay/wap/create','create');
$app->mount($wap);

// qr支付
$qr = new Collection();
$qr->setHandler('BITPAY\Api\Controllers\QRController', true);
$qr->post('/pay/scan/create','create');
$app->mount($qr);

// 代付
$remit = new Collection();
$remit->setHandler('BITPAY\Api\Controllers\RemitController', true);
$remit->post('/remit/create','create');
$app->mount($remit);

// 查询
$query = new Collection();
$query->setHandler('BITPAY\Api\Controllers\QueryController', true);
$query->post('/query/tx/pay','pay');
$query->post('/query/tx/remit','remit');
$app->mount($query);

// 账户信息
$acc = new Collection();
$acc->setHandler('BITPAY\Api\Controllers\RemitController', true);
$acc->post('/acc/balance','balance');
$acc->post('/acc/stats','stats');
$app->mount($acc);

// TODO 测试控制器
$t = new Collection();
$t->setHandler('BITPAY\Api\Controllers\TestController', true);
$t->get('/test','index');
$app->mount($t);