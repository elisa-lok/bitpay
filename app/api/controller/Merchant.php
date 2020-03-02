<?php
namespace app\api\controller;
use app\home\model\OrderModel;
use think\Db;

class Merchant extends Base {
	private $model;
	private $merchant;

	public function _initialize() {
		parent::_initialize();
		$this->model = new \app\common\model\Usdt();
		$this->checkSign(input('post.'));
		$this->merchant = Db::name('merchant')->where(['appid' => input('post.appid')])->find();
	}

	public function getInfo() {
		$return = $this->model->index('getinfo', $addr = NULL, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
		$this->suc($return);
	}

	/**
	 * 生成地址
	 * @param $username 用户名
	 */
	public function newAddress() {
		$data = input('post.');
		if (empty($data['username'])) {
			$this->err('用户名不能为空');
		}
		$return = $this->model->index('getnewaddress', $addr = NULL, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
		if ($return['code'] == 1 && !empty($return['data'])) {
			$rs = Db::name('merchant_user_address')->insert(['merchant_id' => $this->merchant['id'], 'username' => $data['username'], 'address' => $return['data'], 'addtime' => time()]);
			if ($rs) {
				if ($this->merchant['pid'] > 0) {
					$request_param = "username=>" . $data['username'];
					apilog($this->merchant['pid'], $this->merchant['id'], '生成地址', $request_param, $return['data']);
				}
				$this->suc($return['data']);
			}
			$this->err('数据库更新失败');
		}
		$this->err('生成钱包地址失败');
	}

	/**
	 * 充值记录接口
	 * address:钱包地址
	 */
	public function rechargeRecord() {
		$data = input('post.');
		empty($data['address']) && $this->err('钱包地址不能为空');
		$list = Db::name('merchant_user_recharge')->field('from_address, to_address, txid, mum, status, confirmations')->where(['to_address' => $data['address'], 'merchant_id' => $this->merchant['id']])->select();
		if ($this->merchant['pid'] > 0) {
			$request_param = "address=>" . $data['address'];
			apilog($this->merchant['pid'], $this->merchant['id'], '充值记录', $request_param, json_encode($list));
		}
		$this->suc($list);
	}

	/**
	 * 获取usdt账户余额
	 * address:钱包地址
	 */
	public function getBalance() {
		$data = input('post.');
		empty($data['address']) && $this->err('钱包地址不能为空');
		$return = $this->model->index('getbalance', $data['address'], $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
		($return['code'] == 0) && $this->err($return['msg']);
		if ($this->merchant['pid'] > 0) {
			$request_param = "address=>" . $data['address'];
			apilog($this->merchant['pid'], $this->merchant['id'], '获取usdt账户余额', $request_param, $return['data']);
		}
		$this->suc($return['data']);
	}

	/**
	 * 用户提币接口
	 * address:钱包地址，num:数量，username:用户名
	 */
	public function makeWithdraw() {
		$data = input('post.');
		empty($data['address']) && $this->err('钱包地址不能为空');
		empty($data['num']) && $this->err('提现数量不能为空');
		($data['num'] < 0) && $this->err('请输入正确的提现数量');
		empty($data['username']) && $this->err('用户名不能为空');
		$usdt = $this->merchant['usdt'];
		($usdt * 100000000 < $data['num'] * 100000000) && $this->err('商户余额不足');
		$orderSn = createOrderNo(2, $this->merchant['id']);
		$rs      = Db::name('merchant_user_withdraw')->insert([
				'merchant_id' => $this->merchant['id'],
				'address'     => $data['address'],
				'username'    => $data['username'],
				'num'         => $data['num'],
				'addtime'     => time(),
				'ordersn'     => $orderSn
		]);
		if (!$rs) {
			$this->err('提交失败，请稍后再试');
		} else {
			if ($this->merchant['pid'] > 0) {
				$request_param = "address=>" . $data['address'] . 'num=>' . $data['num'] . 'username=>' . $data['username'];
				apilog($this->merchant['pid'], $this->merchant['id'], '获取usdt账户余额', $request_param, $orderSn);
			}
			$this->suc($orderSn);
		}
	}

	/**
	 * 获取用户提币的状态
	 * ordersn:订单号
	 */
	public function getWithdraw() {
		$data = input('post.');
		empty($data['ordersn']) && $this->err('提币订单号不能为空');
		$withdraw = Db::name('merchant_user_withdraw')->where(['ordersn' => $data['ordersn']])->find();
		empty($withdraw) && $this->err('提币订单号不存在');
		if ($this->merchant['pid'] > 0) {
			$request_param = "ordersn=>" . $data['ordersn'];
			apilog($this->merchant['pid'], $this->merchant['id'], '获取用户提币状态', $request_param, json_encode(['status' => $withdraw['status'], 'txid' => $withdraw['txid']]));
		}
		$this->suc(['status' => $withdraw['status'], 'txid' => $withdraw['txid']]);
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