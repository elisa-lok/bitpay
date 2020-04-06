<?php
namespace app\home\controller;
use app\common\model\Data;
use app\common\model\PHPExcel;
use app\common\model\Usdt;
use app\home\model\AdbuyModel;
use app\home\model\AddressModel;
use app\home\model\AdModel;
use app\home\model\BankModel;
use app\home\model\DetailModel;
use app\home\model\MerchantModel;
use app\home\model\OrderModel;
use app\home\model\RechargeModel;
use app\home\model\TibiModel;
use app\home\model\WithdrawModel;
use app\home\model\WxModel;
use app\home\model\YsfModel;
use app\home\model\ZfbModel;
use com\GoogleAuthenticator;
use think\Cache;
use think\db;
use Think\Exception;
use think\Exception\DbException;
use think\request;

class Merchant extends Base {
	//商户首页
	public function index() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$model = new MerchantModel();
		$this->assign('merchant', $model->getUserByParam($this->uid, 'id'));
		$myInfo = $model->getUserByParam($this->uid, 'id');
		$this->assign('myacc', $model->getUserByParam($myInfo['pid'], 'id'));
		$ids  = Db::name('article_cate')->field('id, name')->order('orderby asc')->select();
		$list = Db::name('article_cate')->field('a.name, b.id, b.title, b.cate_id, b.create_time')->alias('a')->join('article b', 'a.id=b.cate_id')->where('a.status', 1)->select();
		//$haveAdSum = Db::name('ad_sell')->where('userid', $this->uid)->where('state', 1)->sum('amount');
		$haveAdSum = Db::name('ad_sell')->where('userid', $this->uid)->sum('remain_amount');
		foreach ($ids as $k => $v) {
			foreach ($list as $kk => $vv) {
				if ($v['id'] == $vv['cate_id']) {
					$ids[$k]['article'][] = $vv;
				}
			}
		}
		empty($myInfo['vcode']) && ($myInfo['vcode'] = generateVCode($this->uid));
		$downloadUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/send_notify_1.0.apk';
		$notifyUrl   = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/send/' . $myInfo['vcode'];
		$this->assign('downloadUrl', $downloadUrl);
		$this->assign('notifyUrl', $notifyUrl);
		$this->assign('article', $ids);
		$this->assign('froze', round($haveAdSum, 8));
		$this->assign('price', getUsdtPrice());
		return $this->fetch();
	}

	public function checkpaypass() {
		if (request()->isPost()) {
			$password = input('post.paypassword');
			(empty($password)) && $this->error('请输入交易密码');
			$model = new MerchantModel();
			$user  = $model->getUserByParam($this->uid, 'id');
			($user['paypassword'] != md5($password)) ? $this->error('交易密码错误') : $this->success('ok');
		}
	}

	public function detail() {
		$id     = input('param.id');
		$model  = new DetailModel();
		$detail = $model->getDetail($id);
		$this->assign('detail', $detail);
		return $this->fetch();
	}

	public function setting() {
		if (isset($_GET['_pjax'])) {
			echo $this->fetch();
		} else {
			//$this->assign('flag', 1);
			//return $this->fetch();
		}
		$this->assign('flag', 1);
		return $this->fetch();
	}

	public function payinfo() {
		$id    = input('post.id');
		$order = Db::name('order_sell')->where('id', $id)->find();
		empty($order) && die('订单信息错误');
		($this->uid != $order['sell_id']) && ($this->uid != $order['buy_id']) && die('无权限查看');
		if ($order['sell_id'] == $this->uid) {                                      //卖家显示内容
			$user = Db::name('merchant')->where('id', $order['buy_id'])->find();      //查找买家信息
		} else {
			//买家显示内容,显示卖家的收款信息
			$user = Db::name('merchant')->where('id', $order['sell_id'])->find();  //查找卖家信息
			// 银行卡
			if ($order['pay'] > 0) {
				$user['bank'] = (new BankModel())->getOne(['merchant_id' => $order['sell_id'], 'id' => $order['pay']])->toArray();
			}
			// 支付宝
			if ($order['pay2'] > 0) {
				$alipay         = (new ZfbModel())->getOne(['merchant_id' => $order['sell_id'], 'id' => $order['pay2']]);
				$user['alipay'] = ['c_bank' => $alipay['c_bank'], 'c_bank_card' => $alipay['c_bank_card'], 'truename' => $alipay['truename'], 'qrcode' => StrToMicroTime($alipay['qrcode'], TRUE)];
			}
			// 微信
			if ($order['pay3'] > 0) {
				$wx         = (new WxModel())->getOne(['merchant_id' => $order['sell_id'], 'id' => $order['pay3']]);
				$user['wx'] = ['c_bank' => $wx['c_bank'], 'c_bank_card' => $wx['c_bank_card'], 'truename' => $wx['truename'], 'qrcode' => $wx['qrcode']];
			}
			// 云闪付
			if ($order['pay4'] > 0) {
				$user['union'] = (new YsfModel())->getOne(['merchant_id' => $order['sell_id'], 'id' => $order['pay4']])->toArray();
			}
		}
		$this->assign('uid', $this->uid);
		$this->assign('user', $user);
		$this->assign('order', $order);
		return $this->fetch();
	}

	public function dosetting() {
		if (request()->isPost()) {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			/*$file = request()->file('avatar');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				$info && $this->error('请上传正确的图片：' . $file->getError());
				$param['headpic'] = $info->getSaveName();
			}*/
			$name       = input('post.name');
			$password   = trim(input('post.password'));
			$payPsw     = trim(input('post.paypassword'));
			$repassword = input('post.password_confirmation');
			$repayPsw   = input('post.paypassword_confirmation');
			($password != $repassword && !empty($password)) && $this->error('登录密码错误！');
			($payPsw != $repayPsw && !empty($payPsw)) && $this->error('交易密码错误！');
			(!$name) && $this->error('请输入用户名');
			if (!empty($payPsw) && !empty($password)) {
				($payPsw == $password) && $this->error('交易密码不能与登录密码相同！');
			}
			/*if (empty($smscode)) {
				$this->error('请填写短信验证码');
			}*/
			/*if ($smscode != session($mobile . '_mcode')) {
				$this->error('短信验证码错误!');
			}*/
			$param['id'] = $this->uid;
			if (!empty($password)) {
				$param['password'] = md5($password);
			}
			if (!empty($payPsw)) {
				$param['paypassword'] = md5($payPsw);
			}
			$param['name'] = $name;
			$model         = new MerchantModel();
			$return        = $model->updateOne($param);
			if ($return['code'] == 1) {
				$user = $model->where('id', $this->uid)->find();
				session('user', $user);
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	//商户用户钱包地址
	public function address() {
		$order = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$model = new AddressModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['merchant_id'] = $this->uid;
		$this->assign('list', $model->getAddress($where, $order));
		return $this->fetch();
	}

	//导出用户钱包地址
	public function outUserAddress() {
		/* [
 ['id','序号'],
 ['username','用户名'],
 ['address','地址'],
 ['addtime','申请时间'],
 ] */
		!$this->uid && $this->error('请登陆操作');
		$where['merchant_id'] = $this->uid;
		$order                = 'id DESC';
		$model                = new AddressModel();
		$data                 = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "用户钱包地址" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
		$Excel['H']          = ['A' => 10, 'B' => 15, 'C' => 40, 'D' => 30];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];                      //纵向垂直高度
		$Excel['sheetTitle'] = "用户钱包地址记录";                                  //大标题，自定义
		$Excel['xlsCell']    = Data::headAddress();
		foreach ($data as $k => $v) {
			$data[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
		}
		PHPExcel::excelPut($Excel, $data);
	}

	public function RechargeAmount() {
		$post = input('post.');
		!$this->uid && $this->error('请登录操作！');
		$model = new MerchantModel();
		$this->assign('merchant', $model->getUserByParam($this->uid, 'id'));
		if ($post) {
			ini_set('display_errors', '1');
			error_reporting(-1);
			$data   = $_POST;
			$reqUrl = $data['req_url'];
			unset($data['req_url']);
			$appKey = $data['appkey'];
			unset($data['appkey']);
			$data['orderid'] = 'T' . str_replace('.', '', microtime(TRUE)) . mt_rand(1000, 9999);
			$data['sign']    = $this->sign($data, $appKey);
			$ch              = curl_init($reqUrl);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			//允许请求以文件流的形式返回
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
			$res = curl_exec($ch); //执行发送
			curl_close($ch);
			die($res);
		} else {
			$txId = 'T' . date('ymdHis') . mt_rand(100000, 999999); //订单号
			$srv  = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
			$url  = $srv . '/api/merchant/requestTraderRechargeRmb';
			$this->assign('url', $url);
		}
		return $this->fetch();
	}
	/*public function sign($dataArr, $key) {
		ksort($dataArr);
		$str = '';
		foreach ($dataArr as $k => $v) {
			$str .= $k . $v;
		}
		$str = $str . $key;
		return strtoupper(sha1($str));
	}*/
	//商户用户充值记录
	public function recharge() {
		$get = input('get.');
		!$this->uid && $this->error('请登陆操作！');
		$order = 'a.id DESC';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$username = input('get.username');
		$status   = input('get.status');
		if (!empty($username)) {
			$where['b.username'] = $username;
		}
		if (isset($status) && $status > 0) {
			$where['a.status'] = $status - 1;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start              = strtotime($get['created_at']['start']);
			$end                = strtotime($get['created_at']['end']);
			$where['a.addtime'] = ['between', [$start, $end]];
		}
		/* if(!empty($get['created_at']['end'])){
 $end = strtotime($get['created_at']['end']);
 $where['a.addtime'] = array('between', array($end));
 } */
		if (!empty($get['buy_amount']['start']) && !empty($get['buy_amount']['end'])) {
			$where['num'] = ['between', [$get['buy_amount']['start'], $get['buy_amount']['end']]];
		}
		/* if(!empty($get['buy_amount']['end'])){
 $where['num'] = array('elt', $get['buy_amount']['end']);
 } */
		$where['a.merchant_id'] = $this->uid;
		$model                  = new RechargeModel();
		$this->assign('list', $model->getRecharge($where, $order));
		return $this->fetch();
	}

	//导出商户用户充值记录
	public function outUserRecharge() {
		/* [
 ['id','序号'],
 ['username','用户名'],
 ['mobile','手机号码'],
 ['num','客户充值USDT'],
 ['from_address','转出地址'],
 ['to_address','转入地址'],
 ['fee','手续费支出'],
 ['mum','实到'],
 ['status','状态'],
 ['addtime','日期'],
 ] */
		!$this->uid && $this->error('请登陆操作');
		$where['a.merchant_id'] = $this->uid;
		$order                  = 'a.id DESC';
		$model                  = new RechargeModel();
		$data                   = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "用户充值记录" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
		$Excel['H']          = ['A' => 10, 'B' => 15, 'C' => 15, 'D' => 35, 'E' => 35, 'F' => 15, 'G' => 15, 'H' => 20, 'I' => 30];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];                                                                             //纵向垂直高度
		$Excel['sheetTitle'] = "用户充值记录";                                                                                           //大标题，自定义
		$Excel['xlsCell']    = Data::head();
		foreach ($data as $k => $v) {
			if ($v['status'] == 0) {
				$data[$k]['status'] = '生成充值订单';
			} elseif ($v['status'] == 1) {
				$data[$k]['status'] = '平台已收款';
			} elseif ($v['status'] == 2) {
				$data[$k]['status'] = '商户已收款';
			}
			$data[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
		}
		PHPExcel::excelPut($Excel, $data);
	}

	//商户用户提币记录
	public function withdraw() {
		!$this->uid && $this->error('请登陆操作');
		$where['merchant_id'] = $this->uid;
		$get                  = input('get.');
		$order                = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$username = input('get.username');
		$status   = input('get.status');
		$orderSn  = input('get.ordersn');
		if (!empty($username)) {
			$where['username'] = $username;
		}
		if (!empty($orderSn)) {
			$where['ordersn'] = $orderSn;
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status - 1;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$where['addtime'] = ['between', [strtotime($get['created_at']['start']), strtotime($get['created_at']['end'])]];
		}
		if (!empty($get['end_at']['start']) && !empty($get['end_at']['end'])) {
			$start            = strtotime($get['end_at']['start']);
			$end              = strtotime($get['end_at']['end']);
			$where['endtime'] = ['between', [$start, $end]];
		}
		if (!empty($get['buy_amount']['start']) && !empty($get['buy_amount']['end'])) {
			$where['num'] = ['between', [$get['buy_amount']['start'], $get['buy_amount']['end']]];
		}
		$model = new WithdrawModel();
		$this->assign('list', $model->getWithdraw($where, $order));
		return $this->fetch();
	}

	//提币记录导出
	public function outUserWithdraw() {
		/* [
 ['id','序号'],
 ['username','用户名'],
 ['num','客户提币USDT'],
 ['address','转出地址'],
 ['fee','手续费'],
 ['mum','实到'],
 ['txid','Txid'],
 ['status','状态'],
 ['addtime','创建日期'],
 ['endtime','审核日期'],
 ] */
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['merchant_id'] = $this->uid;
		$order                = 'id DESC';
		$model                = new WithdrawModel();
		$data                 = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "用户提币记录" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
		$Excel['H']          = ['A' => 10, 'B' => 20, 'C' => 15, 'D' => 40, 'E' => 15, 'F' => 15, 'G' => 50, 'H' => 10, 'I' => 20, 'J' => 20];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];                                                                                        //纵向垂直高度
		$Excel['sheetTitle'] = "用户提币记录";                                                                                                      //大标题，自定义
		$Excel['xlsCell']    = Data::headWithdraw();
		foreach ($data as $k => $v) {
			if ($v['status'] == 0) {
				$data[$k]['status'] = '待审核';
			} elseif ($v['status'] == 1) {
				$data[$k]['status'] = '已通过';
			} elseif ($v['status'] == 2) {
				$data[$k]['status'] = '已拒绝';
			}
			$data[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
			if (!empty($v['endtime'])) {
				$data[$k]['endtime'] = date('Y-m-d H:i:s', $v['endtime']);
			}
		}
		PHPExcel::excelPut($Excel, $data);
	}

	//商户提币记录
	public function tibi() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['merchant_id'] = $this->uid;
		$get                  = input('get.');
		$order                = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderSn)) {
			$where['ordersn'] = ['like', '%' . $orderSn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status - 1;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$where['addtime'] = ['between', [strtotime($get['created_at']['start']), strtotime($get['created_at']['end'])]];
		}
		if (!empty($get['end_at']['start']) && !empty($get['end_at']['end'])) {
			$start            = strtotime($get['end_at']['start']);
			$end              = strtotime($get['end_at']['end']);
			$where['endtime'] = ['between', [$start, $end]];
		}
		if (!empty($get['buy_amount']['start']) && !empty($get['buy_amount']['end'])) {
			$where['num'] = ['between', [$get['buy_amount']['start'], $get['buy_amount']['end']]];
		}
		$model = new TibiModel();
		$this->assign('list', $model->getWithdraw($where, $order));
		return $this->fetch();
	}

	//提币卖币导出
	public function outTiBi() {
		/* [
 ['id','序号'],
 ['ordersn','订单号'],
 ['num','提币USDT'],
 ['address','转出地址'],
 ['fee','手续费'],
 ['mum','实到'],
 ['txid','Txid'],
 ['status','状态'],
 ['addtime','创建日期'],
 ['endtime','审核日期'],
 ] */
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['merchant_id'] = $this->uid;
		$order                = 'id DESC';
		$model                = new TibiModel();
		$data                 = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "商户提币记录" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
		$Excel['H']          = ['A' => 10, 'B' => 20, 'C' => 15, 'D' => 40, 'E' => 15, 'F' => 15, 'G' => 50, 'H' => 10, 'I' => 20, 'J' => 20];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];                                                                                        //纵向垂直高度
		$Excel['sheetTitle'] = "用户提币记录";                                                                                                      //大标题，自定义
		$Excel['xlsCell']    = Data::headTibi();
		foreach ($data as $k => $v) {
			if ($v['status'] == 0) {
				$data[$k]['status'] = '待审核';
			} elseif ($v['status'] == 1) {
				$data[$k]['status'] = '已通过';
			} elseif ($v['status'] == 2) {
				$data[$k]['status'] = '已拒绝';
			} elseif ($v['status'] == 3) {
				$data[$k]['status'] = '已撤销';
			}
			$data[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
			if (!empty($v['endtime'])) {
				$data[$k]['endtime'] = date('Y-m-d H:i:s', $v['endtime']);
			}
		}
		PHPExcel::excelPut($Excel, $data);
	}

	public function addTiBi() {
		$model = new MerchantModel();
		if (request()->isPost()) {
			$address  = input('post.address');
			$num      = input('post.num');
			$remark   = input('post.remark');
			$ga       = input('post.goole');
			$fee1     = getConfig('merchant_tibi_fee');
			$tibi_min = getConfig('merchant_tibi_min');
			$tibi_max = getConfig('merchant_tibi_max');
			$user     = $model->getUserByParam($this->uid, 'id');
			$fee2     = $user['merchant_tibi_fee'];
			$fee      = $fee2;
			if (empty($fee2)) {
				$fee = $fee1;
			}
			(empty($fee)) && $this->error('手续费未设置，请联系管理员');
			(empty($address)) && $this->error('请填写提币地址');
			if (config('wallettype') == 'omni') {
				$model = new Usdt();
				$a     = $model->index('validateaddress', $addr = $address, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
				($a != 1) && $this->error('请填写正确的提币地址');
			}
			if (config('wallettype') == 'erc') {
				if (!(preg_match('/^(0x)?[0-9a-fA-F]{40}$/', $address))) {
					// return false; //满足if代表地址不合法
					$this->error('请填写正确的提币地址');
				}
			}
			($num <= 0) && $this->error('请填写正确的金额');
			($num < $tibi_min || $num > $tibi_max) && $this->error('提币区间：' . $tibi_min . '-' . $tibi_max);
			$feenum = 0;
			// if($fee){
			$feenum = $fee + $fee1;
			// }
			$mum = $num - $feenum;
			($mum <= 0) && $this->error('请填写正确的金额');
			($user['usdt'] < $mum) && $this->error('账户余额不足');
			if (!empty($user['ga'])) {
				$arr         = explode('|', $user['ga']);
				$secret      = $arr[0];
				$ga_is_login = $arr[2];
				if ($ga_is_login) {
					$ga_n = new GoogleAuthenticator();
					// 判断登录有无验证码
					$aa = $ga_n->verifyCode($secret, $ga, 1);
					(!$aa) && $this->error('谷歌验证码错误！');
				}
			}
			Db::startTrans();
			try {
				$orderSn = createOrderNo(1, $this->uid);
				$rs1     = balanceChange(FALSE, $this->uid, -$num, 0, $num, 0, BAL_WITHDRAW, $orderSn, '用户提币');
				$rs2     = Db::name('merchant_withdraw')->insert([
					'merchant_id' => $this->uid,
					'address'     => $address,
					'num'         => $num,
					'fee'         => $feenum,
					'mum'         => $mum,
					'note'        => $remark,
					'addtime'     => time(),
					'ordersn'     => $orderSn
				]);
				if ($rs1 && $rs2) {
					// 提交事务
					Db::commit();
					$this->success('提交成功，请等待审核', url('home/merchant/tibi'));
				} else {
					// 回滚事务
					Db::rollback();
					$this->error('提交失败');
				}
			} catch (DbException $e) {
				// 回滚事务
				Db::rollback();
				$this->error('提交失败，参考信息：' . $e->getMessage());
			}
		} else {
			!$this->uid && $this->error('请登陆操作', url('home/login/login'));
			$user = $model->getUserByParam($this->uid, 'id');
			$show = 0;
			if (!empty($user['ga'])) {
				$arr         = explode('|', $user['ga']);
				$ga_is_login = $arr[2];
				if ($ga_is_login) {
					$show = 1;
				}
			}
			$this->assign('show', $show);
			$this->assign('user', $user);
			$this->assign('merchant_tibi_fee', getConfig('merchant_tibi_fee'));
			$this->assign('tibi_min', getConfig('merchant_tibi_min'));
			$this->assign('tibi_max', getConfig('merchant_tibi_max'));
			return $this->fetch();
		}
	}

	public function cancel() {
		if (request()->isPost()) {
			$id = input('post.id');
			!$id && $this->error('参数错误');
			$model  = new TibiModel();
			$return = $model->cancel($id);
			return json($return);
		}
	}

	public function merchantSet() {
		!$this->uid && $this->error('请登陆操作');
		$model = new MerchantModel();
		if (request()->isPost()) {
			!$this->uid && $this->error('登录已经失效,请重新登录!');
			$delete      = '';
			$gacode      = trim(input('post.ga'));
			$type        = trim(input('post.type'));
			$ga_login    = (input('post.ga_login') == FALSE ? 0 : 1);
			$ga_transfer = (input('post.ga_transfer') == FALSE ? 0 : 1);
			$ga_trust    = (input('post.ga_trust') == FALSE ? 0 : 1);
			$ga_binding  = (input('post.ga_binding') == FALSE ? 0 : 1);
			!$gacode && $this->error('请输入验证码!');
			if ($type == 'add') {
				$secret = session('secret');
				!$secret && $this->error('验证码已经失效,请刷新网页!');
			} elseif (($type == 'updat') || ($type == 'delet')) {
				$user = $model->getUserByParam($this->uid, 'id');
				!$user['ga'] && $this->error('还未设置谷歌验证码!');
				$arr    = explode('|', $user['ga']);
				$secret = $arr[0];
				$delete = ($type == 'delet' ? 1 : 0);
			} else {
				$this->error('操作未定义');
			}
			$ga = new GoogleAuthenticator();
			if ($ga->verifyCode($secret, $gacode, 1)) {
				$ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer . '|' . $ga_trust . '|' . $ga_binding : '');
				$rs     = $model->updateOne(['id' => $this->uid, 'ga' => $ga_val]);
				$rs ? $this->success('操作成功') : $this->error('操作失败');
			} else {
				$this->error('验证失败');
			}
		} else {
			$user  = $model->getUserByParam($this->uid, 'id');
			$is_ga = ($user['ga'] ? 1 : 0);
			$this->assign('is_ga', $is_ga);
			if (!$is_ga) {
				$ga     = new GoogleAuthenticator();
				$secret = $ga->createSecret();
				session('secret', $secret);
				$this->assign('Asecret', $secret);
				$zhanghu = $user['mobile'] . ' - ' . $_SERVER['HTTP_HOST'];
				$this->assign('zhanghu', $zhanghu);
				$qrCodeUrl = $ga->getQRCodeGoogleUrl($user['mobile'] . '%20-%20' . $_SERVER['HTTP_HOST'], $secret);
				$this->assign('qrCodeUrl', $qrCodeUrl);
				return $this->fetch('merchantSet');
			} else {
				$arr = explode('|', $user['ga']);
				$this->assign('ga_login', $arr[1]);
				$this->assign('ga_transfer', $arr[2]);
				$this->assign('ga_trust', $arr[3]);
				$this->assign('ga_binding', $arr[4]);
				$this->assign('Asecret', '');
				return $this->fetch('merchantSet');
			}
			return $this->fetch('merchantSet');
		}
	}

	public function applyAgent() {
		!$this->uid && $this->error('登录已经失效,请重新登录!');
		$model    = new MerchantModel();
		$merchant = $model->getUserByParam($this->uid, 'id');
		($merchant['agent_check'] != 0) && $this->error('请勿重复申请');
		$flag = $model->updateOne(['id' => $this->uid, 'agent_check' => 3]);
		($flag['code'] == 1) ? $this->success('申请成功，请等待审核') : $this->error($flag['msg']);
	}

	public function applyTrader() {
		!$this->uid && $this->error('登录已经失效,请重新登录!');
		$model    = new MerchantModel();
		$merchant = $model->getUserByParam($this->uid, 'id');
		($merchant['trader_check'] != 0) && $this->error('请勿重复申请');
		$flag = $model->updateOne(['id' => $this->uid, 'trader_check' => 3]);
		($flag['code'] == 1) ? $this->success('申请成功，请等待审核') : $this->error($flag['msg']);
	}

	public function downmerchant() {
		$order = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['pid'] = $this->uid;
		$get          = input('get.');
		$order        = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderid = input('get.orderid');
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderid)) {
			$where['id'] = ['like', '%' . $orderid . '%'];
		}
		if (!empty($orderSn)) {
			$where['name'] = ['like', '%' . $orderSn . '%'];
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$where['addtime'] = ['between', [strtotime($get['created_at']['start']), strtotime($get['created_at']['end'])]];
		}
		$this->assign('list', $model->getMerchant($where, $order));
		return $this->fetch();
	}

	public function shanghurecord() {
		$order = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['pid']      = $this->uid;
		$where['reg_type'] = 1;
		$get               = input('get.');
		$order             = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderid = input('get.orderid');
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderid)) {
			$where['id'] = ['like', '%' . $orderid . '%'];
		}
		if (!empty($orderSn)) {
			$where['name'] = ['like', '%' . $orderSn . '%'];
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$where['addtime'] = ['between', [strtotime($get['created_at']['start']), strtotime($get['created_at']['end'])]];
		}
		$lists = $model->getMerchantStatistics($where, $order);
		$today = strtotime(date('Y-m-d 00:00:00'));
		foreach ($lists as $key => $list) {
			$recharge_number = $list->orderSell()->count('id');                                                                                    // 充值笔数
			$recharge_amount = $list->orderSell()->sum('deal_amount');                                                                             // 充值数量
			$success_number  = $list->orderSell()->where('status', 4)->count('id');                                                                // 成功笔数
			$success_amount  = $list->orderSell()->where('status', 4)->sum('deal_amount');                                                         // 成功数量
			$buy_number      = $list->orderSell()->count('id');                                                                                    // 购买数量
			if ($success_number == 0 || $buy_number == 0) $success_rate = 0; else $success_rate = round(($success_number / $buy_number) * 100, 2); // 成功率
			// 获取当天笔数
			$where2['ctime']      = ['egt', $today];
			$today_number         = $list->orderSell()->where($where2)->count('id');                                                                                           // 当天笔数
			$today_amount         = $list->orderSell()->where($where2)->sum('deal_amount');                                                                                    // 当天数量
			$today_success_number = $list->orderSell()->where($where2)->where('status', 4)->count('id');                                                                       // 当天成功笔数
			$today_success_amount = $list->orderSell()->where($where2)->where('status', 4)->sum('deal_amount');                                                                // 当天成功数量
			if ($today_success_number == 0 || $today_number == 0) $today_success_rate = 0; else $today_success_rate = round(($today_success_number / $today_number) * 100, 2); // 成功率
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
		$this->assign('list', $lists);
		return $this->fetch();
	}

	public function editdown() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$model = new MerchantModel();
		if (request()->isPost()) {
			$id                = input('post.id');
			$merchant_tibi_fee = input('post.merchant_tibi_fee');
			$user_withdraw_fee = input('post.user_withdraw_fee');
			$user_recharge_fee = input('post.user_recharge_fee');
			$flag              = $model->updateOne(['id' => $id, 'merchant_tibi_fee' => $merchant_tibi_fee, 'user_withdraw_fee' => $user_withdraw_fee, 'user_recharge_fee' => $user_recharge_fee]);
			($flag['code'] == 1) ? $this->success('编辑成功') : $this->error($flag['msg']);
		} else {
			$id = $_GET['id'];
			!$id && $this->error('参数错误');
			$merchant = $model->getUserByParam($id, 'id');
			(empty($merchant) || $merchant['pid'] != $this->uid) && $this->error('商户不存在');
			$this->assign('merchant', $merchant);
			return $this->fetch();
		}
	}

	public function checkdown() {
		$id   = input('get.id/d');
		$type = input('get.type/d');
		($type != 1 && $type != 2) && $this->error('审核类型错误');
		$check    = $type;
		$m        = new MerchantModel();
		$merchant = $m->getUserByParam($id, 'id');
		(empty($merchant) || $merchant['pid'] != $this->uid) && $this->error('下级商户不存在');
		($merchant['reg_check'] != 0) && $this->error('用户已审核');
		if ($merchant['reg_type'] == 1) {
			$update = ['reg_check' => $check];
		} elseif ($merchant['reg_type'] == 2) {
			$update = ['reg_check' => $check, 'trader_check' => $check == 1 ? 1 : 2];
		} elseif ($merchant['reg_type'] == 3) {
			//代理商
			for (; TRUE;) {
				$tradeno = tradenoa();
				if (!Db::name('merchant')->where('invite', $tradeno)->find()) {
					break;
				}
			}
			$update = ['reg_check' => $check, 'agent_check' => $check == 1 ? 1 : 2, 'invite' => $tradeno];
		} else {
			$this->error('下级商户注册类型错误');
		}
		(Db::name('merchant')->where('id', $id)->update($update)) ? $this->success('审核成功') : $this->error('审核失败');
	}

	public function agentreward() {
		$order = 'a.id DESC';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['uid'] = $this->uid;
		$username     = input('get.username');
		$get          = input('get.');
		if (!empty($username)) {
			$duid          = Db::name('merchant')->where('name', $username)->value('id');
			$where['duid'] = $duid;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start                = strtotime($get['created_at']['start']);
			$end                  = strtotime($get['created_at']['end']);
			$where['create_time'] = ['between', [$start, $end]];
		}
		$this->assign('list', $model->getAgentReward($where, $order));
		return $this->fetch();
	}

	public function downapilog() {
		$order = 'a.id DESC';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['uid'] = $this->uid;
		$username     = input('get.username');
		$get          = input('get.');
		if (!empty($username)) {
			$duid          = Db::name('merchant')->where('name', $username)->value('id');
			$where['duid'] = $duid;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start                = strtotime($get['created_at']['start']);
			$end                  = strtotime($get['created_at']['end']);
			$where['create_time'] = ['between', [$start, $end]];
		}
		$this->assign('list', $model->getApiLog($where, $order));
		return $this->fetch();
	}

	public function traderrecharge() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$model    = new MerchantModel();
		$merchant = $model->getUserByParam($this->uid, 'id');
		($merchant['trader_check'] != 1) && $this->error('请先申请承兑商', url('home/merchant/index'));
		$qianbao1 = $merchant['usdtb'];
		$qianbao2 = $merchant['usdte'];
		if (config('wallettype') == 'omni') {
			//新方法
			if (!$qianbao1) {
				$address = Db::name('address')->where(['status' => 0, 'type' => 'btc'])->find();
				(!$address) && $this->error('系统可用地址池错误');
				$rs = $model->updateOne(['id' => $this->uid, 'usdtb' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = $this->uid;
					Db::name('address')->where('address', $address['address'])->update($mp);
					$qianbao1 = $address['address'];
					$rs       = Db::name('merchant_user_address')->insert(['merchant_id' => $this->uid, 'username' => session('username'), 'address' => $qianbao1, 'addtime' => time()]);
				} else {
					$this->error($rs['msg']);
				}
			}
			$this->assign('qianbao', $qianbao1);
		}
		if (config('wallettype') == 'erc') {
			//新方法
			if (!$qianbao2) {
				$address = Db::name('address')->where(['status' => 0, 'type' => 'eth'])->find();
				(!$address) && $this->error('系统可用地址池错误');
				$rs = $model->updateOne(['id' => $this->uid, 'usdte' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = $this->uid;
					Db::name('address')->where('address', $address['address'])->update($mp);
					$qianbao2 = $address['address'];
					$rs       = Db::name('merchant_user_address')->insert(['merchant_id' => $this->uid, 'username' => session('username'), 'address' => $qianbao2, 'addtime' => time()]);
				} else {
					$this->error($rs['msg']);
				}
			}
			$this->assign('qianbao', $qianbao2);
		}
		if (config('wallettype') == 'all') {
			//新方法
			if (!$qianbao1) {
				$address = Db::name('address')->where(['status' => 0, 'type' => 'btc'])->find();
				(!$address) && $this->error('系统可用地址池错误');
				$rs = $model->updateOne(['id' => $this->uid, 'usdtb' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = $this->uid;
					Db::name('address')->where('address', $address['address'])->update($mp);
					$qianbao1 = $address['address'];
				} else {
					$this->error($rs['msg']);
				}
			}
			//新方法
			if (!$qianbao2) {
				$address = Db::name('address')->where(['status' => 0, 'type' => 'eth'])->find();
				(!$address) && $this->error('系统可用地址池错误');
				$rs = $model->updateOne(['id' => $this->uid, 'usdtb' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = $this->uid;
					Db::name('address')->where('address', $address['address'])->update($mp);
					$qianbao2 = $address['address'];
				} else {
					$this->error($rs['msg']);
				}
			}
		}
		//新方法
		/*if(!$qianbao){$address=Db::name('address')->where('status',0)->find();if(!$address){$this->error('系统可用地址池错误');}$rs = $model->updateOne(['id'=>$this->uid, 'usdtb'=>$address['address']]);if($rs['code'] == 1){$mp['status']=1;$mp['uid']=$this->uid;Db::name('address')->where('address',$address['address'])->update($mp);$qianbao =$address['address'];}else{$this->error($rs['msg']);}}*/                                                                                                                                                                                                                         //原方法
		/*if (!$qianbao) {$model2 = new \app\common\model\Usdt();$return = $model2->index('getnewaddress', $addr = NULL, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);if ($return['code'] == 1 && !empty($return['data'])) {// $rs = Db::name('merchant_user_address')->insert(['merchant_id'=>$this->merchant['id'], 'username'=>$data['username'], 'address'=>$return['data'], 'addtime'=>time()]); $rs = $model->updateOne(['id' => $this->uid, 'usdtb' => $return['data']]); if ($rs['code'] == 1) { $qianbao = $return['data']; } else { $this->error($rs['msg']); } } else { $this->error('生成钱包地址失败'); } } */// $this->assign('qianbao', $qianbao);
		// $this->assign('qianbao2', $qianbao2);
		$confirms = config('usdt_confirms');
		$this->assign('confirms', $confirms);
		$list = Db::name('merchant_recharge')->where(['merchant_id' => $this->uid])->order('id DESC')->paginate(20);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function newad() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$usdtPriceWay        = config('usdt_price_way');
		$usdtPriceMin        = config('usdt_price_min');
		$usdtPriceMax        = config('usdt_price_max');
		$defaultAddPriceRate = 1 + getTopAgentFeeRate($this->uid);
		$usdtCurrentPrice    = getUsdtPrice();
		$priceLimit          = $usdtPriceWay == 2 ? number_format($usdtCurrentPrice * $defaultAddPriceRate, 6, '.', ',') : $usdtCurrentPrice;
		$m                   = new BankModel();
		$alipay              = new ZfbModel();
		$wx                  = new WxModel();
		$unionpay            = new YsfModel();
		$model               = new MerchantModel();
		$user                = $model->getUserByParam($this->uid, 'id');
		if (request()->isPost()) {
			$amount = input('post.amount');
			($amount <= 0) && showMsg('请输入正确的出售数量', 0);
			$minLimit = input('post.min_limit');
			($minLimit < 100 || $minLimit > 300) && showMsg('最小限额不能超出 100 ~ 300', 0);
			$maxLimit = input('post.max_limit');
			($maxLimit <= 0) && showMsg('请输入正确的最大限额', 0);
			($minLimit > $maxLimit) && showMsg('最小限额不能大于最大限额！', 0);
			$price = $usdtPriceWay == 0 ? input('post.price') : $priceLimit;
			($price > $usdtPriceMax || $price < $usdtPriceMin) && showMsg('价格区间：' . $usdtPriceMin . '~' . $usdtPriceMax, 0);
			($user['trader_check'] != 1) && showMsg('您的承兑商资格未通过');
			Db::name('ad_sell')->where('userid', $this->uid)->whereIn('state', '1,2')->count() > 0 && showMsg('只允许一张挂单, 你有未完成订单');
			($user['usdt'] < $amount) && showMsg('账户余额不足');
			(empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx']) && empty($_POST['ysf'])) && showMsg('请选择收款方式');
			$codes = ['zfb' => (int)$_POST['zfb'], 'bank' => (int)$_POST['bank'], 'wx' => (int)$_POST['wx'], 'ysf' => (int)$_POST['ysf']];
			//查询用户的银行卡信息
			$bank = $m->getOne(['merchant_id' => $this->uid, 'id' => $codes['bank'], 'state' => 1]);
			//查询用户的支付宝信息
			$isAlipay = $alipay->getOne(['merchant_id' => $this->uid, 'id' => (int)$codes['zfb'], 'state' => 1]);
			//查询用户的微信信息
			$isWxpay = $wx->getOne(['merchant_id' => $this->uid, 'id' => $codes['wx'], 'state' => 1]);
			//查询用户的云闪付信息
			$where4['merchant_id'] = $this->uid;
			$where4['id']          = $codes['ysf'];
			$isUnionPay            = $unionpay->getOne($where4);
			($codes['bank'] && !$bank) && showMsg('请先设置您的银行卡账户信息', 0);
			($codes['zfb'] && !$isAlipay) && showMsg('请先设置您的支付宝账户信息', 0);
			($codes['wx'] && !$isWxpay) && showMsg('请先设置您的微信账户信息', 0);
			($codes['ysf'] && !$isUnionPay) && showMsg('请先设置您的云闪付账户信息', 0);
			Db::startTrans();
			// 减少余额 增加冻结余额
			$adNo = $this->getAdvNo();
			$res1 = balanceChange(FALSE, $this->uid, -$amount, 0, $amount, 0, BAL_ENTRUST, $adNo, '用户挂卖单');
			if ($res1) {
				$model2 = new AdModel();
				$flag   = $model2->insertOne([
					'userid'        => $this->uid,
					'add_time'      => time(),
					'coin'          => '0',
					'min_limit'     => $minLimit,
					'max_limit'     => $maxLimit,
					'pay_method'    => $codes['bank'],
					'pay_method2'   => $codes['zfb'],
					'pay_method3'   => $codes['wx'],
					'pay_method4'   => $codes['ysf'],
					'ad_no'         => $adNo,
					'amount'        => $amount,
					'remain_amount' => $amount,
					'price'         => $price,
					'message'       => '',
					'state'         => 1
				]);
				//增加在售挂单数
				$count = $model2->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
				$res   = $model->updateOne(['id' => $this->uid, 'ad_on_sell' => $count]);
				($flag['code'] !== 1) && $this->rollbackAndShow($flag['msg'], 0);
				($res['code'] !== 1) && $this->rollbackAndShow($res['msg'], 0);
				Db::commit();
				showMsg($flag['msg'], 1);
			} else {
				Db::rollback();
				showMsg('挂单失败,无法冻结余额', 0);
			}
		}
		$this->assign('balance', round($user['usdt'], 8));
		$this->assign('frozen', round($user['usdtd'], 8));
		$this->assign('usdt_price', $usdtCurrentPrice);
		$this->assign('usdt_price_min', $usdtPriceMin);
		$this->assign('usdt_price_max', $usdtPriceMax);
		$this->assign('usdt_price_way', $usdtPriceWay);
		$model2          = new AdModel();
		$where['userid'] = $this->uid;
		$list            = $model2->getAd($where, 'id DESC');
		foreach ($list as $k => $v) {
			//$dealNum = Db::name('order_buy')->where(['sell_sid' => $v['id'], 'status' => ['neq', 5], 'status' => ['neq', 9]])->sum('deal_num');
			$dealNum                    = Db::name('order_buy')->where('sell_sid', $v['id'])->where('status', 'neq', 5)->where('status', 'neq', 7)->sum('deal_num');
			$dealNum                    = $dealNum ? $dealNum : 0;
			$list[$k]['deal']           = round($dealNum, 8);
			$list[$k]['remain']         = round($v['amount'] - $list[$k]['deal'], 8);
			$list[$k]['trading_volume'] = round($v['trading_volume'], 8);
			$list[$k]['remain_amount']  = round($v['remain_amount'], 8);
			$list[$k]['price']          = round($v['price'], 8);
		}
		$this->assign('list', $list);
		$this->assign('priceLimit', $priceLimit);
		$banks = $m->where(['merchant_id' => $this->uid, 'state' => 1])->order('id DESC')->select();
		$this->assign('zfb', $alipay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('wx', $wx->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('ysf', $unionpay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('banks', $banks);
		return $this->fetch();
	}

	public function newadbuy() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$usdtPriceWay = config('usdt_price_way_buy');
		$usdtPriceMin = config('usdt_price_min_buy');
		$usdtPriceMax = config('usdt_price_max_buy');
		if ($usdtPriceWay == 2) {
			$priceLimit = getUsdtPrice() + config('usdt_price_add_buy');
		} else {
			$priceLimit = 0;
		}
		$m        = new BankModel();
		$alipay   = new ZfbModel();
		$wx       = new WxModel();
		$unionpay = new YsfModel();
		if (request()->isPost()) {
			$amount = input('post.amount');
			($amount <= 0) && showMsg('请输入正确的购买数量');
			$minLimit = input('post.min_limit');
			($minLimit <= 0) && showMsg('请输入正确的最小限额');
			$maxLimit = input('post.max_limit');
			($maxLimit <= 0) && showMsg('请输入正确的最大限额');
			($minLimit > $maxLimit) && showMsg('最小限额不能大于最大限额！');
			if ($usdtPriceWay == 0) {
				$price = input('post.price');
				($price > $usdtPriceMax || $price < $usdtPriceMin) && showMsg('价格区间：' . $usdtPriceMin . '~' . $usdtPriceMax);
			}
			if ($usdtPriceWay == 1) {
				$price = getUsdtPrice();
			}
			if ($usdtPriceWay == 2) {
				// $priceLimit = floatval(getUsdtPrice()+config('usdt_price_add_buy'));
				$price = floatval(getUsdtPrice() + config('usdt_price_add_buy'));
			}
			// $pay_method = $codes['pay_method'];
			$model = new MerchantModel();
			$user  = $model->getUserByParam($this->uid, 'id');
			($user['trader_check'] != 1) && showMsg('您的承兑商资格未通过');
			$haveAdSum = Db::name('ad_buy')->where('userid', $this->uid)->where('state', 1)->count();
			$haveAdSum = $haveAdSum ? $haveAdSum : 0;
			($haveAdSum > 10) && showMsg('挂买最多发布10个');
			(empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx']) && empty($_POST['ysf'])) && showMsg('请选择收款方式');
			$codes = ['zfb' => (int)$_POST['zfb'], 'bank' => (int)$_POST['bank'], 'wx' => (int)$_POST['wx'], 'ysf' => (int)$_POST['ysf']];
			//查询用户的银行卡信息
			$where1['merchant_id'] = $this->uid;
			$where1['id']          = $codes['bank'];
			$bank                  = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = $this->uid;
			$where2['id']          = $codes['zfb'];
			$isAlipay              = $alipay->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = $this->uid;
			$where3['id']          = $codes['wx'];
			$isWxpay               = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = $this->uid;
			$where4['id']          = $codes['ysf'];
			$isUnionPay            = $unionpay->getOne($where4);
			($codes['bank'] && !$bank) && showMsg('请先设置您的银行卡账户信息');
			($codes['zfb'] && !$isAlipay) && showMsg('请先设置您的支付宝账户信息');
			($codes['wx'] && !$isWxpay) && showMsg('请先设置您的微信账户信息');
			($codes['ysf'] && !$isUnionPay) && showMsg('请先设置您的云闪付账户信息');
			$adNo   = $this->getAdvNo();
			$model2 = new AdbuyModel();
			$flag   = $model2->insertOne([
				'userid'      => $this->uid,
				'add_time'    => time(),
				'coin'        => 'usdt',
				'min_limit'   => $minLimit,
				'max_limit'   => $maxLimit,
				'pay_method'  => $codes['bank'],
				'pay_method2' => $codes['zfb'],
				'pay_method3' => $codes['wx'],
				'pay_method4' => $codes['ysf'],
				'ad_no'       => $adNo,
				'amount'      => $amount,
				'price'       => $price,
				'state'       => 1
			]);
			//增加挂买数
			$count = $model2->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
			$model->updateOne(['id' => $this->uid, 'ad_on_buy' => $count]);
			($flag['code'] == 1) ? showMsg($flag['msg'], 1) : showMsg($flag['msg']);
		} else {
			$this->assign('usdt_price_min', $usdtPriceMin);
			$this->assign('usdt_price_max', $usdtPriceMax);
			$this->assign('usdt_price_way', $usdtPriceWay);
			$model2          = new AdbuyModel();
			$where['userid'] = $this->uid;
			$list            = $model2->getAd($where, 'id DESC');
			foreach ($list as $k => $v) {
				$dealNum            = Db::name('order_sell')->where(['buy_bid' => $v['id'], 'status' => ['neq', 5]])->sum('deal_num');
				$dealNum            = $dealNum ? $dealNum : 0;
				$list[$k]['deal']   = $dealNum;
				$list[$k]['remain'] = $v['amount'] - $list[$k]['deal'];
			}
			$this->assign('list', $list);
			$this->assign('priceLimit', $priceLimit);
			// $m = new \app\home\model\BankModel();
			$banks = $m->where(['merchant_id' => $this->uid, 'state' => 1])->order('id DESC')->select();
			$this->assign('zfb', $alipay->getBank(['merchant_id' => $this->uid], 'id DESC'));
			$this->assign('wx', $wx->getBank(['merchant_id' => $this->uid], 'id DESC'));
			$this->assign('ysf', $unionpay->getBank(['merchant_id' => $this->uid], 'id DESC'));
			$this->assign('banks', $banks);
			return $this->fetch();
		}
	}

	//挂单上下架
	public function setShelf() {
		!$this->uid && $this->error('请登录操作');
		$id  = (int)input('post.id');
		$act = (int)input('post.act');
		!in_array($act, [1, 2, 3]) && $this->error('参数错误');
		Cache::has($id) && $this->error('操作频繁,请稍后重试');
		Cache::set($id, TRUE, 2);
		$adInfo = Db::name('ad_sell')->where(['id' => $id, 'userid' => $this->uid])->lock()->find();
		!$adInfo && $this->error('挂单不存在！');
		($adInfo['state'] == 4) && $this->error('此挂单已冻结禁止上下架操作！');
		$merchant = Db::name('merchant')->where('id', $this->uid)->lock()->find();
		if (($act == 1 && $adInfo['state'] == 2) || ($act == 2 && $adInfo['state'] == 1)) {
			!Db::name('ad_sell')->where(['id' => $id, 'userid' => $this->uid, 'state' => $adInfo['state']])->update(['state' => $act]) && $this->error('操作失败,code: 101');
		} elseif ($act == 3 && ($adInfo['state'] == 1 || $adInfo['state'] == 2)) {
			// $merchant['usdtd'] < $adInfo['remain_amount'] && $this->error('冻结不足', $id);
			Db::startTrans();
			!Db::name('ad_sell')->where(['id' => $id, 'userid' => $this->uid])->update(['state' => $act, 'finished_time' => time()]) && $this->rollbackAndMsg('订单操作失败', $id);
			!balanceChange(FALSE, $this->uid, $adInfo['remain_amount'], 0, -$adInfo['remain_amount'], 0, BAL_REDEEM, $id, '用户撤单') && $this->rollbackAndMsg('撤单失败：退款失败', $id);
			Cache::rm($id);
			$count = Db::name('ad_sell')->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
			Db::name('merchant')->update(['id' => $this->uid, 'ad_on_sell' => $count ? $count : 0]);
			Db::commit();
		} else {
			$this->error('错误: 请刷新后重试');
		}
		$this->success('操作成功');
	}

	public function setShelfbuy() {
		$id   = input('post.id');
		$type = input('post.type');
		$act  = input('post.act');
		!$this->uid && $this->error('请登录操作');
		($type != 0 && $type != 1) && $this->error("挂单类型错误！");
		$model           = new AdbuyModel();
		$model2          = new MerchantModel();
		$where['id']     = $id;
		$where['userid'] = $this->uid;
		$adInfo          = $model->getOne($where);
		!$adInfo && $this->error("挂单不存在！");
		($adInfo['state'] == 4) && $this->error("此挂单已冻结禁止上下架操作！");
		$merchant = $model2->getUserByParam($this->uid, 'id');
		if ($act == 1) {
			$haveAdSum = Db::name('ad_buy')->where('userid', $this->uid)->where('state', 1)->count();
			$haveAdSum = $haveAdSum ? $haveAdSum : 0;
			($haveAdSum > 20) && $this->error('开启失败：挂买最多上架20个');
		}
		$result = $model->updateOne(['id' => $id, 'state' => $act]);
		if ($result['code'] == 1) {
			$count = $model->where('userid', $this->uid)->where('state', 1)->where('amount', 'gt', 0)->count();
			$model2->updateOne(['id' => $this->uid, 'ad_on_buy' => $count ? $count : 0]);
			$this->success("操作成功");
		}
		$this->error("操作失败");
	}

	public function adindex() {
		$order = 'a.id DESC';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new AdbuyModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['state']  = 1;
		$where['userid'] = ['neq', $this->uid];
		$list            = $model->getAdIndex($where, $order);
		foreach ($list as $k => $v) {
			$dealNum                = Db::name('order_sell')->where('buy_bid', $v['id'])->where('status', 'neq', 5)->sum('deal_num');
			$list[$k]['remain_num'] = $v['amount'] - $dealNum;
		}
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function addetail() {
		$id        = input('get.id');
		$adModel   = new AdbuyModel();
		$userModel = new MerchantModel();
		$ad        = $adModel->getOne(['id' => $id]);
		empty($ad) && $this->error('挂单不存在');
		($ad['state'] != 1) && $this->error('挂单未上架');
		$m            = new BankModel();
		$alipay       = new ZfbModel();
		$wx           = new WxModel();
		$unionpay     = new YsfModel();
		$AdOwner      = $userModel->getUserByParam($ad['userid'], 'id');
		$dealNum      = Db::name('order_sell')->where('buy_bid', $id)->where('status', 'neq', 5)->sum('deal_num');
		$remainNum    = $ad['amount'] - $dealNum;
		$usdtPriceWay = config('usdt_price_way_buy');
		$addFee       = $usdtPriceWay == 2 ? config('usdt_price_add_buy') : 0;
		$maxLimit     = (getUsdtPrice() + $addFee) * $remainNum;
		$rs1          = Db::name('ad_buy')->where('id', $ad['id'])->update(['max_limit' => $maxLimit]);
		//!$rs1 && $this->error('交易限额更新失败');
		$ad              = $adModel->getOne(['id' => $id]);
		$ad['RemainNum'] = $ad['amount'] - $dealNum;
		$this->assign('ad', $ad);
		$this->assign('AdOwner', $AdOwner);
		$banks = $m->where(['merchant_id' => $this->uid, 'state' => 1])->order('id DESC')->select();
		$this->assign('zfb', $alipay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('wx', $wx->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('ysf', $unionpay->getBank(['merchant_id' => $this->uid], 'id DESC'));
		$this->assign('banks', $banks);
		$user = $userModel->getUserByParam($this->uid, 'id');
		$ga   = explode('|', $user['ga']);
		$this->assign('ga', ($ga['3'] ?? 0));
		return $this->fetch();
	}

	//TODO 需要锁住
	public function trade_ajax() {
		//type0买挂单1卖挂单 num用户要交易的数量 tid挂单的id tamount用户要交易的价格
		$type    = 0;
		$num     = input('post.qty');//数量
		$tid     = input('post.tid');
		$tamount = input('post.amount');//金额
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		(empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx'])) && $this->error('请选择收款方式');
		$model = new MerchantModel();
		$my    = $model->getUserByParam($this->uid, 'id');
		$ga    = explode('|', $my['ga']);
		if (isset($ga[3]) && $ga[3]) {
			$code = input('post.ga');
			!$code && $this->error('请输入谷歌验证码');
			$google = new GoogleAuthenticator();
			!$google->verifyCode($ga['0'], $code, 1) && $this->error('谷歌验证码错误！');
		}
		$m        = new BankModel();
		$alipay   = new ZfbModel();
		$wx       = new WxModel();
		$unionpay = new YsfModel();
		//查询用户的银行卡信息
		$where1['merchant_id'] = $this->uid;
		$where1['id']          = $_POST['bank'];
		$bank                  = $m->getOne($where1);
		//查询用户的支付宝信息
		$where2['merchant_id'] = $this->uid;
		$where2['id']          = $_POST['zfb'];
		$isAlipay              = $alipay->getOne($where2);
		//查询用户的微信信息
		$where3['merchant_id'] = $this->uid;
		$where3['id']          = $_POST['wx'];
		$isWxpay               = $wx->getOne($where3);
		//查询用户的云闪付信息
		$where4['merchant_id'] = $this->uid;
		$where4['id']          = $_POST['ysf'];
		$isUnionPay            = $unionpay->getOne($where4);
		($_POST['bank'] && !$bank) && $this->error('请先设置您的银行卡账户信息');
		($_POST['zfb'] && !$isAlipay) && $this->error('请先设置您的支付宝账户信息');
		($_POST['wx'] && !$isWxpay) && $this->error('请先设置您的微信账户信息');
		($_POST['ysf'] && !$isUnionPay) && $this->error('请先设置您的云闪付账户信息');
		($tid <= 0) && $this->error('挂单不存在');
		($num <= 0) && $this->error('交易数量必须大于0');
		($tamount <= 0) && $this->error('交易金额必须大于0');
		/**************我要出售*******************/
		if ($type == 0) {
			$orderInfo = Db::name('ad_buy')->where(['id' => $tid])->find();
			(!$orderInfo) && $this->error('此挂单不存在');
			// $pay_method = explode(',', $orderInfo['pay_method']);
			// $pay_length = count($pay_method);
			$k2  = 0;
			$msg = '';
			if ($orderInfo['pay_method'] > 0) {
				$banks = $m->getBank(['merchant_id' => $this->uid], 'id DESC');
				!$banks && $this->error('银行转账信息未设置 ');
			}
			if ($orderInfo['pay_method2'] > 0) {
				$alipay = $alipay->getBank(['merchant_id' => $this->uid], 'id DESC');
				!$alipay && $this->error('支付宝信息未设置 ');
			}
			if ($orderInfo['pay_method3'] > 0) {
				$wx = $wx->getBank(['merchant_id' => $this->uid], 'id DESC');
				!$wx && $this->error('微信支付信息未设置 ');
			}
			if ($orderInfo['pay_method4'] > 0) {
				$unionpay = $unionpay->getBank(['merchant_id' => $this->uid], 'id DESC');
				!$unionpay && $this->error('云闪付支付信息未设置 ');
			}
			// }
			($k2 > 0) && $this->error($msg);
			//判断交易范围
			($tamount < $orderInfo['min_limit']) && $this->error('交易金额超出范围');
			($tamount > $orderInfo['max_limit']) && $this->error('交易金额超出范围');
			$mchFee = config('usdt_buy_merchant_fee');
			$fee    = 0;
			if ($mchFee) {
				$fee = $num * $mchFee / 100;
			}
			if ($my['usdt'] * 1 < ($num + $fee) * 1) {
				$this->error('您的账户余额不足，请先充值USDT，再进行出售');
			}
			//判断剩余数量, 防止超卖
			$soldNum = Db::name('order_sell')->where('buy_bid', $orderInfo['id'])->sum('deal_num');
			($orderInfo['amount'] - $soldNum < number_format(($tamount / $orderInfo['price']), 8, '.', '')) && $this->error('挂单余量不足,请选择其它挂单');
			$arr                = [];
			$arr['buy_id']      = $orderInfo['userid'];
			$arr['buy_bid']     = $orderInfo['id'];
			$arr['sell_id']     = $this->uid;
			$arr['deal_amount'] = $tamount;
			$arr['deal_num']    = $num;
			$arr['deal_price']  = $orderInfo['price'];
			$arr['ctime']       = time();
			$arr['ltime']       = config('order_expire');
			$arr['order_no']    = createOrderNo(4, $this->uid);
			$arr['fee']         = $fee;
			$arr['pay']         = $_POST['bank'];
			$arr['pay2']        = $_POST['zfb'];
			$arr['pay3']        = $_POST['wx'];
			$arr['pay4']        = $_POST['ysf'];
			try {
				Db::startTrans();
				$rs1 = $id = Db::name('order_sell')->insertGetId($arr);
				//卖家的btc需要冻结起来
				$rs2 = balanceChange(FALSE, $this->uid, -$num, $fee, $num, $fee, BAL_SOLD, $arr['order_no'], '商户出售');
				if ($rs1 && $rs2) {
					Db::commit();
					financeLog($this->uid, ($num + $fee), '卖出USDT_冻结1', 1, session('user.name'));//添加日志
					//todo:发送短信给买家
					$mobile = Db::name('merchant')->where('id', $orderInfo['userid'])->value('mobile');
					if (!empty($mobile)) {
						// $content = '您发布的买单有人出售。数量:' . $num . ',交易码:{check_code} ,请尽快处理';
						$content = '您发布的买单有人出售。数量:' . $num . ',请尽快处理';
						sendSms($mobile, $content);
					}
					$this->success('下单成功！');
				} else {
					throw new Exception('下单失败！');
				}
			} catch (Exception $e) {
				Db::rollback();
				$this->error('下单失败！');
			}
		}
	}

	public function ordersell() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['sell_id'] = $this->uid;
		$get              = input('get.');
		$order            = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderSn)) {
			$where['order_no'] = ['like', '%' . $orderSn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}
		$list = Db::name('order_sell')->where($where)->order('id DESC')->paginate(20, FALSE, ['query' => Request::instance()->param()]);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function outOrderSell() {
		/* [
 ['order_no','订单编号'],
 ['deal_amount','交易金额'],
 ['deal_num','交易数量'],
 ['deal_price','交易价格'],
 ['ctime','创建时间'],
 ['status','交易状态'],
 ] */
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['sell_id'] = $this->uid;
		$status           = input('get.status');
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}
		$data = Db::name('order_sell')->where($where)->order('id DESC')->select();
		//文件名称
		$Excel['fileName']   = "下发订单" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F'];
		$Excel['H']          = ['A' => 10, 'B' => 20, 'C' => 15, 'D' => 40, 'E' => 15, 'F' => 15];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];                                            //纵向垂直高度
		$Excel['sheetTitle'] = "下发订单";                                                            //大标题，自定义
		$Excel['xlsCell']    = Data::ordersell();
		foreach ($data as $k => $v) {
			if ($v['status'] == 0) {
				$data[$k]['status'] = '待付款';
			} elseif ($v['status'] == 1) {
				$data[$k]['status'] = '待放行';
			} elseif ($v['status'] == 4) {
				$data[$k]['status'] = '已完成';
			} elseif ($v['status'] == 5) {
				$data[$k]['status'] = '已关闭';
			} elseif ($v['status'] == 6) {
				$data[$k]['status'] = '申诉中';
			} elseif ($v['status'] == 9) {
				$data[$k]['status'] = '订单失败';
			}
			$data[$k]['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
		}
		PHPExcel::excelPut($Excel, $data);
	}

	public function CheckOutTime() {
		$id    = input('post.id');
		$order = Db::name('order_buy')->where('id', $id)->find();
		if ($order['status'] == 5) {
			$this->success('ok');
			exit;
		}
		(empty($order)) && $this->error('no order');
		$remainTime = $order['ltime'] * 60 + $order['ctime'] - time();
		($remainTime < 0) ? $this->success('ok') : $this->error('no');
	}

	public function uptrade() {
		$id    = input('post.id');
		$appId = input('post.appid');
		$order = Db::name('order_buy')->where('id', $id)->find();
		!$order && $this->error('订单不存在');
		$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();
		!$merchant && $this->error('商户不存在');
		($merchant['appid'] != $appId) && $this->error('appid错误');
		($order['status'] == 5) && $this->error('此订单已取消');
		($order['status'] >= 1) && $this->error('你已经标记了已付款完成，请勿重复操作');
		$rs = Db::name('order_buy')->where('id', $id)->update(['status' => 1, 'dktime' => time(), 'desc' => '用户确认交易完成']);
		if ($rs) {
			/*$mobile = Db::name('merchant')->where('id', $order['sell_id'])->value('mobile');
			if (!empty($mobile)) {
				$send_content = config('send_message_content');
				$content = str_replace('{usdt}', round($order['deal_num'], 2), $send_content);
				$content = str_replace('{cny}', round($order['deal_amount'], 2), $content);
				$content = str_replace('{tx_id}', $order['orderid'], $content);
				$content = str_replace('{check_code}', $order['check_code'], $content);
				sendSms($mobile, $content);
			}*/
			$this->success($order['return_url']);
		}
		$this->error('确认失败，请稍后再试');
	}

	/**
	 * 承兑商标记付款
	 */
	public function uptradeinner() {
		!$this->uid && $this->error('请登录操作');
		$id    = input('post.id');
		$order = Db::name('order_sell')->where('id', $id)->find();
		!$order && $this->error('订单不存在');
		($order['buy_id'] != $this->uid) && $this->error('不是您的买单');
		($order['status'] == 5) && $this->error('此订单已取消');
		($order['status'] >= 1) && $this->error('你已经标记了已付款完成，请勿重复操作');
		$rs = Db::name('order_sell')->where('id', $id)->update(['status' => 1, 'dktime' => time()]);
		if ($rs) {
			//todo:是否发送短信给商家即卖家
			//$mobile = Db::name('merchant')->where('id', $order['sell_id'])->value('mobile');
			//if(!empty($mobile)){
			// $content = str_replace('{usdt}',$order['deal_num'],config('send_message_content'));
			// sendSms($mobile, $content);
			//}
			$this->success('标记成功');
		}
		$this->error('确认失败，请稍后再试');
	}

	public function pkorder() {
		!$this->uid && $this->error('请登录操作', url('home/login/login'));
		$model2          = new OrderModel();
		$where['buy_id'] = $this->uid;
		$get             = input('get.');
		$order           = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderSn)) {
			$where['order_no'] = ['like', '%' . $orderSn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}
		$list = $model2->getOrder($where, 'id DESC');
		if ($list) {
			$newList   = $list->toArray();
			$sellerIds = array_unique(array_column($newList['data'], 'sell_id'));
			$mcModel   = Db::name('merchant');
			$agentIds  = $mcModel->where('id', 'in', array_unique($sellerIds))->column('pid', 'id');
			$agFeeRate = 0;
			if ($agentIds) {
				$agFeeRate = $mcModel->where('id', 'in', array_values($agentIds))->column('trader_parent_get', 'id');
			}
			$user      = Db::name('merchant')->where('id', $this->uid)->find();
			$currPrice = getUsdtPrice();
			$dealerFee = $currPrice * (config('usdt_price_add') / 100);
			foreach ($list as $k => $v) {
				$list[$k]['fee_amount'] = $list[$k]['fee'] = $list[$k]['rec_amount'] = $list[$k]['rec'] = $list[$k]['fee_rate'] = 0;
				if ($v['status'] == 4) {
					// 14.14427157	* 1 - 0.0193 * 7.07
					$agentFeeRate           = isset($agentIds[$v['sell_id']]) && isset($agFeeRate[$agentIds[$v['sell_id']]]) ? $agFeeRate[$agentIds[$v['sell_id']]] / 100 : 0;
					$list[$k]['fee_amount'] = $v['deal_amount'] - (($v['deal_num'] - $v['platform_fee'] - number_format($v['deal_num'] * $agentFeeRate, 8, '.', '')) * ($v['deal_price'] - $dealerFee)); //费用金额
					$list[$k]['fee']        = $list[$k]['fee_amount'] / $v['deal_price'];
					$list[$k]['rec_amount'] = $v['deal_amount'] - $list[$k]['fee_amount'];                                  // 到账费用
					$list[$k]['rec']        = $v['deal_num'] - $list[$k]['fee'];                                            // 到账数量
					$list[$k]['fee_rate']   = number_format($list[$k]['fee_amount'] * 100 / $v['deal_amount'], 1, '.', ''); // 到账数量
				}
			}
		}
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function orderlist() {
		!$this->uid && $this->error('请登录操作', url('home/login/login'));
		$get   = input('get.');
		$order = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderSn)) {
			$where['order_no'] = ['like', '%' . $orderSn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}
		$model2           = new OrderModel();
		$where['sell_id'] = $this->uid;
		$list             = $model2->getOrder($where, 'id DESC');
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function orderlistbuy() {
		!$this->uid && $this->error('请登录操作', url('home/login/login'));
		$where['buy_id'] = $this->uid;
		$get             = input('get.');
		$order           = 'id DESC';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderSn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderSn)) {
			$where['order_no'] = ['like', '%' . $orderSn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}
		$list = Db::name('order_sell')->where($where)->order('id DESC')->paginate(20, FALSE, ['query' => Request::instance()->param()]);
		$this->assign('list', $list);
		return $this->fetch();
	}

	private function sign($dataArr, $key) {
		ksort($dataArr);
		$str = '';
		foreach ($dataArr as $ke => $value) {
			$str .= $ke . $value;
		}
		$str = $str . $key;
		return strtoupper(sha1($str));
	}

	private function curl($url, $data = []) {
		//使用crul模拟
		$ch = curl_init();
		//禁用https
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		//允许请求以文件流的形式返回
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch); //执行发送
		curl_close($ch);
		return $result;
	}

	public function appeal_ajax() {
		if (request()->isPost()) {
			$content = input('post.content');
			$id      = input('post.id');
			!$this->uid && $this->error('请登录操作');
			$model            = new OrderModel();
			$where['id']      = $id;
			$where['sell_id'] = $this->uid;
			$orderInfo        = $model->getOne($where);
			(!$orderInfo) && $this->error('订单不存在');
			($orderInfo['status'] == 5) && $this->error('该订单已经被取消');
			/*if ($orderInfo['status'] == 0) {
				$this->error('该订单已经被拍下，还未付款,不能申诉');
			}*/
			($orderInfo['status'] == 6) && $this->error('该订单已经处于申诉状态，请耐心等待');
			($orderInfo['status'] == 4 || $orderInfo['status'] == 3) && $this->error('该订单已经完成，无法申诉');
			$rs = $model->updateOne(['id' => $id, 'status' => 6, 'su_reason' => $content]);
			($rs['code'] == 1) ? $this->success('申诉成功') : $this->error($rs['msg']);
		}
	}

	public function traderreward() {
		$order = 'a.id DESC';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$where['uid'] = $this->uid;
		$this->assign('list', $model->getTraderReward($where, $order));
		return $this->fetch();
	}

	//承兑商买单申诉
	public function appeal_ajax_trader() {
		if (request()->isPost()) {
			$content = input('post.content');
			$id      = input('post.id');
			!$this->uid && $this->error('请登录操作');
			$where['id']     = $id;
			$where['buy_id'] = $this->uid;
			$orderInfo       = Db::name('order_sell')->where($where)->find();
			(!$orderInfo) && $this->error('订单不存在');
			($orderInfo['status'] == 5) && $this->error('该订单已经被取消');
			($orderInfo['status'] == 0) && $this->error('该订单已经被拍下，还未付款,不能申诉');
			($orderInfo['status'] == 6) && $this->error('该订单已经处于申诉状态，请耐心等待');
			($orderInfo['status'] == 4 || $orderInfo['status'] == 3) && $this->error('该订单已经完成，无法申诉');
			$rs = Db::name('order_sell')->where('id', $id)->update(['status' => 6, 'su_reason' => $content]);
			($rs['code'] == 1) ? $this->success('申诉成功') : $this->error('申诉失败，请稍后再试');
		}
	}

	//商户申诉
	public function appeal_ajax_merchant() {
		if (request()->isPost()) {
			$content = input('post.content');
			$id      = input('post.id');
			!$this->uid && $this->error('请登录操作');
			$where['id']      = $id;
			$where['sell_id'] = $this->uid;
			$orderInfo        = Db::name('order_sell')->where($where)->find();
			(!$orderInfo) && $this->error('订单不存在');
			($orderInfo['status'] == 5) && $this->error('该订单已经被取消');
			($orderInfo['status'] == 0) && $this->error('该订单已经被拍下，还未付款,不能申诉');
			($orderInfo['status'] == 6) && $this->error('该订单已经处于申诉状态，请耐心等待');
			($orderInfo['status'] == 4 || $orderInfo['status'] == 3) && $this->error('该订单已经完成，无法申诉');
			$rs = Db::name('order_sell')->where('id', $id)->update(['status' => 6, 'su_reason' => $content]);
			($rs['code'] == 1) ? $this->success('申诉成功') : $this->error('申诉失败，请稍后再试');
		}
	}

	public function log() {
		$model             = new MerchantModel();
		$where['admin_id'] = $this->uid;
		$log               = $model->getLoginLog($where, 'log_id DESC');
		$this->assign('log', $log);
		return $this->fetch();
	}
}