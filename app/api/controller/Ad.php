<?php
namespace app\api\controller;
use think\Db;

/**
 * swagger: 挂单
 */
class Ad {
	/**
	 * get: 挂单列表
	 * path: list
	 * method: list
	 * param: position - {int} 挂单位
	 */
	public function list($position) {
		$map['ad_position_id'] = $position;
		$map['status']         = 1;
		$info                  = Db::name('ad')->where($map)->order('id DESC')->field('images')->select();
		if ($info) {
			$data['code']  = 200;
			$data['datas'] = $info;
			$data['msg']   = '获取挂单列表成功';
			return json($data);
		} else {
			$data['code'] = 404;
			$data['msg']  = '获取挂单列表失败';
			return json($data);
		}
	}
}