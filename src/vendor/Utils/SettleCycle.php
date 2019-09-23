<?php

namespace BITPAY\Utils;

class SettleCycle {
	const SettleTime = 36000; //早上10点
	/**
	 *  登录时获取用户ip ，地区
	 */
	public static function D0() {
		return time() + 7200; //延迟2小时
	}

	public function D1(){
		return mktime(24,0,0) + self::SettleTime;
	}

	// TODO 工作日判断会因为公众假期而变化
	public function T0(){
		return date('w');
	}

	public function T1(){
		return date('w');
	}

	public function T7(){

	}

}
