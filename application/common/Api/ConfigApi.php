<?php
// +----------------------------------------------------------------------
// | TwoThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.twothink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 田建龙 <864491238@qq.com>
// +----------------------------------------------------------------------
namespace app\common\Api;
class ConfigApi {
	/**
	 * 获取数据库中的配置列表
	 * @return array 配置数组
	 */
	public static function lists() {
		$data   = \think\Db::name('Config')->field('name,value')->select();
		$config = [];
		if ($data) {
			foreach ($data as $value) {
				$config[$value['name']] = $value['value'];
			}
		}
		return $config;
	}

	/**
	 * 获取数据库中的配置列表
	 * @return array 配置数组
	 */
	public static function getValue($name) {
		$map  = ['status' => 1, 'name' => $name];
		$data = \think\Db::name('Config')->where($map)->field('type,name,value')->find();
		return self::parse($data['type'], $data['value']);
	}

	/**
	 * 根据配置类型解析配置
	 * @param integer $type  配置类型
	 * @param string  $value 配置值
	 */
	private static function parse($type, $value) {
		switch ($type) {
			case 3: //解析数组
				$array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
				if (strpos($value, ':')) {
					$value = [];
					foreach ($array as $val) {
						[$k, $v] = explode(':', $val);
						$value[$k] = $v;
					}
				} else {
					$value = $array;
				}
				break;
		}
		return $value;
	}
}