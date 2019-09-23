<?php


use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter; //    Phalcon\Session\Adapter\Files as SessionAdapterFiles,
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger, Phalcon\Logger\Adapter\File as FileLogger;


date_default_timezone_set('Asia/Shanghai');
ini_set('display_errors', '1');
error_reporting(-1);
set_time_limit(0);
ignore_user_abort(true);


//关闭非空验证, 否则update和save会报错
Phalcon\Mvc\Model::setup(['notNullValidations' => false]);

//添加设置
$config = include __DIR__ . '/../vendor/config/config.php';

$loader = new Phalcon\Loader();
$loader->registerDirs([
	PROJ_DIR . '/cli/tasks',
	PROJ_DIR . '/vendor',
])->registerNamespaces([
	'BITPAY' => $config->application->vendorDir,
])->register();

//inject config
$di = new Phalcon\DI\FactoryDefault\CLI();
$di->set('config', $config, true);
$di->set('request', 'Phalcon\Http\Request');

/**
 * 注入數據庫, debug模式记录查询SQL日志
 */
$di->setShared('db', function () use ($config) {
	$db = new DbAdapter([
		'host'       => $config->database->host,
		'username'   => $config->database->username,
		'password'   => $config->database->password,
		'dbname'     => $config->database->dbname,
		'charset'    => $config->database->charset,
		'persistent' => $config->database->persistent, //使用长连接
		'options'    => [
			PDO::ATTR_TIMEOUT => 1,
			// PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		],
	]);
	//記錄sql語句
	if ($config->debug) {
		$eventsManager = new EventsManager();
		$path          = PROJ_DIR . '/log/' . date('Ymd') . '_cli_sql.log';
		$logger        = new FileLogger($path);
		@chgrp($path, 'www-data');
		@chown($path, 'www-data');
		@chmod($path, 0755);
		$eventsManager->attach('db', function ($event, $connection) use ($logger) {
			if ($event->getType() == 'beforeQuery') {  //查询后afterQuery , 查询前beforeQuery
				$logger->log($connection->getSQLStatement(), Logger::INFO);
			}
		});
		$db->setEventsManager($eventsManager);
	}

	return $db;
});
/**
 * debug模式使用File存储，正式使用redis
 */
$di->setShared('cache', function () use ($config) {
	// 默认15分钟
	$frontCache = new \Phalcon\Cache\Frontend\Data(['lifetime' => 900]);

	return new \Phalcon\Cache\Backend\Redis($frontCache, [
		'host'    => $config->redis->host,
		'port'    => $config->redis->port,
		'timeout' => $config->redis->timeout,
		'prefix'  => $config->redis->prefix,
	]);
});

// Redis
$di->setShared('redis', function () use ($config) {
	$redis = new Redis();
	$redis->pconnect($config->redis->host, $config->redis->port, $config->redis->timeout);
	return $redis;
});

$di->setShared('s', function () {
	return new BITPAY\Service;
});

//從庫
$di->setShared('dbWrite', function () use ($config) {
	$db = new DbAdapter([
		'host'       => $config->dbWrite->host,
		'username'   => $config->dbWrite->username,
		'password'   => $config->dbWrite->password,
		'dbname'     => $config->dbWrite->dbname,
		'charset'    => $config->dbWrite->charset,
		'persistent' => $config->dbWrite->persistent, //使用长连接
		'options'    => [
			PDO::ATTR_TIMEOUT => '1',
			// PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
		],
	]);

	if ($config->debug) {
		$eventsManager = new EventsManager();
		$path          = PROJ_DIR . '/log/' . date('Ymd') . '_cli_sql.log';
		$logger        = new FileLogger($path);
		chgrp($path, 'www-data');
		chown($path, 'www-data');
		chmod($path, 0755);
		$eventsManager->attach('db', function ($event, $connection) use ($logger) {
			if ($event->getType() == 'beforeQuery') {
				$logger->log($connection->getSQLStatement(), Logger::INFO);
			}
		});
		$db->setEventsManager($eventsManager);
	}

	return $db;
});

//注入服务
$di->setShared('service', function () {
	return new BITPAY\Service();
});

//设置console
$console = new Phalcon\CLI\Console();
$console->setDI($di);
$di->set('console', $console, true);

$arguments = [];
foreach ($argv as $k => $arg) {
	if ($k == 1) {
		$arguments['task'] = $arg;
	} elseif ($k == 2) {
		$arguments['action'] = $arg;
	} elseif ($k >= 3) {
		$arguments['params'][] = $arg;
	}
}

try {
	$console->handle($arguments);
} catch (Phalcon\Exception $e) {
	echo $e->getMessage();
	exit(255);
}
