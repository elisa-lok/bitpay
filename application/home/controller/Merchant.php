<?php

namespace app\home\controller;

use think\db;
use think\request;
use app\home\model\ZfbModel;
use app\home\model\WxModel;
use app\home\model\YsfModel;
use app\home\model\MerchantModel;
use app\home\model\AddressModel;
use app\home\model\RechargeModel;
use app\home\model\WithdrawModel;
use app\home\model\TibiModel;
use app\home\model\AdModel;
use app\home\model\AdbuyModel;
use app\home\model\OrderModel;
use app\home\model\DetailModel;

class Merchant extends Base {
	//商户首页
	public function index() {
		// echo session('user.name');die;
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$model = new MerchantModel();
		$this->assign('merchant', $model->getUserByParam(session('uid'), 'id'));
		$myinfo = $model->getUserByParam(session('uid'), 'id');
		$this->assign('myacc', $model->getUserByParam($myinfo['pid'], 'id'));
		$ids  = Db::name('article_cate')->field('id, name')->order('orderby asc')->select();
		$list = Db::name('article_cate')->field('a.name, b.id, b.title, b.cate_id, b.create_time')->alias('a')->join('think_article b', 'a.id=b.cate_id')->where('a.status', 1)->select();
		foreach ($ids as $k => $v) {
			foreach ($list as $kk => $vv) {
				if ($v['id'] == $vv['cate_id']) {
					$ids[$k]['article'][] = $vv;
				}
			}
		}
		// dump(getUsdtPrice());die;
		$this->assign('article', $ids);
		$this->assign('price', getUsdtPrice());
		return $this->fetch();
	}

	public function checkpaypass() {
		if (request()->isPost()) {
			$password = input('post.paypassword');
			if (empty($password)) {
				$this->error('请输入交易密码');
			}
			$model = new MerchantModel();
			$user  = $model->getUserByParam(session('uid'), 'id');
			if ($user['paypassword'] != md5($password)) {
				$this->error('交易密码错误');
			} else {
				$this->success('ok');
			}
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

	public function payinfo_bak() {
		$id    = input('post.id');
		$order = Db::name('order_sell')->where('id', $id)->where('buy_id', session('uid'))->find();
		if (empty($order)) {
			echo '订单信息错误';
			die;
		}
		$ad                       = Db::name('ad_buy')->where('id', $order['buy_bid'])->find();
		$merchant                 = Db::name('merchant')->where('id', $order['sell_id'])->find();
		$merchant['c_wechat_img'] = str_replace("\\", "/", $merchant['c_wechat_img']);
		$merchant['c_alipay_img'] = str_replace("\\", "/", $merchant['c_alipay_img']);
		if (isset($arr[0]) && $arr[0] > 4) {
			$bank                    = Db::name('merchant_bankcard')->where('id', $arr[0])->find();
			$merchant['c_bank_card'] = $bank['c_bank_card'];
			$merchant['name']        = $bank['truename'];
		}
		$this->assign('merchant', $merchant);//dump($merchant['c_wechat_img']);
		$this->assign('order', $order);
		$this->assign('ad', $ad);
		return $this->fetch();
	}

	public function payinfo() {
		$id    = input('post.id');
		$order = Db::name('order_sell')->where('id', $id)->find();
		if (empty($order)) {
			echo '订单信息错误';
			die;
		}
		$ad = Db::name('ad_buy')->where('id', $order['buy_bid'])->find();

		$bank = new \app\home\model\BankModel();
		$zfb  = new \app\home\model\ZfbModel();
		$wx   = new \app\home\model\WxModel();
		if ($order['buy_id'] == session('uid')) {//买家显示内容,显示卖家的收款信息
			$merchant = Db::name('merchant')->where('id', $order['sell_id'])->find();//查找卖家信息
			if ($order['pay'] > 0) {
				$where1['merchant_id']   = $order['sell_id'];
				$where1['id']            = $order['pay'];
				$isbank                  = $bank->getOne($where1);
				$merchant['c_bank']      = $isbank['c_bank'] . $isbank['c_bank_detail'];
				$merchant['c_bank_card'] = $isbank['c_bank_card'];
				$merchant['name']        = $isbank['truename'];
			}
			if ($order['pay2'] > 0) {
				$where2['merchant_id']    = $order['sell_id'];
				$where2['id']             = $order['pay2'];
				$iszfb                    = $zfb->getOne($where2);
				$merchant['c_alipay_img'] = str_replace("\\", "/", $iszfb['c_bank_detail']);
			}
			if ($order['pay3'] > 0) {
				$where3['merchant_id']    = $order['sell_id'];
				$where3['id']             = $order['pay3'];
				$iswx                     = $wx->getOne($where3);
				$merchant['c_wechat_img'] = str_replace("\\", "/", $iswx['c_bank_detail']);
			}
		}
		if ($order['sell_id'] == session('uid')) {//卖家显示内容
			$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();//查找买家信息
		}
		// dump($order);

		$this->assign('merchant', $merchant);
		//dump($merchant['c_wechat_img']);
		$this->assign('order', $order);
		$this->assign('ad', $ad);
		return $this->fetch();
	}

	public function payinfo2() {
		$id    = input('post.id');
		$order = Db::name('order_sell')->where('id', $id)->where('buy_id', session('uid'))->find();
		// dump(session('uid'));die;
		if (empty($order)) {
			echo '订单信息错误';
			die;
		}
		$ad       = Db::name('ad_buy')->where('id', $order['buy_bid'])->find();
		$merchant = Db::name('merchant')->where('id', $order['sell_id'])->find();//查找卖家信息
		$bank     = new \app\home\model\BankModel();
		$zfb      = new \app\home\model\ZfbModel();
		$wx       = new \app\home\model\WxModel();
		if ($order['pay'] > 0) {
			$where1['merchant_id']   = $order['sell_id'];
			$where1['id']            = $order['pay'];
			$isbank                  = $bank->getOne($where1);
			$merchant['c_bank_card'] = $bank['c_bank_card'];
			$merchant['name']        = $bank['truename'];
			$merchant['kh']          = $bank['c_bank_detail'];
		}
		if ($order['pay2'] > 0) {
			$where2['merchant_id']    = $order['sell_id'];
			$where2['id']             = $order['pay2'];
			$iszfb                    = $zfb->getOne($where2);
			$merchant['c_alipay_img'] = str_replace("\\", "/", $iszfb['c_bank_detail']);
		}
		if ($order['pay3'] > 0) {
			$where3['merchant_id']    = $order['sell_id'];
			$where3['id']             = $order['pay3'];
			$iswx                     = $wx->getOne($where3);
			$merchant['c_wechat_img'] = str_replace("\\", "/", $iswx['c_bank_detail']);
		}
		// dump($isbank);

		$this->assign('merchant', $merchant);//dump($merchant['c_wechat_img']);
		$this->assign('order', $order);
		$this->assign('ad', $ad);
		return $this->fetch('payinfo');
	}

	public function dosetting() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$file = request()->file('avatar');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['headpic'] = $info->getSaveName();
				} else {
					$this->error('请上传正确的图片：' . $file->getError());
				}
			}
			$smscode       = input('post.code');
			$name          = input('post.name');
			$password      = input('post.password');
			$paypassword   = input('post.paypassword');
			$repassword    = input('post.password_confirmation');
			$repaypassword = input('post.paypassword_confirmation');
			if ($password != $repassword && !empty($password)) {
				$this->error('登录密码错误！');
			}
			if ($paypassword != $repaypassword && !empty($paypassword)) {
				$this->error('交易密码错误！');
			}

