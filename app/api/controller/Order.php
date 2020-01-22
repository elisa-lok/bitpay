<?php
namespace app\api\controller;
use app\home\model\OrderModel;
use think\Cache;
use think\Db;
use think\Exception\DbException;

class Order extends Base {
	private $model;
	private $merchant;

	public function _initialize() {
		parent::_initialize();
		$this->model = new \app\common\model\Usdt();
		$this->checkSign(input('post.'));
		$this->merchant = Db::name('merchant')->where(['appid' => input('post.appid')])->find();
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
	public function tradeCrypto() {
		$data = input('post.');
		(empty($data['amount']) || $data['amount'] <= 0) && $this->err('请输入正确的充值数量');
		empty($data['address']) && $this->err('充值地址不正确');
		empty($data['username']) && $this->err('用户名不正确');
		(strlen($data['username']) >= 15) && $this->err('用户名不能超过15个字符');
		empty($data['orderid']) && $this->err('订单号不能为空');
		empty($data['return_url']) && $this->err('同步通知页面地址错误');
		empty($data['notify_url']) && $this->err('异步回调页面地址错误');
		$find = Db::name('order_buy')->where('orderid', $data['orderid'])->find();
		!empty($find) && $this->err('订单号已存在，请勿重复提交');
		$pkNum       = config('pk_waiting_finished_num');//盘口订单限制
		$traderLimit = config('trader_pp_max_unfinished_order');
		if ($pkNum > 0) {
			$count = Db::name('order_buy')->where('buy_id', $this->merchant['id'])->where('status', 'in', [0, 1])->count();
			if ($count >= $pkNum) {
				$this->err('您有未完成的订单');
			}
		}
		//设置承兑商在线状态
		$ids = Db::name('login_log')->where('online=1 and unix_timestamp(now())-update_time<1800')->column('merchant_id');
		Db::query('Update think_merchant set online=0');
		Db::name('merchant')->where('id', 'in', $ids)->update(['online' => 1]);
		//系统自动选择在线的承兑商和能够交易这个金额的承兑商
		$where['state']  = 1;
		$where['amount'] = ['egt', $data['amount']];
		$where['usdt']   = ['egt', $data['amount']];
		//判断是否商户匹配交易
		$matchTrader    = $this->merchant['pptrader'];
		$matchTraderArr = explode(',', $matchTrader);
		if (!empty($matchTrader) && is_array($matchTraderArr)) {
			$where['c.id'] = ['in', $matchTraderArr];
		}
		$join      = [['__MERCHANT__ c', 'a.userid=c.id', 'LEFT']];
		$ads       = Db::name('ad_sell')->field('a.*, c.id as traderid, c.mobile')->alias('a')->join($join)->group('a.id')->where($where)->order('online desc,price asc,averge asc,pp_amount asc,id asc')->select();
		$onlineAd  = [];
		$actualAmt = 0;
		//$this->suc($ads);
		foreach ($ads as $k => $v) {
			$total = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 9)->sum('deal_num');
			if (($v['amount'] - $total) < $data['amount']) {
				continue;
			}
			$actualAmt = $data['amount'] * $v['price'];
			if ($v['min_limit'] > $actualAmt) {
				continue;
			}
			if ($v['max_limit'] < $actualAmt) {
				continue;
			}
			//判断承兑商是否被其它盘口设置过
			if (empty($matchTrader)) {
				$find = Db::name('merchant')->where('pptrader', 'like', '%' . $v['traderid'] . '%')->find();
				if (!empty($find)) {
					continue;
				}
			}
			//判断未完成的单子
			$traderCounter = Db::name('order_buy')->where('sell_id', $v['traderid'])->where('status', 'in', [0, 1])->count();
			if ($traderLimit && $traderCounter >= $traderLimit) {
				continue;
			}
			$onlineAd = $v;
			break;
		}
		if (empty($onlineAd)) {
			$this->err('暂无可用订单');
		}
		//开始冻结承兑商usdt
		Db::startTrans();
		try {
			$checkCode = $this->check_code();
			$rs1       = balanceChange(FALSE, $onlineAd['traderid'], -$data['amount'], 0, $data['amount'], 0, BAL_BOUGHT, $data['orderid'], '商家买币');
			$rs4       = Db::name('merchant')->where('id', $onlineAd['traderid'])->setInc('pp_amount', 1);
			//$rs6       = Db::name('ad_sell')->where('id', $onlineAd['id'])-> setDec('remain_amount', $data['amount']);
			//$rs7       = Db::name('ad_sell')->where('id', $onlineAd['id'])-> setInc('trading_volume', $data['amount']);
			$rs2 = Db::name('order_buy')->insertGetId([
				'buy_id'       => $this->merchant['id'],//接口请求时,返回商户的id,放行时增加商户的USDT,有疑问?!
				'sell_id'      => $onlineAd['traderid'],
				'sell_sid'     => $onlineAd['id'],
				'deal_amount'  => $actualAmt,
				'deal_num'     => $data['amount'],
				'deal_price'   => $onlineAd['price'],
				'ctime'        => time(),
				'ltime'        => config('order_expire'),
				'order_no'     => $data['orderid'],
				'buy_username' => $data['username'],
				'buy_address'  => $data['address'],
				'return_url'   => $data['return_url'],
				'notify_url'   => $data['notify_url'],
				'orderid'      => $data['orderid'],
				'check_code'   => $checkCode,
				'status'       => 1
			]);
			if ($rs1 && $rs2 && $rs4) {
				// 提交事务
				Db::commit();
				//todo 发送短信给承兑商
				if (!empty($onlineAd['mobile'])) {
					$send_content = config('send_sms_notify');
					if ($send_content) {
						$content = str_replace('{usdt}', round($data['amount'], 2), $send_content);
						$content = str_replace('{cny}', round($actualAmt, 2), $content);
						// $content = str_replace('{tx_id}', $data['orderid'], $content);
						$content = str_replace('{tx_id}', '', $content);
						$content = str_replace('{check_code}', '' . $checkCode . '', $content);
						sendSms($onlineAd['mobile'], $content);
						sendNotice($onlineAd['id'], '你有订单已匹配, 请及时处理', $content);
					}
				}
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$url       = $http_type . $_SERVER['HTTP_HOST'] . '/merchant/pay?id=' . $rs2 . '&appid=' . $data['appid'];
				$this->suc($url);
			} else {
				// 回滚事务
				Db::rollback();
				$this->err('提交失败');
			}
		} catch (DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->err('提交失败，参考信息：' . $e->getMessage());
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
	public function tradeFiat() {
		//!config('web_api_close') && $this->err('通道维护中, 约30分钟恢复');
		$data = input('post.');
		(empty($data['amount']) || $data['amount'] <= 0) && $this->err('请输入正确的充值金额');
		$data['amount'] < 100 && $this->err('你的充值金额不能小于100');
		$data['amount'] > 10000 && $this->err('你的充值金额不能大于10000');
		empty($data['username']) && $this->err('用户名不正确');
		(strlen($data['username']) >= 15) && $this->err('用户名不能超过15个字符');
		empty($data['orderid']) && $this->err('订单号不能为空');
		empty($data['return_url']) && $this->err('同步通知页面地址错误');
		empty($data['notify_url']) && $this->err('异步回调页面地址错误');
		(empty($data['type']) || !in_array($data['type'], ['wxpay', 'alipay', 'unionpay', 'bank', 'all'])) && $this->err('支付方式不正确');
		$data['type'] == 'all' && ($data['type'] = 'alipay');
		$find = Db::name('order_buy')->where('orderid', $data['orderid'])->find();
		!empty($find) && $this->err('订单号已存在，请勿重复提交');
		$pkNum       = config('pk_waiting_finished_num');
		$traderLimit = config('trader_pp_max_unfinished_order');
		// if ($pkNum > 0) {
		// 	$count = Db::name('order_buy')->where('buy_id', $this->merchant['id'])->where('status', 'in', [0, 1])->count();
		// 	($count >= $pkNum) && $this->err('您有未完成的订单');
		// }
		//设置承兑商在线状态
		$ids = Db::name('login_log')->where('online=1 and unix_timestamp(now())-update_time<1800')->column('merchant_id');
		Db::query('UPDATE think_merchant SET online=0');
		Db::name('merchant')->where('id', 'in', $ids)->update(['online' => 1]);
		//系统自动选择在线的承兑商和能够交易这个金额的承兑商
		$where                         = ['state' => 1, 'min_limit' => ['elt', $data['amount']], 'max_limit' => ['egt', $data['amount']]];
		$method                        = ['bank' => 'pay_method', 'alipay' => 'pay_method2', 'wxpay' => 'pay_method3', 'unionpay' => 'pay_method4'];
		$where[$method[$data['type']]] = ['gt', 0];
		//判断是否商户匹配交易
		$matchTrader    = $this->merchant['pptrader'];
		$matchTraderArr = explode(',', $matchTrader);
		(!empty($matchTrader) && is_array($matchTraderArr)) && ($where['c.id'] = ['in', $matchTraderArr]);
		$join = [['__MERCHANT__ c', 'a.userid=c.id', 'LEFT']];
		// 匹配所有订单
		//$ads  = Db::name('ad_sell')->field('a.*, c.id as traderid, c.mobile, c.usdt')->alias('a')->join($join)->group('a.id')->where($where)->order('online DESC,price ASC,averge ASC,pp_amount ASC,id ASC')->select();
		$ads = Db::name('ad_sell')->field('a.*, c.id as traderid, c.mobile, c.usdt, c.usdtd')->alias('a')->join($join)->group('a.id')->where($where)->order('match_time ASC, pp_amount ASC,online DESC,averge ASC,price ASC,id ASC')->select();
		// $ads  = Db::name('ad_sell')->field('a.*, c.id as traderid, c.mobile, c.usdt')->alias('a')->join($join)->group('a.id')->where($where)->orderRaw(' rand() ')->select();
		$onlineAd         = [];
		$actualAmt        = 0;
		$minSellOrder     = NULL;
		$minCount         = 1;
		$matchTraderEmpty = empty($matchTrader);
		foreach ($ads as $k => $v) {
			//开始判断挂单剩余
			//$total     = Db::name('order_buy')->where('sell_sid', $v['id'])->whereNotIn('status', '5,9')->sum('deal_num');
			$actualAmt = number_format($data['amount'] / $v['price'], 8, '.', ''); //todo 修改成动态价格查询匹配的价格
			// 冻结余额不足, 或者订单余量不足, 跳过
			if ($v['remain_amount'] < $actualAmt || $v['usdtd'] < $actualAmt) continue;
			//判断可匹配用户
			if ($matchTraderEmpty && Db::name('merchant')->where('pptrader', 'like', '%' . $v['traderid'] . '%')->find()) continue;
			if ($v['usdtd'] < $actualAmt) continue;
			//判断未完成的单子
			$traderCounter = Db::name('order_buy')->where('sell_id', $v['traderid'])->where('status', 'in', [0, 1])->count();
			if ($traderCounter < $minCount) {
				$minCount     = $traderCounter;
				$minSellOrder = $v;
			}
			if ($traderLimit && $traderCounter >= $traderLimit) continue;
			// 同金额不允许匹配
			if ($traderCounter > 0) {
				$sameAmtCount = Db::name('order_buy')->where('sell_id', $v['traderid'])->where('status', 'in', [0, 1])->where('raw_amount', $data['amount'])->count();
				if ($sameAmtCount > 0) continue;
			}
			if (Cache::has('sell_order_lock_' . $v['id'])) continue; //锁单不允许同一张单同时在卖
			Cache::set('sell_order_lock_' . $v['id'], $v['id'], 5);
			$onlineAd = $v;
			break;
		}
		if (!$onlineAd) {
			!$minSellOrder && $this->err('暂无可用订单');
			$onlineAd = $minSellOrder;
		}
		//开始冻结承兑商usdt
		Db::startTrans();
		try {
			$checkCode = $this->check_code();
			// 更新卖家最后匹配时间与次数
			$sellerRes = Db::name('merchant')->where('id', $onlineAd['traderid'])->update(['pp_amount' => Db::raw('pp_amount + 1'), 'match_time' => time()]);
			// 更新卖单委托的剩余数量, 与正在交易的数量
			$sellOrderRes = Db::name('ad_sell')->where('id', $onlineAd['id'])->update(['remain_amount' => Db::raw('remain_amount - ' . $actualAmt), 'trading_volume' => Db::raw('trading_volume + ' . $actualAmt),]);
			// 创建的交易订单信息
			$orderAddRes = Db::name('order_buy')->insertGetId([
				'buy_id'       => $this->merchant['id'],//接口请求时,返回商户的id,放行时增加商户的USDT,有疑问?!
				'sell_id'      => $onlineAd['traderid'],
				'sell_sid'     => $onlineAd['id'],
				'raw_amount'   => $data['amount'],
				'raw_num'      => $actualAmt,
				'deal_amount'  => $data['amount'],
				'deal_num'     => $actualAmt,
				'deal_price'   => $onlineAd['price'],
				'ctime'        => time(),
				'ltime'        => config('order_expire'),
				'order_no'     => $data['orderid'],
				'buy_username' => $data['username'],
				'buy_address'  => $data['address'],
				'return_url'   => $data['return_url'],
				'notify_url'   => $data['notify_url'],
				'orderid'      => $data['orderid'],
				'check_code'   => $checkCode,
				'status'       => 0,
			]);
			if ($sellerRes && $sellOrderRes && $orderAddRes) {
				// 提交事务
				Db::commit();
				//发送短信给承兑商
				if (!empty($onlineAd['mobile'])) {
					$send_content = config('send_sms_notify');
					$content      = str_replace('{usdt}', round($actualAmt, 2), $send_content);
					$content      = str_replace('{cny}', round($data['amount'], 2), $content);
					// $content      = str_replace('{tx_id}', $data['orderid'], $content);
					$content = str_replace('{tx_id}', '', $content);
					$content = str_replace('{check_code}', '' . $checkCode . '', $content);
					sendSms($onlineAd['mobile'], $content);
					sendNotice($onlineAd['id'], '你有订单已匹配, 请及时处理', $content);
				}
				$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
				$url       = $http_type . $_SERVER['HTTP_HOST'] . '/api/pay?id=' . $orderAddRes . '&appid=' . $data['appid'] . '&type=' . $data['type'];
				Cache::rm('sell_order_lock_' . $onlineAd['id']);
				$this->suc($url);
			} else {
				// 回滚事务
				Db::rollback();
				Cache::rm('sell_order_lock_' . $onlineAd['id']);
				$this->err('提交失败');
			}
		} catch (DbException $e) {
			// 回滚事务
			Db::rollback();
			$this->err('提交失败，参考信息：' . $e->getMessage());
		}
	}

	public function OrderList() {
		$data = input('post.orderid');
		!$data && $this->err('填写订单号');
		$model2            = new OrderModel();
		$where['order_no'] = $data;
		$list              = $model2->getOne($where, 'id DESC');
		!$list && $this->err('订单不存在');
		$arr ['order_no']     = $list['order_no'];    //订单号
		$arr ['deal_amount']  = $list['deal_amount']; //金额
		$arr ['buy_username'] = $list['buy_username'];//买家
		$arr ['deal_num']     = $list['deal_num'];    //交易数量
		$arr ['deal_price']   = $list['deal_price'];  //交易价格
		$arr ['ctime']        = $list['ctime'];       //创建时间
		$arr ['status']       = $list['status'];      //交易状态
		$this->suc($arr);
	}

	private function checkSign($data) {
		ksort($data);
		empty($data['appid']) && exit(json_encode(['status' => 0, 'err' => '缺少appid参数' . __LINE__]));
		empty($data['type']) && exit(json_encode(['status' => 0, 'err' => '缺少支付方式' . __LINE__]));
		$appsecret_arr = Db::name('merchant')->where(['appid' => $data['appid']])->find();
		empty($appsecret_arr) && exit(json_encode(['status' => 0, 'err' => 'appid不存在' . __LINE__]));
		($appsecret_arr['status'] == 0) && exit(json_encode(['status' => 0, 'err' => 'appid被禁用' . __LINE__]));
		$sign = $data['sign'];
		unset($data['sign']);
		$serverStr = '';
		foreach ($data as $k => $v) {
			$serverStr = $serverStr . $k . trim($v);
		}
		$reserverStr  = $serverStr . $appsecret_arr['key'];
		$reserverSign = strtoupper(sha1($reserverStr));
		($sign != $reserverSign) && exit(json_encode(['status' => 0, 'err' => '签名错误' . __LINE__]));
	}
}

?>