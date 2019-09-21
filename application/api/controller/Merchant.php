<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

class Merchant extends Controller {
	private $model;
	private $merchant;

	protected function _initialize() {
		$this->model = new \app\common\model\Usdt();
		$this->checkSign(input('post.'));
		// echo json_encode(array('status'=>0,'err'=>input('post.sign')));
		$this->merchant = Db::name('merchant')->where(['appid' => input('post.appid')])->find();
	}

	private function mysuccess($data) {
		$return = [
			'status' => 1,    /* 返回状态，200 成功，500失败 */
			'data'   => $data,
		];
		echo json_encode($return);
		die;
	}

	private function myerror($message) {
		$return = [
			'status' => 0,    /* 返回状态，200 成功，500失败 */
			'err'    => $message,
		];
		echo json_encode($return);
		die;
	}

	public function getInfo() {
		die;
		$return = $this->model->index('getinfo', $addr = NULL, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
		$this->mysuccess($return);
	}

	/**
	 * 生成地址
	 * @param $username 用户名
	 */
	public function newAddress() {
		$data = input('post.');
		if (empty($data['username'])) {
			$this->myerror('用户名不能为空');
		}
		$return = $this->model->index('getnewaddress', $addr = NULL, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
		if ($return['code'] == 1 && !empty($return['data'])) {
			$rs = Db::name('merchant_user_address')->insert(['merchant_id' => $this->merchant['id'], 'username' => $data['username'], 'address' => $return['data'], 'addtime' => time()]);
			if ($rs) {
				if ($this->merchant['pid'] > 0) {
					$request_param = "username=>" . $data['username'];
					apilog($this->merchant['pid'], $this->merchant['id'], '生成地址', $request_param, $return['data']);
				}
				$this->mysuccess($return['data']);
			} else {
				$this->myerror('数据库更新失败');
			}
		} else {
			$this->myerror('生成钱包地址失败');
		}
	}

	/**
	 * 充值记录接口
	 * address:钱包地址
	 */
	public function rechargeRecord() {
		$data = input('post.');
		if (empty($data['address'])) {
			$this->myerror('钱包地址不能为空');
		}
		$list = Db::name('merchant_user_recharge')->field('from_address, to_address, txid, mum, status, confirmations')->where(['to_address' => $data['address'], 'merchant_id' => $this->merchant['id']])->select();
		if ($this->merchant['pid'] > 0) {
			$request_param = "address=>" . $data['address'];
			apilog($this->merchant['pid'], $this->merchant['id'], '充值记录', $request_param, json_encode($list));
		}
		$this->mysuccess($list);
	}

	/**
	 * 获取usdt账户余额
	 * address:钱包地址
	 */
	public function getBalance() {
		$data = input('post.');
		if (empty($data['address'])) {
			$this->myerror('钱包地址不能为空');
		}
		$return = $this->model->index('getbalance', $data['address'], $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
		if ($return['code'] == 0) {
			$this->myerror($return['msg']);
		} else {
			if ($this->merchant['pid'] > 0) {
				$request_param = "address=>" . $data['address'];
				apilog($this->merchant['pid'], $this->merchant['id'], '获取usdt账户余额', $request_param, $return['data']);
			}
			$this->mysuccess($return['data']);
		}
	}

	/**
	 * 用户提币接口
	 * address:钱包地址，num:数量，username:用户名
	 */
	public function makeWithdraw() {
		$data = input('post.');
		if (empty($data['address'])) {
			$this->myerror('钱包地址不能为空');
		}
		if (empty($data['num'])) {
			$this->myerror('提现数量不能为空');
		}
		if ($data['num'] < 0) {
			$this->myerror('请输入正确的提现数量');
		}
		if (empty($data['username'])) {
			$this->myerror('用户名不能为空');
		}
		$usdt = $this->merchant['usdt'];
		if ($usdt * 100000000 < $data['num'] * 100000000) {
			$this->myerror('商户余额不足');
		}
		$ordersn = createOrderNo(2, $this->merchant['id']);
		$rs      = Db::name('merchant_user_withdraw')->insert([
			'merchant_id' => $this->merchant['id'],
			'address'     => $data['address'],
			'username'    => $data['username'],
			'num'         => $data['num'],
			'addtime'     => time(),
			'ordersn'     => $ordersn
		]);
		if (!$rs) {
			$this->myerror('提交失败，请稍后再试');
		} else {
			if ($this->merchant['pid'] > 0) {
				$request_param = "address=>" . $data['address'] . 'num=>' . $data['num'] . 'username=>' . $data['username'];
				apilog($this->merchant['pid'], $this->merchant['id'], '获取usdt账户余额', $request_param, $ordersn);
			}
			$this->mysuccess($ordersn);
		}
	}

	/**
	 * 获取用户提币的状态
	 * ordersn:订单号
	 */
	public function getWithdraw() {
		$data = input('post.');
		if (empty($data['ordersn'])) {
			$this->myerror('提币订单号不能为空');
		}
		$withdraw = Db::name('merchant_user_withdraw')->where(['ordersn' => $data['ordersn']])->find();
		if (empty($withdraw)) {
			$this->myerror('提币订单号不存在');
		}
		if ($this->merchant['pid'] > 0) {
			$request_param = "ordersn=>" . $data['ordersn'];
			apilog($this->merchant['pid'], $this->merchant['id'], '获取用户提币状态', $request_param, json_encode(['status' => $withdraw['status'], 'txid' => $withdraw['txid']]));
		}
		$this->mysuccess(['status' => $withdraw['status'], 'txid' => $withdraw['txid']]);
	}

	/**
	 * 请求承兑商充值
	 * amount:充值数量
	 * address:充值地址
	 * username:充值用户名
	 * orderid:订单号
	 * return_url:同步通知页面
	 * notify_url:异步回调页面
	 */
	public function requestTraderRecharge() {
		$data = input('post.');
		if (empty($data['amount']) || $data['amount'] <= 0) {
			$this->myerror('请输入正确的充值数量');
		}
		if (empty($data['address'])) {
			$this->myerror('充值地址不正确');
		}
		if (empty($data['username'])) {
			$this->myerror('用户名不正确');
		}
		if (strlen($data['username']) >= 15) {
			$this->myerror('用户名不能超过15个字符');
		}
		if (empty($data['orderid'])) {
			$this->myerror('订单号不能为空');
		}
		if (empty($data['return_url'])) {
			$this->myerror('同步通知页面地址错误');
		}
		if (empty($data['notify_url'])) {
			$this->myerror('异步回调页面地址错误');
		}
		$find = Db::name('order_buy')->where('orderid', $data['orderid'])->find();
		if (!empty($find)) {
			$this->myerror('订单号已存在，请勿重复提交');
		}
		$pk_num       = Db::table('think_config')->where('name', 'pk_waiting_finished_num')->value('value');//盘口订单限制
		$trader_limit = Db::table('think_config')->where('name', 'trader_pp_max_unfinished_order')->value('value');
		if ($pk_num > 0) {
			$count = Db::name('order_buy')->where('buy_id', $this->merchant['id'])->where('status', 'in', [0, 1])->count();
			if ($count >= $pk_num) {
				$this->myerror('您有未完成的订单');
			}
		}
		//设置承兑商在线状态
		$ids    = Db::name('login_log')->where('online=1 and unix_timestamp(now())-update_time<1800')->column('merchant_id');
		Db::query('Update think_merchant set online=0');
		Db::name('merchant')->where('id', 'in', $ids)->update(['online' => 1]);
		//系统自动选择在线的承兑商和能够交易这个金额的承兑商
		$where['state']  = 1;
		$where['amount'] = ['egt', $data['amount']];
		$where['usdt']   = ['egt', $data['amount']];
		//判断是否商户匹配交易
		$pptrader      = $this->merchant['pptrader'];
		$pptraderarray = explode(',', $pptrader);
		if (!empty($pptrader) && is_array($pptraderarray)) {
			$where['c.id'] = ['in', $pptraderarray];
		}
		$join         = [['__MERCHANT__ c', 'a.userid=c.id', 'LEFT']];
		$ads          = Db::name('ad_sell')->field('a.*, c.id as traderid, c.mobile')->alias('a')->join($join)->group('a.id')->where($where)->order('online desc,price asc,averge asc,pp_amount asc,id asc')->select();
		$onlinead     = [];
		$actualamount = 0;
		//$this->mysuccess($ads);
		foreach ($ads as $k => $v) {
			$total = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 9)->sum('deal_num');
			if (($v['amount'] - $total) < $data['amount']) {
				continue;
			}
			$actualamount = $data['amount'] * $v['price'];
			if ($v['min_limit'] > $actualamount) {
				continue;
			}
			if ($v['max_limit'] < $actualamount) {
				continue;
			}
			//判断承兑商是否被其它盘口设置过
			if (empty($pptrader)) {
				$find = Db::name('merchant')->where('pptrader', 'like', '%' . $v['traderid'] . '%')->find();
				if (!empty($find)) {
					continue;
				}
			}
			//判断未完成的单子
			$trader_count = Db::name('order_buy')->where('sell_id', $v['traderid'])->where('status', 'in', [0, 1])->count();
			if ($trader_limit && $trader_count >= $trader_limit) {
				continue;
			}
			$onlinead = $v;
			break;
		}
		if (empty($onlinead)) {
			$this->myerror('暂无可用订单');
		}
		//开始冻结承兑商usdt
		Db::startTrans();
		try {
			$rs1 = Db::table('think_merchant')->where('id', $onlinead['traderid'])->setDec('usdt', $data['amount']);
			$rs3 = Db::table('think_merchant')->where('id', $onlinead['traderid'])->setInc('usdtd', $data['amount']);
			$rs4 = Db::table('think_merchant')->where('id', $onlinead['traderid'])->setInc('pp_amount', 1);
			$rs2 = Db::table('think_order_buy')->insertGetId([
				'buy_id'       => $this->merchant['id'],//接口请求时,返回商户的id,放行时增加商户的USDT,有疑问?!
				// 'buy_id'=>'',//暂时改成空
				'sell_id'      => $onlinead['traderid'],
				'sell_sid'     => $onlinead['id'],
				'deal_amount'  => $actualamount,
				'deal_num'     => $data['amount'],
				'deal_price'   => $onlinead['price'],
				'ctime'        => time(),
				'ltime'        => 15,
				'order_no'     => $data['orderid'],
				'buy_username' => $data['username'],
				'buy_address'  => $data['address'],
				'return_url'   => $data['return_url'],
				'notify_url'   => $data['notify_url'],
				'orderid'      => $data['orderid'],
				'status'       => 1
			]);
			if ($rs1 && $rs2 && $rs3 && $rs4) {
				// 提交事务
				Db::commit();
				//todo 发送短信给承兑商
				if (!empty($onlinead['mobile'])) {
					$send_content = Db::table('think_config')->where('name', 'send_message_content')->value('value');
					if ($send_content) {

						$content = str_replace('{usdt}', $data['amount'], $send_content);//dump($content);
						$content = str_replace('{tx_id}', $onlinead['id'], $content);//dump($content);
						//$this->myerror($onlinead['mobile']);die;
						sendSms($onlinead['mobile'], $content);
					}
				}
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$url       = $http_type . $_SERVER['HTTP_HOST'] . '/merchant/pay?id=' . $rs2 . '&appid=' . $data['appid'];
				$this->mysuccess($url);
			} else {
				// 回滚事务
				Db::rollback();
				$this->myerror('提交失败');
			}
		} catch (\think\Exception\DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->myerror('提交失败，参考信息：' . $e->getMessage());
		}
	}

	/**
	 * 请求承兑商充值，按rmb
	 * amount:充值人民币
	 * address:充值地址
	 * username:充值用户名
	 * orderid:订单号
	 * return_url:同步通知页面
	 * notify_url:异步回调页面
	 */
	public function requestTraderRechargeRmb() {
		$data = input('post.');
		if (empty($data['amount']) || $data['amount'] <= 0) {
			$this->myerror('请输入正确的充值金额');
		}
		if (empty($data['address'])) {
			//$this->myerror('充值地址不正确');
		}
		if (empty($data['username'])) {
			$this->myerror('用户名不正确');
		}
		if (strlen($data['username']) >= 15) {
			$this->myerror('用户名不能超过15个字符');
		}
		if (empty($data['orderid'])) {
			$this->myerror('订单号不能为空');
		}
		if (empty($data['return_url'])) {
			$this->myerror('同步通知页面地址错误');
		}
		if (empty($data['notify_url'])) {
			$this->myerror('异步回调页面地址错误');
		}
		$find = Db::name('order_buy')->where('orderid', $data['orderid'])->find();
		if (!empty($find)) {
			$this->myerror('订单号已存在，请勿重复提交');
		}
		$pk_num       = Db::table('think_config')->where('name', 'pk_waiting_finished_num')->value('value');
		$trader_limit = Db::table('think_config')->where('name', 'trader_pp_max_unfinished_order')->value('value');
		if ($pk_num > 0) {
			$count = Db::name('order_buy')->where('buy_id', $this->merchant['id'])->where('status', 'in', [0, 1])->count();
			if ($count >= $pk_num) {
				$this->myerror('您有未完成的订单');
			}
		}
		//设置承兑商在线状态
		$ids    = Db::name('login_log')->where('online=1 and unix_timestamp(now())-update_time<1800')->column('merchant_id');
		$sql    = 'Update think_merchant set online=0';
		$result = Db::query($sql);
		Db::name('merchant')->where('id', 'in', $ids)->update(['online' => 1]);
		//系统自动选择在线的承兑商和能够交易这个金额的承兑商
		$where['state']     = 1;
		$where['min_limit'] = ['elt', $data['amount']];
		$where['max_limit'] = ['egt', $data['amount']];
		//判断是否商户匹配交易
		$pptrader = $this->merchant['pptrader'];

		$pptraderarray = explode(',', $pptrader);
		if (!empty($pptrader) && is_array($pptraderarray)) {
			$where['c.id'] = ['in', $pptraderarray];
		}
		$join         = [
			['__MERCHANT__ c', 'a.userid=c.id', 'LEFT'],
		];
		$ads          = Db::name('ad_sell')->field('a.*, c.id as traderid, c.mobile, c.usdt')->alias('a')->join($join)->group('a.id')->where($where)->order('online desc,price asc,averge asc,pp_amount asc,id asc')->select();
		$onlinead     = [];
		$actualamount = 0;
		// $this->myerror(json_encode($ads));
		// dump($ads);
		// $this->myerror($where);
		foreach ($ads as $k => $v) {
			//开始判断挂单剩余
			$total        = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 9)->sum('deal_num');
			$actualamount = round($data['amount'] / $v['price'], 8);
			if (($v['amount'] - $total) < $actualamount || $v['usdt'] < $actualamount) {
				//dump(1);die;
				continue;
			}
			//判断承兑商是否被其它盘口设置过
			if (empty($pptrader)) {
				$find = Db::name('merchant')->where('pptrader', 'like', '%' . $v['traderid'] . '%')->find();
				if (!empty($find)) {
					continue;
				}

			}
			//判断未完成的单子
			$trader_count = Db::name('order_buy')->where('sell_id', $v['traderid'])->where('status', 'in', [0, 1])->count();
			if ($trader_limit && $trader_count >= $trader_limit) {
				//dump($trader_limit);die;
				continue;
			}
			$onlinead = $v;
			break;
		}
		if (empty($onlinead)) {
			$this->myerror('暂无可用订单');
		}
		// $this->myerror($onlinead);
		//开始冻结承兑商usdt
		Db::startTrans();
		try {
			$rs1 = Db::table('think_merchant')->where('id', $onlinead['traderid'])->setDec('usdt', $actualamount);
			$rs3 = Db::table('think_merchant')->where('id', $onlinead['traderid'])->setInc('usdtd', $actualamount);
			$rs4 = Db::table('think_merchant')->where('id', $onlinead['traderid'])->setInc('pp_amount', 1);
			$rs2 = Db::table('think_order_buy')->insertGetId([
				'buy_id'       => $this->merchant['id'],//接口请求时,返回商户的id,放行时增加商户的USDT,有疑问?!
				// 'buy_id'=>'',//暂时改成空
				'sell_id'      => $onlinead['traderid'],
				'sell_sid'     => $onlinead['id'],
				'deal_amount'  => $data['amount'],
				'deal_num'     => $actualamount,
				'deal_price'   => $onlinead['price'],
				'ctime'        => time(),
				'ltime'        => 15,
				'order_no'     => $data['orderid'],
				'buy_username' => $data['username'],
				'buy_address'  => $data['address'],
				'return_url'   => $data['return_url'],
				'notify_url'   => $data['notify_url'],
				'orderid'      => $data['orderid'],
				'status'       => 0,
			]);

			if ($rs1 && $rs2 && $rs3 && $rs4) {
				// 提交事务
				Db::commit();
				//todo 发送短信给承兑商
				if (!empty($onlinead['mobile'])) {
					$content = str_replace('{usdt}', $actualamount, config('send_message_content'));
					$content = str_replace('{tx_id}', $onlinead['id'], $content);
					sendSms($onlinead['mobile'], $content);
				}
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$url       = $http_type . $_SERVER['HTTP_HOST'] . '/merchant/pay?id=' . $rs2 . '&appid=' . $data['appid'];
				$this->mysuccess($url);
			} else {
				// 回滚事务
				Db::rollback();
				$this->myerror('提交失败');
			}
		} catch (\think\Exception\DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->myerror('提交失败，参考信息：' . $e->getMessage());
		}
	}

	private function checkSign($data) {
		ksort($data);
		if (empty($data['appid'])) {
			echo json_encode(['status' => 0, 'err' => '缺少appid参数' . __LINE__]);
			exit();
		}
		$appsecret_arr = Db::name('merchant')->where(['appid' => $data['appid']])->find();
		if (empty($appsecret_arr)) {
			echo json_encode(['status' => 0, 'err' => 'appid不存在' . __LINE__]);
			exit();
		}
		if ($appsecret_arr['status'] == 0) {
			echo json_encode(['status' => 0, 'err' => 'appid被禁用' . __LINE__]);
			exit();
		}
		$sign = $data['sign'];
		// echo json_encode(array('status'=>0,'err'=>$data));
		// echo json_encode(array('status'=>0,'err'=>$data['sign']));
		unset($data['sign']);
		$serverStr = "";
		foreach ($data as $k => $v) {
			$serverStr = $serverStr . $k . $v;
		}
		$reserverStr  = $serverStr . $appsecret_arr['key'];
		$reserverSign = strtoupper(sha1($reserverStr));

		// $reserverSign = $this->sign($data,$appsecret_arr['key']);
		// echo json_encode(array('status'=>0,'err'=>$reserverSign));exit;

		if ($sign != $reserverSign) {
			echo json_encode(['status' => 0, 'err' => '签名错误' . __LINE__]);
			exit();
		}
	}

	private function sign($dataArr, $key) {
		ksort($dataArr);
		$str = '';
		foreach ($dataArr as $key => $value) {
			$str .= $key . $value;
		}

		$str = $str . $key;

		return strtoupper(sha1($str));
	}
}

?>