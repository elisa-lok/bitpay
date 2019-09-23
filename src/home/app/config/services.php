<?php

use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

include __DIR__ . '/../../../vendor/config/services.php';

/* @var $di Phalcon\DI\FactoryDefault */
$di->setShared('router', function () {
	return include __DIR__ . '/routes.php';
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($config) {
	$view = new Phalcon\Mvc\View;
	$view->setViewsDir($config->application->viewsDir);
	$view->registerEngines(['.phtml' => 'Phalcon\Mvc\View\Engine\Php',]);
	return $view;
});

// 设置session, debug模式使用File存储，正式使用redis
$di->setShared('session', function () use ($config) {
	$session = new Phalcon\Session\Adapter\Redis([
		//'uniqueId'   => $config->session->uniqueId, // 唯一ID
		'path'       => $config->session->path,
		//'host'       => $config->redis->host,
		//'port'       => $config->redis->port,
		'name'       => $config->session->name,
		'lifetime'   => $config->session->lifetime,
		'persistent' => FALSE,
		'prefix'     => $config->redis->prefix
	]);

	session_set_cookie_params(86400, '/', NULL, FALSE, TRUE);
	!$session->isStarted() && $session->start();
	return $session;
});

$di->setShared('dispatcher', function () {
	//Create an EventsManager
	$eventsManager = new EventsManager();
	//Attach a listener
	$eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
		//Handle 404 exceptions
		if ($exception instanceof DispatchException) {
			// 404 页面
			$dispatcher->forward([
				'controller' => 'err',
				'action'     => 'notFound',
				'params'     => ['message' => $exception->getMessage()],
			]);
			return FALSE;
		}
	});

	$dispatcher = new MvcDispatcher();
	$dispatcher->setDefaultNamespace('\BITPAY\Home\Controllers');
	//Bind the EventsManager to the dispatcher
	$dispatcher->setEventsManager($eventsManager);
	return $dispatcher;
});