<?php

namespace app\api\controller;

use think\Db;

class app {
	//h5获取设备
	public function version() {
		die(json_encode([
			'msg'  => '成功',
			'code' => 200,
			'data'    => ''
		],320));

		die(json_encode([
			'msg'  => '成功',
			'code' => 200,
			'data'    => [
				'apkVersion'     => '2.0',//版本号
				'apkPath'        => 'https://huobi-1253283450.file.myqcloud.com/bit/ops/app/android/533/huobi-0.apk?v=1568949568127',//下载路径
				'apkDescription' => '更新一下, 提示一下',//更新描述
				'lastUpdateTime' => time(),//上传更新时间
				'apkSize'        => '410000',//apk大小
				'isForceUpdate'  => '0',//是否强制更新 0/不强制 1/强制更新
			]
		],320));
	}

	//获取对应消息（每隔一分钟调用）
	public function getMessage() {
		$params = json_decode(file_get_contents('php://input'),true);
		$msg    = Db::name('msg')->where(['is_read' => 0, 'device_id' => $params['deviceId']])->field('title AS messTitle, msg AS messContent')->select();
		if ($msg) {
			Db::name('msg')->where(['is_read' => 0, 'device_id' => $params['deviceId']])->update(['is_read' => 1]);
			die(json_encode(['msg' => '成功', 'code' => 200, 'data' => $msg],320));
		}
		die(json_encode(['msg' => '成功', 'code' => 200, 'data' => []], 320));
	}

	public function getUrl(){
		die(json_encode(['msg' => '成功', 'code' => 200, 'data' => ['url'=> 'http://zpay.cc/']], 320));
	}
}