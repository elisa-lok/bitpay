<?php
// 定义应用目录
const APP_PATH     = __DIR__ . '/../app/';
const UPLOAD_PATH  = __DIR__ . '/../public';   // 定义上传目录
const RUNTIME_PATH = __DIR__ . '/../runtime/'; // 定义应用缓存目录
const APP_DEBUG    = TRUE;                     // 开启调试模式
const ADMIN_KEY    = '123Abc';
error_reporting(0);
ini_set('display_errors', '0');
if (APP_DEBUG) {
	ini_set('display_errors', '1');
	error_reporting(-1);
}
ini_set('session.auto_start', 1);
ini_set('session.save_handler', 'redis');
ini_set('session.name', 'sid');
ini_set('session.save_path', 'tcp://127.0.0.1:6379?weight=1&timeout=3&read_timeout=3&persistent=1');
ini_set('session.gc_maxlifetime', 5184000);
ini_set('session.cookie_lifetime', 5184000);
ini_set('session.serialize_handler ', 'php');
// 加载框架引导文件
require __DIR__ . '/../vendor/thinkphp/start.php';