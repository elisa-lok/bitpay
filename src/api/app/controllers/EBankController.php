<?php

namespace BITPAY\Api\Controllers;

// 网银支付

use BITPAY\Api\Common\Base;
use BITPAY\Api\Common\Msg;

class EBankController extends ControllerBase {
	public function create() {
		// 过滤并校验数据

		// 判断请求IP是否合法

		// 1. 获取用户数据

		// 2. 签名检验

		// 3. 判断成功单数, IP绑定情况

		// 校验时间

		// 计算结算时间

		// 4. 判断用户黑名单

		// 5. 生成订单

		// 6. 商户订单号是否已存在,如果订单已经存在,而且未进行支付则继续进行

		(int)$this->param['uid'] < 1 && $this->api(Msg::ErrInvalidAcc);
		$param['amt']       = (int)$this->param['uid'] < 1000;
		$param['uid']       = (int)$this->param['uid'] < 1;
		$param['tx_out_no'] = (int)$this->param['uid'];

		$data = [
			'acc_id'     => 0,
			'tx_out_no'  => '',
			'amt'        => '',
			'bank_code'  => '',
			'return_url' => '',
			'notify_url' => '',
			'timestamp'  => '',
			'ip'         => '',
			'sig_type'   => '',
			'sig'        => '',
		];

		$args = [
			'acc_id'       => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1]],
			'tx_out_no'    => [],
			'amt'          => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1000]],
			'doesnotexist' => FILTER_VALIDATE_INT,
			'testscalar'   => [
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_REQUIRE_SCALAR,
			],
			'testarray'    => [
				'filter' => FILTER_VALIDATE_INT,
				'flags'  => FILTER_FORCE_ARRAY,
			]
		];

		$myinputs = filter_var_array($data, $args);

		var_dump($myinputs);

		// $params =
	}

	// 校验网银网管的参数
	private function checkEBankParams() {
		$res = [];

		// 校验tx_out_no合法性

		// 检查bank code
		!in_array($this->params, Base::PAY_CODE_BANK) && $this->api(Msg::ErrInvalidBankCode);
		// 校验签名时间
		(($this->params['timestamp'] < (TIMESTAMP - 120)) || ($this->params['timestamp'] > (TIMESTAMP + 120))) && $this->api(Msg::ErrTimeoutSig);
		// 校验回调地址是否正确
		(filter_var($this->params['return_url'], FILTER_VALIDATE_URL) === false || filter_var($this->params['notify_url'], FILTER_VALIDATE_URL) === false ) && $this->api(Msg::ErrInvalidURL);
		// 校验订单金额是否正确
		filter_var($this->params['amt'], FILTER_VALIDATE_INT) === FALSE && $this->api(Msg::ErrInvalidAmt);
		// 校验IP正确性
		filter_var($this->params['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE && $this->api(Msg::ErrInvalidIP);
		return;
	}
}


