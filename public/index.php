<?php
// 定义应用目录
const APP_PATH     = __DIR__ . '/../app/';
const UPLOAD_PATH  = __DIR__ . '/../public';   // 定义上传目录
const RUNTIME_PATH = __DIR__ . '/../runtime/'; // 定义应用缓存目录
const APP_DEBUG    = TRUE;                     // 开启调试模式
const ADMIN_KEY    = 'Jwdlh789';
error_reporting(0);
ini_set('display_errors', '0');
if (APP_DEBUG) {
	ini_set('display_errors', '1');
	error_reporting(-1);
}
// 加载框架引导文件
require __DIR__ . '/../vendor/thinkphp/start.php';