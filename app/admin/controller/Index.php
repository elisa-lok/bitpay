<?php
namespace app\admin\controller;
use app\common\model\Usdt;
use think\Db;

class Index extends Base {
	public function index() {
		return $this->fetch('/index');
	}

	public function addaddress() {
		// $wallettype=config('wallettype');
		// if($wallettype=='omni'){
		$model2 = new Usdt();
		for ($i = 0; $i < 10; $i++) {
			$return = $model2->index('getnewaddress', $addr = NULL, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
			if ($return['code'] == 1 && !empty($return['data'])) {
				$map['address'] = $return['data'];
				$map['addtime'] = time();
				$map['status']  = 0;
				$map['type']    = 'btc';
				$rs[]           = Db::name('address')->insert($map);
			}
		}
		// }
		// if($wallettype=='erc'){
		//       $model2 = new \app\common\model\Eth();
		//       for ($i=0; $i <10 ; $i++) {
		//           $password=time().rand(10000,99999);
		//          $return = $model2->index('getnewaddress', $addr = null, $mum = null, $index=null, $count=null,$skip=null,$password);
		//
		//           if($return['code'] == 1 && $return['data']!=false){
		//               $map['address']=$return['data'];
		//               $map['addtime']=time();
		//               $map['status']=0;
		//               $map['password']=$password;
		//               $map['type']='eth';
		//               $rs[] = Db::name('address')->insert($map);
		//           }else{
		//                return json(['code' => -1, 'url' => '', 'msg' => '钱包链接失败!']);
		//           }
		//       }
		// }
		// if($wallettype=='all'){
		//        $model2 = new \app\common\model\Usdt();
		//       for ($i=0; $i <10 ; $i++) {
		//          $return = $model2->index('getnewaddress', $addr = null, $mum = null, $index=null, $count=null,$skip=null);
		//           if($return['code'] == 1 && !empty($return['data'])){
		//               $map['address']=$return['data'];
		//               $map['addtime']=time();
		//               $map['status']=0;
		//               $map['type']='btc';
		//               $rs[] = Db::name('address')->insert($map);
		//           }
		//       }
		// }
		if ($rs) {
			return json(['code' => 1, 'url' => '', 'msg' => '生成成功!']);
		} else {
			return json(['code' => -1, 'url' => '', 'msg' => '生成失败,请检查钱包链接参数']);
		}
	}

	public function addaddresseth() {
		$data              = input('post.');
		$prive             = explode('0x', $data['prive']);
		$map['address']    = strtolower($data['addr']);
		$map['addtime']    = time();
		$map['status']     = 0;
		$map['type']       = 'eth';
		$map['privatekey'] = $prive[1];
		$rs[]              = Db::name('address')->insert($map);
		if ($rs) {
			return json(['code' => 1, 'url' => '', 'msg' => '生成成功!']);
		} else {
			return json(['code' => -1, 'url' => '', 'msg' => '生成失败,请检查钱包链接参数']);
		}
	}

	/**
	 * [indexPage 后台首页]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function indexPage() {
		//今日新增会员
		$today              = strtotime(date('Y-m-d 00:00:00'));//今天开始日期
		$map['create_time'] = ['egt', $today];
		$info               = [
				'web_server' => $_SERVER['SERVER_SOFTWARE'],
				'onload'     => ini_get('upload_max_filesize'),
				'think_v'    => THINK_VERSION,
				'phpversion' => phpversion(),
		];
		//统计充值数量
		$beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
		$endThismonth   = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
		$timestamp      = time();
		$beginWeek      = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));
		$endWeek        = strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))) + 24 * 3600 - 1;
		$beginToday     = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$endToday       = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
		//echo '月开始：'.$beginThismonth.'月结束：'.$endThismonth.'周开始：'.$beginWeek.'周结束：'.$endWeek.'日开始：'.$beginToday.'日结束：'.$endToday;
		$rechargeMap['status']  = 1;
		$rechargeMap['addtime'] = ['between', [$beginThismonth, $endThismonth]];
		//$recharge = Db::name('merchant_user_recharge')->where('status=1')->sum('num');
		$rechargeMonth = getTotalInfo($rechargeMap, 'merchant_user_recharge', 'num');
		//承兑商充值
		$rechargeMonth2 = getTotalInfo($rechargeMap, 'merchant_recharge', 'num');
		$this->assign('rechargeMonth', $rechargeMonth + $rechargeMonth2);
		$rechargeMap['addtime'] = ['between', [$beginWeek, $endWeek]];
		$rechargeWeek           = getTotalInfo($rechargeMap, 'merchant_user_recharge', 'num');
		$rechargeWeek2          = getTotalInfo($rechargeMap, 'merchant_recharge', 'num');
		$this->assign('rechargeWeek', $rechargeWeek + $rechargeWeek2);
		$rechargeMap['addtime'] = ['between', [$beginToday, $endToday]];
		$rechargeToday          = getTotalInfo($rechargeMap, 'merchant_user_recharge', 'num');
		$rechargeToday2         = getTotalInfo($rechargeMap, 'merchant_recharge', 'num');
		$this->assign('rechargeToday', $rechargeToday);
		//统计提币数量，商家提币加用户提币
		//商家提币
		$merchantTiMap['status']  = 1;
		$merchantTiMap['endtime'] = ['between', [$beginThismonth, $endThismonth]];
		$merchantTi               = getTotalInfo($merchantTiMap, 'merchant_withdraw', 'mum');
		$userTi                   = getTotalInfo($merchantTiMap, 'merchant_user_withdraw', 'mum');
		$tiMonth                  = $merchantTi + $userTi;
		$this->assign('tiMonth', $tiMonth);
		$merchantTiMap['endtime'] = ['between', [$beginWeek, $endWeek]];
		$merchantTi               = getTotalInfo($merchantTiMap, 'merchant_withdraw', 'mum');
		$userTi                   = getTotalInfo($merchantTiMap, 'merchant_user_withdraw', 'mum');
		$tiWeek                   = $merchantTi + $userTi;
		$this->assign('tiWeek', $tiWeek);
		$merchantTiMap['endtime'] = ['between', [$beginToday, $endToday]];
		$merchantTi               = getTotalInfo($merchantTiMap, 'merchant_withdraw', 'mum');
		$userTi                   = getTotalInfo($merchantTiMap, 'merchant_user_withdraw', 'mum');
		$tiToday                  = $merchantTi + $userTi;
		$this->assign('tiToday', $tiToday);
		//统计手续费数量，用户充值手续费+用户提币手续费+商户提币手续费+场外交易商户手续费
		$feeMap['status']         = 1;
		$feeMap['addtime']        = ['between', [$beginThismonth, $endThismonth]];
		$fee1                     = getTotalInfo($feeMap, 'merchant_user_recharge', 'fee');
		$feeMap2['status']        = 1;
		$feeMap2['endtime']       = ['between', [$beginThismonth, $endThismonth]];
		$fee2                     = getTotalInfo($feeMap2, 'merchant_user_withdraw', 'fee');
		$fee3                     = getTotalInfo($feeMap2, 'merchant_withdraw', 'fee');
		$feeMap3['status']        = 4;
		$feeMap3['finished_time'] = ['between', [$beginThismonth, $endThismonth]];
		$fee4                     = getTotalInfo($feeMap3, 'order_sell', 'fee');
		$feeMonth                 = $fee1 + $fee2 + $fee3 + $fee4;
		$this->assign('feeMonth', $feeMonth);
		$feeMap['addtime']        = ['between', [$beginWeek, $endWeek]];
		$fee1                     = getTotalInfo($feeMap, 'merchant_user_recharge', 'fee');
		$feeMap2['status']        = 1;
		$feeMap2['endtime']       = ['between', [$beginWeek, $endWeek]];
		$fee2                     = getTotalInfo($feeMap2, 'merchant_user_withdraw', 'fee');
		$fee3                     = getTotalInfo($feeMap2, 'merchant_withdraw', 'fee');
		$feeMap3['status']        = 4;
		$feeMap3['finished_time'] = ['between', [$beginWeek, $endWeek]];
		$fee4                     = getTotalInfo($feeMap3, 'order_sell', 'fee');
		$feeWeek                  = $fee1 + $fee2 + $fee3 + $fee4;
		$this->assign('feeWeek', $feeWeek);
		$feeMap['addtime']        = ['between', [$beginToday, $endToday]];
		$fee1                     = getTotalInfo($feeMap, 'merchant_user_recharge', 'fee');
		$feeMap2['status']        = 1;
		$feeMap2['endtime']       = ['between', [$beginToday, $endToday]];
		$fee2                     = getTotalInfo($feeMap2, 'merchant_user_withdraw', 'fee');
		$fee3                     = getTotalInfo($feeMap2, 'merchant_withdraw', 'fee');
		$feeMap3['status']        = 4;
		$feeMap3['finished_time'] = ['between', [$beginToday, $endToday]];
		$fee4                     = getTotalInfo($feeMap3, 'order_sell', 'fee');
		$feeToday                 = $fee1 + $fee2 + $fee3 + $fee4;
		$this->assign('feeToday', $feeToday);
		//统计地址生成数量
		$addressMap['addtime'] = ['between', [$beginThismonth, $endThismonth]];
		$addressMonth          = Db::name('merchant_user_address')->where($addressMap)->count();
		$address2              = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->where($addressMap)->count();
		$this->assign('addressMonth', $addressMonth + $address2);
		$addressMap['addtime'] = ['between', [$beginWeek, $endWeek]];
		$addressWeek           = Db::name('merchant_user_address')->where($addressMap)->count();
		$address2              = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->where($addressMap)->count();
		$this->assign('addressWeek', $addressWeek + $address2);
		$addressMap['addtime'] = ['between', [$beginToday, $endToday]];
		$addressToday          = Db::name('merchant_user_address')->where($addressMap)->count();
		$alladdress1           = Db::name('address')->where(['status' => 0, 'type' => 'btc'])->count();
		$alladdress2           = Db::name('address')->where(['status' => 0, 'type' => 'eth'])->count();
		$address2              = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->where($addressMap)->count();
		$this->assign('addressToday', $addressToday + $address2);
		$this->assign('alladdress1', $alladdress1);
		$this->assign('alladdress2', $alladdress2);
		//usdt总量
		//$address = Db::name('merchant_user_address')->column('address');
		/*$model = new \app\common\model\Usdt();
		$addressarray = $model->index('getAllAddress', $address = null, $money = null, $index=null, $count=null, $skip=null);
		$usdt = 0;
		foreach($addressarray['data'] as $k=>$v){
			$temp = array();
			if(!empty($v)){
				$temp = $model->index('getbalance', $v, $money = null, $index=null, $count=null, $skip=null);
				if($temp['code'] == 1 && is_numeric($temp['data'])){
					$usdt += $temp['data'];
				}
			}
		}
		$baseusdt = $model->index('getbalance', '1PnhPBJ6JiAFuw5HFSpjuugci7SJjo179U', $money = null, $index=null, $count=null, $skip=null);
		$temp2 = $model->index('getbtcbalance', $v, $money = null, $index=null, $count=null, $skip=null);
		$this->assign('usdt', $usdt+$baseusdt['data']);
		$baseusdt2 = $model->index('getBtcBalance', '1PnhPBJ6JiAFuw5HFSpjuugci7SJjo179U', $money = null, $index=null, $count=null, $skip=null);
		$this->assign('btc', $temp2 ['data']);*/
		$usdt = Db::name('coin_log')->where('coin_type', 0)->order('id DESC')->limit(1)->value('balance');
		$this->assign('usdt', $usdt);
		$btc = Db::name('coin_log')->where('coin_type', 1)->order('id DESC')->limit(1)->value('balance');
		$this->assign('btc', $btc);
		$this->assign('info', $info);
		return $this->fetch('index');
	}

