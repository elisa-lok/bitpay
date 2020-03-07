<?php
use think\Db;

function writeMerchantLog($uid, $username, $description, $status) {
	$data['admin_id']    = $uid;
	$data['admin_name']  = $username;
	$data['description'] = $description;
	$data['status']      = $status;
	$data['ip']          = request()->ip();
	$data['add_time']    = time();
	$log                 = Db::name('merchant_log')->insert($data);
}