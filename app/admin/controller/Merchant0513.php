<?php
//
// namespace app\admin\controller;
// use app\admin\model\AddressModel;
// use app\admin\model\MerchantModel;
// use app\admin\model\RechargeModel;
// use app\admin\model\TibiModel;
// use app\admin\model\WithdrawModel;
// use app\common\model\Usdt;
// use app\home\controller\Auto;
// use think\db;
// use think\Exception\DbException;
//
// class Merchant extends Base {
// 	public function index() {
// 		$key       = input('key');
// 		$reg_type  = input('reg_type');
// 		$map['id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$map['name|mobile'] = $key;
// 		}
// 		$map['reg_type'] = $reg_type;
// 		$member          = new MerchantModel();
// 		$Nowpage         = input('get.page') ? input('get.page') : 1;
// 		$limits          = config('list_rows');       // 获取总条数
// 		$count           = $member->getAllCount($map);//计算总页面
// 		$allpage         = intval(ceil($count / $limits));
// 		$lists           = $member->getMerchantByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => &$v) {
// 			$v['addtime'] = getTime($v['addtime']);
// 			$v['parent']  = $member->where('id', $v['pid'])->value('name');
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('reg_type', $reg_type);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function agentreward() {
// 		$key         = input('key');
// 		$map['a.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$uid        = Db::name('merchant')->where('name|mobile', $key)->value('id');
// 			$map['uid'] = $uid;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');            // 获取总条数
// 		$count   = $member->getAllCountAgent($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getRewardByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['create_time'] = date("Y-m-d H:i:s", $v['create_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function traderreward() {
// 		$key         = input('key');
// 		$map['a.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$uid        = Db::name('merchant')->where('name|mobile', $key)->value('id');
// 			$map['uid'] = $uid;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');            // 获取总条数
// 		$count   = $member->getAllCountAgent($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getTraderRewardByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['create_time'] = date("Y-m-d H:i:s", $v['create_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function usdtlog() {
// 		$key              = input('key');
// 		$map['coin_type'] = 0;
// 		if ($key && $key !== "") {
// 			$map['name|mobile'] = $id;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');           // 获取总条数
// 		$count   = $member->getAllCountUsdt($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getUsdtByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['create_time'] = date("Y-m-d H:i:s", $v['create_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function btclog() {
// 		$key              = input('key');
// 		$map['coin_type'] = 1;
// 		if ($key && $key !== "") {
// 			$map['name|mobile'] = $id;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');           // 获取总条数
// 		$count   = $member->getAllCountUsdt($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getUsdtByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['create_time'] = date("Y-m-d H:i:s", $v['create_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function merchant_status() {
// 		$id     = input('param.id');
// 		$status = Db::name('merchant')->where('id', $id)->value('status');//判断当前状态情况
// 		if ($status == 1) {
// 			$flag = Db::name('merchant')->where('id', $id)->setField(['status' => 0]);
// 			return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
// 		} else {
// 			$flag = Db::name('merchant')->where('id', $id)->setField(['status' => 1]);
// 			return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
// 		}
// 	}
//
// 	public function merchant_check() {
// 		$id    = input('param.id');
// 		$check = input('param.check');
// 		$user  = Db::name('merchant')->where('id', $id)->find();
// 		if ($user['reg_type'] == 1) {
// 			$update = ['reg_check' => $check];
// 		} elseif ($user['reg_type'] == 2) {
// 			$update = ['reg_check' => $check, 'trader_check' => $check == 1 ? 1 : 2];
// 		} elseif ($user['reg_type'] == 3) {
// 			//代理商
// 			for (; TRUE;) {
// 				$tradeno = tradenoa();
// 				if (!Db::name('merchant')->where('invite', $tradeno)->find()) {
// 					break;
// 				}
// 			}
// 			$update = ['reg_check' => $check, 'agent_check' => $check == 1 ? 1 : 2, 'invite' => $tradeno];
// 		} else {
// 			return json(['code' => 0, 'msg' => '用户注册类型错误']);
// 		}
// 		if (Db::name('merchant')->where('id', $id)->update($update)) {
// 			return json(['code' => 1, 'msg' => '操作成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '操作失败']);
// 		}
// 	}
//
// 	public function merchant_agent_check() {
// 		$id    = input('param.id');
// 		$check = input('param.check');
// 		if ($check == 1) {
// 			for (; TRUE;) {
// 				$tradeno = tradenoa();
// 				if (!Db::name('merchant')->where('invite', $tradeno)->find()) {
// 					break;
// 				}
// 			}
// 			$flag = Db::name('merchant')->where('id', $id)->update(['agent_check' => $check, 'invite' => $tradeno]);
// 		} else {
// 			$flag = Db::name('merchant')->where('id', $id)->setField(['agent_check' => $check]);
// 		}
// 		if ($flag) {
// 			return json(['code' => 1, 'msg' => '操作成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '操作失败']);
// 		}
// 	}
//
// 	public function merchant_trader_check() {
// 		$id    = input('param.id');
// 		$check = input('param.check');
// 		$flag  = Db::name('merchant')->where('id', $id)->setField(['trader_check' => $check]);
// 		if ($flag) {
// 			return json(['code' => 1, 'msg' => '操作成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '操作失败']);
// 		}
// 	}
//
// 	public function edit_merchant() {
// 		$member = new MerchantModel();
// 		if (request()->isAjax()) {
// 			$param = input('post.');
// 			if (empty($param['password'])) {
// 				unset($param['password']);
// 			} else {
// 				$param['password'] = md5($param['password']);
// 			}
// 			if (!empty($param['pptrader']) && is_array($param['pptrader'])) {
// 				$param['pptrader'] = implode(',', $param['pptrader']);
// 			} else {
// 				$param['pptrader'] = '';
// 			}
// 			$flag = $member->editMerchant($param);
// 			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
// 		}
// 		$id       = input('param.id');
// 		$reg_type = input('param.reg_type');
// 		$minfo    = $member->getOneByWhere($id, 'id');
// 		$pptrader = explode(',', $minfo['pptrader']);
// 		$traders  = $member->field('id, name')->where('trader_check', 1)->order('id asc')->select();
// 		foreach ($traders as $k => &$v) {
// 			if (in_array($v['id'], $pptrader)) {
// 				$v['ispp'] = 1;
// 			} else {
// 				$v['ispp'] = 0;
// 			}
// 		}
// 		$this->assign([
// 			'merchant' => $minfo,
// 			'traders'  => $traders,
// 			'reg_type' => $reg_type
// 		]);
// 		return $this->fetch();
// 	}
//
// 	public function del_merchant() {
// 		$id     = input('param.id');
// 		$member = new MerchantModel();
// 		$flag   = $member->delMerchant($id);
// 		return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
// 	}
//
// 	public function tibi() {
// 		$key                                   = input('key');
// 		$map['think_merchant_withdraw.status'] = ['egt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['merchant_id']   = $id;
// 		}
// 		$member  = new TibiModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');       // 获取总条数
// 		$count   = $member->getAllCount($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getTibiByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => &$v) {
// 			$v['addtime'] = getTime($v['addtime']);
// 			$v['endtime'] = getTime($v['endtime']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function mytest() {
// 		$m       = new AddressModel();
// 		$address = $m->getAddressByUsername('1');
// 		dump($address);
// 		echo config('user_tibi_fee');
// 		die;
// 		$method = 'getblockcount';
// 		$model  = new Usdt();
// 		$return = $model->index($method, $addr = NULL, $money = NULL, $index = NULL, $count = NULL, $skip = NULL);
// 	}
//
// 	//审核通过提币
// 	public function passTibi() {
// 		$id    = input('id');
// 		$model = new TibiModel();
// 		if (empty($id)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$find = $model->getOneByWhere($id, 'id');
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		if ($find['status'] != 0) {
// 			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
// 		}
// 		$type = input('type');//1走钱包,2不走钱包
// 		if ($type != 1 && $type != 2) {
// 			return json(['code' => 0, 'msg' => '请选择方式']);
// 		}
// 		if ($type == 1) {
// 			$model2 = new Usdt();
// 			$return = $model2->index('send', $find['address'], $find['mum'], $index = NULL, $count = NULL, $skip = NULL);
// 		} else {
// 			$return['code'] = 1;
// 			$return['data'] = '';
// 		}
// 		if ($return['code'] == 1) {
// 			/* $dbreturn = $model->editWithdraw(['id'=>$id, 'status'=>1, 'endtime'=>time(), 'txid'=>$return['data']]);
// 			if($dbreturn['code'] == 0){
// 				return json(['code'=>0, 'msg'=>'转账成功，修改订单状态失败：'.$dbreturn['msg'], 'data'=>'']);
// 			}else{
// 				$rs = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);
// 				if(!$rs){
// 					return json(['code'=>0, 'msg'=>'转账成功，扣除冻结失败', 'data'=>'']);
// 				}else{
// 					return json(['code'=>0, 'msg'=>'转账成功', 'data'=>'']);
// 				}
// 			} */
// 			$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
// 			Db::startTrans();
// 			try {
// 				$rs1 = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);//0
// 				$rs2 = $model->editWithdraw(['id' => $id, 'status' => 1, 'endtime' => time(), 'txid' => $return['data'], 'type' => $type]);
// 				//商户提币
// 				$fee = config('agent_tibi_fee');
// 				if ($merchant['pid'] && $find['fee'] && $fee) {
// 					//$fee = round($fee*$find['fee']/100, 8);
// 					$rsArr = agentReward($merchant['pid'], $find['merchant_id'], $fee, 0);
// 				} else {
// 					$rsArr[0] = 1;
// 					$rsArr[1] = 1;
// 				}
// 				if ($rs1 && $rs2['code'] == 1 && $rsArr[0] && $rsArr[1]) {
// 					// 提交事务
// 					Db::commit();
// 					//统计商户提币数量
// 					Db::name('merchant')->where('id', $find['merchant_id'])->setInc('withdraw_amount', $find['num']);
// 					return ['code' => 1, 'data' => '', 'msg' => '转账成功'];
// 				} else {
// 					// 回滚事务
// 					Db::rollback();
// 					return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败'];
// 				}
// 			} catch (DbException $e) {
// 				// 回滚事务
// 				Db::rollback();
// 				return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败:' . $e->getMessage()];
// 			}
// 		} else {
// 			return json($return);
// 		}
// 	}
//
// 	//拒绝提币
// 	public function refuseTibi() {
// 		$id    = input('id');
// 		$model = new TibiModel();
// 		if (empty($id)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$find = $model->getOneByWhere($id, 'id');
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		if ($find['status'] != 0) {
// 			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
// 		}
// 		$return = $model->cancel($id);
// 		return json($return);
// 	}
//
// 	public function merchantSet() {
// 		if (request()->isPost()) {
// 		} else {
// 			return $this->fetch();
// 		}
// 	}
//
// 	public function address() {
// 		$key                                   = input('key');
// 		$type                                  = input('addresstype');
// 		$map['think_merchant_user_address.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['merchant_id']   = $id;
// 		}
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');// 获取总条数
// 		if ($type == 2) {
// 			$count   = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->count();
// 			$allpage = intval(ceil($count / $limits));
// 			$lists   = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->page($Nowpage, $limits)->order('id desc')->select();
// 			foreach ($lists as $k => &$v) {
// 				$v['addtime']  = getTime($v['addtime']);
// 				$v['username'] = $v['name'];
// 				$v['address']  = $v['usdtb'];
// 			}
// 		} else {
// 			$member  = new AddressModel();
// 			$count   = $member->getAllCount($map);//计算总页面
// 			$allpage = intval(ceil($count / $limits));
// 			$lists   = $member->getAddressByWhere($map, $Nowpage, $limits);
// 			foreach ($lists as $k => &$v) {
// 				$v['addtime'] = getTime($v['addtime']);
// 			}
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('type', $type);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function withdrawlist() {
// 		$key                                    = input('key');
// 		$keyuser                                = input('keyuser');
// 		$status                                 = input('status');
// 		$map['think_merchant_user_withdraw.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['merchant_id']   = $id;
// 		}
// 		if ($keyuser && $keyuser !== "") {
// 			$map['username'] = $keyuser;
// 		}
// 		if (!empty($status)) {
// 			$map['think_merchant_user_withdraw.status'] = $status - 1;
// 		}
// 		$member  = new WithdrawModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');       // 获取总条数
// 		$count   = $member->getAllCount($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getWithdrawByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => &$v) {
// 			$v['addtime'] = getTime($v['addtime']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('valuser', $keyuser);
// 		$this->assign('status', $status);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	//审核通过用户提币
// 	public function passWithdraw() {
// 		$id    = input('id');
// 		$model = new WithdrawModel();
// 		if (empty($id)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$find = $model->getOneByWhere($id, 'id');
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		if ($find['status'] != 0) {
// 			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
// 		}
// 		$type = input('type');
// 		if ($type != 1 && $type != 2) {
// 			return json(['code' => 0, 'msg' => '请选择方式']);
// 		}
// 		$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
// 		$mum      = $find['num'];
// 		$fee1     = config('user_tibi_fee');
// 		$fee2     = $merchant['user_withdraw_fee'];
// 		$fee      = $fee2;
// 		if (empty($fee2)) {
// 			$fee = $fee1;
// 		}
// 		if (empty($fee)) {
// 			return json(['code' => 0, 'msg' => '用户提币手续费未设置']);
// 		}
// 		$sfee = 0;
// 		if ($fee) {
// 			$sfee = $find['num'] * $fee / 100;
// 			$mum  = $find['num'] - $sfee;
// 		}
// 		if ($merchant['usdt'] * 100000000 < $mum * 100000000) {
// 			return json(['code' => 0, 'msg' => '商户余额不足']);
// 		}
// 		if ($type == 1) {
// 			$model2 = new Usdt();
// 			$return = $model2->index('send', $find['address'], $mum, $index = NULL, $count = NULL, $skip = NULL);
// 		} else {
// 			$return['code'] = 1;
// 			$return['data'] = '';
// 		}
// 		if ($return['code'] == 1) {
// 			/* $dbreturn = $model->editWithdraw(['id'=>$id, 'status'=>1, 'endtime'=>time(), 'txid'=>$return['data']]);
// 			 if($dbreturn['code'] == 0){
// 			 return json(['code'=>0, 'msg'=>'转账成功，修改订单状态失败：'.$dbreturn['msg'], 'data'=>'']);
// 			 }else{
// 			 $rs = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);
// 			 if(!$rs){
// 			 return json(['code'=>0, 'msg'=>'转账成功，扣除冻结失败', 'data'=>'']);
// 			 }else{
// 			 return json(['code'=>0, 'msg'=>'转账成功', 'data'=>'']);
// 			 }
// 			 } */
// 			$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
// 			Db::startTrans();
// 			try {
// 				$rs1 = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdt', $find['num']);//0
// 				$rs2 = $model->editWithdraw(['id' => $id, 'status' => 1, 'endtime' => time(), 'txid' => $return['data'], 'fee' => $sfee, 'mum' => $mum, 'type' => $type]);
// 				//商户提币
// 				$feeMy = config('agent_withdraw_fee');
// 				if ($merchant['pid'] && $sfee && $feeMy) {
// 					$feeMy = round($feeMy * $sfee / 100, 8);
// 					$rsArr = agentReward($merchant['pid'], $find['merchant_id'], $feeMy, 1);
// 				} else {
// 					$rsArr[0] = 1;
// 					$rsArr[1] = 1;
// 				}
// 				if ($rs1 && $rs2['code'] == 1 && $rsArr[0] && $rsArr[1]) {
// 					// 提交事务
// 					Db::commit();
// 					//统计商户提币数量
// 					Db::name('merchant')->where('id', $find['merchant_id'])->setInc('withdraw_amount', $find['num']);
// 					return ['code' => 1, 'data' => '', 'msg' => '转账成功'];
// 				} else {
// 					// 回滚事务
// 					Db::rollback();
// 					return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败'];
// 				}
// 			} catch (DbException $e) {
// 				// 回滚事务
// 				Db::rollback();
// 				return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败:' . $e->getMessage()];
// 			}
// 		} else {
// 			return json($return);
// 		}
// 	}
//
// 	//拒绝提币
// 	public function refuseWithdraw() {
// 		$id    = input('id');
// 		$model = new WithdrawModel();
// 		if (empty($id)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$find = $model->getOneByWhere($id, 'id');
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		if ($find['status'] != 0) {
// 			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
// 		}
// 		$return = $model->editWithdraw(['id' => $id, 'status' => 2, 'endtime' => time()]);
// 		return json($return);
// 	}
//
// 	public function rechargelist() {
// 		$key                                    = input('key');
// 		$keyuser                                = input('keyuser');
// 		$status                                 = input('status');
// 		$map['think_merchant_user_recharge.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['merchant_id']   = $id;
// 		}
// 		if ($keyuser && $keyuser !== "") {
// 			$m                 = new AddressModel();
// 			$address           = $m->getAddressByUsername($keyuser);
// 			$map['to_address'] = ['in', $address];
// 		}
// 		if (!empty($status)) {
// 			$map['think_merchant_user_recharge.status'] = $status;
// 		}
// 		$member  = new RechargeModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');       // 获取总条数
// 		$count   = $member->getAllCount($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getRechargeByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => &$v) {
// 			$v['addtime'] = getTime($v['addtime']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('valuser', $keyuser);
// 		$this->assign('status', $status);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function traderrecharge() {
// 		$key = input('key');
// 		//$keyuser = input('keyuser');
// 		$status                            = input('status');
// 		$map['think_merchant_recharge.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['merchant_id']   = $id;
// 		}
// 		if (!empty($status)) {
// 			$map['think_merchant_recharge.status'] = $status - 1;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');         // 获取总条数
// 		$count   = $member->getAllCountTr($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getTraderRechargeByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('status', $status);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function adlist() {
// 		$key = input('key');
// 		//$keyuser = input('keyuser');
// 		$status                  = input('status');
// 		$map['think_ad_sell.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['userid']        = $id;
// 		}
// 		if (!empty($status)) {
// 			$map['think_ad_sell.state'] = $status;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');         // 获取总条数
// 		$count   = $member->getAllCountAd($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getAdByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['add_time'] = date("Y-m-d H:i:s", $v['add_time']);
// 			$temp                  = explode(',', $v['pay_method']);
// 			$str                   = '';
// 			if (in_array(2, $temp)) {
// 				$str .= '|银行转账';
// 			}
// 			if (in_array(3, $temp)) {
// 				$str .= '|支付宝';
// 			}
// 			if (in_array(4, $temp)) {
// 				$str .= '|微信支付';
// 			}
// 			$dealNum            = Db::name('order_buy')->where(['sell_sid' => $v['id'], 'status' => ['neq', 5], 'status' => ['neq', 9]])->sum('deal_num');
// 			$dealNum            = $dealNum ? $dealNum : 0;
// 			$lists[$k]['deal']   = $dealNum;
// 			$lists[$k]['remain'] = $v['amount'] - $lists[$k]['deal'];
// 			$lists[$k]['payway'] = $str;
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('status', $status);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function buyadlist() {
// 		$key = input('key');
// 		//$keyuser = input('keyuser');
// 		$status                 = input('status');
// 		$map['think_ad_buy.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['userid']        = $id;
// 		}
// 		if (!empty($status)) {
// 			$map['think_ad_sell.state'] = $status;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');            // 获取总条数
// 		$count   = $member->getAllCountAdBuy($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getAdBuyByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['add_time'] = date("Y-m-d H:i:s", $v['add_time']);
// 			$temp                  = explode(',', $v['pay_method']);
// 			$str                   = '';
// 			if (in_array(2, $temp)) {
// 				$str .= '|银行转账';
// 			}
// 			if (in_array(3, $temp)) {
// 				$str .= '|支付宝';
// 			}
// 			if (in_array(4, $temp)) {
// 				$str .= '|微信支付';
// 			}
// 			$dealNum            = Db::name('order_sell')->where(['buy_bid' => $v['id'], 'status' => ['neq', 5]])->sum('deal_num');
// 			$dealNum            = $dealNum ? $dealNum : 0;
// 			$lists[$k]['deal']   = $dealNum;
// 			$lists[$k]['remain'] = $v['amount'] - $lists[$k]['deal'];
// 			$lists[$k]['payway'] = $str;
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('status', $status);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function deletead() {
// 		$id   = input('id');
// 		$find = Db::name('ad_sell')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_sell')->delete($id);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '删除成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '删除失败']);
// 		}
// 	}
//
// 	public function deletebuyad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_buy')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_buy')->delete($id);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '删除成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '删除失败']);
// 		}
// 	}
//
// 	public function downad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_sell')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 2]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '下架成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '下架失败']);
// 		}
// 	}
//
// 	public function downbuyad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_buy')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '下架成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '下架失败']);
// 		}
// 	}
//
// 	public function upad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_sell')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 1]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '上架成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '上架失败']);
// 		}
// 	}
//
// 	public function upbuyad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_buy')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 1]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '上架成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '上架失败']);
// 		}
// 	}
//
// 	public function frozenad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_sell')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 4]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '冻结成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '冻结失败']);
// 		}
// 	}
//
// 	public function frozenbuyad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_buy')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 4]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '冻结成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '冻结失败']);
// 		}
// 	}
//
// 	public function unfrozenad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_sell')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 2]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '冻结成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '冻结失败']);
// 		}
// 	}
//
// 	public function unfrozenbuyad() {
// 		$id   = input('id');
// 		$find = Db::name('ad_buy')->where('id', $id)->find();
// 		if (empty($find)) {
// 			return json(['code' => 0, 'msg' => '参数错误']);
// 		}
// 		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
// 		if ($rs) {
// 			return json(['code' => 1, 'msg' => '冻结成功']);
// 		} else {
// 			return json(['code' => 0, 'msg' => '冻结失败']);
// 		}
// 	}
//
// 	public function orderlist() {
// 		$key                       = input('key');
// 		$status                    = input('status');
// 		$map['think_order_buy.id'] = ['gt', 0];
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['sell_id']       = $id;
// 		}
// 		if (!empty($status)) {
// 			$map['think_order_buy.status'] = $status - 1;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');            // 获取总条数
// 		$count   = $member->getAllCountOrder($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getOrderByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['ctime']         = date("Y-m-d H:i:s", $v['ctime']);
// 			$lists[$k]['finished_time'] = date("Y-m-d H:i:s", $v['finished_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('status', $status);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function orderlistbuy() {
// 		$key                        = input('key');
// 		$status                     = input('status');
// 		$map['think_order_sell.id'] = ['gt', 0];
// 		$reg_type                   = input('reg_type', 0);
// 		if ($reg_type) {
// 			$map['c.reg_type'] = $reg_type;
// 		}
// 		if ($key && $key !== "") {
// 			$where['name|mobile'] = $key;
// 			$id                   = Db::name('merchant')->where($where)->value('id');
// 			$map['buy_id']        = $id;
// 		}
// 		if (!empty($status)) {
// 			$map['think_order_sell.status'] = $status - 1;
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');               // 获取总条数
// 		$count   = $member->getAllCountOrderBuy($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getOrderBuyByWhere($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['ctime']         = date("Y-m-d H:i:s", $v['ctime']);
// 			$lists[$k]['finished_time'] = date("Y-m-d H:i:s", $v['finished_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('status', $status);
// 		$this->assign('reg_type', $reg_type);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	public function sssuccess() {
// 		$id        = input('post.id');
// 		$type      = input('post.type');
// 		$orderInfo = Db::name('order_buy')->where('id', $id)->find();
// 		if (!$orderInfo) {
// 			return json(['code' => 0, 'msg' => '订单不存在']);
// 		}
// 		if ($orderInfo['status'] == 4) {
// 			//return json(['code'=>0, 'msg'=>'订单已完成，请刷新']);
// 		}
// 		if ($type != 1 && $type != 2) {
// 			return json(['code' => 0, 'msg' => '回调选择错误']);
// 		}
// 		$buymerchant = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
// 		$trader      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
// 		if ($trader['usdtd'] < $orderInfo['deal_num']) {
// 			return json(['code' => 0, 'msg' => '承兑商冻结不足']);
// 		}
// 		Db::startTrans();
// 		try {
// 			$rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num']);
// 			$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 9, 'finished_time' => time()]);
// 			$rs3 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $orderInfo['deal_num']);
// 			//$rs4 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('transact', 1);
// 			//$total = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderInfo['sell_id'])->where('status', 4)->select();
// 			//$tt = $total[0]['total'];
// 			//$transact = Db::name('merchant')->where('id', $orderInfo['sell_id'])->value('transact');
// 			//$rs5 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->update(['averge'=>intval($tt/$transact)]);
// 			if ($rs1 && $rs2 && $rs3) {
// 				// 提交事务
// 				Db::commit();
// 				//请求回调接口
// 				$data['rmb']     = $orderInfo['deal_amount'];
// 				$data['amount']  = $orderInfo['deal_num'];
// 				$data['orderid'] = $orderInfo['orderid'];
// 				$data['appid']   = $buymerchant['appid'];
// 				if ($type == 1) {
// 					$status = 1;
// 				} elseif ($type == 2) {
// 					$status = 0;
// 				}
// 				$data['status'] = $status;
// 				askNotify($data, $orderInfo['notify_url'], $buymerchant['key']);
// 				$this->success('操作成功');
// 			} else {
// 				// 回滚事务
// 				Db::rollback();
// 				$this->error('操作失败');
// 			}
// 		} catch (DbException $e) {
// 			// 回滚事务
// 			Db::rollback();
// 			$this->error('操作失败，参考信息：' . $e->getMessage());
// 		}
// 	}
//
// 	/**
// 	 * 币给承兑商
// 	 * @return unknown
// 	 */
// 	public function sssuccessbuy() {
// 		$id        = input('post.id');
// 		$orderInfo = Db::name('order_sell')->where('id', $id)->find();
// 		if (!$orderInfo) {
// 			return json(['code' => 0, 'msg' => '订单不存在']);
// 		}
// 		if ($orderInfo['status'] == 4) {
// 			return json(['code' => 0, 'msg' => '订单已完成，请刷新']);
// 		}
// 		$buymerchant = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
// 		$trader      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
// 		if ($trader['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) {
// 			return json(['code' => 0, 'msg' => '商户冻结不足']);
// 		}
// 		$fee  = config('usdt_buy_trader_fee');
// 		$fee  = $fee ? $fee : 0;
// 		$sfee = $orderInfo['deal_num'] * $fee / 100;
// 		$mum  = $orderInfo['deal_num'] - $sfee;
// 		Db::startTrans();
// 		try {
// 			$rs1      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num'] + $orderInfo['fee']);
// 			$rs2      = Db::name('order_sell')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'buyer_fee' => $sfee]);
// 			$rs3      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
// 			$rs4      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('transact_buy', 1);
// 			$total    = Db::name('order_sell')->field('sum(dktime-ctime) as total')->where('buy_id', $orderInfo['buy_id'])->where('status', 4)->select();
// 			$tt       = $total[0]['total'];
// 			$transact = Db::name('merchant')->where('id', $orderInfo['buy_id'])->value('transact_buy');
// 			$rs5      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->update(['averge_buy' => intval($tt / $transact)]);
// 			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
// 				// 提交事务
// 				Db::commit();
// 				getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num'] + $orderInfo['fee']);
// 				$this->success('操作成功');
// 			} else {
// 				// 回滚事务
// 				Db::rollback();
// 				$this->error('操作失败');
// 			}
// 		} catch (DbException $e) {
// 			// 回滚事务
// 			Db::rollback();
// 			$this->error('操作失败，参考信息：' . $e->getMessage());
// 		}
// 	}
//
// 	public function ssfail() {
// 		$id        = input('post.id');
// 		$type      = input('post.type');
// 		$orderInfo = Db::name('order_buy')->where('id', $id)->find();
// 		if (!$orderInfo) {
// 			return json(['code' => 0, 'msg' => '订单不存在']);
// 		}
// 		if ($orderInfo['status'] == 4) {
// 			//return json(['code'=>0, 'msg'=>'订单已完成，请刷新']);
// 		}
// 		if ($type != 1 && $type != 2) {
// 			return json(['code' => 0, 'msg' => '回调选择错误']);
// 		}
// 		//$fee = config('trader_merchant_fee');
// 		//$fee = $fee ? $fee : 0;
// 		//$sfee = $orderInfo['deal_num']*$fee/100;
// 		$sfee        = 0;
// 		$mum         = $orderInfo['deal_num'] - $sfee;
// 		$buymerchant = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
// 		$trader      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
// 		if ($trader['usdtd'] < $orderInfo['deal_num']) {
// 			return json(['code' => 0, 'msg' => '承兑商冻结不足']);
// 		}
// 		//盘口费率
// 		$pkfee = $buymerchant['merchant_pk_fee'];
// 		$pkfee = $pkfee ? $pkfee : 0;
// 		$pkdec = $orderInfo['deal_num'] * $pkfee / 100;
// 		//平台利润
// 		$platformGet   = config('trader_platform_get');
// 		$platformGet   = $platformGet ? $platformGet : 0;
// 		$platformMoney = $platformGet * $orderInfo['deal_num'] / 100;
// 		//承兑商卖单奖励
// 		$traderGet         = $trader['trader_trader_get'];
// 		$traderGet         = $traderGet ? $traderGet : 0;
// 		$traderMoney       = $traderGet * $orderInfo['deal_num'] / 100;
// 		$traderParentMoney = $traderMParentMoney = $tpexist = $mpexist = 0;
// 		$model2            = new MerchantModel();
// 		if ($trader['pid']) {
// 			$traderP = $model2->getUserByParam($trader['pid'], 'id');
// 			if ($traderP['agent_check'] == 1 && $traderP['trader_parent_get']) {
// 				//承兑商代理利润
// 				$tpexist           = 1;
// 				$traderParentGet   = $traderP['trader_parent_get'];
// 				$traderParentGet   = $traderParentGet ? $traderParentGet : 0;
// 				$traderParentMoney = $traderParentGet * $orderInfo['deal_num'] / 100;
// 			}
// 		}
// 		if ($buymerchant['pid']) {
// 			$buymerchantP = $model2->getUserByParam($buymerchant['pid'], 'id');
// 			if ($buymerchantP['agent_check'] == 1 && $buymerchantP['trader_merchant_parent_get']) {
// 				//商户代理利润
// 				$mpexist            = 1;
// 				$traderMParentGet   = $buymerchantP['trader_merchant_parent_get'];
// 				$traderMParentGet   = $traderMParentGet ? $traderMParentGet : 0;
// 				$traderMParentMoney = $traderMParentGet * $orderInfo['deal_num'] / 100;
// 			}
// 		}
// 		//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
// 		$moneyArr           = getMoneyByLevel($pkdec, $platformMoney, $traderParentMoney, $traderMParentMoney, $traderMoney);
// 		$mum                = $mum - $pkdec;
// 		$traderParentMoney  = $moneyArr[1];
// 		$traderMParentMoney = $moneyArr[2];
// 		$traderMoney        = $moneyArr[3];
// 		Db::startTrans();
// 		try {
// 			$rs1      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num']);
// 			$rs2      = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0]]);
// 			$rs3      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
// 			$rs4      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('transact', 1);
// 			$total    = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderInfo['sell_id'])->where('status', 4)->select();
// 			$tt       = $total[0]['total'];
// 			$transact = Db::name('merchant')->where('id', $orderInfo['sell_id'])->value('transact');
// 			$rs5      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->update(['averge' => intval($tt / $transact)]);
// 			//承兑商卖单奖励
// 			$rs6 = $rs7 = $rs8 = $rs9 = $rs10 = $rs11 = TRUE;
// 			if ($traderMoney > 0) {
// 				$rs6 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $traderMoney);
// 				$rs7 = Db::name('trader_reward')->insert(['uid' => $orderInfo['sell_id'], 'orderid' => $orderInfo['id'], 'amount' => $traderMoney, 'type' => 0, 'create_time' => time()]);
// 			}
// 			//承兑商代理利润
// 			if ($traderParentMoney > 0 && $tpexist) {
// 				$rsArr = agentReward($trader['pid'], $orderInfo['sell_id'], $traderParentMoney, 3);//3
// 				$rs8   = $rsArr[0];
// 				$rs9   = $rsArr[1];
// 			}
// 			//商户代理利润
// 			if ($traderMParentMoney > 0 && $mpexist) {
// 				$rsArr = agentReward($buymerchant['pid'], $orderInfo['buy_id'], $traderMParentMoney, 4);//4
// 				$rs10  = $rsArr[0];
// 				$rs11  = $rsArr[1];
// 			}
// 			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11) {
// 				// 提交事务
// 				Db::commit();
// 				getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num']);
// 				//请求回调接口
// 				$data['amount']  = $orderInfo['deal_num'];
// 				$data['rmb']     = $orderInfo['deal_amount'];
// 				$data['orderid'] = $orderInfo['orderid'];
// 				$data['appid']   = $buymerchant['appid'];
// 				if ($type == 1) {
// 					$status = 1;
// 				} elseif ($type == 2) {
// 					$status = 0;
// 				}
// 				$data['status'] = $status;
// 				//askNotify($data, $orderInfo['notify_url'], $buymerchant['key']);
// 				$this->success('操作成功');
// 			} else {
// 				// 回滚事务
// 				Db::rollback();
// 				$this->error('操作失败');
// 			}
// 		} catch (DbException $e) {
// 			// 回滚事务
// 			Db::rollback();
// 			$this->error('操作失败，参考信息：' . $e->getMessage());
// 		}
// 	}
//
// 	/**
// 	 * 币给商户
// 	 * @return unknown
// 	 */
// 	public function ssfailbuy() {
// 		$id        = input('post.id');
// 		$orderInfo = Db::name('order_sell')->where('id', $id)->find();
// 		if (!$orderInfo) {
// 			return json(['code' => 0, 'msg' => '订单不存在']);
// 		}
// 		if ($orderInfo['status'] == 4) {
// 			return json(['code' => 0, 'msg' => '订单已完成，请刷新']);
// 		}
// 		$buymerchant = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
// 		$trader      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
// 		if ($trader['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) {
// 			return json(['code' => 0, 'msg' => '商家冻结不足']);
// 		}
// 		Db::startTrans();
// 		try {
// 			$rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num'] + $orderInfo['fee']);
// 			$rs2 = Db::name('order_sell')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time()]);
// 			$rs3 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $orderInfo['deal_num'] + $orderInfo['fee']);
// 			if ($rs1 && $rs2 && $rs3) {
// 				// 提交事务
// 				Db::commit();
// 				$this->success('操作成功');
// 			} else {
// 				// 回滚事务
// 				Db::rollback();
// 				$this->error('操作失败');
// 			}
// 		} catch (DbException $e) {
// 			// 回滚事务
// 			Db::rollback();
// 			$this->error('操作失败，参考信息：' . $e->getMessage());
// 		}
// 	}
//
// 	/*
//      * 平台统计
//      * */
// 	public function statistics() {
// 		$map['id'] = ['gt', 0];
// 		$count     = Db::name('statistics')->count();
// 		if (empty($count)) {
// 			$auto = new Auto;
// 			$auto->statistics();
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');             // 获取总条数
// 		$count   = $member->getAllCountStatistics();//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getStatistics($map, $Nowpage, $limits);
// 		foreach ($lists as $k => $v) {
// 			$lists[$k]['create_time'] = date("Y-m-d H:i:s", $v['create_time']);
// 		}
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
//
// 	/**
// 	 * 商户统计
// 	 */
// 	public function merchantstatistics() {
// 		$key       = input('key');
// 		$order2    = input('order');
// 		$map['id'] = ['gt', 0];
// 		if ($key && $key != '' && $order2 && $order2 != '') {
// 			$order[$key] = $order2;
// 		} else {
// 			$order['id'] = 'desc';
// 		}
// 		$member  = new MerchantModel();
// 		$Nowpage = input('get.page') ? input('get.page') : 1;
// 		$limits  = config('list_rows');       // 获取总条数
// 		$count   = $member->getAllCount($map);//计算总页面
// 		$allpage = intval(ceil($count / $limits));
// 		$lists   = $member->getMerchantStatistics($map, $Nowpage, $limits, $order);
// 		$this->assign('Nowpage', $Nowpage); //当前页
// 		$this->assign('allpage', $allpage); //总页数
// 		$this->assign('val', $key);
// 		$this->assign('order', $order2);
// 		if (input('get.page')) {
// 			return json($lists);
// 		}
// 		return $this->fetch();
// 	}
// }
?>