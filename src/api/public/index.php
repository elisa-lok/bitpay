<?php

try {
	// 这里必须按顺序 config > loader >services > micro > routes
	include __DIR__ . '/../app/common/config.php';   // 载入config
	include BASE_DIR . '/app/common/loader.php';     // 载入loader
	include BASE_DIR . '/app/common/di.php';         // 载入$di

	$app = new Phalcon\Mvc\Micro($di);
	include BASE_DIR . '/app/routes.php'; // 载入routes
	$app->handle();
} catch (\Exception $e) {
	header('HTTP/2.0 500 Internal Server Error');
	header('Content-type:application/json;charset=utf-8');
	echo json_encode(['code' => 50000, 'msg' => $e->getMessage(), 'data' => '']);
	$err = "\n********************************************************\n[T] \t" . date('Y-m-d H:i:s') . "\n[F] \t" . $e->getFile() . ' : ' . $e->getLine() . "\n[C] \t" . $e->getCode() . "\n[M] \t" . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
	file_put_contents(BASE_DIR . '/errors.log', $err, FILE_APPEND);
}