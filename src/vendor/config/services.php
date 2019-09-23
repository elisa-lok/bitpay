<?php

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\View;

//关闭数据库字段非空验证, 否则update和save会报错
Phalcon\Mvc\Model::setup(['notNullValidations' => FALSE]);

//The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
$di = new FactoryDefault();

//inject config
$di->set('config', $config, TRUE);

//设置cookies和加密模式
$di->setShared('cookies', function () {
	$cookies = new Phalcon\Http\Response\Cookies();
	$cookies->useEncryption(FALSE); //如果为true则使用下面的crypt加密 , 如果使用加密那速度非常慢, 默认为加密
	return $cookies;
});
//cookie加密
//$di->set('crypt', function () {
//	$crypt = new Phalcon\Crypt();
//	$crypt->setKey('#1dj8$=dp?.aU//j1V$'); // Use your own key!
//	return $crypt;
//});

// The URL component is used to generate all kind of urls in the application
$di->setShared('url', function () use ($config) {
	$url = new Phalcon\Mvc\Url();
	$url->setBaseUri($config->application->baseUri);
	return $url;
});

// Setting up the view component
$di->setShared('view', function () use ($config) {
	$view = new View();
	//$view->setViewsDir($config->application->viewsDir);
	$view->registerEngines(['.phtml' => 'Phalcon\Mvc\View\Engine\Php']);
	//$view->registerEngines(['.volt' => 'voltService', '.phtml' => 'Phalcon\Mvc\View\Engine\Php']);
	// $view->setRenderLevel(1); //模板渲染级别
	$view->disable(); //模板渲染级别
	return $view;
});

// debug模式记录查询SQL日志
$di->setShared('db', function () use ($config) {
	$db = new DbAdapter([
		'host'       => $config->database->host,
		'username'   => $config->database->username,
		'password'   => $config->database->password,
		'dbname'     => $config->database->dbname,
		'charset'    => $config->database->charset,
		'persistent' => $config->database->persistent,
		'options'    => [PDO::ATTR_TIMEOUT => 5,]
	]);
	if ($config->debug) {
		$eventsManager = new EventsManager();
		$logger        = new FileLogger(PROJ_DIR . '../debug.log');
		$eventsManager->attach('db', function ($event, $connection) use ($logger) {
			if ($event->getType() == 'beforeQuery') {
				$logger->log($connection->getRealSQLStatement() . ' ' . json_encode($connection->getSQLVariables()));
				$logger->log($connection->getErrorInfo());
				$logger->log($connection->getDescriptor());
			}
		});
		$db->setEventsManager($eventsManager);
	}

	return $db;
});

// debug模式不缓存
$di->set('modelsMetadata', function () use ($config) {
	return $config->debug ? new Phalcon\Mvc\Model\MetaData\Memory() : new Phalcon\Mvc\Model\MetaData\Files(['metaDataDir' => $config->application->metaDataDir,]);
});

// 设置session, debug模式使用File存储，正式使用redis
$di->setShared('session', function () use ($config) {
	$session = new Phalcon\Session\Adapter\Redis([
		//'uniqueId'        => $config->session->uniqueId,
		'path'       => $config->session->path,
		'host'       => $config->redis->host,
		'port'       => $config->redis->port,
		'name'       => $config->session->name,
		'lifetime'   => $config->session->lifetime,
		'persistent' => FALSE,
		'prefix'     => $config->redis->prefix,
	]);
	session_set_cookie_params(86400, '/', NULL, FALSE, TRUE);
	!$session->isStarted() && $session->start();
	return $session;
});

// debug模式使用File存储，正式使用redis
$di->setShared('cache', function () use ($config) {
	// 默认15分钟
	$frontCache = new \Phalcon\Cache\Frontend\Data(["lifetime" => 900]);
	return new \Phalcon\Cache\Backend\Redis($frontCache, [
		"host"       => $config->redis->host,
		"port"       => $config->redis->port,
		'persistent' => $config->redis->persistent,
		"prefix"     => $config->redis->prefix,
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
	return new BITPAY\Service;
});