<?php

namespace app\admin\controller;

use app\home\model\OrderModel;
use think\db;
use app\admin\model\TibiModel;
use app\admin\model\AddressModel;
use app\admin\model\WithdrawModel;
use app\admin\model\RechargeModel;
use app\admin\model\MerchantModel;
use app\home\controller\Auto;
use com\IpLocation;

class Merchant extends Base {
	public function log() {
		$key = input('key');
		$map = [];
		if ($key && $key !== "") {
			$uid             = Db::name('merchant')->where('mobile|name', $key)->value('id');//dump($uid);
			$map['admin_id'] = $uid;
		}
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = Db::name('merchant_log')->where($map)->count();//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = Db::name('merchant_log')->field('ml.*, m.name, m.reg_type')->alias('ml')->join('merchant m', 'ml.admin_id=m.id', 'left')->where($map)->page($Nowpage, $limits)->order('add_time desc')->select();
		$Ip      = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
		foreach ($lists as $k => $v) {
			$lists[$k]['add_time'] = date('Y/m/d H:i:s', $v['add_time']);
			$lists[$k]['ipaddr']   = $Ip->getlocation($lists[$k]['ip']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('count', $count);
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function del_log() {
		$id = input('param.id');
		$rs = Db::name('merchant_log')->where('log_id', $id)->delete();
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除登录日志:' . $id . '成功', 1);
			return json(['code' => 1, 'data' => '', 'msg' => '删除成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除登录日志:' . $id . '失败', 0);
			return json(['code' => 0, 'data' => '', 'msg' => '删除失败']);
		}

	}

	public function index() {
		$key       = input('key');
		$reg_type  = input('reg_type');
		$map['id'] = ['gt', 0];
		if ($key && $key !== "") {
			$map['name|mobile'] = $key;
		}
		$map['reg_type'] = $reg_type;
		$member          = new MerchantModel();
		$Nowpage         = input('get.page') ? input('get.page') : 1;
		$limits          = config('list_rows');// 获取总条数
		$count           = $member->getAllCount($map);//计算总页面
		$allpage         = intval(ceil($count / $limits));
		$lists           = $member->getMerchantByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
			$v['parent']  = $member->where('id', $v['pid'])->value('name');
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('reg_type', $reg_type);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function agentreward() {
		$key         = input('key');
		$map['a.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$uid        = Db::name('merchant')->where('name|mobile', $key)->value('id');
			$map['uid'] = $uid;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountAgent($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getRewardByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function traderreward() {
		$key         = input('key');
		$map['a.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$uid        = Db::name('merchant')->where('name|mobile', $key)->value('id');
			$map['uid'] = $uid;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountAgent($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getTraderRewardByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function usdtlog() {
		$key              = input('key');
		$map['coin_type'] = 0;
		if ($key && $key !== "") {
			$map['name|mobile'] = $id;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountUsdt($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getUsdtByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function btclog() {
		$key              = input('key');
		$map['coin_type'] = 1;
		if ($key && $key !== "") {
			$map['name|mobile'] = $id;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountUsdt($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getUsdtByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function merchant_status() {
		$id     = input('param.id');
		$status = Db::name('merchant')->where('id', $id)->value('status');//判断当前状态情况
		if ($status == 1) {
			$flag = Db::name('merchant')->where('id', $id)->setField(['status' => 0]);
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】禁用商户:' . $id . '成功', 1);
			return json(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
		} else {
			$flag = Db::name('merchant')->where('id', $id)->setField(['status' => 1]);
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】启用商户:' . $id . '成功', 1);
			return json(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
		}
	}

	public function merchant_check() {
		$id    = input('param.id');
		$check = input('param.check');
		$user  = Db::name('merchant')->where('id', $id)->find();
		if ($user['reg_type'] == 1) {
			$update = ['reg_check' => $check];
		} elseif ($user['reg_type'] == 2) {
			$update = ['reg_check' => $check, 'trader_check' => $check == 1 ? 1 : 2];
		} elseif ($user['reg_type'] == 3) {
			//代理商
			for (; TRUE;) {
				$tradeno = tradenoa();
				if (!Db::name('merchant')->where('invite', $tradeno)->find()) {
					break;
				}
			}
			$update = ['reg_check' => $check, 'agent_check' => $check == 1 ? 1 : 2, 'invite' => $tradeno];
		} else {
			return json(['code' => 0, 'msg' => '用户注册类型错误']);
		}
		if (Db::name('merchant')->where('id', $id)->update($update)) {
		    if ($user['reg_type'] == 2){
                Db::name('merchant')->where('reg_type', 1)
                    ->update(['pptrader' => Db::raw("CONCAT(pptrader, ',{$id}')")]);
		    }

			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '操作成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '操作失败']);
		}
	}

	public function merchant_agent_check() {
		$id    = input('param.id');
		$check = input('param.check');
		if ($check == 1) {
			for (; TRUE;) {
				$tradeno = tradenoa();

				if (!Db::name('merchant')->where('invite', $tradeno)->find()) {
					break;
				}
			}
			$flag = Db::name('merchant')->where('id', $id)->update(['agent_check' => $check, 'invite' => $tradeno]);
		} else {
			$flag = Db::name('merchant')->where('id', $id)->setField(['agent_check' => $check]);
		}
		if ($flag) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '操作成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '操作失败']);
		}
	}

	public function merchant_trader_check() {
		$id    = input('param.id');
		$check = input('param.check');
		$flag  = Db::name('merchant')->where('id', $id)->setField(['trader_check' => $check]);
		if ($flag) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '操作成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '操作失败']);
		}
	}

	public function edit_merchant() {
        $member = new MerchantModel();

		if (request()->isAjax()) {
			$param = input('post.');
			if (empty($param['password'])) {
				unset($param['password']);
			} else {
				$param['password'] = md5($param['password']);
			}
			if (!empty($param['pptrader'])) {
				$traders_id = $member->where('trader_check', 1)->order('id asc')->column('id');
				shuffle($traders_id);
				$param['pptrader'] = implode(',', $traders_id);

			} elseif (!empty($param['pptraders']) && is_array($param['pptraders'])) {
				$param['pptrader'] = implode(',', $param['pptraders']);
			} else {
				$param['pptrader'] = '';
			}
			//20190830新增
			$user = Db::name('merchant')->where('id', $param['id'])->find();
			if ($user['usdt'] != $param['usdt']) {
				$amount = $param['usdt'] - $user['usdt'];
				if ($amount < 0) {
					$amount = abs($amount);
					$type   = 1;
				} else {
					$type = 0;
				}
				financelog($param['id'], $amount, '后台修改USDT余额', $type, session('username'));//添加日志
			}
			if ($user['usdtd'] != $param['usdtd']) {
				$amount = $param['usdtd'] - $user['usdtd'];
				if ($amount < 0) {
					$amount = abs($amount);
					$type   = 1;
				} else {
					$type = 0;
				}
				financelog($param['id'], $amount, '后台修改USDT冻结余额', $type, session('username'));//添加日志
			}

			$flag = $member->editMerchant($param);
			return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}

		$id       = input('param.id');
		$reg_type = input('param.reg_type');
		$minfo    = $member->getOneByWhere($id, 'id');
		$pptrader = explode(',', $minfo['pptrader']);
		$traders  = $member->field('id, name')->where('trader_check', 1)->order('id asc')->select();
		/*	foreach ($traders as $k => &$v) {
				if (in_array($v['id'], $pptrader)) {
					$status = 1;
				} else {
					$status = 0;
				}
			}*/
		foreach ($traders as $k => &$v) {
			if (in_array($v['id'], $pptrader)) {
				$v['ispp'] = 1;
			} else {
				$v['ispp'] = 0;
			}
		}
		$this->assign([
			'merchant' => $minfo,
			'traders'  => $traders,
			'reg_type' => $reg_type,
		]);
		return $this->fetch();
	}

	public function del_merchant() {
		$id     = input('param.id');
		$member = new MerchantModel();
		$flag   = $member->delMerchant($id);
		writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除用户:' . $id . '成功', 1);
		return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
	}

	public function tibi() {
		$key                                   = input('key');
		$map['think_merchant_withdraw.status'] = ['egt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		$member  = new TibiModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getTibiByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
			$v['endtime'] = getTime($v['endtime']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	//审核通过提币
	public function passTibi() {
		$id    = input('id');
		$model = new TibiModel();
		if (empty($id)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$find = $model->getOneByWhere($id, 'id');
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		if ($find['status'] != 0) {
			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
		}
		$type = input('type');//1走钱包,2不走钱包
		if ($type != 1 && $type != 2) {
			return json(['code' => 0, 'msg' => '请选择方式']);
		}
		if ($type == 1) {
			if (config('wallettype') == 'omni') {
				$model2 = new \app\common\model\Usdt();
				$return = $model2->index('send', $find['address'], $find['mum'], $index = NULL, $count = NULL, $skip = NULL);
			}
			if (config('wallettype') == 'erc') {//20190828增加erc提币审核
				die('1');
			}

		} else {
			$return['code'] = 1;
			$return['data'] = '';
		}
		//dump($return);die;
		if ($return['code'] == 1) {
			/* $dbreturn = $model->editWithdraw(['id'=>$id, 'status'=>1, 'endtime'=>time(), 'txid'=>$return['data']]);
            if($dbreturn['code'] == 0){
                return json(['code'=>0, 'msg'=>'转账成功，修改订单状态失败：'.$dbreturn['msg'], 'data'=>'']);
            }else{
                $rs = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);
                if(!$rs){
                    return json(['code'=>0, 'msg'=>'转账成功，扣除冻结失败', 'data'=>'']);
                }else{
                    return json(['code'=>0, 'msg'=>'转账成功', 'data'=>'']);
                }
            } */
			$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
			Db::startTrans();
			try {
				$rs1 = Db::table('think_merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);//0
				$rs2 = $model->editWithdraw(['id' => $id, 'status' => 1, 'endtime' => time(), 'txid' => $return['data'], 'type' => $type]);
				//商户提币
				$fee = config('agent_tibi_fee');
				if ($merchant['pid'] && $find['fee'] && $fee) {
					//$fee = round($fee*$find['fee']/100, 8);
					$rsarr = agentReward($merchant['pid'], $find['merchant_id'], $fee, 0);
				} else {
					$rsarr[0] = 1;
					$rsarr[1] = 1;
				}
				if ($rs1 && $rs2['code'] == 1 && $rsarr[0] && $rsarr[1]) {


					// 提交事务
					Db::commit();
					//统计商户提币数量
					Db::table('think_merchant')->where('id', $find['merchant_id'])->setInc('withdraw_amount', $find['num']);
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '成功', 1);
					financelog($find['merchant_id'], $find['num'], '提币_1', 1, session('username'));//添加日志
					return ['code' => 1, 'data' => '', 'msg' => '转账成功'];
				} else {
					// 回滚事务
					Db::rollback();
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '失败', 0);
					return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败'];
				}
			} catch (\think\Exception\DbException $e) {
				// 回滚事务
				Db::rollback();
				return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败:' . $e->getMessage()];
			}
		} else {
			return json($return);
		}
	}

	//拒绝提币
	public function refuseTibi() {
		$id    = input('id');
		$model = new TibiModel();
		if (empty($id)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$find = $model->getOneByWhere($id, 'id');
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		if ($find['status'] != 0) {
			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
		}
		$return = $model->cancel($id);
		writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】拒绝提币:' . $id . '成功', 1);
		return json($return);
	}

	public function merchantSet() {
		if (request()->isPost()) {

		} else {
			return $this->fetch();
		}
	}

	public function addresslist() {
		$key  = input('key');
		$type = input('addresstype');

		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['uid']           = $id;
		}

		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		if ($type == 0) {
			$map['status'] = 0;
		}
		if ($type == 1) {
			$map['status'] = 1;
		}
		if ($type == -1) {
			$map['status'] = ['neq', 3];
		}
		// dump($map);

		$count   = Db::name('address')->where($map)->count();
		$allpage = intval(ceil($count / $limits));
		$lists   = Db::name('address')->where($map)->page($Nowpage, $limits)->order('id desc')->select();
		foreach ($lists as $k => &$v) {
			$user = Db::name('merchant')->where(['id' => $v['uid']])->find();
			// dump($user);
			$v['addtime']  = getTime($v['addtime']);
			$v['mobile']   = ($user['mobile'] == NULL ? '未分配' : $user['mobile']);
			$v['username'] = ($user['name'] == NULL ? '未分配' : $user['name']);
			$v['type']     = strtoupper($v['type']);
			$v['status']   = ($v['status'] == 0 ? '未分配' : '已分配');

		}

		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('type', $type);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function address() {
		$key                                   = input('key');
		$type                                  = input('addresstype');
		$map['think_merchant_user_address.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		if ($type == 2) {
			$count   = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->count();
			$allpage = intval(ceil($count / $limits));
			$lists   = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->page($Nowpage, $limits)->order('id desc')->select();
			foreach ($lists as $k => &$v) {
				$v['addtime']  = getTime($v['addtime']);
				$v['username'] = $v['name'];
				$v['address']  = $v['usdtb'];
			}
		} else {
			$member  = new AddressModel();
			$count   = $member->getAllCount($map);//计算总页面
			$allpage = intval(ceil($count / $limits));
			$lists   = $member->getAddressByWhere($map, $Nowpage, $limits);
			foreach ($lists as $k => &$v) {
				$v['addtime'] = getTime($v['addtime']);
			}
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('type', $type);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function withdrawlist() {
		$key                                    = input('key');
		$keyuser                                = input('keyuser');
		$status                                 = input('status');
		$map['think_merchant_user_withdraw.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		if ($keyuser && $keyuser !== "") {
			$map['username'] = $keyuser;
		}
		if (!empty($status)) {
			$map['think_merchant_user_withdraw.status'] = $status - 1;
		}
		$member  = new WithdrawModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getWithdrawByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('valuser', $keyuser);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	//审核通过用户提币
	public function passWithdraw() {
		$id    = input('id');
		$model = new WithdrawModel();
		if (empty($id)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$find = $model->getOneByWhere($id, 'id');
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		if ($find['status'] != 0) {
			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
		}
		$type = input('type');
		if ($type != 1 && $type != 2) {
			return json(['code' => 0, 'msg' => '请选择方式']);
		}
		$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
		$mum      = $find['num'];
		$fee1     = config('user_tibi_fee');
		$fee2     = $merchant['user_withdraw_fee'];
		$fee      = $fee2;
		if (empty($fee2)) {
			$fee = $fee1;
		}
		if (empty($fee)) {
			return json(['code' => 0, 'msg' => '用户提币手续费未设置']);
		}
		$sfee = 0;
		if ($fee) {
			$sfee = $find['num'] * $fee / 100;
			$mum  = $find['num'] - $sfee;
		}

		if ($merchant['usdt'] * 100000000 < $mum * 100000000) {
			return json(['code' => 0, 'msg' => '商户余额不足']);
		}
		if ($type == 1) {
			$model2 = new \app\common\model\Usdt();
			$return = $model2->index('send', $find['address'], $mum, $index = NULL, $count = NULL, $skip = NULL);
		} else {
			$return['code'] = 1;
			$return['data'] = '';
		}
		if ($return['code'] == 1) {
			/* $dbreturn = $model->editWithdraw(['id'=>$id, 'status'=>1, 'endtime'=>time(), 'txid'=>$return['data']]);
             if($dbreturn['code'] == 0){
             return json(['code'=>0, 'msg'=>'转账成功，修改订单状态失败：'.$dbreturn['msg'], 'data'=>'']);
             }else{
             $rs = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);
             if(!$rs){
             return json(['code'=>0, 'msg'=>'转账成功，扣除冻结失败', 'data'=>'']);
             }else{
             return json(['code'=>0, 'msg'=>'转账成功', 'data'=>'']);
             }
             } */
			$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
			Db::startTrans();
			try {
				$rs1 = Db::table('think_merchant')->where('id', $find['merchant_id'])->setDec('usdt', $find['num']);//0
				$rs2 = $model->editWithdraw(['id' => $id, 'status' => 1, 'endtime' => time(), 'txid' => $return['data'], 'fee' => $sfee, 'mum' => $mum, 'type' => $type]);
				//商户提币
				$feemy = config('agent_withdraw_fee');
				if ($merchant['pid'] && $sfee && $feemy) {
					$feemy = round($feemy * $sfee / 100, 8);
					$rsarr = agentReward($merchant['pid'], $find['merchant_id'], $feemy, 1);
				} else {
					$rsarr[0] = 1;
					$rsarr[1] = 1;
				}
				if ($rs1 && $rs2['code'] == 1 && $rsarr[0] && $rsarr[1]) {
					// 提交事务
					Db::commit();
					//统计商户提币数量
					Db::table('think_merchant')->where('id', $find['merchant_id'])->setInc('withdraw_amount', $find['num']);
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '成功', 1);
					financelog($find['merchant_id'], $find['num'], '提币_1', 1, session('username'));//添加日志
					return ['code' => 1, 'data' => '', 'msg' => '转账成功'];
				} else {
					// 回滚事务
					Db::rollback();
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '失败', 0);
					return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败'];
				}
			} catch (\think\Exception\DbException $e) {
				// 回滚事务
				Db::rollback();
				return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败:' . $e->getMessage()];
			}
		} else {
			return json($return);
		}
	}

	//拒绝提币
	public function refuseWithdraw() {
		$id    = input('id');
		$model = new WithdrawModel();
		if (empty($id)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$find = $model->getOneByWhere($id, 'id');
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		if ($find['status'] != 0) {
			return json(['code' => 0, 'msg' => '状态错误：不是待审核']);
		}
		$return = $model->editWithdraw(['id' => $id, 'status' => 2, 'endtime' => time()]);
		writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】拒绝提币:' . $id . '成功', 1);
		return json($return);
	}

	public function rechargelist() {
		$key                                    = input('key');
		$keyuser                                = input('keyuser');
		$status                                 = input('status');
		$map['think_merchant_user_recharge.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		if ($keyuser && $keyuser !== "") {
			$m                 = new AddressModel();
			$address           = $m->getAddressByUsername($keyuser);
			$map['to_address'] = ['in', $address];
		}
		if (!empty($status)) {
			$map['think_merchant_user_recharge.status'] = $status;
		}
		$member  = new RechargeModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getRechargeByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('valuser', $keyuser);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function traderrecharge() {
		$key = input('key');
		$oid = input('oid');
		//$keyuser = input('keyuser');
		$status                            = input('status');
		$map['think_merchant_recharge.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		if ($oid && $oid !== "") {
			$map['to_address'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_merchant_recharge.status'] = $status - 1;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountTr($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getTraderRechargeByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['addtime'] = date("Y/m/d H:i:s", $v['addtime']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function adlist() {
		$key = input('key');
		$oid = input('oid');
		//$keyuser = input('keyuser');
		$status                  = input('status');
		$map['think_ad_sell.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['userid']        = $id;
		}
		if (!empty($status)) {
			$map['think_ad_sell.state'] = $status;
		}
		if ($oid && $oid !== "") {
			$map['ad_no'] = ['like', '%' . $oid . '%'];
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountAd($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getAdByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['add_time'] = date("Y/m/d H:i:s", $v['add_time']);
			// $temp = explode(',', $v['pay_method']);
			$str = '';
			// if(in_array(2, $temp) || $temp[0] > 4){
			//     $str.='|银行转账';
			// }
			// if(in_array(3, $temp)){
			//     $str.='|支付宝';
			// }
			// if(in_array(4, $temp)){
			//     $str.='|微信支付';
			// }

			//20190817新
			if ($v['pay_method'] > 0) {
				$str .= '银行卡';
			}
			if ($v['pay_method2'] > 0) {
				$str .= '|支付宝';
			}
			if ($v['pay_method3'] > 0) {
				$str .= '|微信';
			}
			if ($v['pay_method4'] > 0) {
				$str .= '|云闪付';
			}

			//新-结束
			$deal_num            = Db::name('order_buy')->where(['sell_sid' => $v['id'], 'status' => ['neq', 5], 'status' => ['neq', 9]])->sum('deal_num');
			$deal_num            = $deal_num ? $deal_num : 0;
			$lists[$k]['deal']   = $deal_num;
			//$lists[$k]['remain'] = $v['amount'] - $lists[$k]['deal'];
			$lists[$k]['remain'] = $v['remain_amount'];
			$lists[$k]['trading_volume'] = $v['trading_volume'];
			$lists[$k]['payway'] = $str;
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function buyadlist() {
		$key = input('key');
		$oid = input('oid');
		//$keyuser = input('keyuser');
		$status                 = input('status');
		$map['think_ad_buy.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['userid']        = $id;
		}
		if ($oid && $oid !== "") {
			$map['ad_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_ad_sell.state'] = $status;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountAdBuy($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getAdBuyByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['add_time'] = date("Y/m/d H:i:s", $v['add_time']);
			// $temp = explode(',', $v['pay_method']);
			$str = '';

			// if(in_array(2, $temp) || $temp[0] > 4){
			//     $str.='|银行转账';
			// }
			// if(in_array(3, $temp)){
			//     $str.='|支付宝';
			// }
			// if(in_array(4, $temp)){
			//     $str.='|微信支付';
			// }
			//20190817新
			if ($v['pay_method'] > 0) {
				$str .= '银行卡';
			}
			if ($v['pay_method2'] > 0) {
				$str .= '|支付宝';
			}
			if ($v['pay_method3'] > 0) {
				$str .= '|微信';
			}
			if ($v['pay_method4'] > 0) {
				$str .= '|云闪付';
			}

			//新-结束
			$deal_num            = Db::name('order_sell')->where(['buy_bid' => $v['id'], 'status' => ['neq', 5]])->sum('deal_num');
			$deal_num            = $deal_num ? $deal_num : 0;
			$lists[$k]['deal']   = $deal_num;
			$lists[$k]['remain'] = $v['amount'] - $lists[$k]['deal'];
			$lists[$k]['payway'] = $str;
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function deletead() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_sell')->delete($id);
		if ($rs) {
			return json(['code' => 1, 'msg' => '删除成功']);
		} else {
			return json(['code' => 0, 'msg' => '删除失败']);
		}
	}

	public function deletebuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_buy')->delete($id);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '删除成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '删除失败']);
		}
	}

	public function downad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '下架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '下架失败']);
		}
	}

	public function downbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '下架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '下架失败']);
		}
	}

	public function upad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 1]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '上架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '上架失败']);
		}
	}

	public function upbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 1]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '上架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '上架失败']);
		}
	}

	public function frozenad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 4]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function frozenbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 4]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function unfrozenad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function unfrozenbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		if (empty($find)) {
			return json(['code' => 0, 'msg' => '参数错误']);
		}
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			return json(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			return json(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function orderlist() {
		$key = input('key');
		$oid = input('oid');
		// dump($oid);
		$status                    = input('status');
		$map['think_order_buy.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['sell_id']       = $id;
		}
		if ($oid && $oid !== "") {
			$map['order_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_order_buy.status'] = $status - 1;
		}
		// dump($map);
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountOrder($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getOrderByWhere($map, $Nowpage, $limits);
		// dump($lists);
		$buyerIds      = array_column($lists, 'buy_id');
		$buyerUsername = Db::name('merchant')->where('id', 'in', $buyerIds)->select();
		$buyerUsername = array_column($buyerUsername, 'name', 'id');
		foreach ($lists as $k => $v) {
			$user    = Db::name('merchant')->where(['id' => $v['sell_id']])->find();
			$accuser = Db::name('merchant')->where(['id' => $user['pid']])->find();

			$lists[$k]['accuser'] = $accuser['name'] . '/' . $accuser['mobile'];
			$lists[$k]['name']    = $buyerUsername[$lists[$k]['buy_id']];
			$lists[$k]['ctime']   = date("Y/m/d H:i:s", $v['ctime']);
			if ($lists[$k]['finished_time']) {
				$lists[$k]['finished_time'] = date("Y/m/d H:i:s", $v['finished_time']);
			} else {
				$lists[$k]['finished_time'] = '无';
			}
			if (!$lists[$k]['su_reason']) {
				$lists[$k]['su_reason'] = '无';
			}
			// $str='';
			$sorder = Db::name('ad_sell')->where(['id' => $v['sell_sid']])->find();
			if ($sorder['pay_method'] > 0) {
				$bank                  = Db::name('merchant_bankcard')->where(['id' => $sorder['pay_method']])->find();
				$lists[$k]['bankinfo'] = "收款人:" . $bank['truename'] . "<br>开户行:" . $bank['c_bank'] . $bank['c_bank_detail'] . "<br>收款账号:" . $bank['c_bank_card'];
			}
			if ($sorder['pay_method2'] > 0) {
				$zfb                  = Db::name('merchant_zfb')->where(['id' => $sorder['pay_method2']])->find();
				$lists[$k]['zfbinfo'] = '/uploads/face/' . $zfb['c_bank_detail'];
				// $str.='|<a onclick="showzfb({{d[i].zfbinfo}})">支付宝</a>';
			}
			if ($sorder['pay_method3'] > 0) {
				$wx                  = Db::name('merchant_wx')->where(['id' => $sorder['pay_method3']])->find();
				$lists[$k]['wxinfo'] = '/uploads/face/' . $wx['c_bank_detail'];
			}
			if ($sorder['pay_method4'] > 0) {
				$wx                   = Db::name('merchant_ysf')->where(['id' => $sorder['pay_method4']])->find();
				$lists[$k]['ysfinfo'] = '/uploads/face/' . $wx['c_bank_detail'];
			}

			// dump($str);
			// $lists[$k]['payway'] = $str;

		}
		// dump($lists);
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function orderlistbuy() {
		$key                        = input('key');
		$oid                        = input('oid');
		$status                     = input('status');
		$map['think_order_sell.id'] = ['gt', 0];
		$reg_type                   = input('reg_type', 0);
		if ($reg_type) {
			$map['c.reg_type'] = $reg_type;
		}
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['buy_id']        = $id;
		}
		if ($oid && $oid !== "") {
			$map['order_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_order_sell.status'] = $status - 1;
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountOrderBuy($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getOrderBuyByWhere($map, $Nowpage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['ctime'] = date("Y/m/d H:i:s", $v['ctime']);
			if ($lists[$k]['finished_time']) {
				$lists[$k]['finished_time'] = date("Y/m/d H:i:s", $v['finished_time']);
			} else {
				$lists[$k]['finished_time'] = '无';
			}
			if (!$lists[$k]['su_reason']) {
				$lists[$k]['su_reason'] = '无';
			}
			if ($v['pay'] > 0) {
				$bank                  = Db::name('merchant_bankcard')->where(['id' => $v['pay']])->find();
				$lists[$k]['bankinfo'] = "收款人:" . $bank['truename'] . "<br>开户行:" . $bank['c_bank'] . $bank['c_bank_detail'] . "<br>收款账号:" . $bank['c_bank_card'];
			}
			if ($v['pay2'] > 0) {
				$zfb                  = Db::name('merchant_zfb')->where(['id' => $v['pay2']])->find();
				$lists[$k]['zfbinfo'] = '/uploads/face/' . $zfb['c_bank_detail'];
				// $str.='|<a onclick="showzfb({{d[i].zfbinfo}})">支付宝</a>';
			}
			if ($v['pay3'] > 0) {
				$wx                  = Db::name('merchant_wx')->where(['id' => $v['pay3']])->find();
				$lists[$k]['wxinfo'] = '/uploads/face/' . $wx['c_bank_detail'];
			}
		}
		// dump($lists);
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		$this->assign('reg_type', $reg_type);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	public function huitiao() {
		$id        = input('post.id');
		$type      = input('post.type');
		$orderinfo = Db::name('order_buy')->where('id', $id)->find();
		if (!$orderinfo) {
			return json(['code' => 0, 'msg' => '订单不存在']);
		}
		$buymerchant = Db::name('merchant')->where('id', $orderinfo['buy_id'])->find();
		//请求回调接口
		$data['rmb']     = $orderinfo['deal_amount'];
		$data['amount']  = $orderinfo['deal_num'];
		$data['orderid'] = $orderinfo['orderid'];
		$data['appid']   = $buymerchant['appid'];
		if ($type == 1) {
			$status = 1;
		} elseif ($type == 2) {
			$status = 0;
		}
		$data['status'] = $status;
		askNotify($data, $orderinfo['notify_url'], $buymerchant['key']);
		$this->success('操作成功');

	}

	public function sssuccess() {
		$id        = input('post.id');
		$type      = input('post.type');
		$orderinfo = Db::name('order_buy')->where('id', $id)->find();
		if (!$orderinfo) {
			return json(['code' => 0, 'msg' => '订单不存在']);
		}
		if ($orderinfo['status'] == 4) {
			//return json(['code'=>0, 'msg'=>'订单已完成，请刷新']);
		}
		if ($type != 1 && $type != 2) {
			return json(['code' => 0, 'msg' => '回调选择错误']);
		}
		$buymerchant = Db::name('merchant')->where('id', $orderinfo['buy_id'])->find();
		$trader      = Db::name('merchant')->where('id', $orderinfo['sell_id'])->find();
		if ($trader['usdtd'] < $orderinfo['deal_num']) {
			return json(['code' => 0, 'msg' => '承兑商冻结不足']);
		}
		Db::startTrans();
		try {
			$rs1 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setDec('usdtd', $orderinfo['deal_num']);
			$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 9, 'finished_time' => time()]);
			$rs3 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('usdt', $orderinfo['deal_num']);
			//$rs4 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('transact', 1);
			//$total = Db::table('think_order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderinfo['sell_id'])->where('status', 4)->select();
			//$tt = $total[0]['total'];
			//$transact = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->value('transact');
			//$rs5 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->update(['averge'=>intval($tt/$transact)]);
			if ($rs1 && $rs2 && $rs3) {
				// 提交事务
				Db::commit();
				//请求回调接口
				$data['rmb']     = $orderinfo['deal_amount'];
				$data['amount']  = $orderinfo['deal_num'];
				$data['orderid'] = $orderinfo['orderid'];
				$data['appid']   = $buymerchant['appid'];
				if ($type == 1) {
					$status = 0;
				} elseif ($type == 2) {
					$status = 0;
				}
				$data['status'] = $status;
				//askNotify($data, $orderinfo['notify_url'], $buymerchant['key']);
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】申诉订单:' . $orderinfo['id'] . '成功', 1);
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】申诉订单:' . $orderinfo['id'] . '失败', 0);
				$this->error('操作失败');
			}
		} catch (\think\Exception\DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	/**
	 * 币给承兑商
	 * @return unknown
	 */
	public function sssuccessbuy() {
		$id        = input('post.id');
		$orderinfo = Db::name('order_sell')->where('id', $id)->find();
		if (!$orderinfo) {
			return json(['code' => 0, 'msg' => '订单不存在']);
		}
		if ($orderinfo['status'] == 4) {
			return json(['code' => 0, 'msg' => '订单已完成，请刷新']);
		}
		$buymerchant = Db::name('merchant')->where('id', $orderinfo['buy_id'])->find();
		$trader      = Db::name('merchant')->where('id', $orderinfo['sell_id'])->find();
		if ($trader['usdtd'] < $orderinfo['deal_num'] + $orderinfo['fee']) {
			return json(['code' => 0, 'msg' => '商户冻结不足']);
		}
		$fee  = config('usdt_buy_trader_fee');
		$fee  = $fee ? $fee : 0;
		$sfee = $orderinfo['deal_num'] * $fee / 100;
		$mum  = $orderinfo['deal_num'] - $sfee;
		Db::startTrans();
		try {
			$rs1      = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setDec('usdtd', $orderinfo['deal_num'] + $orderinfo['fee']);
			$rs2      = Db::table('think_order_sell')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'buyer_fee' => $sfee]);
			$rs3      = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->setInc('usdt', $mum);
			$rs4      = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->setInc('transact_buy', 1);
			$total    = Db::table('think_order_sell')->field('sum(dktime-ctime) as total')->where('buy_id', $orderinfo['buy_id'])->where('status', 4)->select();
			$tt       = $total[0]['total'];
			$transact = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->value('transact_buy');
			$rs5      = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->update(['averge_buy' => intval($tt / $transact)]);
			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
				// 提交事务
				Db::commit();
				financelog($orderinfo['sell_id'], ($orderinfo['deal_num'] + $orderinfo['fee']), '卖出USDT_释放_1', 1, session('username'));//添加日志
				financelog($orderinfo['buy_id'], $mum, '买入USDT_1', 0, session('username'));//添加日志
				getStatisticsOfOrder($orderinfo['buy_id'], $orderinfo['sell_id'], $mum, $orderinfo['deal_num'] + $orderinfo['fee']);
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				$this->error('操作失败');
			}
		} catch (\think\Exception\DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	public function ssfail() {
		$id        = input('post.id');
		$type      = input('post.type');
		$amount    = input('post.amount');
		$orderinfo = Db::name('order_buy')->where('id', $id)->find();
		if (!$orderinfo) {
			return json(['code' => 0, 'msg' => '订单不存在']);
		}
		if ($orderinfo['status'] == 4) {
			//return json(['code'=>0, 'msg'=>'订单已完成，请刷新']);
		}
		if ($type != 1 && $type != 2) {
			return json(['code' => 0, 'msg' => '回调选择错误']);
		}
		if ($amount == '' || ($amount * 100 > $orderinfo['deal_amount'] * 100)) {
			return json(['code' => 0, 'msg' => '回调金额错误']);
		}
		$oldNum = $orderinfo['deal_num'];
		if ($amount * 100 != $orderinfo['deal_amount'] * 100) {
			$orderinfo['deal_num'] = round($amount / $orderinfo['deal_price'], 8);
		}
		//$fee = config('trader_merchant_fee');
		//$fee = $fee ? $fee : 0;
		//$sfee = $orderinfo['deal_num']*$fee/100;
		$sfee        = 0;
		$mum         = $orderinfo['deal_num'] - $sfee;
		$buymerchant = Db::name('merchant')->where('id', $orderinfo['buy_id'])->find();
		$trader      = Db::name('merchant')->where('id', $orderinfo['sell_id'])->find();
		if ($trader['usdtd'] < $oldNum) {
			return json(['code' => 0, 'msg' => '承兑商冻结不足']);
		}
		if ($amount * 100 > $orderinfo['deal_amount'] * 100 && $trader['usdt'] < ($orderinfo['deal_num'] - $oldNum)) {
			return json(['code' => 0, 'msg' => '承兑商余额不足']);
		}
		//盘口费率
		$pkfee = $buymerchant['merchant_pk_fee'];
		$pkfee = $pkfee ? $pkfee : 0;
		$pkdec = $orderinfo['deal_num'] * $pkfee / 100;
		//平台利润
		$platformGet   = config('trader_platform_get');
		$platformGet   = $platformGet ? $platformGet : 0;
		$platformMoney = $platformGet * $orderinfo['deal_num'] / 100;
		//承兑商卖单奖励
		$traderGet         = $trader['trader_trader_get'];
		$traderGet         = $traderGet ? $traderGet : 0;
		$traderMoney       = $traderGet * $orderinfo['deal_num'] / 100;
		$traderParentMoney = $traderMParentMoney = $tpexist = $mpexist = 0;
		$model2            = new MerchantModel();
		if ($trader['pid']) {
			$traderP = $model2->getUserByParam($trader['pid'], 'id');
			if ($traderP['agent_check'] == 1 && $traderP['trader_parent_get']) {
				//承兑商代理利润
				$tpexist           = 1;
				$traderParentGet   = $traderP['trader_parent_get'];
				$traderParentGet   = $traderParentGet ? $traderParentGet : 0;
				$traderParentMoney = $traderParentGet * $orderinfo['deal_num'] / 100;
			}
		}
		if ($buymerchant['pid']) {
			$buymerchantP = $model2->getUserByParam($buymerchant['pid'], 'id');
			if ($buymerchantP['agent_check'] == 1 && $buymerchantP['trader_merchant_parent_get']) {
				//商户代理利润
				$mpexist            = 1;
				$traderMParentGet   = $buymerchantP['trader_merchant_parent_get'];
				$traderMParentGet   = $traderMParentGet ? $traderMParentGet : 0;
				$traderMParentMoney = $traderMParentGet * $orderinfo['deal_num'] / 100;
			}
		}
		//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
		$moneyArr           = getMoneyByLevel($pkdec, $platformMoney, $traderParentMoney, $traderMParentMoney, $traderMoney);
		$mum                = $mum - $pkdec;
		$traderParentMoney  = $moneyArr[1];
		$traderMParentMoney = $moneyArr[2];
		$traderMoney        = $moneyArr[3];
		Db::startTrans();
		try {
			$rs1 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setDec('usdtd', $oldNum);
			if ($amount * 100 != $orderinfo['deal_amount'] * 100) {
				$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0], 'deal_amount' => $amount, 'deal_num' => $orderinfo['deal_num']]);
			} else {
				$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0]]);
			}
			$rs22 = TRUE;
			if ($amount * 100 > $orderinfo['deal_amount'] * 100) {
				$rs22    = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setDec('usdt', $orderinfo['deal_num'] - $oldNum);
				$samount = $orderinfo['deal_num'] - $oldNum;
			}
			if ($amount * 100 < $orderinfo['deal_amount'] * 100) {
				$rs22    = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('usdt', $oldNum - $orderinfo['deal_num']);
				$samount = $oldNum - $orderinfo['deal_num'];
			}
			$rs3      = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->setInc('usdt', $mum);
			$rs4      = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('transact', 1);
			$total    = Db::table('think_order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderinfo['sell_id'])->where('status', 4)->select();
			$tt       = $total[0]['total'];
			$transact = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->value('transact');
			$rs5      = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->update(['averge' => intval($tt / $transact)]);
			//承兑商卖单奖励
			$rs6 = $rs7 = $rs8 = $rs9 = $rs10 = $rs11 = TRUE;
			if ($traderMoney > 0) {
				$rs6 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('usdt', $traderMoney);
				$rs7 = Db::table('think_trader_reward')->insert(['uid' => $orderinfo['sell_id'], 'orderid' => $orderinfo['id'], 'amount' => $traderMoney, 'type' => 0, 'create_time' => time()]);
			}
			//承兑商代理利润
			if ($traderParentMoney > 0 && $tpexist) {
				$rsarr = agentReward($trader['pid'], $orderinfo['sell_id'], $traderParentMoney, 3);//3
				$rs8   = $rsarr[0];
				$rs9   = $rsarr[1];
			}
			//商户代理利润
			if ($traderMParentMoney > 0 && $mpexist) {
				$rsarr = agentReward($buymerchant['pid'], $orderinfo['buy_id'], $traderMParentMoney, 4);//4
				$rs10  = $rsarr[0];
				$rs11  = $rsarr[1];
			}
			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11 && $rs22) {
				// 提交事务
				Db::commit();
				financelog($orderinfo['sell_id'], $mum, '卖出USDT_释放', 0, session('username'));//添加日志
				financelog($orderinfo['buy_id'], $mum, '买入USDT_2', 0, session('username'));//添加日志
				financelog($orderinfo['sell_id'], $traderMoney, '卖出USDT_承兑商卖单奖励_2', 1, session('username'));//添加日志
				// financelog($orderinfo['buy_id'],$mum,'买入USDT',0);//添加日志
				getStatisticsOfOrder($orderinfo['buy_id'], $orderinfo['sell_id'], $mum, $orderinfo['deal_num']);
				//请求回调接口
				$data['amount'] = $orderinfo['deal_num'];
				if ($amount * 100 != $orderinfo['deal_amount'] * 100) {
					$data['rmb'] = $amount;
				} else {
					$data['rmb'] = $orderinfo['deal_amount'];
				}
				$data['orderid'] = $orderinfo['orderid'];
				$data['appid']   = $buymerchant['appid'];
				if ($type == 1) {
					$status = 1;
				} elseif ($type == 2) {
					$status = 0;
				}
				$data['status'] = $status;
				$status && askNotify($data, $orderinfo['notify_url'], $buymerchant['key']);
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				$this->error('操作失败');
			}
		} catch (\think\Exception\DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	/**
	 * 币给商户
	 */
	public function ssfailbuy() {
		$id        = input('post.id');
		$orderinfo = Db::name('order_sell')->where('id', $id)->find();
		if (!$orderinfo) {
			return json(['code' => 0, 'msg' => '订单不存在']);
		}
		if ($orderinfo['status'] == 4) {
			return json(['code' => 0, 'msg' => '订单已完成，请刷新']);
		}
		$buymerchant = Db::name('merchant')->where('id', $orderinfo['buy_id'])->find();
		$trader      = Db::name('merchant')->where('id', $orderinfo['sell_id'])->find();
		if ($trader['usdtd'] < $orderinfo['deal_num'] + $orderinfo['fee']) {
			return json(['code' => 0, 'msg' => '商家冻结不足']);
		}
		Db::startTrans();
		try {
			$rs1 = Db::table('think_merchant')->where('id', $orderinfo['buy_id'])->setDec('usdtd', $orderinfo['deal_num'] + $orderinfo['fee']);
			$rs2 = Db::table('think_order_sell')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time()]);
			$rs3 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setInc('usdt', $orderinfo['deal_num'] + $orderinfo['fee']);
			if ($rs1 && $rs2 && $rs3) {
				financelog($orderinfo['sell_id'], ($orderinfo['deal_num'] + $orderinfo['fee']), '卖出USDT_释放_3', 1, session('username'));//添加日志
				financelog($orderinfo['buy_id'], ($orderinfo['deal_num'] + $orderinfo['fee']), '买入USDT_3', 0, session('username'));//添加日志
				// 提交事务
				Db::commit();
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				$this->error('操作失败');
			}
		} catch (\think\Exception\DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	/*
     * 平台统计
     * */
	public function statistics() {
		$map['id'] = ['gt', 0];
		$count     = Db::name('statistics')->count();
		if (empty($count)) {
			$auto = new Auto;
			$auto->statistics();
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountStatistics();//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getStatistics($map, $Nowpage, $limits);

		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	/**
	 * 商户统计
	 */
	public function merchantstatistics() {
		$key       = input('key');
		$order2    = input('order');
		$map['id'] = ['gt', 0];
		if ($key && $key != '' && $order2 && $order2 != '') {
			$order[$key] = $order2;
		} else {
			$order['id'] = 'desc';
		}
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getMerchantStatistics($map, $Nowpage, $limits, $order);
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('order', $order2);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}

	/**
	 * [user_order 商户订单]
	 * @return mixed
	 */
	public function order() {
		$key = input('key');
		$oid = input('oid');
		// dump($oid);
		$status                    = input('status');
		$map['think_order_buy.id'] = ['gt', 0];
		if ($key && $key !== "") {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['sell_id']       = $id;
		}
		if ($oid && $oid !== "") {
			$map['order_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_order_buy.status'] = $status - 1;
		}
		// dump($map);
		$member  = new MerchantModel();
		$Nowpage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountOrder($map);//计算总页面
		$allpage = intval(ceil($count / $limits));
		$lists   = $member->getOrderByWhere($map, $Nowpage, $limits);
		if ($lists) {
			$buyerIds      = array_column($lists, 'buy_id');
			$buyerUsername = Db::name('merchant')->where('id', 'in', $buyerIds)->select();
			$buyerUsername = array_column($buyerUsername, 'name', 'id');
			foreach ($lists as $k => $v) {
				$lists[$k]['name']  = $buyerUsername[$lists[$k]['buy_id']];
				$lists[$k]['ctime'] = date("Y/m/d H:i:s", $v['ctime']);
				if ($lists[$k]['finished_time']) {
					$lists[$k]['finished_time'] = date("Y/m/d H:i:s", $v['finished_time']);
				} else {
					$lists[$k]['finished_time'] = '无';
				}
			}
		}
		//dump($lists);die;
		// dump($lists);
		$this->assign('Nowpage', $Nowpage); //当前页
		$this->assign('allpage', $allpage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		if (input('get.page')) {
			return json($lists);
		}
		return $this->fetch();
	}
}

?>