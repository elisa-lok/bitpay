<?php
$rawContent = file_get_contents('php://input');
$rawArr = json_decode($rawContent, true);
$now = date('Y-m-d H:i:s');
$notifyPath = 'notify_' . date('ymd') . '.log';
// 异步回调通知形式
if ($_POST) {
	$str = "【" . $now . "】 【FORM】\n" . var_export($_POST);
	file_put_contents($notifyPath, $str, $now);
	die;
} elseif ($rawArr) {
	$str = "【" . $now . "】 【JSON】: $rawContent";
	file_put_contents($notifyPath, $str);
	die;
}

$ct = file_get_contents($notifyPath);
echo $ct ? $ct : 'no content';
