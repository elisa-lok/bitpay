<?php

namespace BITPAY\Api\Common;

class Filter{

	public function AlphaNum($str) {
		return preg_replace('/[\W]/', '', $str);
	}

	public function Md5($str){
		return preg_replace('/[^0-9a-f]/', '', $str);
	}

	public function Int($str){
		return preg_replace('/[\D]/', '', $str);
	}

	public function txEBank($data){

	}

	public function txQuick($data){

	}

	public function txScan($data){

	}

	public function txWap($data){

	}

	public function txRemit($data){

	}

	public function txQuery($data){

	}
}