			if (!$name) {
				$this->error('请输入用户名');
			}
			if (!empty($paypassword) && !empty($password)) {
				if ($paypassword == $password) {
					$this->error('交易密码不能与登录密码相同！');
				}
			}
			if (empty($smscode)) {
				$this->error('请填写短信验证码');
			}
			if ($smscode != session($mobile . '_mcode')) {
				$this->error('短信验证码错误!');
			}
			$param['id'] = session('uid');
			if (!empty($password)) {
				$param['password'] = md5($password);
			}
			if (!empty($password)) {
				$param['paypassword'] = md5($paypassword);
			}
			$param['name'] = $name;
			$model         = new MerchantModel();
			$return        = $model->updateOne($param);
			if ($return['code'] == 1) {
				$user = $model->where('id', session('uid'))->find();
				session('user', $user);
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	//商户用户钱包地址
	public function address() {
		$order = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$model = new AddressModel();
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['merchant_id'] = session('uid');
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
		if (!session('uid')) {
			$this->error('请登陆操作');
		}
		$where['merchant_id'] = session('uid');
		$order                = 'id desc';
		$model                = new AddressModel();
		$data                 = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "用户钱包地址" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
		$Excel['H']          = ['A' => 10, 'B' => 15, 'C' => 40, 'D' => 30];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];//纵向垂直高度
		$Excel['sheetTitle'] = "用户钱包地址记录";//大标题，自定义
		$Excel['xlsCell']    = \app\common\model\Data::headAddress();
		foreach ($data as $k => $v) {
			$data[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
		}
		\app\common\model\PHPExcel::excelPut($Excel, $data);
	}

	//商户用户充值记录
	public function recharge() {
		$get = input('get.');
		if (!session('uid')) {
			$this->error('请登陆操作！');
		}
		$order = 'a.id desc';
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
		$where['a.merchant_id'] = session('uid');//dump($where);
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
		if (!session('uid')) {
			$this->error('请登陆操作');
		}
		$where['a.merchant_id'] = session('uid');
		$order                  = 'a.id desc';
		$model                  = new RechargeModel();
		$data                   = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "用户充值记录" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
		$Excel['H']          = ['A' => 10, 'B' => 15, 'C' => 15, 'D' => 35, 'E' => 35, 'F' => 15, 'G' => 15, 'H' => 20, 'I' => 30];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];//纵向垂直高度
		$Excel['sheetTitle'] = "用户充值记录";//大标题，自定义
		$Excel['xlsCell']    = \app\common\model\Data::head();
		foreach ($data as $k => $v) {
			if ($v['status'] == 0) {
				$data[$k]['status'] = '生成充值订单';
			} elseif ($v['status'] == 1) {
				$data[$k]['status'] = '平台已收款';
			} elseif ($v['status'] == 2) {
				$data[$k]['status'] = '商户已收款';
			}
			$data[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
		}
		\app\common\model\PHPExcel::excelPut($Excel, $data);
	}

	//商户用户提币记录
	public function withdraw() {
		if (!session('uid')) {
			$this->error('请登陆操作');
		}
		$where['merchant_id'] = session('uid');
		$get                  = input('get.');
		$order                = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$username = input('get.username');
		$status   = input('get.status');
		$ordersn  = input('get.ordersn');
		if (!empty($username)) {
			$where['username'] = $username;
		}
		if (!empty($ordersn)) {
			$where['ordersn'] = $ordersn;
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status - 1;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start            = strtotime($get['created_at']['start']);
			$end              = strtotime($get['created_at']['end']);
			$where['addtime'] = ['between', [$start, $end]];
		}
		if (!empty($get['end_at']['start']) && !empty($get['end_at']['end'])) {
			$start            = strtotime($get['end_at']['start']);
			$end              = strtotime($get['end_at']['end']);
			$where['endtime'] = ['between', [$start, $end]];
		}
		//dump($get['buy_amount']['start']);
		if (!empty($get['buy_amount']['start']) && !empty($get['buy_amount']['end'])) {
			$where['num'] = ['between', [$get['buy_amount']['start'], $get['buy_amount']['end']]];
		}
		$model = new WithdrawModel();//dump($where);
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
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['merchant_id'] = session('uid');
		$order                = 'id desc';
		$model                = new WithdrawModel();
		$data                 = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "用户提币记录" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
		$Excel['H']          = ['A' => 10, 'B' => 20, 'C' => 15, 'D' => 40, 'E' => 15, 'F' => 15, 'G' => 50, 'H' => 10, 'I' => 20, 'J' => 20];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];//纵向垂直高度
		$Excel['sheetTitle'] = "用户提币记录";//大标题，自定义
		$Excel['xlsCell']    = \app\common\model\Data::headWithdraw();
		foreach ($data as $k => $v) {
			if ($v['status'] == 0) {
				$data[$k]['status'] = '待审核';
			} elseif ($v['status'] == 1) {
				$data[$k]['status'] = '已通过';
			} elseif ($v['status'] == 2) {
				$data[$k]['status'] = '已拒绝';
			}
			$data[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
			if (!empty($v['endtime'])) {
				$data[$k]['endtime'] = date("Y-m-d H:i:s", $v['endtime']);
			}
		}
		\app\common\model\PHPExcel::excelPut($Excel, $data);
	}

	//商户提币记录
	public function tibi() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['merchant_id'] = session('uid');
		$get                  = input('get.');
		$order                = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$ordersn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($ordersn)) {
			$where['ordersn'] = ['like', '%' . $ordersn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status - 1;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start            = strtotime($get['created_at']['start']);
			$end              = strtotime($get['created_at']['end']);
			$where['addtime'] = ['between', [$start, $end]];
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
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['merchant_id'] = session('uid');
		$order                = 'id desc';
		$model                = new TibiModel();
		$data                 = $model->getAllByWhere($where, $order);
		//文件名称
		$Excel['fileName']   = "商户提币记录" . date('Y年m月d日-His', time());//or $xlsTitle
		$Excel['cellName']   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
		$Excel['H']          = ['A' => 10, 'B' => 20, 'C' => 15, 'D' => 40, 'E' => 15, 'F' => 15, 'G' => 50, 'H' => 10, 'I' => 20, 'J' => 20];//横向水平宽度
		$Excel['V']          = ['1' => 40, '2' => 26];//纵向垂直高度
		$Excel['sheetTitle'] = "用户提币记录";//大标题，自定义
		$Excel['xlsCell']    = \app\common\model\Data::headTibi();
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
			$data[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
			if (!empty($v['endtime'])) {
				$data[$k]['endtime'] = date("Y-m-d H:i:s", $v['endtime']);
			}
		}
		\app\common\model\PHPExcel::excelPut($Excel, $data);
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
			$user     = $model->getUserByParam(session('uid'), 'id');
			$fee2     = $user['merchant_tibi_fee'];
			$fee      = $fee2;
			if (empty($fee2)) {
				$fee = $fee1;
			}
			if (empty($fee)) {
				$this->error('手续费未设置，请联系管理员');
			}
			if (empty($address)) {
				$this->error('请填写提币地址');
			}
			if (config('wallettype') == 'omni') {
				$model = new \app\common\model\Usdt();
				$a     = $model->index('validateaddress', $addr = $address, $mum = NULL, $index = NULL, $count = NULL, $skip = NULL);
				if ($a != 1) {
					$this->error('请填写正确的提币地址');
				}
			}
			if (config('wallettype') == 'erc') {
				if (!(preg_match('/^(0x)?[0-9a-fA-F]{40}$/', $address))) {
					// return false; //满足if代表地址不合法
					$this->error('请填写正确的提币地址');
				}
			}
			if ($num <= 0) {
				$this->error('请填写正确的金额');
			}
			if ($num < $tibi_min || $num > $tibi_max) {
				$this->error('提币区间：' . $tibi_min . '-' . $tibi_max);
			}
			$feenum = 0;
			// if($fee){
			$feenum = $fee + $fee1;
			// }
			$mum = $num - $feenum;
			if ($mum <= 0) {
				$this->error('请填写正确的金额');
			}
			if ($user['usdt'] < $mum) {
				$this->error('账户余额不足');
			}
			if (!empty($user['ga'])) {
				$arr         = explode('|', $user['ga']);
				$secret      = $arr[0];
				$ga_is_login = $arr[2];
				if ($ga_is_login) {
					$ga_n = new \com\GoogleAuthenticator();
					// 判断登录有无验证码
					$aa = $ga_n->verifyCode($secret, $ga, 1);
					if (!$aa) {
						$this->error('谷歌验证码错误！');
					}
				}
			}
			Db::startTrans();
			try {
				$rs1 = Db::table('think_merchant')->where('id', session('uid'))->setDec('usdt', $num);
				$rs3 = Db::table('think_merchant')->where('id', session('uid'))->setInc('usdtd', $num);
				$rs2 = Db::table('think_merchant_withdraw')->insert([
					'merchant_id' => session('uid'),
					'address'     => $address,
					'num'         => $num,
					'fee'         => $feenum,
					'mum'         => $mum,
					'note'        => $remark,
					'addtime'     => time(),
					'ordersn'     => createOrderNo(1, session('uid'))
				]);
				if ($rs1 && $rs2 && $rs3) {
					// 提交事务
					Db::commit();
					$this->success('提交成功，请等待审核', url('home/merchant/tibi'));
				} else {
					// 回滚事务
					Db::rollback();
					$this->error('提交失败');
				}
			} catch (\think\Exception\DbException $e) {
				// 回滚事务
				Db::rollback();
				$this->error('提交失败，参考信息：' . $e->getMessage());
			}
		} else {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$user = $model->getUserByParam(session('uid'), 'id');
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
			if (empty($id)) {
				$this->error('参数错误');
			}
			$model  = new TibiModel();
			$return = $model->cancel($id);
			return json($return);
		}
	}

	public function merchantSet() {
		if (!session('uid')) {
			$this->error('请登陆操作');
		}
		$model = new MerchantModel();
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('登录已经失效,请重新登录!');
			}
			$delete      = '';
			$gacode      = trim(input('post.ga'));
			$type        = trim(input('post.type'));
			$ga_login    = (input('post.ga_login') == FALSE ? 0 : 1);
			$ga_transfer = (input('post.ga_transfer') == FALSE ? 0 : 1);

			if (!$gacode) {
				$this->error('请输入验证码!');
			}

			if ($type == 'add') {
				$secret = session('secret');

				if (!$secret) {
					$this->error('验证码已经失效,请刷新网页!');
				}
			} elseif (($type == 'updat') || ($type == 'delet')) {
				$user = $model->getUserByParam(session('uid'), 'id');;

				if (!$user['ga']) {
					$this->error('还未设置谷歌验证码!');
				}

				$arr    = explode('|', $user['ga']);
				$secret = $arr[0];
				$delete = ($type == 'delet' ? 1 : 0);
			} else {
				$this->error('操作未定义');
			}

			$ga = new \com\GoogleAuthenticator();

