<?php

//defined('PROJ_DIR') || define('PROJ_DIR', str_replace('\\', '/', realpath(__DIR__ . '/../../../')));
defined('PROJ_DIR') || define('PROJ_DIR', __DIR__ . '/../../');
//defined('DOMAIN') || define('DOMAIN', $_SERVER['HTTP_HOST']);
defined('DOMAIN') || define('DOMAIN', 'jdpay.com');

return new \Phalcon\Config([
	'debug'        => TRUE,
	'send_captcha' => TRUE,
	'otp_title'    => '', //身份验证
	'base'         => [
		'adminVerifyTime' => 5,      // 校验次数
		'adminFrozenTime' => 7200,  // 校验失败冻结时间
		'userVerifyTime'  => 5,    // 校验最大次数
		'userFrozenTime'  => 900,  // 校验失败冻结时间
	],
	'domain'       => [
		'home'  => DOMAIN, //主页
		'admin' => 'a.' . DOMAIN
	],
	//管理员校验
	'admin'        => [
		'sKey' => 'adminAuth.',
		'cKey' => 'adminCookies',
	],
	'application'  => [
		'baseUri'     => '/',
		'vendorDir'   => PROJ_DIR . '/vendor/',
		'modelsDir'   => PROJ_DIR . '/vendor/model/',
		'cacheDir'    => PROJ_DIR . '/cache/',
		'metaDataDir' => PROJ_DIR . '/cache/metadata/',
	],
	//主从DB
	'database'     => [
		'adapter'    => 'mysql',
		'host'       => '47.100.187.93',
		'port'       => '3306',
		'username'   => 'root',
		'password'   => '4rfv(IJN',
		'dbname'     => 'xpay',
		'charset'    => 'utf8',
		'persistent' => FALSE  //TRUE, //线上开启长连接
	],
	//redis
	'redis'        => [
		'host'       => '127.0.0.1',
		'port'       => '6379',
		'timeout'    => '2.5',
		'persistent' => TRUE,
		'prefix'     => 'sess',
	],
	'sms'          => [
		'url'       => 'https://api.mysubmail.com/message/send.json', //云通信接口
		'appid'     => '25328',                                       //appid
		'signature' => '0ba24b9330a813dff4197dbcbc79bff7',            //key 密钥
	],
	'cookies'      => [
		'prefix'   => 'pay_',
		'lifetime' => 2592000, // 30天
	],
	'session'      => [
		'uniqueId'        => 'xpay_app',
		'path'            => 'tcp://127.0.0.1:6379?weight=1&timeout=2',
		'name'            => 'sid',
		'lifetime'        => 86400,
		'cookie_lifetime' => 172800,
		'cookie_domain'   => DOMAIN,
	],
	'mail'         => [
		'prefix'  => '[JPAY即付] ',
		'accName' => 'JPAY即付',    //发送者姓名

		'host'   => 'smtp.163.com', //SMTP服务器，如smtp.163.com
		'port'   => 25, //SMTP服务器端口
		'secure' => '', //SMTP服务器端口
		'acc'    => '13046255488@163.com', //SMTP服务器的用户帐号
		'pass'   => 'A4439673123', //SMTP服务器的用户密码

		//'host'   => 'smtp.gmail.com', //SMTP服务器，如smtp.163.com
		//'port'   => 587,              //SMTP服务器端口
		//'secure' => 'tls',            //SMTP服务器端口
		//'acc'   => 'atac.vip@gmail.com', //SMTP服务器的用户帐号
		//'pass'   => 'juzoylfdgsfjpmod',   //SMTP服务器的用户密码
	],
]);