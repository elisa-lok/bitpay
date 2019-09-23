<?php
//The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
$di = new Phalcon\DI\FactoryDefault();

// inject config
$di->setShared('config', $config);

$di->setShared('url', function () use ($config) {
	$url = new Phalcon\Mvc\Url();
	$url->setBaseUri($config->app->baseUri);
	return $url;
});

// set routers
// $di->setShared('router', function () {
// 	return include BASE_DIR . '/app/common/routes.php';
// });

// debug模式记录查询SQL日志
$di->setShared('db', function () use ($config) {
	$db = new Phalcon\Db\Adapter\Pdo\Mysql([
		'host'       => $config->dbm->host,
		'username'   => $config->dbm->username,
		'password'   => $config->dbm->password,
		'dbname'     => $config->dbm->dbname,
		'charset'    => $config->dbm->charset,
		'persistent' => $config->dbm->persistent, //使用长连接
		'options'    => [PDO::ATTR_TIMEOUT => 5],
	]);
	// 调试时候输出日志
	if ($config->debug) {
		$eventsManager = new  Phalcon\Events\Manager();
		$logger        = new Phalcon\Logger\Adapter\File(BASE_DIR . '/sql.log');
		$eventsManager->attach('db', function ($event, $connection) use ($logger) {
			if ($event->getType() == 'beforeQuery') {
				$logger->log($connection->getRealSQLStatement() . ' : ' . json_encode($connection->getSQLVariables()));
				$logger->log($connection->getErrorInfo());
				$logger->log($connection->getDescriptor());
			}
		});
		$db->setEventsManager($eventsManager);
	}
	return $db;
});

$di->setShared('dbs', function () use ($config) {
	$db = new Phalcon\Db\Adapter\Pdo\Mysql([
		'host'       => $config->dbs->host,
		'username'   => $config->dbs->username,
		'password'   => $config->dbs->password,
		'dbname'     => $config->dbs->dbname,
		'charset'    => $config->dbs->charset,
		'persistent' => $config->dbs->persistent, //使用长连接
		'options'    => [PDO::ATTR_TIMEOUT => 5],
	]);

	// 调试时候输出日志
	if ($config->debug) {
		$eventsManager = new  Phalcon\Events\Manager();
		$logger        = new Phalcon\Logger\Adapter\File(BASE_DIR . '/sql.log');
		$eventsManager->attach('db', function ($event, $connection) use ($logger) {
			if ($event->getType() == 'beforeQuery') {
				$logger->log($connection->getRealSQLStatement() . ' : ' . json_encode($connection->getSQLVariables()));
				$logger->log($connection->getErrorInfo());
				$logger->log($connection->getDescriptor());
			}
		});
		$db->setEventsManager($eventsManager);
	}
	return $db;
});

// 数据字段缓存
$di->setShared('modelsMetadata', function () use ($config) {
	return new Phalcon\Mvc\Model\MetaData\Files([
		'metaDataDir' => $config->app->metadataDir,
	]);

	/*
	return new Phalcon\Mvc\Model\MetaData\Redis([
		'host'       => $config->redis->host,
		'port'       => $config->redis->port,
		'persistent' => $config->redis->persistent,
		'statsKey'   => '_MM_',
		'lifetime'   => 3600,
		'index'      => 2,
	]);
	*/
});

/**
 * debug模式使用File存储，正式使用redis
 */
$di->setShared('cache', function () use ($config) {
	// 默认15分钟
	$frontCache = new \Phalcon\Cache\Frontend\Data(['lifetime' => 3600]);
	return new \Phalcon\Cache\Backend\Redis($frontCache, [
		'host'       => $config->redis->host,
		'port'       => $config->redis->port,
		'persistent' => $config->redis->persistent,
		'prefix'     => $config->redis->prefix,
	]);
});

// Redis
$di->setShared('redis', function () use ($config) {
	$redis = new Redis();
	$redis->pconnect($config->redis->host, $config->redis->port, $config->redis->timeout);
	return $redis;
});

// 服务
$di->setShared('s', function () {
	return new BITPAY\Api\Lib\Service();
});
// 支付
$di->setShared('pay', function () {
	return new BITPAY\Api\Lib\Pay();
});