			if ($ga->verifyCode($secret, $gacode, 1)) {
				$ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer : '');
				$rs     = $model->updateOne(['id' => session('uid'), 'ga' => $ga_val]);
				if ($rs) {
					$this->success('操作成功');
				} else {
					$this->error('操作失败');
				}
			} else {
				$this->error('验证失败');
			}
		} else {
			$user  = $model->getUserByParam(session('uid'), 'id');
			$is_ga = ($user['ga'] ? 1 : 0);
			$this->assign('is_ga', $is_ga);//dump($_SERVER['HTTP_HOST']);
			if (!$is_ga) {
				$ga     = new \com\GoogleAuthenticator();
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
				$this->assign('Asecret', '');
				return $this->fetch('merchantSet');
			}
			return $this->fetch('merchantSet');
		}
	}

	public function applyAgent() {
		if (!session('uid')) {
			$this->error('登录已经失效,请重新登录!');
		}
		$model    = new MerchantModel();
		$merchant = $model->getUserByParam(session('uid'), 'id');
		if ($merchant['agent_check'] != 0) {
			$this->error('请勿重复申请');
		}
		$flag = $model->updateOne(['id' => session('uid'), 'agent_check' => 3]);
		if ($flag['code'] == 1) {
			$this->success('申请成功，请等待审核');
		} else {
			$this->error($flag['msg']);
		}
	}

	public function applyTrader() {
		if (!session('uid')) {
			$this->error('登录已经失效,请重新登录!');
		}
		$model    = new MerchantModel();
		$merchant = $model->getUserByParam(session('uid'), 'id');
		if ($merchant['trader_check'] != 0) {
			$this->error('请勿重复申请');
		}
		$flag = $model->updateOne(['id' => session('uid'), 'trader_check' => 3]);
		if ($flag['code'] == 1) {
			$this->success('申请成功，请等待审核');
		} else {
			$this->error($flag['msg']);
		}
	}

	public function downmerchant() {
		$order = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}

		$model = new MerchantModel();
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['pid'] = session('uid');

		$get   = input('get.');
		$order = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$orderid = input('get.orderid');
		$ordersn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($orderid)) {
			$where['id'] = ['like', '%' . $orderid . '%'];
		}
		if (!empty($ordersn)) {
			$where['name'] = ['like', '%' . $ordersn . '%'];
		}

		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start            = strtotime($get['created_at']['start']);
			$end              = strtotime($get['created_at']['end']);
			$where['addtime'] = ['between', [$start, $end]];
		}
		$this->assign('list', $model->getMerchant($where, $order));
		return $this->fetch();
	}

	public function editdown() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$model = new MerchantModel();
		if (request()->isPost()) {
			$id                = input('post.id');
			$merchant_tibi_fee = input('post.merchant_tibi_fee');
			$user_withdraw_fee = input('post.user_withdraw_fee');
			$user_recharge_fee = input('post.user_recharge_fee');
			$flag              = $model->updateOne(['id' => $id, 'merchant_tibi_fee' => $merchant_tibi_fee, 'user_withdraw_fee' => $user_withdraw_fee, 'user_recharge_fee' => $user_recharge_fee]);
			if ($flag['code'] == 1) {
				$this->success('编辑成功');
			} else {
				$this->error($flag['msg']);
			}
		} else {
			$id = $_GET['id'];
			if (!$id) {
				$this->error('参数错误');
			}

			$merchant = $model->getUserByParam($id, 'id');
			if (empty($merchant) || $merchant['pid'] != session('uid')) {
				$this->error('商户不存在');
			}
			$this->assign('merchant', $merchant);
			return $this->fetch();
		}
	}

	public function checkdown() {
		$id   = input('get.id/d');
		$type = input('get.type/d');
		if ($type != 1 && $type != 2) {
			$this->error('审核类型错误');
		}
		$check    = $type;
		$m        = new MerchantModel();
		$merchant = $m->getUserByParam($id, 'id');
		if (empty($merchant) || $merchant['pid'] != session('uid')) {
			$this->error('下级商户不存在');
		}
		if ($merchant['reg_check'] != 0) {
			$this->error('用户已审核');
		}
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
		if (Db::name('merchant')->where('id', $id)->update($update)) {
			$this->success('审核成功');
		} else {
			$this->error('审核失败');
		}
	}

	public function agentreward() {
		$order = 'a.id desc';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['uid'] = session('uid');
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
		$order = 'a.id desc';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['uid'] = session('uid');
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
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$model    = new MerchantModel();
		$merchant = $model->getUserByParam(session('uid'), 'id');
		if ($merchant['trader_check'] != 1) {
			$this->error('请先申请承兑商', url('home/merchant/index'));
		}
		// dump(config('wallettype'));
		$qianbao1 = $merchant['usdtb'];
		$qianbao2 = $merchant['usdte'];
		if (config('wallettype') == 'omni') {
			//新方法
			if (!$qianbao1) {
				$address = Db::name('address')->where(['status' => 0, 'type' => 'btc'])->find();
				if (!$address) {
					$this->error('系统可用地址池错误');
				}
				$rs = $model->updateOne(['id' => session('uid'), 'usdtb' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = session('uid');
					Db::table('think_address')->where('address', $address['address'])->update($mp);
					$qianbao1 = $address['address'];
					$rs       = Db::name('merchant_user_address')->insert(['merchant_id' => session('uid'), 'username' => session('username'), 'address' => $qianbao1, 'addtime' => time()]);
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
				if (!$address) {
					$this->error('系统可用地址池错误');
				}
				$rs = $model->updateOne(['id' => session('uid'), 'usdte' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = session('uid');
					Db::table('think_address')->where('address', $address['address'])->update($mp);
					$qianbao2 = $address['address'];
					$rs       = Db::name('merchant_user_address')->insert(['merchant_id' => session('uid'), 'username' => session('username'), 'address' => $qianbao2, 'addtime' => time()]);
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
				if (!$address) {
					$this->error('系统可用地址池错误');
				}
				$rs = $model->updateOne(['id' => session('uid'), 'usdtb' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = session('uid');
					Db::table('think_address')->where('address', $address['address'])->update($mp);
					$qianbao1 = $address['address'];
				} else {
					$this->error($rs['msg']);
				}
			}
			//新方法
			if (!$qianbao2) {
				$address = Db::name('address')->where(['status' => 0, 'type' => 'eth'])->find();
				if (!$address) {
					$this->error('系统可用地址池错误');
				}
				$rs = $model->updateOne(['id' => session('uid'), 'usdtb' => $address['address']]);
				if ($rs['code'] == 1) {
					$mp['status'] = 1;
					$mp['uid']    = session('uid');
					Db::table('think_address')->where('address', $address['address'])->update($mp);
					$qianbao2 = $address['address'];
				} else {
					$this->error($rs['msg']);
				}
			}
		}

		//新方法
		// if(!$qianbao){
		//     $address=Db::name('address')->where('status',0)->find();
		//     if(!$address){
		//          $this->error('系统可用地址池错误');
		//     }
		//     $rs = $model->updateOne(['id'=>session('uid'), 'usdtb'=>$address['address']]);
		//         if($rs['code'] == 1){
		//             $mp['status']=1;
		//             $mp['uid']=session('uid');
		//             Db::table('think_address')->where('address',$address['address'])->update($mp);
		//             $qianbao =$address['address'];
		//         }else{
		//             $this->error($rs['msg']);
		//         }
		// }
		//原方法
		/*
        if(!$qianbao){
            $model2 = new \app\common\model\Usdt();
            $return = $model2->index('getnewaddress', $addr = null, $mum = null, $index=null, $count=null,$skip=null);
            if($return['code'] == 1 && !empty($return['data'])){
             //   $rs = Db::name('merchant_user_address')->insert(['merchant_id'=>$this->merchant['id'], 'username'=>$data['username'], 'address'=>$return['data'], 'addtime'=>time()]);
                $rs = $model->updateOne(['id'=>session('uid'), 'usdtb'=>$return['data']]);
                if($rs['code'] == 1){
                    $qianbao = $return['data'];
                }else{
                    $this->error($rs['msg']);
                }
            }else{
                $this->error('生成钱包地址失败');
            }
        }
        */ // $this->assign('qianbao', $qianbao);
		// $this->assign('qianbao2', $qianbao2);
		$confirms = Db::name('config')->where('name', 'usdt_confirms')->value('value');
		$this->assign('confirms', $confirms);
		$list = Db::name('merchant_recharge')->where(['merchant_id' => session('uid')])->order('id desc')->paginate(20);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function payset() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$user = Db::name('merchant')->where(['id' => session('uid')])->find();
		$this->assign('user', $user);
		$bankModel = new \app\home\model\BankModel();
		$zfb       = new \app\home\model\ZfbModel();
		$wx        = new \app\home\model\WxModel();
		$ysf       = new \app\home\model\YsfModel();
		$this->assign('list', $bankModel->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('list2', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('list3', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('list4', $ysf->getBank(['merchant_id' => session('uid')], 'id desc'));
		return $this->fetch();
	}

	public function delBank() {
		$id = input('param.id');
		$m  = new \app\home\model\BankModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => session('uid')]);
		return json($rs);
	}

	public function delZfb() {
		$id = input('param.id');
		$m  = new \app\home\model\ZfbModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => session('uid')]);
		return json($rs);
	}

	public function delWx() {
		$id = input('param.id');
		$m  = new \app\home\model\WxModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => session('uid')]);
		return json($rs);
	}

	public function delYsf() {
		$id = input('param.id');
		$m  = new \app\home\model\YsfModel();
		$rs = $m->delOne(['id' => $id, 'merchant_id' => session('uid')]);
		return json($rs);
	}

	public function doaccount() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$c_bank                     = input('post.c_bank');
			$c_bank_detail              = input('post.c_bank_detail');
			$c_bank_card                = input('post.c_bank_card');
			$c_bank_card_again          = input('post.c_bank_card_again');
			$id                         = input('post.id');
			$m                          = new \app\home\model\BankModel();
			$param['c_bank']            = $c_bank;
			$param['c_bank_detail']     = $c_bank_detail;
			$param['c_bank_card']       = $c_bank_card;
			$param['c_bank_card_again'] = $c_bank_card_again;
			$param['merchant_id']       = session('uid');
			$param['name']              = input('post.name');
			$param['truename']          = input('post.truename');
			if ($id) {
				$param['id'] = $id;
				$rs          = $m->updateOne($param);
			} else {
				$rs = $m->insertOne($param);
			}
			if ($rs['code'] == 1) {
				$this->success($rs['msg']);
			} else {
				$this->error($rs['msg']);
			}
			$param['id']            = session('uid');
			$param['c_bank']        = $c_bank;
			$param['c_bank_detail'] = $c_bank_detail;
			$param['c_bank_card']   = $c_bank_card;
			$param['name']          = $name;
			$name                   = input('post.name');
			if (empty($name) || !checkName($name)) {
				$this->error('请填写真实姓名');
			}
			if ($c_bank_card_again != $c_bank_card && !empty($c_bank_card)) {
				$this->error('确认银行卡卡号错误！');
			}
			if (strlen($c_bank_card) < 16 || strlen($c_bank_card) > 22) {
				$this->error('请输入正确的银行卡号');
			}
			if (!$c_bank) {
				$this->error('请输入开户银行');
			}
			if (!$c_bank_detail) {
				$this->error('请输入开户支行');
			}
			$param['id']            = session('uid');
			$param['c_bank']        = $c_bank;
			$param['c_bank_detail'] = $c_bank_detail;
			$param['c_bank_card']   = $c_bank_card;
			$param['name']          = $name;
			$model                  = new MerchantModel();
			$return                 = $model->updateOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	public function doalipay() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$name = input('post.name');
			if (empty($name) || !checkName($name)) {
				$this->error('请填写真实姓名');
			}
			$file = request()->file('avatar');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_alipay_img'] = $info->getSaveName();
				} else {
					$this->error('请上传支付宝收款码：' . $file->getError());
				}
			} else {
				$last_img = input('post.last_alipay_img');
				if (empty($last_img)) {
					$this->error('请上传支付宝收款码');
				}
				$param['c_alipay_img'] = $last_img;
			}
			$alipay_account = input('post.alipay_account');
			if (!$alipay_account) {
				$this->error('请输入支付宝账户');
			}
			$param['id']               = session('uid');
			$param['c_alipay_account'] = $alipay_account;
			$param['name']             = $name;
			$model                     = new MerchantModel();
			$return                    = $model->updateOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	public function dowechat() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$name = input('post.name');
			if (empty($name) || !checkName($name)) {
				$this->error('请填写真实姓名');
			}
			$file = request()->file('avatar2');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_wechat_img'] = $info->getSaveName();
				} else {
					$this->error('请上传微信收款码：' . $file->getError());
				}
			} else {
				$last_img = input('post.last_wechat_img');
				if (empty($last_img)) {
					$this->error('请上传微信收款码');
				}
				$param['c_wechat_img'] = $last_img;
			}
			$wechat_account = input('post.wechat_account');
			if (!$wechat_account) {
				$this->error('请输入微信账户');
			}
			$param['id']               = session('uid');
			$param['c_wechat_account'] = $wechat_account;
			$param['name']             = $name;
			$model                     = new MerchantModel();
			$return                    = $model->updateOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	public function doalipaynew() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			// dump(input());die;
			$truename       = input('post.zfbtruename');
			$name           = input('post.zfbname');
			$alipay_account = input('post.alipay_account');
			if (empty($truename)) {
				$this->error('请填写真实姓名');
			}
			if (empty($name)) {
				$this->error('请填写标识名称');
			}
			// if(!$alipay_account){
			//     $this->error('请输入支付宝账户');
			// }
			$file = request()->file('avatar');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_bank_detail'] = $info->getSaveName();
				} else {
					$this->error('请上传支付宝收款码：' . $file->getError());
				}
			} else {
				$last_img = input('post.last_alipay_img');
				if (empty($last_img)) {
					$this->error('请上传支付宝收款码');
				}
				$param['c_bank_detail'] = $last_img;
			}

			$param['merchant_id'] = session('uid');
			$param['c_bank']      = $alipay_account;
			$param['truename']    = $truename;
			$param['name']        = $name;
			$model                = new ZfbModel();
			$return               = $model->insertOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	public function dowechatnew() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$truename       = input('post.wxtruename');
			$name           = input('post.wxname');
			$wechat_account = input('post.wechat_account');
			if (empty($truename)) {
				$this->error('请填写真实姓名');
			}
			if (empty($name)) {
				$this->error('请填写标识名称');
			}
			if (!$wechat_account) {
				$this->error('请输入微信账户');
			}
			$file = request()->file('avatar2');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_bank_detail'] = $info->getSaveName();
				} else {
					$this->error('请上传微信收款码：' . $file->getError());
				}
			} else {
				$last_img = input('post.last_wechat_img');
				if (empty($last_img)) {
					$this->error('请上传微信收款码');
				}
				$param['c_bank_detail'] = $last_img;
			}

			$param['merchant_id'] = session('uid');
			$param['c_bank']      = $wechat_account;
			$param['truename']    = $truename;
			$param['name']        = $name;
			$model                = new WxModel();
			$return               = $model->insertOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	public function doysfnew() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登陆操作', url('home/login/login'));
			}
			$truename = input('post.ysftruename');
			$name     = input('post.ysfname');
			if (empty($truename)) {
				$this->error('请填写真实姓名');
			}
			if (empty($name)) {
				$this->error('请填写标识名称');
			}

			$file = request()->file('avatar2');
			if ($file) {
				$info = $file->validate(['size' => 3145728, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/face');
				if ($info) {
					$param['c_bank_detail'] = $info->getSaveName();
				} else {
					$this->error('请上传已释放收款码：' . $file->getError());
				}
			} else {
				$last_img = input('post.ysfimg');
				if (empty($last_img)) {
					$this->error('请上传微信收款码');
				}
				$param['c_bank_detail'] = $last_img;
			}

			$param['merchant_id'] = session('uid');
			// $param['c_bank'] = $wechat_account;
			$param['truename'] = $truename;
			$param['name']     = $name;
			$model             = new YsfModel();
			$return            = $model->insertOne($param);
			if ($return['code'] == 1) {
				$this->success($return['msg']);
			} else {
				$this->error($return['msg']);
			}
		}
	}

	public function newad_bak() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max')->value('value');
		if (request()->isPost()) {
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的出售数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			} else {
				$price = 7;//本地测试用
				// $price = getUsdtPrice();
			}
			$pay_method = $_POST['pay_method'];
			// dump($pay_method);die;
			$model = new MerchantModel();
			$user  = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			//$haveadsum = Db::name('ad_sell')->where('userid', session('uid'))->where('state', 1)->sum('amount');
			$haveadsum = 0;
			if ($user['usdt'] < $amount + $haveadsum) {
				$this->error('账户余额不足');
			}
			if (empty($pay_method)) {
				$this->error('请选择收款方式');
			}
			foreach ($pay_method as $k => $v) {
				if ($v == 2) {
					if (empty($user['c_bank_card']) || empty($user['c_bank_detail'])) {
						$this->error('请先设置您的银行卡支付信息');
					}
				} elseif ($v == 3) {
					if (empty($user['c_alipay_img']) || empty($user['c_alipay_account'])) {
						$this->error('请先设置您的支付宝信息');
					}
				} elseif ($v == 4) {
					if (empty($user['c_wechat_account']) || empty($user['c_wechat_img'])) {
						$this->error('请先设置您的微信信息');
					}
				}
			}
			$pay_str = implode(',', $pay_method);
			dump($pay_str);
			die;
			$bank = input('post.bank');
			if ($bank) {
				$pay_str = $bank . ',' . $pay_str;
			}
			$ad_no  = $this->getadvno();
			$model2 = new AdModel();
			$flag   = $model2->insertOne(['userid' => session('uid'), 'add_time' => time(), 'coin' => 'usdt', 'min_limit' => $min_limit, 'max_limit' => $max_limit, 'state' => 0, 'pay_method' => $pay_str, 'ad_no' => $ad_no, 'amount' => $amount, 'price' => $price, 'state' => 1]);
			// $flag = $model2->insertOne(['userid'=>session('uid'),'add_time' => time(),'coin'=>'usdt','min_limit'=>$min_limit,'max_limit'=>$max_limit,'state'=>0,'pay_method'=>$pay_str,'ad_no'=>$ad_no,'amount'=>$amount,'price'=>$price,'state'=>1]);
			//增加在售挂单数
			$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
			$model->updateOne(['id' => session('uid'), 'ad_on_sell' => $count]);
			if ($flag['code'] == 1) {
				$this->success($flag['msg']);
			} else {
				$this->error($flag['msg']);
			}
		} else {
			$this->assign('usdt_price_min', $usdt_price_min);
			$this->assign('usdt_price_max', $usdt_price_max);
			$this->assign('usdt_price_way', $usdt_price_way);
			$model2          = new AdModel();
			$where['userid'] = session('uid');
			$list            = $model2->getAd($where, 'id desc');
			foreach ($list as $k => $v) {
				$deal_num           = Db::name('order_buy')->where(['sell_sid' => $v['id'], 'status' => ['neq', 5], 'status' => ['neq', 9]])->sum('deal_num');
				$deal_num           = $deal_num ? $deal_num : 0;
				$list[$k]['deal']   = $deal_num;
				$list[$k]['remain'] = $v['amount'] - $list[$k]['deal'];
			}
			$this->assign('list', $list);
			$m     = new \app\home\model\BankModel();
			$zfb   = new \app\home\model\ZfbModel();
			$wx    = new \app\home\model\WxModel();
			$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
			$this->assign('zfb', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('wx', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('banks', $banks);
			return $this->fetch();
		}
	}

	public function newadbuy_bak() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way_buy')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min_buy')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max_buy')->value('value');
		if (request()->isPost()) {
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的购买数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			} else {
				$price = getUsdtPrice();
			}
			$pay_method = $_POST['pay_method'];//dump($pay_method);die;
			$model      = new MerchantModel();
			$user       = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_buy')->where('userid', session('uid'))->where('state', 1)->count();
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($haveadsum > 20) {
				$this->error('挂买最多发布20个');
			}
			if (empty($pay_method)) {
				$this->error('请选择收款方式');
			}
			foreach ($pay_method as $k => $v) {
				if ($v == 2) {
					if (empty($user['c_bank_card']) || empty($user['c_bank_detail'])) {
						$this->error('请先设置您的银行卡支付信息');
					}
				} elseif ($v == 3) {
					if (empty($user['c_alipay_img']) || empty($user['c_alipay_account'])) {
						$this->error('请先设置您的支付宝信息');
					}
				} elseif ($v == 4) {
					if (empty($user['c_wechat_account']) || empty($user['c_wechat_img'])) {
						$this->error('请先设置您的微信信息');
					}
				}
			}
			$pay_str = implode(',', $pay_method);//dump($pay_str);die;
			$bank    = input('post.bank');
			if ($bank) {
				$pay_str = $bank . ',' . $pay_str;
			}
			$ad_no  = $this->getadvno();
			$model2 = new AdbuyModel();
			$flag   = $model2->insertOne(['userid' => session('uid'), 'add_time' => time(), 'coin' => 'usdt', 'min_limit' => $min_limit, 'max_limit' => $max_limit, 'pay_method' => $pay_str, 'ad_no' => $ad_no, 'amount' => $amount, 'price' => $price, 'state' => 1]);
			//增加挂买数
			$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
			$model->updateOne(['id' => session('uid'), 'ad_on_buy' => $count]);
			if ($flag['code'] == 1) {
				$this->success($flag['msg']);
			} else {
				$this->error($flag['msg']);
			}
		} else {
			$this->assign('usdt_price_min', $usdt_price_min);
			$this->assign('usdt_price_max', $usdt_price_max);
			$this->assign('usdt_price_way', $usdt_price_way);
			$model2          = new AdbuyModel();
			$where['userid'] = session('uid');
			$list            = $model2->getAd($where, 'id desc');
			foreach ($list as $k => $v) {
				$deal_num           = Db::name('order_sell')->where(['buy_bid' => $v['id'], 'status' => ['neq', 5]])->sum('deal_num');
				$deal_num           = $deal_num ? $deal_num : 0;
				$list[$k]['deal']   = $deal_num;
				$list[$k]['remain'] = $v['amount'] - $list[$k]['deal'];
			}
			$this->assign('list', $list);
			$m     = new \app\home\model\BankModel();
			$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
			$this->assign('banks', $banks);
			return $this->fetch();
		}
	}

	public function newad() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max')->value('value');
		if ($usdt_price_way == 2) {
			$pricelimit = getUsdtPrice() + config('usdt_price_add');
		} else {
			$pricelimit = 0;
		}
		$m   = new \app\home\model\BankModel();
		$zfb = new \app\home\model\ZfbModel();
		$wx  = new \app\home\model\WxModel();
		$ysf = new \app\home\model\YsfModel();
		if (request()->isPost()) {
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的出售数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			}
			if ($usdt_price_way == 1) {
				$price = getUsdtPrice();
			}
			// if($usdt_price_way == 2){
			//     $price = input('post.price');
			//     $pricelimit = getUsdtPrice()+config('usdt_price_add');

			//     if($price !=$pricelimit){
			//         $this->error('价格错误!');
			//     }
			// }
			if ($usdt_price_way == 2) {
				$pricelimit = floatval(getUsdtPrice() + config('usdt_price_add'));
				$price      = floatval(getUsdtPrice() + config('usdt_price_add'));
			}

			// $pay_method = $_POST['pay_method'];
			// dump($pay_method);die;
			$model = new MerchantModel();
			$user  = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_sell')->where('userid', session('uid'))->where('state', 1)->sum('amount');
			// $haveadsum = 0;
			if ($user['usdt'] < $amount + $haveadsum) {
				$this->error('账户余额不足');
			}
			if (empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx']) && empty($_POST['ysf'])) {
				$this->error('请选择收款方式');
			}
			// dump($_POST);die;
			//查询用户的银行卡信息
			$where1['merchant_id'] = session('uid');
			$where1['id']          = $_POST['bank'];
			$isbank                = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = session('uid');
			$where2['id']          = $_POST['zfb'];
			$iszfb                 = $zfb->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = session('uid');
			$where3['id']          = $_POST['wx'];
			$iswx                  = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = session('uid');
			$where4['id']          = $_POST['ysf'];
			$isysf                 = $ysf->getOne($where4);
			if ($_POST['bank'] && !$isbank) {
				$this->error('请先设置您的银行卡账户信息');
			}
			if ($_POST['zfb'] && !$iszfb) {
				$this->error('请先设置您的支付宝账户信息');
			}
			if ($_POST['wx'] && !$iswx) {
				$this->error('请先设置您的微信账户信息');
			}
			if ($_POST['ysf'] && !$isysf) {
				$this->error('请先设置您的云闪付账户信息');
			}
			// dump($isbank['name']);die;

			$ad_no  = $this->getadvno();
			$model2 = new AdModel();
			$flag   = $model2->insertOne([
				'userid'      => session('uid'),
				'add_time'    => time(),
				'coin'        => 'usdt',
				'min_limit'   => $min_limit,
				'max_limit'   => $max_limit,
				'state'       => 0,
				'pay_method'  => $_POST['bank'],
				'pay_method2' => $_POST['zfb'],
				'pay_method3' => $_POST['wx'],
				'pay_method4' => $_POST['ysf'],
				'ad_no'       => $ad_no,
				'amount'      => $amount,
				'price'       => $price,
				'state'       => 1
			]);
			//增加在售挂单数
			$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
			$model->updateOne(['id' => session('uid'), 'ad_on_sell' => $count]);
			if ($flag['code'] == 1) {
				$this->success($flag['msg']);
			} else {
				$this->error($flag['msg']);
			}
		} else {
			$this->assign('usdt_price_min', $usdt_price_min);
			$this->assign('usdt_price_max', $usdt_price_max);
			$this->assign('usdt_price_way', $usdt_price_way);
			$model2          = new AdModel();
			$where['userid'] = session('uid');
			$list            = $model2->getAd($where, 'id desc');
			foreach ($list as $k => $v) {
				$deal_num           = Db::name('order_buy')->where(['sell_sid' => $v['id'], 'status' => ['neq', 5], 'status' => ['neq', 9]])->sum('deal_num');
				$deal_num           = $deal_num ? $deal_num : 0;
				$list[$k]['deal']   = $deal_num;
				$list[$k]['remain'] = $v['amount'] - $list[$k]['deal'];
			}
			$this->assign('list', $list);
			$this->assign('pricelimit', $pricelimit);
			$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
			$this->assign('zfb', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('wx', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('ysf', $ysf->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('banks', $banks);
			return $this->fetch();
		}
	}

	public function newadbuy() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way_buy')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min_buy')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max_buy')->value('value');
		if ($usdt_price_way == 2) {
			$pricelimit = getUsdtPrice() + config('usdt_price_add_buy');
		} else {
			$pricelimit = 0;
		}
		$m   = new \app\home\model\BankModel();
		$zfb = new \app\home\model\ZfbModel();
		$wx  = new \app\home\model\WxModel();
		$ysf = new \app\home\model\YsfModel();
		if (request()->isPost()) {
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的购买数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			// if($usdt_price_way == 0){
			//     $price = input('post.price');
			//     if($price > $usdt_price_max || $price < $usdt_price_min){
			//         $this->error('价格区间：'.$usdt_price_min.'~'.$usdt_price_max);
			//     }
			// }else{
			//     $price = getUsdtPrice();
			// }
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			}
			if ($usdt_price_way == 1) {
				$price = getUsdtPrice();
			}
			// if($usdt_price_way == 2){
			//     $price = input('post.price');
			//     $pricelimit = getUsdtPrice()+config('usdt_price_add');

			//     if($price !=$pricelimit){
			//         $this->error('价格错误!');
			//     }
			// }
			if ($usdt_price_way == 2) {
				// $pricelimit = floatval(getUsdtPrice()+config('usdt_price_add_buy'));
				$price = floatval(getUsdtPrice() + config('usdt_price_add_buy'));
			}
			// $pay_method = $_POST['pay_method'];//dump($pay_method);die;
			$model = new MerchantModel();
			$user  = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_buy')->where('userid', session('uid'))->where('state', 1)->count();
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($haveadsum > 20) {
				$this->error('挂买最多发布20个');
			}
			if (empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx']) && empty($_POST['ysf'])) {
				$this->error('请选择收款方式');
			}
			// dump($_POST);die;
			//查询用户的银行卡信息
			$where1['merchant_id'] = session('uid');
			$where1['id']          = $_POST['bank'];
			$isbank                = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = session('uid');
			$where2['id']          = $_POST['zfb'];
			$iszfb                 = $zfb->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = session('uid');
			$where3['id']          = $_POST['wx'];
			$iswx                  = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = session('uid');
			$where4['id']          = $_POST['ysf'];
			$isysf                 = $ysf->getOne($where4);
			if ($_POST['bank'] && !$isbank) {
				$this->error('请先设置您的银行卡账户信息');
			}
			if ($_POST['zfb'] && !$iszfb) {
				$this->error('请先设置您的支付宝账户信息');
			}
			if ($_POST['wx'] && !$iswx) {
				$this->error('请先设置您的微信账户信息');
			}
			if ($_POST['ysf'] && !$isysf) {
				$this->error('请先设置您的云闪付账户信息');
			}
			$ad_no  = $this->getadvno();
			$model2 = new AdbuyModel();
			$flag   = $model2->insertOne([
				'userid'      => session('uid'),
				'add_time'    => time(),
				'coin'        => 'usdt',
				'min_limit'   => $min_limit,
				'max_limit'   => $max_limit,
				'pay_method'  => $_POST['bank'],
				'pay_method2' => $_POST['zfb'],
				'pay_method3' => $_POST['wx'],
				'pay_method4' => $_POST['ysf'],
				'ad_no'       => $ad_no,
				'amount'      => $amount,
				'price'       => $price,
				'state'       => 1
			]);
			//增加挂买数
			$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
			$model->updateOne(['id' => session('uid'), 'ad_on_buy' => $count]);
			if ($flag['code'] == 1) {
				$this->success($flag['msg']);
			} else {
				$this->error($flag['msg']);
			}
		} else {
			$this->assign('usdt_price_min', $usdt_price_min);
			$this->assign('usdt_price_max', $usdt_price_max);
			$this->assign('usdt_price_way', $usdt_price_way);
			$model2          = new AdbuyModel();
			$where['userid'] = session('uid');
			$list            = $model2->getAd($where, 'id desc');
			foreach ($list as $k => $v) {
				$deal_num           = Db::name('order_sell')->where(['buy_bid' => $v['id'], 'status' => ['neq', 5]])->sum('deal_num');
				$deal_num           = $deal_num ? $deal_num : 0;
				$list[$k]['deal']   = $deal_num;
				$list[$k]['remain'] = $v['amount'] - $list[$k]['deal'];
			}
			$this->assign('list', $list);
			$this->assign('pricelimit', $pricelimit);
			// $m = new \app\home\model\BankModel();
			$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
			$this->assign('zfb', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('wx', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('ysf', $ysf->getBank(['merchant_id' => session('uid')], 'id desc'));
			$this->assign('banks', $banks);
			return $this->fetch();
		}
	}

	public function editad() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max')->value('value');
		if ($usdt_price_way == 2) {
			$pricelimit = getUsdtPrice() + config('usdt_price_add');
		} else {
			$pricelimit = 0;
		}
		$m   = new \app\home\model\BankModel();
		$zfb = new \app\home\model\ZfbModel();
		$wx  = new \app\home\model\WxModel();
		$ysf = new \app\home\model\YsfModel();
		if (request()->isPost()) {
			$id              = input('post.id');
			$model           = new MerchantModel();
			$model2          = new AdModel();
			$where['id']     = $id;
			$where['userid'] = session('uid');
			$ad              = $model2->getOne($where);
			if (empty($ad)) {
				$this->error('挂单标识错误');
			}
			$order = Db::name('order_buy')->where(['sell_sid' => $id])->find();
			if (!empty($order)) {
				$this->error('该挂单有订单，不能编辑');
			}
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的出售数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			// if($usdt_price_way == 0){
			//     $price = input('post.price');
			//     if($price > $usdt_price_max || $price < $usdt_price_min){
			//         $this->error('价格区间：'.$usdt_price_min.'~'.$usdt_price_max);
			//     }
			// }else{
			//     $price = getUsdtPrice();
			// }
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			}
			if ($usdt_price_way == 1) {
				$price = getUsdtPrice();
			}
			// if($usdt_price_way == 2){
			//     $price = input('post.price');
			//     $pricelimit = getUsdtPrice()+config('usdt_price_add');

			//     if($price !=$pricelimit){
			//         $this->error('价格错误!');
			//     }
			// }
			if ($usdt_price_way == 2) {
				$pricelimit = floatval(getUsdtPrice() + config('usdt_price_add'));
				$price      = floatval(getUsdtPrice() + config('usdt_price_add'));
			}
			// $pay_method = $_POST['pay_method'];//dump($pay_method);die;

			$user = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_sell')->where('userid', session('uid'))->where('state', 1)->sum('amount');
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($user['usdt'] < $amount + $haveadsum) {
				$this->error('账户余额不足');
			}
			if (empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx']) && empty($_POST['ysf'])) {
				$this->error('请选择收款方式');
			}
			// dump($_POST);die;
			//查询用户的银行卡信息
			$where1['merchant_id'] = session('uid');
			$where1['id']          = $_POST['bank'];
			$isbank                = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = session('uid');
			$where2['id']          = $_POST['zfb'];
			$iszfb                 = $zfb->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = session('uid');
			$where3['id']          = $_POST['wx'];
			$iswx                  = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = session('uid');
			$where4['id']          = $_POST['ysf'];
			$isysf                 = $ysf->getOne($where4);
			if ($_POST['bank'] && !$isbank) {
				$this->error('请先设置您的银行卡账户信息');
			}
			if ($_POST['zfb'] && !$iszfb) {
				$this->error('请先设置您的支付宝账户信息');
			}
			if ($_POST['wx'] && !$iswx) {
				$this->error('请先设置您的微信账户信息');
			}
			if ($_POST['ysf'] && !$isysf) {
				$this->error('请先设置您的云闪付账户信息');
			}
			$ad_no = $this->getadvno();
			$flag  = $model2->updateOne([
				'id'          => $id,
				'min_limit'   => $min_limit,
				'max_limit'   => $max_limit,
				'state'       => 1,
				'pay_method'  => $_POST['bank'],
				'pay_method2' => $_POST['zfb'],
				'pay_method3' => $_POST['wx'],
				'pay_method4' => $_POST['ysf'],
				'amount'      => $amount,
				'price'       => $price,
				'state'       => 1
			]);
			if ($flag['code'] == 1) {
				$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
				$model->updateOne(['id' => session('uid'), 'ad_on_sell' => $count]);
				$this->success($flag['msg'], '/merchant/newad/');

			} else {
				$this->error($flag['msg']);
			}
		}
		$id              = input('get.id');
		$where['id']     = $id;
		$where['userid'] = session('uid');
		$model           = new AdModel();
		$ad              = $model->getOne($where);
		if (empty($ad)) {
			$this->error('挂单标识错误');
		}
		$this->assign('ad', $ad);
		$this->assign('pricelimit', $pricelimit);
		$this->assign('usdt_price_min', $usdt_price_min);
		$this->assign('usdt_price_max', $usdt_price_max);
		$this->assign('usdt_price_way', $usdt_price_way);
		// $m = new \app\home\model\BankModel();
		$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
		$this->assign('zfb', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('wx', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('ysf', $ysf->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('banks', $banks);
		return $this->fetch();
	}

	public function editadbuy() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way_buy')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min_buy')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max_buy')->value('value');
		if ($usdt_price_way == 2) {
			$pricelimit = getUsdtPrice() + config('usdt_price_add_buy');
		} else {
			$pricelimit = 0;
		}
		$m   = new \app\home\model\BankModel();
		$zfb = new \app\home\model\ZfbModel();
		$wx  = new \app\home\model\WxModel();
		$ysf = new \app\home\model\YsfModel();
		if (request()->isPost()) {
			$id              = input('post.id');
			$model           = new MerchantModel();
			$model2          = new AdbuyModel();
			$where['id']     = $id;
			$where['userid'] = session('uid');
			$ad              = $model2->getOne($where);
			if (empty($ad)) {
				$this->error('挂单标识错误');
			}
			$order = Db::name('order_sell')->where(['buy_bid' => $id])->find();
			if (!empty($order)) {
				$this->error('该挂单有订单，不能编辑');
			}
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的出售数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			// if($usdt_price_way == 0){
			//     $price = input('post.price');
			//     if($price > $usdt_price_max || $price < $usdt_price_min){
			//         $this->error('价格区间：'.$usdt_price_min.'~'.$usdt_price_max);
			//     }
			// }else{
			//     $price = getUsdtPrice();
			// }
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			}
			if ($usdt_price_way == 1) {
				$price = getUsdtPrice();
			}
			if ($usdt_price_way == 2) {
				// $pricelimit = floatval(getUsdtPrice()+config('usdt_price_add'));
				$price = floatval(getUsdtPrice() + config('usdt_price_add'));
			}
			// $pay_method = $_POST['pay_method'];//dump($pay_method);die;

			$user = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_buy')->where('userid', session('uid'))->where('state', 1)->count();
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($haveadsum > 20) {
				$this->error('购买挂单最多发布20个');
			}
			if (empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx'])) {
				$this->error('请选择收款方式');
			}
			// dump($_POST);die;
			//查询用户的银行卡信息
			$where1['merchant_id'] = session('uid');
			$where1['id']          = $_POST['bank'];
			$isbank                = $m->getOne($where1);
			//查询用户的支付宝信息
			$where2['merchant_id'] = session('uid');
			$where2['id']          = $_POST['zfb'];
			$iszfb                 = $zfb->getOne($where2);
			//查询用户的微信信息
			$where3['merchant_id'] = session('uid');
			$where3['id']          = $_POST['wx'];
			$iswx                  = $wx->getOne($where3);
			//查询用户的云闪付信息
			$where4['merchant_id'] = session('uid');
			$where4['id']          = $_POST['ysf'];
			$isysf                 = $ysf->getOne($where4);
			if ($_POST['bank'] && !$isbank) {
				$this->error('请先设置您的银行卡账户信息');
			}
			if ($_POST['zfb'] && !$iszfb) {
				$this->error('请先设置您的支付宝账户信息');
			}
			if ($_POST['wx'] && !$iswx) {
				$this->error('请先设置您的微信账户信息');
			}
			if ($_POST['ysf'] && !$isysf) {
				$this->error('请先设置您的云闪付账户信息');
			}
			$ad_no = $this->getadvno();
			$flag  = $model2->updateOne(['id' => $id, 'min_limit' => $min_limit, 'max_limit' => $max_limit, 'pay_method' => $_POST['bank'], 'pay_method2' => $_POST['zfb'], 'pay_method3' => $_POST['wx'], 'pay_method4' => $_POST['ysf'], 'amount' => $amount, 'price' => $price, 'state' => 1]);
			if ($flag['code'] == 1) {
				$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
				$model->updateOne(['id' => session('uid'), 'ad_on_buy' => $count ? $count : 0]);
				$this->success($flag['msg'], '/merchant/newadbuy/');
			} else {
				$this->error($flag['msg']);
			}
		}
		$id              = input('get.id');
		$where['id']     = $id;
		$where['userid'] = session('uid');
		$model           = new AdbuyModel();
		$ad              = $model->getOne($where);
		if (empty($ad)) {
			$this->error('挂单标识错误');
		}
		$this->assign('ad', $ad);
		$this->assign('usdt_price_min', $usdt_price_min);
		$this->assign('usdt_price_max', $usdt_price_max);
		$this->assign('usdt_price_way', $usdt_price_way);
		$this->assign('pricelimit', $pricelimit);
		$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
		$this->assign('zfb', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('wx', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('ysf', $ysf->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('banks', $banks);
		return $this->fetch();
	}

	public function editad_bak() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max')->value('value');
		if (request()->isPost()) {
			$id              = input('post.id');
			$model           = new MerchantModel();
			$model2          = new AdModel();
			$where['id']     = $id;
			$where['userid'] = session('uid');
			$ad              = $model2->getOne($where);
			if (empty($ad)) {
				$this->error('挂单标识错误');
			}
			$order = Db::name('order_buy')->where(['sell_sid' => $id])->find();
			if (!empty($order)) {
				$this->error('该挂单有订单，不能编辑');
			}
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的出售数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			} else {
				$price = getUsdtPrice();
			}
			$pay_method = $_POST['pay_method'];//dump($pay_method);die;

			$user = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_sell')->where('userid', session('uid'))->where('state', 1)->sum('amount');
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($user['usdt'] < $amount + $haveadsum) {
				$this->error('账户余额不足');
			}
			if (empty($pay_method)) {
				$this->error('请选择收款方式');
			}
			foreach ($pay_method as $k => $v) {
				if ($v == 2) {
					if (empty($user['c_bank_card']) || empty($user['c_bank_detail'])) {
						$this->error('请先设置您的银行卡支付信息');
					}
				} elseif ($v == 3) {
					if (empty($user['c_alipay_img']) || empty($user['c_alipay_account'])) {
						$this->error('请先设置您的支付宝信息');
					}
				} elseif ($v == 4) {
					if (empty($user['c_wechat_account']) || empty($user['c_wechat_img'])) {
						$this->error('请先设置您的微信信息');
					}
				}
			}
			$pay_str = implode(',', $pay_method);//dump($pay_str);die;
			$bank    = input('post.bank');
			if ($bank) {
				$pay_str = $bank . ',' . $pay_str;
			}
			$ad_no = $this->getadvno();
			$flag  = $model2->updateOne(['id' => $id, 'min_limit' => $min_limit, 'max_limit' => $max_limit, 'state' => 1, 'pay_method' => $pay_str, 'amount' => $amount, 'price' => $price, 'state' => 1]);
			if ($flag['code'] == 1) {
				$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
				$model->updateOne(['id' => session('uid'), 'ad_on_sell' => $count]);
				$this->success($flag['msg']);
				die;
			} else {
				$this->error($flag['msg']);
			}
		}
		$id              = input('get.id');
		$where['id']     = $id;
		$where['userid'] = session('uid');
		$model           = new AdModel();
		$ad              = $model->getOne($where);
		if (empty($ad)) {
			$this->error('挂单标识错误');
		}
		$this->assign('ad', $ad);
		$this->assign('usdt_price_min', $usdt_price_min);
		$this->assign('usdt_price_max', $usdt_price_max);
		$this->assign('usdt_price_way', $usdt_price_way);
		$m     = new \app\home\model\BankModel();
		$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
		$this->assign('banks', $banks);
		return $this->fetch();
	}

	public function editadbuy_bak() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$usdt_price_way = Db::name('config')->where('name', 'usdt_price_way_buy')->value('value');
		$usdt_price_min = Db::name('config')->where('name', 'usdt_price_min_buy')->value('value');
		$usdt_price_max = Db::name('config')->where('name', 'usdt_price_max_buy')->value('value');
		if (request()->isPost()) {
			$id              = input('post.id');
			$model           = new MerchantModel();
			$model2          = new AdbuyModel();
			$where['id']     = $id;
			$where['userid'] = session('uid');
			$ad              = $model2->getOne($where);
			if (empty($ad)) {
				$this->error('挂单标识错误');
			}
			$order = Db::name('order_sell')->where(['buy_bid' => $id])->find();
			if (!empty($order)) {
				$this->error('该挂单有订单，不能编辑');
			}
			$amount = input('post.amount');
			if ($amount <= 0) {
				$this->error('请输入正确的出售数量');
			}
			$min_limit = input('post.min_limit');
			if ($min_limit <= 0) {
				$this->error('请输入正确的最小限额');
			}
			$max_limit = input('post.max_limit');
			if ($max_limit <= 0) {
				$this->error('请输入正确的最大限额');
			}
			if ($min_limit > $max_limit) {
				$this->error('最小限额不能大于最大限额！');
			}
			if ($usdt_price_way == 0) {
				$price = input('post.price');
				if ($price > $usdt_price_max || $price < $usdt_price_min) {
					$this->error('价格区间：' . $usdt_price_min . '~' . $usdt_price_max);
				}
			} else {
				$price = getUsdtPrice();
			}
			$pay_method = $_POST['pay_method'];//dump($pay_method);die;

			$user = $model->getUserByParam(session('uid'), 'id');
			if ($user['trader_check'] != 1) {
				$this->error('您的承兑商资格未通过');
			}
			$haveadsum = Db::name('ad_buy')->where('userid', session('uid'))->where('state', 1)->count();
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($haveadsum > 20) {
				$this->error('购买挂单最多发布20个');
			}
			if (empty($pay_method)) {
				$this->error('请选择收款方式');
			}
			foreach ($pay_method as $k => $v) {
				if ($v == 2) {
					if (empty($user['c_bank_card']) || empty($user['c_bank_detail'])) {
						$this->error('请先设置您的银行卡支付信息');
					}
				} elseif ($v == 3) {
					if (empty($user['c_alipay_img']) || empty($user['c_alipay_account'])) {
						$this->error('请先设置您的支付宝信息');
					}
				} elseif ($v == 4) {
					if (empty($user['c_wechat_account']) || empty($user['c_wechat_img'])) {
						$this->error('请先设置您的微信信息');
					}
				}
			}
			$pay_str = implode(',', $pay_method);//dump($pay_str);die;
			$bank    = input('post.bank');
			if ($bank) {
				$pay_str = $bank . ',' . $pay_str;
			}
			$ad_no = $this->getadvno();
			$flag  = $model2->updateOne(['id' => $id, 'min_limit' => $min_limit, 'max_limit' => $max_limit, 'pay_method' => $pay_str, 'amount' => $amount, 'price' => $price, 'state' => 1]);
			if ($flag['code'] == 1) {
				$count = $model2->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
				$model->updateOne(['id' => session('uid'), 'ad_on_buy' => $count ? $count : 0]);
				$this->success($flag['msg']);
				die;
			} else {
				$this->error($flag['msg']);
			}
		}
		$id              = input('get.id');
		$where['id']     = $id;
		$where['userid'] = session('uid');
		$model           = new AdbuyModel();
		$ad              = $model->getOne($where);
		if (empty($ad)) {
			$this->error('挂单标识错误');
		}
		$this->assign('ad', $ad);
		$this->assign('usdt_price_min', $usdt_price_min);
		$this->assign('usdt_price_max', $usdt_price_max);
		$this->assign('usdt_price_way', $usdt_price_way);
		$m     = new \app\home\model\BankModel();
		$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
		$this->assign('banks', $banks);
		return $this->fetch();
	}

	//挂单上下架
	public function setShelf() {
		$id   = input('post.id');
		$type = input('post.type');
		$act  = input('post.act');
		if (!session('uid')) {
			$this->error('请登录操作');
		}
		if ($type != 0 && $type != 1) {
			$this->error("挂单类型错误！");
		}
		$model           = new AdModel();
		$model2          = new MerchantModel();
		$where['id']     = $id;
		$where['userid'] = session('uid');
		$ad_info         = $model->getOne($where);
		if (!$ad_info) {
			$this->error("挂单不存在！");
		} else {
			if ($ad_info['state'] == 4) {
				$this->error("此挂单已冻结禁止上下架操作！");
			}
		}
		$merchant = $model2->getUserByParam(session('uid'), 'id');
		if ($act == 1) {
			$haveadsum = Db::name('ad_sell')->where('userid', session('uid'))->where('state', 1)->sum('amount');
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if (($ad_info['amount'] + $haveadsum) * 1 > $merchant['usdt'] * 1) {
				$this->error('开启失败：账户余额不足');
			}
		}

		$result = $model->updateOne(['id' => $id, 'state' => $act]);
		if ($result['code'] == 1) {
			$count = $model->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
			$model2->updateOne(['id' => session('uid'), 'ad_on_sell' => $count ? $count : 0]);
			$this->success("操作成功");
		} else {
			$this->error("操作失败");
		}
	}

	public function setShelfbuy() {
		$id   = input('post.id');
		$type = input('post.type');
		$act  = input('post.act');
		if (!session('uid')) {
			$this->error('请登录操作');
		}
		if ($type != 0 && $type != 1) {
			$this->error("挂单类型错误！");
		}
		$model           = new AdbuyModel();
		$model2          = new MerchantModel();
		$where['id']     = $id;
		$where['userid'] = session('uid');
		$ad_info         = $model->getOne($where);
		if (!$ad_info) {
			$this->error("挂单不存在！");
		} else {
			if ($ad_info['state'] == 4) {
				$this->error("此挂单已冻结禁止上下架操作！");
			}
		}
		$merchant = $model2->getUserByParam(session('uid'), 'id');
		if ($act == 1) {
			$haveadsum = Db::name('ad_buy')->where('userid', session('uid'))->where('state', 1)->count();
			$haveadsum = $haveadsum ? $haveadsum : 0;
			if ($haveadsum > 20) {
				$this->error('开启失败：挂买最多上架20个');
			}
		}

		$result = $model->updateOne(['id' => $id, 'state' => $act]);
		if ($result['code'] == 1) {
			$count = $model->where('userid', session('uid'))->where('state', 1)->where('amount', 'gt', 0)->count();
			$model2->updateOne(['id' => session('uid'), 'ad_on_buy' => $count ? $count : 0]);
			$this->success("操作成功");
		} else {
			$this->error("操作失败");
		}
	}

	public function adindex() {
		$order = 'a.id desc';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new AdbuyModel();
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['state']  = 1;
		$where['userid'] = ['neq', session('uid')];
		$list            = $model->getAdIndex($where, $order);//dump($list);
		foreach ($list as $k => $v) {
			$deal_num               = Db::name('order_sell')->where('buy_bid', $v['id'])->where('status', 'neq', 5)->sum('deal_num');
			$list[$k]['remain_num'] = $v['amount'] - $deal_num;
		}
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function addetail() {
		$id     = input('get.id');
		$model  = new AdbuyModel();
		$model2 = new MerchantModel();
		$ad     = $model->getOne(['id' => $id]);
		if (empty($ad)) {
			$this->error('挂单不存在');
		}
		if ($ad['state'] != 1) {
			$this->error('挂单未上架');
		}
		$m               = new \app\home\model\BankModel();
		$zfb             = new \app\home\model\ZfbModel();
		$wx              = new \app\home\model\WxModel();
		$ysf             = new \app\home\model\YsfModel();
		$AdOwner         = $model2->getUserByParam($ad['userid'], 'id');
		$deal_num        = Db::name('order_sell')->where('buy_bid', $id)->where('status', 'neq', 5)->sum('deal_num');
		$ad['RemainNum'] = $ad['amount'] - $deal_num;
		$this->assign('ad', $ad);
		// dump($ad['price']);die;
		$this->assign('AdOwner', $AdOwner);
		$banks = $m->where('merchant_id', session('uid'))->order('id desc')->select();
		$this->assign('zfb', $zfb->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('wx', $wx->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('ysf', $ysf->getBank(['merchant_id' => session('uid')], 'id desc'));
		$this->assign('banks', $banks);
		return $this->fetch();
	}

	public function trade_ajax() {
		//type0买挂单1卖挂单 num用户要交易的数量 tid挂单的id tamount用户要交易的价格
		$type    = 0;
		$num     = input('post.qty');//数量
		$tid     = input('post.tid');
		$tamount = input('post.amount');//金额
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		//       $getpaymethod = input('post.getpaymethod/a', []);
		// if(empty($getpaymethod)){
		//           $this->error('请选择收款方式');
		//       }
		if (empty($_POST['bank']) && empty($_POST['zfb']) && empty($_POST['wx'])) {
			$this->error('请选择收款方式');
		}
		$model = new MerchantModel();
		$my    = $model->getUserByParam(session('uid'), 'id');
		$m     = new \app\home\model\BankModel();
		$zfb   = new \app\home\model\ZfbModel();
		$wx    = new \app\home\model\WxModel();
		$ysf   = new \app\home\model\YsfModel();
		//查询用户的银行卡信息
		$where1['merchant_id'] = session('uid');
		$where1['id']          = $_POST['bank'];
		$isbank                = $m->getOne($where1);
		//查询用户的支付宝信息
		$where2['merchant_id'] = session('uid');
		$where2['id']          = $_POST['zfb'];
		$iszfb                 = $zfb->getOne($where2);
		//查询用户的微信信息
		$where3['merchant_id'] = session('uid');
		$where3['id']          = $_POST['wx'];
		$iswx                  = $wx->getOne($where3);
		//查询用户的云闪付信息
		$where4['merchant_id'] = session('uid');
		$where4['id']          = $_POST['ysf'];
		$isysf                 = $ysf->getOne($where4);
		if ($_POST['bank'] && !$isbank) {
			$this->error('请先设置您的银行卡账户信息');
		}
		if ($_POST['zfb'] && !$iszfb) {
			$this->error('请先设置您的支付宝账户信息');
		}
		if ($_POST['wx'] && !$iswx) {
			$this->error('请先设置您的微信账户信息');
		}
		if ($_POST['ysf'] && !$isysf) {
			$this->error('请先设置您的云闪付账户信息');
		}
		if ($tid <= 0) {
			$this->error('挂单不存在');
		}
		if ($num <= 0) {
			$this->error('交易数量必须大于0');
		}
		if ($tamount <= 0) {
			$this->error('交易金额必须大于0');
		}

		/**************我要出售*******************/
		$coin_name = 'usdt';
		if ($type == 0) {
			$orderinfo = Db::name('ad_buy')->where(['id' => $tid])->find();
			if (!$orderinfo) {
				$this->error('此挂单不存在');
			}
			// dump($orderinfo);die;
			// $pay_method = explode(',', $orderinfo['pay_method']);
			// $pay_length = count($pay_method);
			$k2  = 0;//dump($my['c_alipay_img']);dump($pay_method);
			$msg = '';
			// foreach($getpaymethod as $k=>$v){
			if ($orderinfo['pay_method'] > 0) {
				$banks = $m->getBank(['merchant_id' => session('uid')], 'id desc');
				// $banks = $m->where('merchant_id', session('uid'))->find();
				// dump(session('uid'));die;
				if (empty($banks)) {
					$k2++;
					$msg .= '银行转账信息未设置 ';
				}
			}
			if ($orderinfo['pay_method2'] > 0) {
				$zfb = $zfb->getBank(['merchant_id' => session('uid')], 'id desc');
				if (empty($zfb)) {
					$k2++;
					$msg .= '支付宝信息未设置 ';
				}
			}
			if ($orderinfo['pay_method3'] > 0) {
				$wx = $wx->getBank(['merchant_id' => session('uid')], 'id desc');
				if (empty($wx)) {
					$k2++;
					$msg .= '微信支付信息未设置 ';
				}
			}
			if ($orderinfo['pay_method4'] > 0) {
				$ysf = $ysf->getBank(['merchant_id' => session('uid')], 'id desc');
				if (empty($ysf)) {
					$k2++;
					$msg .= '云闪付支付信息未设置 ';
				}
			}
			// }
			if ($k2 > 0) {
				$this->error($msg);
			}
			//dump($k2);die;
			//判断交易范围
			if ($tamount < $orderinfo['min_limit']) {
				$this->error('交易金额超出范围');
			}
			if ($tamount > $orderinfo['max_limit']) {
				$this->error('交易金额超出范围');
			}
			$merchant_fee = Db::name('config')->where('name', 'usdt_buy_merchant_fee')->value('value');
			$fee          = 0;
			if ($merchant_fee) {
				$fee = $num * $merchant_fee / 100;
			}
			if ($my[$coin_name] * 1 < ($num + $fee) * 1) {
				// if($my[$coin_name]*1<$num*1){
				$this->error('您的账户余额不足，请先充值' . strtoupper($coin_name) . '，再进行出售');
			}

			$arr                = [];
			$arr['buy_id']      = $orderinfo['userid'];
			$arr['buy_bid']     = $orderinfo['id'];
			$arr['sell_id']     = session('uid');
			$arr['deal_amount'] = $tamount;
			$arr['deal_num']    = $num;
			$arr['deal_price']  = $orderinfo['price'];
			$arr['ctime']       = time();
			$arr['ltime']       = 15;
			$arr['order_no']    = createOrderNo(4, session('uid'));
			$arr['fee']         = $fee;
			$arr['pay']         = $_POST['bank'];
			$arr['pay2']        = $_POST['zfb'];
			$arr['pay3']        = $_POST['wx'];
			$arr['pay4']        = $_POST['ysf'];
			// $arr['getpaymethod'] = implode(',', $getpaymethod);
			try {
				Db::startTrans();
				$rs1 = $id = Db::table('think_order_sell')->insertGetId($arr);
				//卖家的btc需要冻结起来
				$rs2 = Db::table('think_merchant')->where('id', session('uid'))->setDec($coin_name, $num + $fee);
				$rs3 = Db::table('think_merchant')->where('id', session('uid'))->setInc($coin_name . 'd', $num + $fee);
				if ($rs1 && $rs2 && $rs3) {
					Db::commit();
					financelog(session('uid'), ($num + $fee), '卖出USDT_冻结1', 1, session('user.name'));//添加日志
					//todo:发送短信给买家
					$mobile = Db::table('think_merchant')->where('id', $orderinfo['userid'])->value('mobile');
					if (!empty($mobile)) {
						$content = '您发布的求购单有人出售。数量是（' . $num . '）,请尽快打款';
						sendSms($mobile, $content);
					}

					$this->success('下单成功！');
				} else {
					throw new \Think\Exception('下单失败！');
				}
			} catch (\Think\Exception $e) {
				Db::rollback();
				$this->error('下单失败！');
			}
		}
	}

	public function ordersell() {
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['sell_id'] = session('uid');
		$get              = input('get.');
		$order            = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$ordersn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($ordersn)) {
			$where['order_no'] = ['like', '%' . $ordersn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}

		$list = Db::name('order_sell')->where($where)->order('id desc')->paginate(20, FALSE, ['query' => Request::instance()->param()]);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function pay_bak() {
		$id    = input('get.id');
		$appid = input('get.appid');
		$order = Db::name('order_buy')->where('id', $id)->find();
		if (empty($order)) {
			$this->error('订单参数错误1');
		}
		$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();
		if (empty($merchant)) {
			$this->error('订单参数错误2');
		}
		if ($merchant['appid'] != $appid) {
			$this->error('请求路径appid错误');
		}
		$this->assign('remaintime', $order['ltime'] * 60 + $order['ctime'] - time());
		$pay = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method');
		// dump($pay);die;
		$payarr = explode(',', $pay);

		$this->assign('payarr', $payarr);
		$this->assign('id', $id);
		$this->assign('appid', $appid);
		$this->assign('money', $order['deal_amount']);
		$this->assign('amount', $order['deal_num']);
		$this->assign('no', $order['order_no']);
		$merchant = Db::name('merchant')->where('id', $order['sell_id'])->find();
		$bank     = [];
		if ($payarr[0] > 4) {
			$bank                    = Db::name('merchant_bankcard')->where('id', $payarr[0])->find();
			$merchant['c_bank_card'] = $bank['c_bank_card'];
			$merchant['name']        = $bank['truename'];
		}
		$this->assign('merchant', $merchant);
		//平均确认时间
		if (!$merchant['transact']) {
			$min    = 0;
			$second = 0;
		} else {
			$total   = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $order['sell_id'])->where('status', 4)->select();
			$average = intval($total[0]['total'] / $merchant['transact']);
			$min     = intval(floor($average / 60));
			$second  = $average % 60;
		}
		$this->assign('min', $min);
		$this->assign('second', $second);
		if (go_mobile()) {
			return $this->fetch('paymobile');
		} else {
			return $this->fetch('paymobile');
		}
	}

	public function pay() {
		$id    = input('get.id');
		$appid = input('get.appid');
		$order = Db::name('order_buy')->where('id', $id)->find();
		if (empty($order)) {
			$this->error('订单参数错误1');
		}
		$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();
		if (empty($merchant)) {
			$this->error('订单参数错误2');
		}
		if ($merchant['appid'] != $appid) {
			$this->error('请求路径appid错误');
		}
		$this->assign('remaintime', $order['ltime'] * 60 + $order['ctime'] - time());
		$bankid = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method');
		$zfbid  = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method2');
		$wxid   = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method3');
		$ysfid  = Db::name('ad_sell')->where('id', $order['sell_sid'])->value('pay_method4');
		// dump($pay);die;
		// $payarr = explode(',', $bankid);
		// $payarr =array();
		// dump($payarr);die;

		// $this->assign('payarr', $payarr);
		$this->assign('id', $id);
		$this->assign('appid', $appid);
		$this->assign('money', $order['deal_amount']);
		$this->assign('amount', $order['deal_num']);
		$this->assign('no', $order['order_no']);
		$merchant = Db::name('merchant')->where('id', $order['sell_id'])->find();
		$bank     = [];
		$payarr   = [];
		if ($bankid > 0) {
			$bank                    = Db::name('merchant_bankcard')->where('id', $bankid)->find();
			$merchant['c_bank_card'] = $bank['c_bank_card'];
			$merchant['name']        = $bank['truename'];
			$merchant['bank']        = $bank['c_bank'] . $bank['c_bank_detail'];
			$payarr[]                = 'bank';
		}
		if ($zfbid > 0) {
			$zfb                      = Db::name('merchant_zfb')->where('id', $zfbid)->find();
			$merchant['zfb']          = $zfb['c_bank_card'];
			$merchant['name']         = $zfb['truename'];
			$merchant['c_alipay_img'] = $zfb['c_bank_detail'];
			$payarr[]                 .= 'zfb';
		}
		if ($wxid > 0) {
			$wx                       = Db::name('merchant_wx')->where('id', $wxid)->find();
			$merchant['wx']           = $wx['c_bank_card'];
			$merchant['name']         = $wx['truename'];
			$merchant['c_wechat_img'] = $wx['c_bank_detail'];
			$payarr[]                 .= 'wx';
		}
		if ($ysfid > 0) {
			$ysf                   = Db::name('merchant_ysf')->where('id', $ysfid)->find();
			$merchant['ysf']       = $ysf['c_bank_card'];
			$merchant['name']      = $ysf['truename'];
			$merchant['c_ysf_img'] = $ysf['c_bank_detail'];
			$payarr[]              .= 'ysf';
		}
		// dump($payarr);die;
		$this->assign('payarr', $payarr);
		$this->assign('merchant', $merchant);
		//平均确认时间
		if (!$merchant['transact']) {
			$min    = 0;
			$second = 0;
		} else {
			$total   = Db::name('order_buy')->field('sum(finished_time-dktime) as total')->where('sell_id', $order['sell_id'])->where('status', 4)->select();
			$average = intval($total[0]['total'] / $merchant['transact']);
			$min     = intval(floor($average / 60));
			$second  = $average % 60;
		}
		// dump($payarr);
		$this->assign('min', $min);
		$this->assign('second', $second);
		if (go_mobile()) {
			return $this->fetch('paymobile');
		} else {
			return $this->fetch('paymobile');
		}
	}

	public function CheckOutTime() {
		$id    = input('post.id');
		$order = Db::name('order_buy')->where('id', $id)->find();
		if ($order['status'] == 5) {
			$this->success('ok');
			exit;
		}
		if (empty($order)) {
			$this->error('no order');
		}
		$remaintime = $order['ltime'] * 60 + $order['ctime'] - time();
		if ($remaintime < 0) {
			$this->success('ok');
		} else {
			$this->error('no');
		}
	}

	public function uptrade() {
		$id    = input('post.id');
		$appid = input('post.appid');
		$order = Db::name('order_buy')->where('id', $id)->find();
		if (empty($order)) {
			$this->error('订单参数错误1');
		}
		$merchant = Db::name('merchant')->where('id', $order['buy_id'])->find();
		if (empty($merchant)) {
			$this->error('订单参数错误2');
		}
		if ($merchant['appid'] != $appid) {
			$this->error('appid错误');
		}
		if ($order['status'] == 5) {
			$this->error('此订单已取消');
		}
		if ($order['status'] >= 1) {
			$this->error('你已经标记了已付款完成，请勿重复操作');
		}
		$rs = Db::name('order_buy')->where('id', $id)->update(['status' => 1, 'dktime' => time()]);
		if ($rs) {
			$moble = Db::name('merchant')->where('id', $order['sell_id'])->value('mobile');
			if (!empty($moble)) {
				$content = str_replace('{usdt}', $order['deal_num'], config('send_message_content'));
				$content = str_replace('{tx_id}', $id, $content);
				sendSms($moble, $content);
			}
			$this->success($order['return_url']);
		} else {
			$this->error('确认失败，请稍后再试');
		}
	}

	/**
	 * 承兑商标记付款
	 */
	public function uptradeinner() {
		if (!session('uid')) {
			$this->error('请登录操作');
		}
		$id    = input('post.id');
		$order = Db::name('order_sell')->where('id', $id)->find();
		if (empty($order)) {
			$this->error('订单参数错误1');
		}
		if ($order['buy_id'] != session('uid')) {
			$this->error('不是您的买单');
		}
		if ($order['status'] == 5) {
			$this->error('此订单已取消');
		}
		if ($order['status'] >= 1) {
			$this->error('你已经标记了已付款完成，请勿重复操作');
		}
		$rs = Db::name('order_sell')->where('id', $id)->update(['status' => 1, 'dktime' => time()]);
		if ($rs) {
			//todo:是否发送短信给商家即卖家
			//$moble = Db::name('merchant')->where('id', $order['sell_id'])->value('mobile');
			//if(!empty($moble)){
			//    $content = str_replace('{usdt}',$order['deal_num'],config('send_message_content'));
			//    sendSms($moble, $content);
			//}
			$this->success('标记成功');
		} else {
			$this->error('确认失败，请稍后再试');
		}
	}

	public function pkorder() {
		if (!session('uid')) {
			$this->error('请登录操作', url('home/login/login'));
		}
		$model2          = new OrderModel();
		$where['buy_id'] = session('uid');
		$get             = input('get.');
		$order           = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$ordersn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($ordersn)) {
			$where['order_no'] = ['like', '%' . $ordersn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}

		$list = $model2->getOrder($where, 'id desc');
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function orderlist() {
		if (!session('uid')) {
			$this->error('请登录操作', url('home/login/login'));
		}
		$get   = input('get.');
		$order = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$ordersn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($ordersn)) {
			$where['order_no'] = ['like', '%' . $ordersn . '%'];
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
		$where['sell_id'] = session('uid');
		$list             = $model2->getOrder($where, 'id desc');
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function orderlistbuy() {
		if (!session('uid')) {
			$this->error('请登录操作', url('home/login/login'));
		}
		$where['buy_id'] = session('uid');

		$get   = input('get.');
		$order = 'id desc';
		if (isset($_GET['order'])) {
			$order = 'id ' . $_GET['order'];
		}
		$ordersn = input('get.ordersn');
		$status  = input('get.status');
		if (!empty($ordersn)) {
			$where['order_no'] = ['like', '%' . $ordersn . '%'];
		}
		if (isset($status) && $status > 0) {
			$where['status'] = $status;
		}
		if (!empty($get['created_at']['start']) && !empty($get['created_at']['end'])) {
			$start          = strtotime($get['created_at']['start']);
			$end            = strtotime($get['created_at']['end']);
			$where['ctime'] = ['between', [$start, $end]];
		}
		$list = Db::name('order_sell')->where($where)->order('id desc')->paginate(20, FALSE, ['query' => Request::instance()->param()]);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function sfbtc_ajax() {//放行usdt
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登录操作');
			}
			$id               = input('post.id');
			$model            = new OrderModel();
			$model2           = new MerchantModel();
			$where['id']      = $id;
			$where['sell_id'] = session('uid');
			$orderinfo        = $model->getOne($where);
			if (empty($orderinfo)) {
				$this->error('订单不存在');
			}
			if ($orderinfo['status'] == 5) {
				$this->error('订单已经被取消');
			}
			if ($orderinfo['status'] == 6) {
				$this->error('订单申诉中，无法释放');
			}
			if ($orderinfo['status'] == 0) {//20190830修改,不打款,也可以确认
				$nopay = 1;//20190830修改
				// $this->error('此订单对方已经拍下还未付款');
			} else {
				$nopay = 0;
			}
			if ($orderinfo['status'] >= 3) {
				$this->error('此订单已经释放无需再次释放');
			}
			$merchant    = $model2->getUserByParam(session('uid'), 'id');
			$buymerchant = $model2->getUserByParam($orderinfo['buy_id'], 'id');
			if ($merchant['usdtd'] < $orderinfo['deal_num']) {
				$this->error('您的冻结不足，释放失败');
			}

			$sfee = 0;
			$mum  = $orderinfo['deal_num'] - $sfee;
			//盘口费率
			$pkfee = $buymerchant['merchant_pk_fee'];
			$pkfee = $pkfee ? $pkfee : 0;
			$pkdec = $orderinfo['deal_num'] * $pkfee / 100;
			//平台利润
			$platformGet   = config('trader_platform_get');
			$platformGet   = $platformGet ? $platformGet : 0;
			$platformMoney = $platformGet * $orderinfo['deal_num'] / 100;
			//承兑商卖单奖励
			$traderGet         = $merchant['trader_trader_get'];
			$traderGet         = $traderGet ? $traderGet : 0;
			$traderMoney       = $traderGet * $orderinfo['deal_num'] / 100;
			$traderParentMoney = $traderMParentMoney = $tpexist = $mpexist = 0;
			if ($merchant['pid']) {
				$traderP = $model2->getUserByParam($merchant['pid'], 'id');
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
				$rs1 = Db::table('think_merchant')->where('id', $orderinfo['sell_id'])->setDec('usdtd', $orderinfo['deal_num']);
				//20190830修改
				if ($nopay == 1) {
					$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'dktime' => time(), 'platform_fee' => $moneyArr[0]]);
				} else {
					$rs2 = Db::table('think_order_buy')->update(['id' => $orderinfo['id'], 'status' => 4, 'finished_time' => time(), 'platform_fee' => $moneyArr[0]]);
				}
				// $rs2 = Db::table('think_order_buy')->update(['id'=>$orderinfo['id'], 'status'=>4, 'finished_time'=>time(), 'platform_fee'=>$moneyArr[0]]);
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
					$rsarr = agentReward($merchant['pid'], $orderinfo['sell_id'], $traderParentMoney, 3);//3
					$rs8   = $rsarr[0];
					$rs9   = $rsarr[1];
				}
				//商户代理利润
				if ($traderMParentMoney > 0 && $mpexist) {
					$rsarr = agentReward($buymerchant['pid'], $orderinfo['buy_id'], $traderMParentMoney, 4);//4
					$rs10  = $rsarr[0];
					$rs11  = $rsarr[1];
				}
				if ($rs1 && $rs2 && $rs3 && $rs4 && $rs6 && $rs7 && $rs8 && $rs9 && $rs10 && $rs11) {
					// 提交事务
					Db::commit();
					financelog($orderinfo['buy_id'], $mum, '买入USDT_f1', 0, session('user.name'));//添加日志
					if ($traderMoney > 0) {
						financelog($orderinfo['sell_id'], $traderMoney, '承兑商卖单奖励_f1', 0, session('user.name'));//添加日志
					}

					getStatisticsOfOrder($orderinfo['buy_id'], $orderinfo['sell_id'], $mum, $orderinfo['deal_num']);
					//请求回调接口
					$data['amount']  = $orderinfo['deal_num'];
					$data['rmb']     = $orderinfo['deal_amount'];
					$data['orderid'] = $orderinfo['orderid'];
					$data['appid']   = $buymerchant['appid'];
					$data['status']  = 1;
					askNotify($data, $orderinfo['notify_url'], $buymerchant['key']);
					$this->success('释放成功');
				} else {
					// 回滚事务
					Db::rollback();
					$this->error('释放失败,请稍后再试!');
				}
			} catch (\think\Exception\DbException $e) {
				// 回滚事务
				Db::rollback();
				$this->error('释放失败，参考信息：' . $e->getMessage());
			}
		}
	}

	/**
	 * 商家释放
	 */
	public function sfbtc_ajax_merchant() {
		if (request()->isPost()) {
			if (!session('uid')) {
				$this->error('请登录操作');
			}

			$id = input('post.id');
			// dump($id);
			$model2           = new MerchantModel();
			$where['id']      = $id;
			$where['sell_id'] = session('uid');
			$orderinfo        = Db::name('order_sell')->where($where)->find();
			if (empty($orderinfo)) {
				$this->error('订单不存在');
			}
			if ($orderinfo['status'] == 5) {
				$this->error('订单已经被取消');
			}
			if ($orderinfo['status'] == 6) {
				$this->error('订单申诉中，无法释放');
			}
			if ($orderinfo['status'] == 0) {
				$this->error('此订单对方已经拍下还未付款');
			}
			if ($orderinfo['status'] >= 3) {
				$this->error('此订单已经释放无需再次释放');
			}
			$merchant    = $model2->getUserByParam(session('uid'), 'id');
			$buymerchant = $model2->getUserByParam($orderinfo['buy_id'], 'id');
			if ($merchant['usdtd'] < $orderinfo['deal_num'] + $orderinfo['fee']) {
				$this->error('您的冻结不足，释放失败');
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
				if ($rs1 && $rs2 && $rs3 && $rs4) {
					// 提交事务
					Db::commit();
					financelog($orderinfo['buy_id'], $mum, '买入USDT_f2', 0, session('user.name'));//添加日志
					getStatisticsOfOrder($orderinfo['buy_id'], $orderinfo['sell_id'], $mum, $orderinfo['deal_num'] + $orderinfo['fee'], session('user.name'));
					$this->success('释放成功');
				} else {
					// 回滚事务
					Db::rollback();
					$this->error('释放失败');
				}
			} catch (\think\Exception\DbException $e) {
				// 回滚事务
				Db::rollback();
				$this->error('释放失败，参考信息：' . $e->getMessage());
			}
		}
	}

	public function shensu_ajax() {
		if (request()->isPost()) {
			$content = input('post.content');
			$id      = input('post.id');
			if (!session('uid')) {
				$this->error('请登录操作');
			}
			$model            = new OrderModel();
			$where['id']      = $id;
			$where['sell_id'] = session('uid');
			$orderinfo        = $model->getOne($where);
			if (!$orderinfo) {
				$this->error('订单不存在');
			}
			if ($orderinfo['status'] == 5) {
				$this->error('该订单已经被取消');
			}
			if ($orderinfo['status'] == 0) {
				$this->error('该订单已经被拍下，还未付款,不能申诉');
			}
			if ($orderinfo['status'] == 6) {
				$this->error('该订单已经处于申诉状态，请耐心等待');
			}
			if ($orderinfo['status'] == 4 || $orderinfo['status'] == 3) {
				$this->error('该订单已经完成，无法申诉');
			}
			$rs = $model->updateOne(['id' => $id, 'status' => 6, 'su_reason' => $content]);
			if ($rs['code'] == 1) {
				$this->success('申诉成功');
			} else {
				$this->error($rs['msg']);
			}
		}
	}

	public function traderreward() {
		$order = 'a.id desc';
		if (isset($_GET['order'])) {
			$order = 'a.id ' . $_GET['order'];
		}
		$model = new MerchantModel();
		if (!session('uid')) {
			$this->error('请登陆操作', url('home/login/login'));
		}
		$where['uid'] = session('uid');
		$this->assign('list', $model->getTraderReward($where, $order));
		return $this->fetch();
	}

	/**
	 * 承兑商买单申诉
	 */
	public function shensu_ajax_trader() {
		if (request()->isPost()) {
			$content = input('post.content');
			$id      = input('post.id');
			if (!session('uid')) {
				$this->error('请登录操作');
			}
			$where['id']     = $id;
			$where['buy_id'] = session('uid');
			$orderinfo       = Db::name('order_sell')->where($where)->find();
			if (!$orderinfo) {
				$this->error('订单不存在');
			}
			if ($orderinfo['status'] == 5) {
				$this->error('该订单已经被取消');
			}
			if ($orderinfo['status'] == 0) {
				$this->error('该订单已经被拍下，还未付款,不能申诉');
			}
			if ($orderinfo['status'] == 6) {
				$this->error('该订单已经处于申诉状态，请耐心等待');
			}
			if ($orderinfo['status'] == 4 || $orderinfo['status'] == 3) {
				$this->error('该订单已经完成，无法申诉');
			}
			$rs = Db::name('order_sell')->where('id', $id)->update(['status' => 6, 'su_reason' => $content]);
			if ($rs['code'] == 1) {
				$this->success('申诉成功');
			} else {
				$this->error('申诉失败，请稍后再试');
			}
		}
	}

	/**
	 * 商户申诉
	 */
	public function shensu_ajax_merchant() {
		if (request()->isPost()) {
			$content = input('post.content');
			$id      = input('post.id');
			if (!session('uid')) {
				$this->error('请登录操作');
			}
			$where['id']      = $id;
			$where['sell_id'] = session('uid');
			$orderinfo        = Db::name('order_sell')->where($where)->find();
			if (!$orderinfo) {
				$this->error('订单不存在');
			}
			if ($orderinfo['status'] == 5) {
				$this->error('该订单已经被取消');
			}
			if ($orderinfo['status'] == 0) {
				$this->error('该订单已经被拍下，还未付款,不能申诉');
			}
			if ($orderinfo['status'] == 6) {
				$this->error('该订单已经处于申诉状态，请耐心等待');
			}
			if ($orderinfo['status'] == 4 || $orderinfo['status'] == 3) {
				$this->error('该订单已经完成，无法申诉');
			}
			$rs = Db::name('order_sell')->where('id', $id)->update(['status' => 6, 'su_reason' => $content]);
			if ($rs['code'] == 1) {
				$this->success('申诉成功');
			} else {
				$this->error('申诉失败，请稍后再试');
			}
		}
	}

	public function log() {
		$model             = new MerchantModel();
		$where['admin_id'] = session('uid');
		$log               = $model->getLoginLog($where, 'log_id desc');
		$this->assign('log', $log);
		return $this->fetch();
	}

	public function testtest_____() {
		//echo config('mobile_user').'1';die;
		$order['deal_num'] = 80;
		$content           = str_replace('{usdt}', $order['deal_num'], config('send_message_content'));
		echo $content;
		$data['amount']  = 5;
		$where['state']  = 1;
		$where['amount'] = ['egt', $data['amount']];
		//$where['usdt'] = ['egt', $data['amount']];
		$join = [
			['__ORDER_BUY__ b', 'b.sell_sid=a.id', 'LEFT'],
			['__MERCHANT__ c', 'a.userid=c.id', 'LEFT'],
		];
		$ads  = Db::name('ad_sell')->field('a.*, sum(b.deal_num) as total, c.id as traderid,c.mobile')->alias('a')->join($join)->group('a.id')->where($where)->order('price asc, id asc')->select();
		dump($ads);
		$onlinead = [];
		if (empty($onlinead)) {
			echo 1;
		}
	}

	private function getadvno() {
		$code = '';
		for ($i = 1; $i <= 5; $i++) {
			$code .= chr(rand(97, 122));
		}
		$adv_no  = $code . time();
		$advsell = Db::name('ad_sell')->where(['ad_no' => $adv_no])->find();
		if (!empty($advsell)) {
			$this->getadvno();
		} else {
			return $adv_no;
		}
	}
}

?>