<?php

namespace app\api\controller;

use think\Db;

class app {
	//h5获取设备
	public function version() {
		$data = [
			'apkVersion'     => '1.01',//版本号
			'apkPath'        => '',//下载路径
			'apkDescription' => '',//更新描述
			'lastUpdateTime' => time(),//上传更新时间
			'apkSize'        => '',//apk大小
			'isForceUpdate'  => '0',//是否强制更新 0/不强制 1/强制更新
		];
		die(json_encode($data));
	}

	//获取对应消息（每隔一分钟调用）
	public function getMessage() {
		$deviceId = $_REQUEST['device'];
		$msg      = Db::name('msg')->where(['is_read' => 0, 'device' => $deviceId])->column('messtitle, msg');
		if ($msg) {
			Db::name('msg')->where(['is_read' => 0, 'device' => $deviceId])->update(['is_read' => 1]);
			die(json_encode(['retmsg' => '成功', 'retcode' => 1, 'data' => $msg]));
		}
		die(json_encode(['retmsg' => '成功', 'retcode' => 1, 'data' => []]));
	}
}