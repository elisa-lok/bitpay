<?php
namespace app\home\controller;
use app\home\model\BankModel;
use app\home\model\WxModel;
use app\home\model\YsfModel;
use app\home\model\ZfbModel;
use com\GoogleAuthenticator;
use think\Db;

class Payment extends Base {
	public function index() {
		!$this->uid && $this->error('请登陆操作', url('home/login/login'));
		$user       = Db::name('merchant')->where(['id' => $this->uid])->find();
		$alipayData = (new ZfbModel())->getBank(['merchant_id' => $this->uid, 'state' => 1], 'id DESC');
		foreach ($alipayData as $k => $v) {
			$alipayData[$k]->qrcode = StrToMicroTime($v->qrcode, true);
		}
		$this->assign('user', $user);
		$this->assign('list', (new BankModel())->getBank(['merchant_id' => $this->uid, 'state' => 1], 'id DESC'));
		$this->assign('list2', $alipayData);
		$this->assign('list3', (new WxModel())->getBank(['merchant_id' => $this->uid, 'state' => 1], 'id DESC'));
		$this->assign('list4', (new YsfModel())->getBank(['merchant_id' => $this->uid, 'state' => 1], 'id DESC'));
		$ga = explode('|', $user['ga']);
		$this->assign('ga', ($ga['4'] ?? 0));
		return $this->fetch();
	}

	public function add($type) {
		!$this->uid && showMsg('请登陆操作');
		$data = json_decode(file_get_contents('php://input'), TRUE);
		!$data && showMsg('参数错误');
		(strlen($data['truename']) < 2 || strlen($data['truename']) > 6) && showMsg('真实姓名有误');
		(strlen($data['tag']) < 1 || strlen($data['truename']) > 20) && showMsg('账户标识不能为空');
		$user = Db::name('merchant')->where('id', $this->uid)->find();
		$ga   = explode('|', $user['ga']);
		if (isset($ga[4]) && $ga[4]) {
			$code = input('post.ga');
			!$code && showMsg('请输入谷歌验证码');
			$google = new GoogleAuthenticator();
			!$google->verifyCode($ga['0'], $code, 1) && showMsg('谷歌验证码错误!');
		}
		$type == 'bank' && $this->setBank($data);
		$type == 'alipay' && $this->setAlipay($data);
		$type == 'wx' && $this->setWx($data);
		$type == 'union' && $this->setUnion($data);
	}

	private function setBank($data) {
		$res = (new BankModel())->insertOne([
			'merchant_id'   => $this->uid,
			'c_bank_detail' => trim($data['bank_branch']),
			'c_bank'        => trim($data['bank_name']),
			'truename'      => trim($data['truename']),
			'name'          => trim($data['tag']),
			'c_bank_card'   => trim($data['bank_card']),
			'create_time'   => time(),
			'update_time'   => time(),
		]);
		showMsg($res['msg'], $res['code']);
	}

	private function setAlipay($data) {
		!$data['qrcode'] && showMsg('二维码上传错误');
		$res = (new ZfbModel())->insertOne([
			'merchant_id'   => $this->uid,
			'c_bank_detail' => '', //上传的图片
			'c_bank'        => trim($data['account']),
			'truename'      => trim($data['truename']),
			'name'          => trim($data['tag']),
			'c_bank_card'   => trim($data['alipay_id']),
			'create_time'   => time(),
			'update_time'   => time(),
			'qrcode'        => trim(MicroTimeToStr($data['qrcode'])),// TODO 替换掉微秒, 使用str_replace 后面替换成对应时间
		]);
		showMsg($res['msg'], $res['code']);
	}

	private function setWx($data) {
		!$data['qrcode'] && showMsg('二维码上传错误');
		$res = (new WxModel())->insertOne([
			'merchant_id'   => $this->uid,
			'c_bank_detail' => '',
			'c_bank'        => trim($data['account']),
			'truename'      => trim($data['truename']),
			'name'          => trim($data['tag']),
			'c_bank_card'   => '',
			'qrcode'        => trim($data['qrcode']),
		]);
		showMsg($res['msg'], $res['code']);
	}

	private function setUnion($data) {
		!$data['qrcode'] && showMsg('二维码上传错误');
		$res = (new YsfModel())->insertOne([
			'merchant_id'   => $this->uid,
			'c_bank_detail' => '',
			'c_bank'        => trim($data['account']),
			'truename'      => trim($data['truename']),
			'name'          => trim($data['tag']),
			'c_bank_card'   => '',
			'create_time'   => time(),
			'qrcode'        => trim($data['qrcode']),
		]);
		showMsg($res['msg'], $res['code']);
	}

	// 软删除支付方式
	public function del($type, $id) {
		!$this->uid && showMsg('请登陆操作');
		$typeArr = ['bank' => 'bankcard', 'alipay' => 'zfb', 'wx' => 'wx', 'union' => 'ysf'];
		!isset($typeArr[$type]) && showMsg('类型不支持');
		$res = Db::name('merchant_' . $typeArr[$type])->where(['id' => (int)$id, 'merchant_id' => $this->uid])->update(['state' => '0']);
		$res === 1 ? showMsg('删除成功', 1) : showMsg('删除失败');
	}

	// 将图片二维码读取并删除原二维码
	public function readAll() {
		ignore_user_abort(TRUE);
		set_time_limit(0);
		$arr = ['zfb', 'wx', 'ysf'];
		foreach ($arr as $val) {
			$model = Db::name('merchant_' . $val);
			$data  = $model->select();
			$url   = 'http://127.0.0.1:8080/parse?del=&file=';
			foreach ($data as $v) {
				if (!$v['c_bank_detail']) continue;
				$file = realpath(UPLOAD_PATH . '/uploads/face/' . $v['c_bank_detail']);
				if (!$file) continue;
				$res = curl_get($url . $file);
				if (!$res) continue; // 解析不了跳过
				$res = json_decode($res, TRUE);
				$model->where('id', $v['id'])->update(['qrcode' => MicroTimeToStr($res['data']['img_content'])]);
			}
		}
	}
}

?>