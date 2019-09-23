<?php

namespace BITPAY;

class Base {
	//结算状态
	const SettleState = ['未结算', '已结算', '不结算'];
	//充值订单状态
	const OrderState = ['处理中', '未结算', '已结算', '失败'];
	//代付订单状态
	const OrderRemitState = ['处理中', '成功', '失败'];
	//异步通知状态
	const NotifyState = ['未通知', '已通知', '通知失败'];
	//冻结
	const FrozenState = ['否', '是', '未处理'];
	//资金记录类型
	const fundRecordType = ['充值', '代付', '提现', '冻结', '解冻', '保证金冻结', '保证金解冻'];
	//结算周期
	const SettleCycle = ['D0', 'D1', 'T0', 'T1', 'T2', 'T7'];
	//blocked
	const IpBlockedKey      = 'ip_blocked_';
	const IpBlockedExpire   = 900;
	const MailBlcokedExpire = 1800;
	const MailBlockedTimes  = 15; //
	//图形验证码
	const PicCaptchaKey    = 'pic_captcha';
	const PicCaptchaExpire = 60;
	//邮箱验证码
	const MailCaptchaPre    = 'mail_captcha_';
	const MailCaptchaExpire = 1800;
	//短信验证码
	const SmsCaptchaPre    = 'sms_captcha_';
	const SmsCaptchaExpire = 180;
	// 日志类型
	const LogActionTypeSignIn      = 0;
	const LogActionTypeModPassword = 1;
	// TODO 网关名, 下面为举例
	const ChanName           = ['CHANPAY_QUICK' => '畅捷支付_快捷', 'LLPAY_EBANK' => '连连支付_网银', 'SANDPAY_ALI' => '杉德支付_支付宝', 'YSEPAY_QUICK' => '银盛支付_快捷'];
	const ChanProdName       = [1 => '代付', 2 => '网银网关', 3 => '网银快捷', 4 => '银联H5', 5 => '银联扫码', 6 => '支付宝H5', 7 => '支付宝扫码', 8 => '微信H5', 9 => '微信扫码',];
	const SupportBank        = ['ICBC', 'CCB', 'BOC', 'ABC', 'BCM', 'CMB', 'CITIC', 'CMBC', 'CIB', 'SPDB', 'PSBC', 'CEB', 'PAB', 'HXB', 'BOB', 'GDB'];
	const SupportServiceType = ['ebank', 'quick', 'unionpay_qrcode', 'alipay_qrcode', 'alipay_wap', 'wechat_qrcode'];
	const SigType            = [0 => 'MD5', 1 => 'RSA', 2 => 'AES']; // 签名类型
	// 通道
	const CHAN_CODE = []; //支持的通道代码
}


