<?php
namespace app\admin\controller;
use app\admin\model\MerchantModel;
use think\db;

class Ad extends Base {
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

	public function downbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】下架挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '下架成功']);
		} else {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】下架挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '下架失败']);
		}
	}

	// todo 卖单状态
	public function sellState() {
		$id    = (int)input('id');
		$state = (int)input('state');
		!in_array($state, [1, 2, 3, 4]) && showMsg('状态错误', 0);
		$orderInfo = Db::name('ad_sell')->where('id', $id)->lock()->find();
		!$orderInfo && showMsg('订单不存在', 0);
		if (in_array($state, [1, 2, 4]) && in_array($orderInfo['state'], [1, 2, 4])) {
			//单纯更改状态
			!Db::name('ad_sell')->where(['id' => $id, 'userid' => $orderInfo['userid'], 'state' => $orderInfo['state']])->update(['state' => $state]) && showMsg('更改订单状态失败', 0);
		} elseif (in_array($orderInfo['state'], [1, 2, 4]) && $state == 3) {
			// $merchant['usdtd'] < $adInfo['remain_amount'] && $this->error('冻结不足', $id);
			Db::startTrans();
			!Db::name('ad_sell')->where(['id' => $id, 'userid' => $orderInfo['userid']])->update(['state' => $state, 'finished_time' => time()]) && $this->rollbackShowMsg('订单操作失败');
			!balanceChange(FALSE,$orderInfo['userid'], $orderInfo['remain_amount'], 0, -$orderInfo['remain_amount'], 0, BAL_REDEEM, $id) && $this->rollbackShowMsg('撤单失败：退款失败');
			$count = Db::name('ad_sell')->where('userid',$orderInfo['userid'])->where('state', 1)->where('amount', 'gt', 0)->count();
			Db::name('merchant')->update(['id' => $orderInfo['userid'], 'ad_on_sell' => $count ? $count : 0]);
			Db::commit();
		} elseif (in_array($state, [1, 2, 4]) && $orderInfo['state'] == 3) {
			Db::startTrans();
			// 更改订单状态
			!Db::name('ad_sell')->where(['id' => $id, 'userid' => $orderInfo['userid'], 'state' => $orderInfo['state']])->update(['state' => $state]) && $this->rollbackShowMsg('更改订单状态失败', 0);
			!balanceChange(FALSE,$orderInfo['userid'], -$orderInfo['remain_amount'], 0, $orderInfo['remain_amount'], 0, BAL_REDEEM, $id) && $this->rollbackShowMsg('冻结余额失败');
			Db::commit();
		} else {
			showMsg('参数错误');
		}
		writelog($this->uid, $this->username, '用户【' . $this->username . '】处理订单:' . $id . ', state: ' . $state . '成功', 1);
		showMsg('处理成功');
	}

	public function upbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 1]);
		if ($rs) {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】上架挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '上架成功']);
		} else {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】上架挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '上架失败']);
		}
	}

	public function frozenbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 4]);
		if ($rs) {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】冻结挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】冻结挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '冻结失败']);
		}
	}

	public function unfrozenbuyad() {
		$id   = input('id');
		$find = Db::name('ad_buy')->where('id', $id)->find();
		(empty($find)) && showJson(['code' => 0, 'msg' => '参数错误']);
		$rs = Db::name('ad_buy')->update(['id' => $id, 'state' => 2]);
		if ($rs) {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】冻结挂单:' . $id . '成功', 1);
			showJson(['code' => 1, 'msg' => '冻结成功']);
		} else {
			writelog($this->uid, $this->username, '用户【' . $this->username . '】冻结挂单:' . $id . '失败', 0);
			showJson(['code' => 0, 'msg' => '冻结失败']);
		}
	}
}

?>