<?php

namespace app\api\controller;

use think\Controller;

class Redirect extends Controller {
	public function go($url){
		die('<!DOCTYPE html><html><head></head><body><script>window.location.href="'.base64_decode($url).'";</script></body></html>');
	}

	public function qr(){

	}
}