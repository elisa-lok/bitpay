<?php
date_default_timezone_set('Asia/Hong_Kong'); // 设置时区

// 设置报错信息
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(-1);

define('BASE_DIR', realpath(__DIR__ . '/../../'));
define('TIMESTAMP', time());

//关闭数据库字段非空验证, 否则update和save会报错
Phalcon\Mvc\Model::setup([
	'notNullValidations'    => FALSE,
	'exceptionOnFailedSave' => TRUE,
	'ignoreUnknownColumns'  => TRUE
]);

$config = new \Phalcon\Config([
	'debug' => TRUE,
	'app'   => [
		'commonDir'      => BASE_DIR . '/app/common/',
		'controllersDir' => BASE_DIR . '/app/controllers/',
		'modelsDir'      => BASE_DIR . '/app/models/',
		'libDir'         => BASE_DIR . '/app/lib/',
		'payDir'         => BASE_DIR . '/app/pay/',
		'cacheDir'       => BASE_DIR . '/cache/',
		'metadataDir'    => BASE_DIR . '/cache/metadata/',
		'baseUri'        => '/',
	],
	'redis' => [
		'host'       => '127.0.0.1',
		'port'       => '6379',
		'timeout'    => '2',
		'persistent' => TRUE,
		'prefix'     => 'sess',
	],
	//DB master
	'dbm'   => [
		'adapter'    => 'mysql',
		'host'       => '47.100.187.93',
		'port'       => '3306',
		'username'   => 'root',
		'password'   => '4rfv(IJN',
		'dbname'     => 'xpay',
		'charset'    => 'utf8',
		'persistent' => FALSE  //TRUE, //线上开启长连接
	],
	// db slaver
	'dbs'   => [
		'adapter'    => 'mysql',
		'host'       => '47.100.187.93',
		'port'       => '3306',
		'username'   => 'root',
		'password'   => '4rfv(IJN',
		'dbname'     => 'xpay',
		'charset'    => 'utf8',
		'persistent' => FALSE  //TRUE, //线上开启长连接
	],
]);