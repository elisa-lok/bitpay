<?php
namespace app\api\controller;
class Test extends Base{
	public function index (){
		var_dump((int)(microtime(true) * 1000));

		die;
	}

}