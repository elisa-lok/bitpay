<?php
use com\IpLocation;
use think\Db;

/**
 * 记录日志
 * @param  [type] $uid         [用户id]
 * @param  [type] $username    [用户名]
 * @param  [type] $description [描述]
 * @param  [type] $status      [状态]
 * @return [type]              [description]
 */
function writeMerchantlog($uid, $username, $description, $status) {
	$data['admin_id']    = $uid;
	$data['admin_name']  = $username;
	$data['description'] = $description;
	$data['status']      = $status;
	$data['ip']          = request()->ip();
	$data['add_time']    = time();
	$log                 = Db::name('merchant_log')->insert($data);
}

function getAddressByIp($ip) {
	$Ip   = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
	$addr = $Ip->getlocation($ip);
	return $addr['country'] . $addr['area'];
}



