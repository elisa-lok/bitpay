<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 * @property \Phalcon\Config $config
 */

$loader->registerNamespaces([
	'BITPAY\Api\Common'      => $config->app->commonDir,
	'BITPAY\Api\Lib'         => $config->app->libDir,
	'BITPAY\Api\Controllers' => $config->app->controllersDir,
	'BITPAY\Api\Model'       => $config->app->modelsDir,
	'BITPAY\Api\Lib\Pay'         => $config->app->payDir,
])->register();