	public function getUsdt() {
		if (request()->isPost()) {
			$model        = new Usdt();
			$addressarray = $model->index('getAllAddress', $address = NULL, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
			$usdt         = 0;
			foreach ($addressarray['data'] as $k => $v) {
				$temp = [];
				if (!empty($v)) {
					$temp = $model->index('getbalance', $v, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
					if ($temp['code'] == 1 && is_numeric($temp['data'])) {
						$usdt += $temp['data'];
					}
				}
			}
			$base_address = Db::name('config')->where('name', 'base_address')->value('value');
			//$baseusdt = $model->index('getbalance', '$1PnhPBJ6JiAFuw5HFSpjuugci7SJjo179U', $money = null, $index=null, $count=null, $skip=null);
			$baseusdt = $model->index('getbalance', $base_address, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
			Db::name('coin_log')->insert(['admin_id' => $this->uid, 'balance' => $usdt + $baseusdt['data'], 'create_time' => time()]);
			//$base_address = Db::name('config')->where('name', 'base_address')->value('value');
			$this->success($usdt + $baseusdt['data']);
		}
	}

	public function getBtc() {
		if (request()->isPost()) {
			$model = new Usdt();
			$temp2 = $model->index('getbtcbalance', $address = NULL, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
			Db::name('coin_log')->insert(['admin_id' => $this->uid, 'balance' => $temp2['data'], 'create_time' => time(), 'coin_type' => 1]);
			$this->success($temp2['data']);
		}
	}

	public function getbalancebtc() {
		if (request()->isPost()) {
			$address = input('post.address');
			dump($address);
			$model = new Usdt();
			$usdt  = $model->index('getbalance', $address, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
			$btc   = $model->index('getbtcbalance', $address, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
			$msg   = 'BTC:' . $btc['data'] . ' / USDT:' . $usdt['data'];
			$this->success($msg);
		}
	}

	/**
	 * [userEdit 修改密码]
	 * @return [type] [description]
	 * @author [田建龙] [864491238@qq.com]
	 */
	public function editpwd() {
		if (request()->isAjax()) {
			$param = input('post.');
			$user  = Db::name('admin')->where('id=' . $this->uid)->find();
			if (md5(md5($param['old_password']) . config('auth_key')) != $user['password']) {
				return json(['code' => -1, 'url' => '', 'msg' => '旧密码错误']);
			} else {
				$pwd['password'] = md5(md5($param['password']) . config('auth_key'));
				Db::name('admin')->where('id=' . $user['id'])->update($pwd);
				session(NULL);
				cache('db_config_data', NULL);//清除缓存中网站配置信息
				return json(['code' => 1, 'url' => 'index/index', 'msg' => '密码修改成功']);
			}
		}
		return $this->fetch();
	}

	/**
	 * 清除缓存
	 */
	public function clear() {
		if (delete_dir_file(CACHE_PATH) && delete_dir_file(TEMP_PATH)) {
			return json(['code' => 1, 'msg' => '清除缓存成功']);
		} else {
			return json(['code' => 0, 'msg' => '清除缓存失败']);
		}
	}
}
