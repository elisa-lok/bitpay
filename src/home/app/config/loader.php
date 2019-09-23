<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs([])->registerNamespaces([
	'BITPAY\Home\Controllers' => $config->application->controllersDir,
	'BITPAY'                  => $config->application->vendorDir
])->register();