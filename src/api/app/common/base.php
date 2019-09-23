<?php

namespace BITPAY\Api\Common;
class Base {
	const IP_OFF_AMT_LIMIT   = 20;  // 关闭IP限制状态下每笔限制额度
	const IP_OFF_COUNT_LIMIT = 50;  // 关闭IP限制状态下每天次数限制
	// 账户状态
	const AccStateNormal = 1;
	const AccStateFrozen = 2;
	// 账户类型
	const AccTypeMerchant = 0; // 商户
	const AccTypeAgent    = 1; // 代理

	//
	const PAY_CODE_BANK = ['ICBC', 'CCB', 'BOC', 'ABC', 'BCM', 'CMB', 'CITIC', 'CMBC', 'CIB', 'SPDB', 'PSBC', 'CEB', 'PAB', 'HXB', 'BOB', 'CGB']; // 银行类型
	const PAY_CODE_SCAN = ['ALIPAY', 'UNIONPAY', 'WBITPAY']; // 扫码类型
}