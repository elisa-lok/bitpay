<?php
namespace app\admin\controller;
use app\admin\model\AddressModel;
use app\admin\model\MerchantModel;
use app\admin\model\RechargeModel;
use app\admin\model\TibiModel;
use app\admin\model\WithdrawModel;
use app\common\model\Usdt;
use app\home\controller\Auto;
use com\IpLocation;
use think\Cache;
use think\db;
use think\Exception\DbException;

class Merchant extends Base {
	public function log() {
		$key = input('key');
		$map = [];
		if ($key && $key !== '') {
			$uid             = Db::name('merchant')->where('mobile|name', $key)->value('id');
			$map['admin_id'] = $uid;
		}
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');                           // 获取总条数
		$count   = Db::name('merchant_log')->where($map)->count();//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = Db::name('merchant_log')->field('ml.*, m.name, m.reg_type')->alias('ml')->join('merchant m', 'ml.admin_id=m.id', 'left')->where($map)->page($nowPage, $limits)->order('add_time desc')->select();
		$Ip      = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
		foreach ($lists as $k => $v) {
			$lists[$k]['add_time'] = date('Y/m/d H:i:s', $v['add_time']);
			$lists[$k]['ipaddr']   = $Ip->getlocation($lists[$k]['ip']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('count', $count);
		$this->assign('val', $key);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function del_log() {
		$id = input('param.id');
		$rs = Db::name('merchant_log')->where('log_id', $id)->delete();
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除登录日志:' . $id . '成功', 1);
			showJson(['code' => 1, 'data' => '', 'msg' => '删除成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除登录日志:' . $id . '失败', 0);
			showJson(['code' => 0, 'data' => '', 'msg' => '删除失败']);
		}
	}

	public function index() {
		$key       = input('key');
		$regType   = input('reg_type');
		$map['id'] = ['gt', 0];
		if ($key && $key !== '') {
			$map['name|mobile'] = $key;
		}
		$map['reg_type'] = $regType;
		$member          = new MerchantModel();
		$nowPage         = input('get.page') ? input('get.page') : 1;
		$limits          = config('list_rows');       // 获取总条数
		$count           = $member->getAllCount($map);//计算总页面
		$allPage         = intval(ceil($count / $limits));
		$lists           = $member->getMerchantByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => &$v) {
			$v['total_usdt'] = $v['usdt'] + $v['usdtd'];
			$v['addtime']    = getTime($v['addtime']);
			$v['parent']     = $member->where('id', $v['pid'])->value('name');
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('reg_type', $regType);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function agentreward() {
		$key         = input('key');
		$map['a.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$uid        = Db::name('merchant')->where('name|mobile', $key)->value('id');
			$map['uid'] = $uid;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');            // 获取总条数
		$count   = $member->getAllCountAgent($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getRewardByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function traderreward() {
		$key         = input('key');
		$map['a.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$uid        = Db::name('merchant')->where('name|mobile', $key)->value('id');
			$map['uid'] = $uid;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');            // 获取总条数
		$count   = $member->getAllCountAgent($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getTraderRewardByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function usdtlog() {
		$key              = input('key');
		$map['coin_type'] = 0;
		if ($key && $key !== '') {
			$map['name|mobile'] = $id;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');           // 获取总条数
		$count   = $member->getAllCountUsdt($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getUsdtByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function btclog() {
		$key              = input('key');
		$map['coin_type'] = 1;
		if ($key && $key !== '') {
			$map['name|mobile'] = $id;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');           // 获取总条数
		$count   = $member->getAllCountUsdt($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getUsdtByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function merchant_status() {
		$id     = input('param.id');
		$status = Db::name('merchant')->where('id', $id)->value('status');//判断当前状态情况
		if ($status == 1) {
			$flag = Db::name('merchant')->where('id', $id)->setField(['status' => 0]);
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】禁用商户:' . $id . '成功', 1);
			showJson(['code' => 1, 'data' => $flag['data'], 'msg' => '已禁止']);
		} else {
			$flag = Db::name('merchant')->where('id', $id)->setField(['status' => 1]);
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】启用商户:' . $id . '成功', 1);
			showJson(['code' => 0, 'data' => $flag['data'], 'msg' => '已开启']);
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
			showJson(['code' => 0, 'msg' => '用户注册类型错误']);
		}
		if (Db::name('merchant')->where('id', $id)->update($update)) {
			if ($user['reg_type'] == 2) {
				// 空
				Db::name('merchant')->where('reg_type', 1)->whereNull()->update(['pptrader' => $id]);
				// 非空
				Db::name('merchant')->where('reg_type', 1)->whereNotNull()->update(['pptrader' => Db::raw("CONCAT(pptrader, ',{$id}')")]);
			}
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '操作成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '操作失败']);
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
			showJson(['code' => 1, 'msg' => '操作成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '操作失败']);
		}
	}

	public function merchant_trader_check() {
		$id    = input('param.id');
		$check = input('param.check');
		$flag  = Db::name('merchant')->where('id', $id)->setField(['trader_check' => $check]);
		if ($flag) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '操作成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核用户:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '操作失败']);
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
				$tradersId = $member->where('trader_check', 1)->order('id ASC')->column('id');
				shuffle($tradersId);
				$param['pptrader'] = implode(',', $tradersId);
			} elseif (!empty($param['pptraders']) && is_array($param['pptraders'])) {
				$param['pptrader'] = implode(',', $param['pptraders']);
			} else {
				$param['pptrader'] = '';
			}
			$amt  = $frozenAmt = 0;
			$user = Db::name('merchant')->where('id', $param['id'])->find();
			if ($user['usdt'] != $param['usdt']) {
				$amt = $amount = $param['usdt'] - $user['usdt'];
				if ($amount < 0) {
					$amount = abs($amount);
					$type   = 1;
				} else {
					$type = 0;
				}
				financeLog($param['id'], $amount, '后台修改USDT余额', $type, session('username'));//添加日志
			}
			if ($user['usdtd'] != $param['usdtd']) {
				$frozenAmt = $amount = $param['usdtd'] - $user['usdtd'];
				if ($amount < 0) {
					$amount = abs($amount);
					$type   = 1;
				} else {
					$type = 0;
				}
				financeLog($param['id'], $amount, '后台修改USDT冻结余额', $type, session('username'));//添加日志
			}
			($amt != 0 || $frozenAmt != 0) && balanceChange(FALSE, $param['id'], $amt, 0, $frozenAmt, 0, BAL_SYS, '', '管理员修改');
			unset($param['usdt'], $param['usdtd']);
			$flag = $member->editMerchant($param);
			showJson(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
		}
		$id          = input('param.id');
		$regType     = input('param.reg_type');
		$minfo       = $member->getOneByWhere($id, 'id');
		$matchTrader = explode(',', $minfo['pptrader']);
		$traders     = $member->field('id, name')->where('trader_check', 1)->order('id ASC')->select();
		/*	foreach ($traders as $k => &$v) {
				if (in_array($v['id'], $matchTrader)) {
					$status = 1;
				} else {
					$status = 0;
				}
			}*/
		foreach ($traders as $k => &$v) {
			if (in_array($v['id'], $matchTrader)) {
				$v['ispp'] = 1;
			} else {
				$v['ispp'] = 0;
			}
		}
		$this->assign([
			'merchant' => $minfo,
			'traders'  => $traders,
			'reg_type' => $regType,
		]);
		return $this->fetch();
	}

	public function del_merchant() {
		$id     = input('param.id');
		$member = new MerchantModel();
		$flag   = $member->delMerchant($id);
		writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除用户:' . $id . '成功', 1);
		showJson(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
	}

	public function tibi() {
		$key                                   = input('key');
		$map['think_merchant_withdraw.status'] = ['egt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		$member  = new TibiModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');       // 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getTibiByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
			$v['endtime'] = getTime($v['endtime']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	//审核通过提币
	public function passTibi() {
		$id    = input('id');
		$model = new TibiModel();
		(empty($id)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$find = $model->getOneByWhere($id, 'id');
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		($find['status'] != 0) && showJson(['code' => 0, 'msg' => '状态错误：不是待审核']);
		$type = input('type');//1走钱包,2不走钱包
		($type != 1 && $type != 2) && showJson(['code' => 0, 'msg' => '请选择方式']);
		if ($type == 1) {
			if (config('wallettype') == 'omni') {
				$model2 = new Usdt();
				$return = $model2->index('send', $find['address'], $find['mum'], $index = NULL, $count = NULL, $skip = NULL);
			}
			if (config('wallettype') == 'erc') {//20190828增加erc提币审核
				die('1');
			}
		} else {
			$return['code'] = 1;
			$return['data'] = '';
		}
		if ($return['code'] == 1) {
			/* $dbreturn = $model->editWithdraw(['id'=>$id, 'status'=>1, 'endtime'=>time(), 'txid'=>$return['data']]);
            if($dbreturn['code'] == 0){
                showJson(['code'=>0, 'msg'=>'转账成功，修改订单状态失败：'.$dbreturn['msg'], 'data'=>'']);
            }else{
                $rs = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);
                if(!$rs){
                    showJson(['code'=>0, 'msg'=>'转账成功，扣除冻结失败', 'data'=>'']);
                }else{
                    showJson(['code'=>0, 'msg'=>'转账成功', 'data'=>'']);
                }
            } */
			$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
			Db::startTrans();
			try {
				$rs1 = balanceChange(FALSE, $find['merchant_id'], 0, 0, -$find['num'], 0, BAL_WITHDRAW, $id, "审核通过提币");
				//$rs1 = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);//0
				$rs2 = $model->editWithdraw(['id' => $id, 'status' => 1, 'endtime' => time(), 'txid' => $return['data'], 'type' => $type]);
				//商户提币
				$fee = config('agent_tibi_fee');
				if ($merchant['pid'] && $find['fee'] && $fee) {
					//$fee = round($fee*$find['fee']/100, 8);
					$rsArr = agentReward($merchant['pid'], $find['merchant_id'], $fee, 0);
				} else {
					$rsArr[0] = 1;
					$rsArr[1] = 1;
				}
				if ($rs1 && $rs2['code'] == 1 && $rsArr[0] && $rsArr[1]) {
					// 提交事务
					Db::commit();
					//统计商户提币数量
					Db::name('merchant')->where('id', $find['merchant_id'])->setInc('withdraw_amount', $find['num']);
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '成功', 1);
					financeLog($find['merchant_id'], $find['num'], '提币_1', 1, session('username'));//添加日志
					return ['code' => 1, 'data' => '', 'msg' => '转账成功'];
				} else {
					// 回滚事务
					Db::rollback();
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '失败', 0);
					return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败'];
				}
			} catch (DbException $e) {
				// 回滚事务
				Db::rollback();
				return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败:' . $e->getMessage()];
			}
		} else {
			showJson($return);
		}
	}

	//拒绝提币
	public function refuseTibi() {
		$id    = input('id');
		$model = new TibiModel();
		(empty($id)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$find = $model->getOneByWhere($id, 'id');
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		($find['status'] != 0) && showJson(['code' => 0, 'msg' => '状态错误：不是待审核']);
		$return = $model->cancel($id);
		writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】拒绝提币:' . $id . '成功', 1);
		showJson($return);
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
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['uid']           = $id;
		}
		$nowPage = input('get.page') ? input('get.page') : 1;
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
		$count   = Db::name('address')->where($map)->count();
		$allPage = intval(ceil($count / $limits));
		$lists   = Db::name('address')->where($map)->page($nowPage, $limits)->order('id DESC')->select();
		foreach ($lists as $k => &$v) {
			$user          = Db::name('merchant')->where(['id' => $v['uid']])->find();
			$v['addtime']  = getTime($v['addtime']);
			$v['mobile']   = ($user['mobile'] == NULL ? '未分配' : $user['mobile']);
			$v['username'] = ($user['name'] == NULL ? '未分配' : $user['name']);
			$v['type']     = strtoupper($v['type']);
			$v['status']   = ($v['status'] == 0 ? '未分配' : '已分配');
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('type', $type);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function address() {
		$key                                   = input('key');
		$type                                  = input('addresstype');
		$map['think_merchant_user_address.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		if ($type == 2) {
			$count   = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->count();
			$allPage = intval(ceil($count / $limits));
			$lists   = Db::name('merchant')->where('usdtb', 'exp', 'is not Null')->page($nowPage, $limits)->order('id DESC')->select();
			foreach ($lists as $k => &$v) {
				$v['addtime']  = getTime($v['addtime']);
				$v['username'] = $v['name'];
				$v['address']  = $v['usdtb'];
			}
		} else {
			$member  = new AddressModel();
			$count   = $member->getAllCount($map);//计算总页面
			$allPage = intval(ceil($count / $limits));
			$lists   = $member->getAddressByWhere($map, $nowPage, $limits);
			foreach ($lists as $k => &$v) {
				$v['addtime'] = getTime($v['addtime']);
			}
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('type', $type);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function withdrawlist() {
		$key                                    = input('key');
		$keyuser                                = input('keyuser');
		$status                                 = input('status');
		$map['think_merchant_user_withdraw.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		if ($keyuser && $keyuser !== '') {
			$map['username'] = $keyuser;
		}
		if (!empty($status)) {
			$map['think_merchant_user_withdraw.status'] = $status - 1;
		}
		$member  = new WithdrawModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');       // 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getWithdrawByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('valuser', $keyuser);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	//审核通过用户提币
	public function passWithdraw() {
		$id    = input('id');
		$model = new WithdrawModel();
		(empty($id)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$find = $model->getOneByWhere($id, 'id');
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		($find['status'] != 0) && showJson(['code' => 0, 'msg' => '状态错误：不是待审核']);
		$type = input('type');
		($type != 1 && $type != 2) && showJson(['code' => 0, 'msg' => '请选择方式']);
		$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
		$mum      = $find['num'];
		$fee1     = config('user_tibi_fee');
		$fee2     = $merchant['user_withdraw_fee'];
		$fee      = $fee2;
		if (empty($fee2)) {
			$fee = $fee1;
		}
		(empty($fee)) && showJson(['code' => 0, 'msg' => '用户提币手续费未设置']);
		$sfee = 0;
		if ($fee) {
			$sfee = $find['num'] * $fee / 100;
			$mum  = $find['num'] - $sfee;
		}
		($merchant['usdt'] * 100000000 < $mum * 100000000) && showJson(['code' => 0, 'msg' => '商户余额不足']);
		if ($type == 1) {
			$model2 = new Usdt();
			$return = $model2->index('send', $find['address'], $mum, $index = NULL, $count = NULL, $skip = NULL);
		} else {
			$return['code'] = 1;
			$return['data'] = '';
		}
		if ($return['code'] == 1) {
			/* $dbreturn = $model->editWithdraw(['id'=>$id, 'status'=>1, 'endtime'=>time(), 'txid'=>$return['data']]);
             if($dbreturn['code'] == 0){
             showJson(['code'=>0, 'msg'=>'转账成功，修改订单状态失败：'.$dbreturn['msg'], 'data'=>'']);
             }else{
             $rs = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdtd', $find['num']);
             if(!$rs){
             showJson(['code'=>0, 'msg'=>'转账成功，扣除冻结失败', 'data'=>'']);
             }else{
             showJson(['code'=>0, 'msg'=>'转账成功', 'data'=>'']);
             }
             } */
			$merchant = Db::name('merchant')->where('id', $find['merchant_id'])->find();
			Db::startTrans();
			try {
				$rs1 = balanceChange(FALSE, $find['merchant_id'], -$find['num'], 0, 0, 0, BAL_WITHDRAW, $id, "审核通过提币");
				//$rs1 = Db::name('merchant')->where('id', $find['merchant_id'])->setDec('usdt', $find['num']);//0
				$rs2 = $model->editWithdraw(['id' => $id, 'status' => 1, 'endtime' => time(), 'txid' => $return['data'], 'fee' => $sfee, 'mum' => $mum, 'type' => $type]);
				//商户提币
				$feeMy = config('agent_withdraw_fee');
				if ($merchant['pid'] && $sfee && $feeMy) {
					$feeMy = round($feeMy * $sfee / 100, 8);
					$rsArr = agentReward($merchant['pid'], $find['merchant_id'], $feeMy, 1);
				} else {
					$rsArr[0] = 1;
					$rsArr[1] = 1;
				}
				if ($rs1 && $rs2['code'] == 1 && $rsArr[0] && $rsArr[1]) {
					// 提交事务
					Db::commit();
					//统计商户提币数量
					Db::name('merchant')->where('id', $find['merchant_id'])->setInc('withdraw_amount', $find['num']);
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '成功', 1);
					financeLog($find['merchant_id'], $find['num'], '提币_1', 1, session('username'));//添加日志
					return ['code' => 1, 'data' => '', 'msg' => '转账成功'];
				} else {
					// 回滚事务
					Db::rollback();
					writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】审核提币:' . $find['merchant_id'] . '失败', 0);
					return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败'];
				}
			} catch (DbException $e) {
				// 回滚事务
				Db::rollback();
				return ['code' => 0, 'data' => '', 'msg' => '转账成功，数据库修改操作失败:' . $e->getMessage()];
			}
		} else {
			showJson($return);
		}
	}

	//拒绝提币
	public function refuseWithdraw() {
		$id    = input('id');
		$model = new WithdrawModel();
		(empty($id)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$find = $model->getOneByWhere($id, 'id');
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		($find['status'] != 0) && showJson(['code' => 0, 'msg' => '状态错误：不是待审核']);
		$return = $model->editWithdraw(['id' => $id, 'status' => 2, 'endtime' => time()]);
		writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】拒绝提币:' . $id . '成功', 1);
		showJson($return);
	}

	public function rechargelist() {
		$key                                    = input('key');
		$keyuser                                = input('keyuser');
		$status                                 = input('status');
		$map['think_merchant_user_recharge.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		if ($keyuser && $keyuser !== '') {
			$m                 = new AddressModel();
			$address           = $m->getAddressByUsername($keyuser);
			$map['to_address'] = ['in', $address];
		}
		if (!empty($status)) {
			$map['think_merchant_user_recharge.status'] = $status;
		}
		$member  = new RechargeModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');       // 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getRechargeByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => &$v) {
			$v['addtime'] = getTime($v['addtime']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('valuser', $keyuser);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function traderrecharge() {
		$key = input('key');
		$oid = input('oid');
		//$keyuser = input('keyuser');
		$status                            = input('status');
		$map['think_merchant_recharge.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['merchant_id']   = $id;
		}
		if ($oid && $oid !== '') {
			$map['to_address'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_merchant_recharge.status'] = $status - 1;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');         // 获取总条数
		$count   = $member->getAllCountTr($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getTraderRechargeByWhere($map, $nowPage, $limits);
		foreach ($lists as $k => $v) {
			$lists[$k]['addtime'] = date("Y/m/d H:i:s", $v['addtime']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function adlist() {
		$key = input('key');
		$oid = input('oid');
		//$keyuser = input('keyuser');
		$status                  = input('status');
		$map['think_ad_sell.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['userid']        = $id;
		}
		if (!empty($status)) {
			$map['think_ad_sell.state'] = $status;
		}
		if ($oid && $oid !== '') {
			$map['ad_no'] = ['like', '%' . $oid . '%'];
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');         // 获取总条数
		$count   = $member->getAllCountAd($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getAdByWhere($map, $nowPage, $limits);
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
			$dealNum           = Db::name('order_buy')->where(['sell_sid' => $v['id'], 'status' => ['neq', 5], 'status' => ['neq', 9]])->sum('deal_num');
			$dealNum           = $dealNum ? $dealNum : 0;
			$lists[$k]['deal'] = $dealNum;
			//$total = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 7)->sum('deal_num');
			//$lists[$k]['remain'] = $v['amount'] - $lists[$k]['deal'];
			$lists[$k]['remain']         = $v['remain_amount'];
			$lists[$k]['trading_volume'] = $v['trading_volume'];
			//$lists[$k]['remain']         = $v['amount'] - $total;
			//$lists[$k]['trading_volume'] = $total;
			$lists[$k]['payway'] = $str;
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function buyadlist() {
		$key = input('key');
		$oid = input('oid');
		//$keyuser = input('keyuser');
		$status                 = input('status');
		$map['think_ad_buy.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['userid']        = $id;
		}
		if ($oid && $oid !== '') {
			$map['ad_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['state'] = $status;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');            // 获取总条数
		$count   = $member->getAllCountAdBuy($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getAdBuyByWhere($map, $nowPage, $limits);
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
			$dealNum             = Db::name('order_sell')->where(['buy_bid' => $v['id'], 'status' => ['neq', 5]])->sum('deal_num');
			$dealNum             = $dealNum ? $dealNum : 0;
			$lists[$k]['deal']   = $dealNum;
			$lists[$k]['remain'] = $v['amount'] - $lists[$k]['deal'];
			$lists[$k]['payway'] = $str;
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function deletead() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_sell')->delete($id);
		if ($rs) {
			showJson(['code' => 1, 'msg' => '删除成功']);
		} else {
			showJson(['code' => 0, 'msg' => '删除失败']);
		}
	}

	public function deletebuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->delete($id);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '删除成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】删除:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '删除失败']);
		}
	}

	public function downad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			!balanceChange(TRUE, $find['userid'], $find['remain_amount'], 0, -$find['remain_amount'], 0, BAL_REDEEM, $find['id'], "后台下架") && showJson(['code' => 0, 'msg' => '下架失败']);
			//Db::name('merchant')->where('id', $find['userid'])->setInc('usdt', $find['remain_amount']);
			//Db::name('merchant')->where('id', $find['userid'])->setDec('usdtd', $find['remain_amount']);
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '下架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '下架失败']);
		}
	}

	public function downbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '下架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】下架挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '下架失败']);
		}
	}

	public function upad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$user = Db::name('merchant')->where('id', $find['userid'])->find();
		if ($find['remain_amount'] > $user['usdt']) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '上架失败']);
		} else {
			$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 1]);
			if ($rs) {
				if (!balanceChange(TRUE, $find['userid'], -$find['remain_amount'], 0, $find['remain_amount'], 0, BAL_ENTRUST, $find['id'], "后台上架")) showJson(['code' => 0, 'msg' => '上架失败']);
				//Db::name('merchant')->where('id', $find['userid'])->setDec('usdt', $find['remain_amount']);
				//Db::name('merchant')->where('id', $find['userid'])->setInc('usdtd', $find['remain_amount']);
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '成功', 1);
				showJson(['code' => 1, 'msg' => '上架成功']);
			} else {
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '失败', 0);
				showJson(['code' => 0, 'msg' => '上架失败']);
			}
		}
	}

	public function upbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 1]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '上架成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】上架挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '上架失败']);
		}
	}

	public function frozenad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 4]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function frozenbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 4]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function unfrozenad() {
		$id   = input('id');
		$find = Db::name('ad_sell')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_sell')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			// 解冻后是下架 所以回滚余额
			if (!balanceChange(TRUE, $find['user_id'], $find['remain_amount'], 0, -$find['remain_amount'], 0, BAL_REDEEM, $find['id'], "后台解冻")) showJson(['code' => 0, 'msg' => '解冻失败']);
			//Db::name('merchant')->where('id', $find['user_id'])->setInc('usdt', $find['remain_volume']);
			//Db::name('merchant')->where('id', $find['user_id'])->setDec('usdtd', $find['remain_volume']);
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '解冻成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '解冻失败']);
		}
	}

	public function unfrozenbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】冻结挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function orderlist() {
		$key                       = input('key');
		$oid                       = input('oid');
		$status                    = input('status');
		$map['think_order_buy.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['sell_id']       = $id;
		}
		if ($oid && $oid !== '') {
			$map['order_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_order_buy.status'] = $status - 1;
		}
		$member        = new MerchantModel();
		$nowPage       = input('get.page') ? input('get.page') : 1;
		$limits        = config('list_rows');            // 获取总条数
		$count         = $member->getAllCountOrder($map);//计算总页面
		$allPage       = intval(ceil($count / $limits));
		$lists         = $member->getOrderByWhere($map, $nowPage, $limits);
		$buyerIds      = array_column($lists, 'buy_id');
		$buyerUsername = Db::name('merchant')->where('id', 'in', $buyerIds)->select();
		$buyerUsername = array_column($buyerUsername, 'name', 'id');
		foreach ($lists as $k => $v) {
			$user                 = Db::name('merchant')->where(['id' => $v['sell_id']])->find();
			$accuser              = Db::name('merchant')->where(['id' => $user['pid']])->find();
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
				$alipay                  = Db::name('merchant_zfb')->where(['id' => $sorder['pay_method2']])->find();
				$lists[$k]['zfbinfo'] = '/uploads/face/' . $alipay['c_bank_detail'];
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
			// $lists[$k]['payway'] = $str;
		}
		$this->assign('Nowpage', $nowPage);        //当前页
		$this->assign('allpage', $allPage);        //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function orderlistbuy() {
		$key                        = input('key');
		$oid                        = input('oid');
		$status                     = input('status');
		$map['think_order_sell.id'] = ['gt', 0];
		$regType                    = input('reg_type', 0);
		if ($regType) {
			$map['c.reg_type'] = $regType;
		}
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['buy_id']        = $id;
		}
		if ($oid && $oid !== '') {
			$map['order_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_order_sell.status'] = $status - 1;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');               // 获取总条数
		$count   = $member->getAllCountOrderBuy($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getOrderBuyByWhere($map, $nowPage, $limits);
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
				$alipay                  = Db::name('merchant_zfb')->where(['id' => $v['pay2']])->find();
				$lists[$k]['zfbinfo'] = '/uploads/face/' . $alipay['c_bank_detail'];
				// $str.='|<a onclick="showzfb({{d[i].zfbinfo}})">支付宝</a>';
			}
			if ($v['pay3'] > 0) {
				$wx                  = Db::name('merchant_wx')->where(['id' => $v['pay3']])->find();
				$lists[$k]['wxinfo'] = '/uploads/face/' . $wx['c_bank_detail'];
			}
		}
		$this->assign('Nowpage', $nowPage);           //当前页
		$this->assign('allpage', $allPage);           //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		$this->assign('reg_type', $regType);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}

	public function huitiao() {
		$id        = input('post.id');
		$type      = input('post.type');
		$orderInfo = Db::name('order_buy')->where('id', $id)->find();
		(!$orderInfo) && showJson(['code' => 0, 'msg' => '订单不存在']);
		$buyer = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
		//请求回调接口
		$data['rmb']     = $orderInfo['deal_amount'];
		$data['amount']  = $orderInfo['deal_num'];
		$data['orderid'] = $orderInfo['orderid'];
		$data['appid']   = $buyer['appid'];
		if ($type == 1) {
			$status = 1;
		} elseif ($type == 2) {
			$status = 0;
		}
		$data['status'] = $status;
		askNotify($data, $orderInfo['notify_url'], $buyer['key']);
		$this->success('操作成功');
	}

	public function sssuccess() {
		$id        = input('post.id');
		$type      = input('post.type');
		$orderInfo = Db::name('order_buy')->where('id', $id)->find();
		(!$orderInfo) && showJson(['code' => 0, 'msg' => '订单不存在']);
		if ($orderInfo['status'] == 4) {
			//showJson(['code'=>0, 'msg'=>'订单已完成，请刷新']);
		}
		($type != 1 && $type != 2) && showJson(['code' => 0, 'msg' => '回调选择错误']);
		$buyer  = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
		$trader = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
		($trader['usdtd'] < $orderInfo['deal_num']) && showJson(['code' => 0, 'msg' => '承兑商冻结不足']);
		// 锁定操作 代码执行完成前不可继续操作 60秒后可再次点击操作
		Cache::has($id) && $this->error('操作频繁,请稍后重试');
		$lock = Cache::set($id, TRUE, 60);
		!$lock && $this->error('锁定操作失败，请重试。');
		Db::startTrans();
		try {
			//$rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num']);
			//$rs3 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $orderInfo['deal_num']);
			$rs1 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 9, 'finished_time' => time()]);
			// 回滚挂单
			$rs2 = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->setInc('remain_amount', $orderInfo['deal_num']);
			$rs3 = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->setDec('trading_volume', $orderInfo['deal_num']);
			// 判断挂单是否已经下架
			$sellInfo = Db::name('ad_sell')->where(['id' => $orderInfo['sell_sid'], 'state' => 2])->find();
			$rs4      = $rs5 = 1;
			if ($sellInfo) {
				// 如果挂单已下架 回滚余额
				$rs4 = balanceChange(FALSE, $orderInfo['sell_id'], $orderInfo['deal_num'], 0, -$orderInfo['deal_num'], 0, BAL_CANCEL, $orderInfo['id'], "申述成功");
				// $rs4 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num']);
				// $rs5 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $orderInfo['deal_num']);
			}
			//$rs4 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('transact', 1);
			//$total = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderInfo['sell_id'])->where('status', 4)->select();
			//$tt = $total[0]['total'];
			//$transact = Db::name('merchant')->where('id', $orderInfo['sell_id'])->value('transact');
			//$rs5 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->update(['averge'=>intval($tt/$transact)]);
			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
				// 提交事务
				Db::commit();
				//请求回调接口
				$data['rmb']     = $orderInfo['deal_amount'];
				$data['amount']  = $orderInfo['deal_num'];
				$data['orderid'] = $orderInfo['orderid'];
				$data['appid']   = $buyer['appid'];
				if ($type == 1) {
					$status = 0;
				} elseif ($type == 2) {
					$status = 0;
				}
				$data['status'] = $status;
				//askNotify($data, $orderInfo['notify_url'], $buyer['key']);
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】申诉订单:' . $orderInfo['id'] . '成功', 1);
				Cache::rm($id);
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				writelog(session('adminuid'), session('username'), '用户【' . session('username') . '】申诉订单:' . $orderInfo['id'] . '失败', 0);
				Cache::rm($id);
				$this->error('操作失败');
			}
		} catch (DbException $e) {
			// 回滚事务
			Db::rollback();
			Cache::rm($id);
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	/**
	 * 币给承兑商
	 * @return unknown
	 */
	public function sssuccessbuy() {
		$id        = input('post.id');
		$orderInfo = Db::name('order_sell')->where('id', $id)->find();
		(!$orderInfo) && showJson(['code' => 0, 'msg' => '订单不存在']);
		($orderInfo['status'] == 4) && showJson(['code' => 0, 'msg' => '订单已完成，请刷新']);
		$buyer  = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
		$trader = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
		($trader['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) && showJson(['code' => 0, 'msg' => '商户冻结不足']);
		$fee  = config('usdt_buy_trader_fee');
		$fee  = $fee ? $fee : 0;
		$sfee = $orderInfo['deal_num'] * $fee / 100;
		$mum  = $orderInfo['deal_num'] - $sfee;
		Db::startTrans();
		try {
			$rs1 = balanceChange(FALSE, $orderInfo['sell_id'], 0, 0, -$orderInfo['deal_num'], 0, BAL_SOLD, $orderInfo['id'], "申述成功->buy");
			// $rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $orderInfo['deal_num'] + $orderInfo['fee']);
			$rs2 = Db::name('order_sell')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'buyer_fee' => $sfee]);
			// $rs3      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
			$rs3      = balanceChange(FALSE, $orderInfo['buy_id'], $mum, 0, 0, 0, BAL_BOUGHT, $orderInfo['id'], "申述成功->buy");
			$rs4      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('transact_buy', 1);
			$total    = Db::name('order_sell')->field('sum(dktime-ctime) as total')->where('buy_id', $orderInfo['buy_id'])->where('status', 4)->select();
			$tt       = $total[0]['total'];
			$transact = Db::name('merchant')->where('id', $orderInfo['buy_id'])->value('transact_buy');
			$rs5      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->update(['averge_buy' => intval($tt / $transact)]);
			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5) {
				// 提交事务
				Db::commit();
				financeLog($orderInfo['sell_id'], ($orderInfo['deal_num'] + $orderInfo['fee']), '卖出USDT_释放_1', 1, session('username'));//添加日志
				financeLog($orderInfo['buy_id'], $mum, '买入USDT_1', 0, session('username'));                                            //添加日志
				getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num'] + $orderInfo['fee']);
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				$this->error('操作失败');
			}
		} catch (DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	public function ssfail() {
		$id        = input('post.id');
		$type      = input('post.type');
		$amount    = input('post.amount');
		$orderInfo = Db::name('order_buy')->where('id', $id)->find();
		(!$orderInfo) && showJson(['code' => 0, 'msg' => '订单不存在']);
		($type != 1 && $type != 2) && showJson(['code' => 0, 'msg' => '回调选择错误']);
		($amount == '' || ($amount > $orderInfo['deal_amount'])) && showJson(['code' => 0, 'msg' => '回调金额错误']);
		// 锁定操作 代码执行完成前不可继续操作 60秒后可再次点击操作
		Cache::has($id) && $this->error('操作频繁,请稍后重试');
		$lock = Cache::set($id, TRUE, 60);
		!$lock && $this->error('锁定操作失败，请重试。');
		$oldNum = $orderInfo['deal_num'];
		if ($amount * 100 != $orderInfo['deal_amount'] * 100) {
			$orderInfo['deal_num'] = round($amount / $orderInfo['deal_price'], 8);
		}
		$sfee   = 0;
		$mum    = $orderInfo['deal_num'] - $sfee;
		$buyer  = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
		$seller = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
		($seller['usdtd'] < $oldNum) && showJson(['code' => 0, 'msg' => '承兑商冻结不足']);
		($amount * 100 > $orderInfo['deal_amount'] * 100 && $seller['usdt'] < ($orderInfo['deal_num'] - $oldNum)) && showJson(['code' => 0, 'msg' => '承兑商余额不足']);
		//盘口费率
		$pkfee = $buyer['merchant_pk_fee'];
		$pkfee = $pkfee ? $pkfee : 0;
		$pkdec = $orderInfo['deal_num'] * $pkfee / 100;
		//承兑商卖单奖励
		$sellerGet         = $seller['trader_trader_get'];
		$sellerGet         = $sellerGet ? $sellerGet : 0;
		$sellerMoney       = $sellerGet * $orderInfo['deal_num'] / 100;
		$sellerParentMoney = $sellerMParentMoney = $tpexist = $mpexist = 0;
		$model2            = new MerchantModel();
		if ($seller['pid']) {
			$sellerP = $model2->getUserByParam($seller['pid'], 'id');
			if ($sellerP['agent_check'] == 1 && $sellerP['trader_parent_get']) {
				//承兑商代理利润
				$tpexist           = 1;
				$sellerParentGet   = $sellerP['trader_parent_get'];
				$sellerParentGet   = $sellerParentGet ? $sellerParentGet : 0;
				$sellerParentMoney = $sellerParentGet * $orderInfo['deal_num'] / 100;
			}
		}
		if ($buyer['pid']) {
			$buyerP = $model2->getUserByParam($buyer['pid'], 'id');
			$buyerP['enable_new_get'] == 0 ? $sellerMParentGet = $buyerP['trader_merchant_parent_get'] : $sellerMParentGet = $buyer['trader_merchant_parent_get_new'];
			if ($buyerP['agent_check'] == 1 && $sellerMParentGet) {
				//商户代理利润
				$mpexist = 1;
				//$sellerMParentGet   = $buyerP['trader_merchant_parent_get'];
				$sellerMParentGet   = $sellerMParentGet ? $sellerMParentGet : 0;
				$sellerMParentMoney = $sellerMParentGet * $orderInfo['deal_num'] / 100;
			}
		}
		//平台，承兑商代理，商户代理，承兑商，商户只能得到这么多，多的给平台
		$moneyArr           = getMoneyByLevel($pkdec, $platformMoney, $sellerParentMoney, $sellerMParentMoney, $sellerMoney);
		$mum                = $mum - $pkdec;
		$platformMoney      = $moneyArr[0];
		$sellerParentMoney  = $moneyArr[1];
		$sellerMParentMoney = $moneyArr[2];
		$sellerMoney        = $moneyArr[3];
		Db::startTrans();
		try {
			// $adInfo = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->find();
			$backAmount = $amount / $orderInfo['deal_price'];  // 返回的数量
			// 回滚
			$res1 = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->setInc('remain_amount', $backAmount);
			$res2 = Db::name('ad_sell')->where('id', $orderInfo['sell_sid'])->setDec('trading_volume', $backAmount);
			$rs1  = balanceChange(FALSE, $orderInfo['sell_id'], 0, 0, -$backAmount, 0, BAL_BOUGHT, $orderInfo['id'], "申述失败操作");
			//$rs1  = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $backAmount);
			// $rs1 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdtd', $oldNum);
			if ($amount * 100 != $orderInfo['deal_amount'] * 100) {
				$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0], 'deal_amount' => $amount, 'deal_num' => $orderInfo['deal_num']]);
			} else {
				$rs2 = Db::name('order_buy')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0]]);
			}
			$rs22 = TRUE;
			if ($amount * 100 > $orderInfo['deal_amount'] * 100) {
				//$rs22    = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setDec('usdt', $orderInfo['deal_num'] - $oldNum);
				$samount = $orderInfo['deal_num'] - $oldNum;
			}
			if ($amount * 100 < $orderInfo['deal_amount'] * 100) {
				//$rs22    = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $oldNum - $orderInfo['deal_num']);
				$samount = $oldNum - $orderInfo['deal_num'];
			}
			//$rs3      = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setInc('usdt', $mum);
			$rs3      = balanceChange(FALSE, $orderInfo['buy_id'], $mum, 0, 0, 0, BAL_BOUGHT, $orderInfo['id'], "申述失败操作");
			$rs4      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('transact', 1);
			$total    = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $orderInfo['sell_id'])->where('status', 4)->select();
			$tt       = $total[0]['total'];
			$transact = Db::name('merchant')->where('id', $orderInfo['sell_id'])->value('transact');
			$rs5      = Db::name('merchant')->where('id', $orderInfo['sell_id'])->update(['averge' => intval($tt / $transact)]);
			//承兑商卖单奖励
			$rs6 = $rs7 = $rs8 = $rs9 = $rs10 = $rs11 = $res3 = TRUE;
			if ($sellerMoney > 0) {
				// $rs6 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $sellerMoney);
				$rs6 = balanceChange(FALSE, $orderInfo['sell_id'], $sellerMoney, 0, 0, 0, BAL_COMMISSION, $orderInfo['id'], "申述失败操作");
				$rs7 = Db::name('trader_reward')->insert(['uid' => $orderInfo['sell_id'], 'orderid' => $orderInfo['id'], 'amount' => $sellerMoney, 'type' => 0, 'create_time' => time()]);
			}
			//承兑商代理利润
			if ($sellerParentMoney > 0 && $tpexist) {
				$rsArr = agentReward($seller['pid'], $orderInfo['sell_id'], $sellerParentMoney, 3);//3
				$rs8   = $rsArr[0];
				$rs9   = $rsArr[1];
			}
			//商户代理利润
			if ($sellerMParentMoney > 0 && $mpexist) {
				$rsArr = agentReward($buyer['pid'], $orderInfo['buy_id'], $sellerMParentMoney, 4);//4
				$rs10  = $rsArr[0];
				$rs11  = $rsArr[1];
			}
			// 平台利润
			if ($platformMoney > 0) {
				$rsArr = agentReward(-1, 0, $platformMoney, 5);//5
				$res3  = $rsArr[1];
			}
			if ($rs1 && $rs2 && $rs3 && $rs4 && $rs5 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11 && $rs22 && $res1 && $res2 && $res3) {
				// 提交事务
				Db::commit();
				financeLog($orderInfo['sell_id'], $mum, '卖出USDT_释放', 0, session('username'));               //添加日志
				financeLog($orderInfo['buy_id'], $mum, '买入USDT_2', 0, session('username'));                 //添加日志
				financeLog($orderInfo['sell_id'], $sellerMoney, '卖出USDT_承兑商卖单奖励_2', 1, session('username'));//添加日志
				// financeLog($orderInfo['buy_id'],$mum,'买入USDT',0);//添加日志
				getStatisticsOfOrder($orderInfo['buy_id'], $orderInfo['sell_id'], $mum, $orderInfo['deal_num']);
				//请求回调接口
				$data['amount'] = $orderInfo['deal_num'];
				if ($amount * 100 != $orderInfo['deal_amount'] * 100) {
					$data['rmb'] = $amount;
				} else {
					$data['rmb'] = $orderInfo['deal_amount'];
				}
				$data['orderid'] = $orderInfo['orderid'];
				$data['appid']   = $buyer['appid'];
				if ($type == 1) {
					$status = 1;
				} elseif ($type == 2) {
					$status = 0;
				}
				$data['status'] = $status;
				//$status && askNotify($data, $orderInfo['notify_url'], $buyer['key']);
				Cache::rm($id);
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				Cache::rm($id);
				$this->error('操作失败');
			}
		} catch (DbException $e) {
			// 回滚事务
			Db::rollback();
			Cache::rm($id);
			$this->error('操作失败，参考信息：' . $e->getMessage());
		}
	}

	/**
	 * 币给商户
	 */
	public function ssfailbuy() {
		$id        = input('post.id');
		$orderInfo = Db::name('order_sell')->where('id', $id)->find();
		(!$orderInfo) && showJson(['code' => 0, 'msg' => '订单不存在']);
		($orderInfo['status'] == 4) && showJson(['code' => 0, 'msg' => '订单已完成，请刷新']);
		$buyer  = Db::name('merchant')->where('id', $orderInfo['buy_id'])->find();
		$seller = Db::name('merchant')->where('id', $orderInfo['sell_id'])->find();
		($seller['usdtd'] < $orderInfo['deal_num'] + $orderInfo['fee']) && showJson(['code' => 0, 'msg' => '商家冻结不足']);
		Db::startTrans();
		try {
			$rs1 = TRUE;
			//$rs1 = balanceChange(false, $orderInfo['buy_id'], $orderInfo['deal_num'] + $orderInfo['fee'], 0, 0, 0, BAL_BOUGHT, $orderInfo['id'], "申述失败操作->buy");
			//$rs1 = Db::name('merchant')->where('id', $orderInfo['buy_id'])->setDec('usdtd', $orderInfo['deal_num'] + $orderInfo['fee']);
			$rs2 = Db::name('order_sell')->update(['id' => $orderInfo['id'], 'status' => 4, 'finished_time' => time()]);
			//$rs3 = Db::name('merchant')->where('id', $orderInfo['sell_id'])->setInc('usdt', $orderInfo['deal_num'] + $orderInfo['fee']);
			$rs3 = balanceChange(FALSE, $orderInfo['sell_id'], $orderInfo['deal_num'] + $orderInfo['fee'], 0, -$orderInfo['deal_num'], $orderInfo['fee'], BAL_BOUGHT, $orderInfo['id'], "申述失败操作->buy");
			if ($rs1 && $rs2 && $rs3) {
				financeLog($orderInfo['sell_id'], ($orderInfo['deal_num'] + $orderInfo['fee']), '卖出USDT_取消', 1, session('username'));//添加日志
				// 提交事务
				Db::commit();
				$this->success('操作成功');
			} else {
				// 回滚事务
				Db::rollback();
				$this->error('操作失败');
			}
		} catch (DbException $e) {
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
		// 获取以前平台利润
		$statistics = Db::name('statistics')->order('id DESC')->find();
		// 获取USDT总数量
		$total_usdt    = Db::name('merchant')->sum('usdt');
		$total_usdtd   = Db::name('merchant')->sum('usdtd');
		$total_balance = $total_usdt + $total_usdtd;
		// 获取充值数量
		$recharge_num = Db::name('merchant_recharge')->sum('num');
		$recharge_fee = Db::name('merchant_recharge')->sum('fee');
		// 获取提币数量
		$withdraw_num = Db::name('merchant_withdraw')->where('status', 1)->sum('num');
		$withdraw_fee = Db::name('merchant_withdraw')->where('status', 1)->sum('fee');
		// 商户代理奖励
		$sellerMParentMoney = Db::name('agent_reward')->where('type', 4)->sum('amount');
		// 承兑商代理奖励
		$sellerParentMoney = Db::name('agent_reward')->where('type', 3)->sum('amount');
		// 平台利润
		// $platformMoney = Db::name('agent_reward')->where('type', 5)->sum('amount');
		// $platformMoney += $statistics['platform_profit'];
		$platformMoney = $statistics['platform_profit'];
		/*
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');// 获取总条数
		$count   = $member->getAllCountStatistics();//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getStatistics($map, $nowPage, $limits);

		foreach ($lists as $k => $v) {
			$lists[$k]['create_time'] = date("Y/m/d H:i:s", $v['create_time']);
		}
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数

		(input('get.page'))  && showJson($lists);
		*/
		$this->assign('Nowpage', 1);                              //当前页
		$this->assign('allpage', 1);                              //总页数
		$this->assign('total_balance', $total_balance);           //总USDT
		$this->assign('withdraw_num', $withdraw_num);             //总提币数量
		$this->assign('withdraw_fee', $withdraw_fee);             //总提币手续费
		$this->assign('recharge_num', $recharge_num);             //总充值数量
		$this->assign('recharge_fee', $recharge_fee);             //总充值手续费
		$this->assign('traderMParentMoney', $sellerMParentMoney); //商户代理奖励
		$this->assign('traderParentMoney', $sellerParentMoney);   //承兑商代理奖励
		$this->assign('platformMoney', $platformMoney);           //平台利润
		return $this->fetch();
	}

	/**
	 * 商户统计
	 */
	public function merchantstatistics() {
		$key             = input('key');
		$order2          = input('order');
		$map['id']       = ['gt', 0];
		$map['reg_type'] = ['eq', 1];  // new 只需要商户
		if ($key && $key != '' && $order2 && $order2 != '') {
			$order[$key] = $order2;
		} else {
			$order['id'] = 'desc';
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');       // 获取总条数
		$count   = $member->getAllCount($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getMerchantStatistics($map, $nowPage, $limits, $order);
		$this->assign('Nowpage', $nowPage); //当前页
		$this->assign('allpage', $allPage); //总页数
		$this->assign('val', $key);
		$this->assign('order', $order2);
		$today = strtotime(date('Y-m-d 00:00:00'));
		foreach ($lists as $key => $list) {
			$recharge_number = $list->orderSell()->count('id');                                                                   // 充值笔数
			$recharge_amount = $list->orderSell()->sum('deal_amount');                                                            // 充值数量
			$success_number  = $list->orderSell()->where('status', 4)->count('id');                                               // 成功笔数
			$success_amount  = $list->orderSell()->where('status', 4)->sum('deal_amount');                                        // 成功数量
			$buy_number      = $list->orderSell()->count('id');                                                                   // 购买数量
			$success_rate    = ($success_number == 0 || $buy_number == 0) ? 0 : round(($success_number / $buy_number) * 100, 2);  // 成功率
			// 获取当天笔数
			$where['ctime']       = ['egt', $today];
			$today_number         = $list->orderSell()->where($where)->count('id');                                                                                             // 当天笔数
			$today_amount         = $list->orderSell()->where($where)->sum('deal_amount');                                                                                      // 当天数量
			$today_success_number = $list->orderSell()->where($where)->where('status', 4)->count('id');                                                                         // 当天成功笔数
			$today_success_amount = $list->orderSell()->where($where)->where('status', 4)->sum('deal_amount');                                                                  // 当天成功数量
			if ($today_success_number == 0 || $today_number == 0) $today_success_rate = 0; else $today_success_rate = round(($today_success_number / $today_number) * 100, 2);  // 成功率
			$lists[$key]['recharge_number']      = $recharge_number;
			$lists[$key]['recharge_amount']      = $recharge_amount;
			$lists[$key]['success_number']       = $success_number;
			$lists[$key]['success_amount']       = $success_amount;
			$lists[$key]['success_rate']         = $success_rate;
			$lists[$key]['today_number']         = $today_number;
			$lists[$key]['today_amount']         = $today_amount;
			$lists[$key]['today_success_number'] = $today_success_number;
			$lists[$key]['today_success_amount'] = $today_success_amount;
			$lists[$key]['today_success_rate']   = $today_success_rate;
		}
		input('get.page') && showJson($lists);
		return $this->fetch();
	}

	/**
	 * [user_order 商户订单]
	 * @return mixed
	 */
	public function order() {
		$key                       = input('key');
		$oid                       = input('oid');
		$status                    = input('status');
		$map['think_order_buy.id'] = ['gt', 0];
		if ($key && $key !== '') {
			$where['name|mobile'] = $key;
			$id                   = Db::name('merchant')->where($where)->value('id');
			$map['sell_id']       = $id;
		}
		if ($oid && $oid !== '') {
			$map['order_no'] = ['like', '%' . $oid . '%'];
		}
		if (!empty($status)) {
			$map['think_order_buy.status'] = $status - 1;
		}
		$member  = new MerchantModel();
		$nowPage = input('get.page') ? input('get.page') : 1;
		$limits  = config('list_rows');            // 获取总条数
		$count   = $member->getAllCountOrder($map);//计算总页面
		$allPage = intval(ceil($count / $limits));
		$lists   = $member->getOrderByWhere($map, $nowPage, $limits);
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
		$this->assign('Nowpage', $nowPage);        //当前页
		$this->assign('allpage', $allPage);        //总页数
		$this->assign('val', $key);
		$this->assign('oid', $oid);
		$this->assign('status', $status);
		(input('get.page')) && showJson($lists);
		return $this->fetch();
	}
}

?>