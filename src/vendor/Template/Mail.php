<?php

namespace BITPAY\Template;

class Mail {
	//注册登录类模板
	//todo 注册登录密码

	const Captcha              = ['subject' => '验证码', 'body' => '您的验证码是: <b>%s</b> , 请在15分钟内输入.'];
	const SignUpCaptchaTemp    = ['subject' => '注册验证码', 'body' => '您正在注册JPAY即付, 您的注册验证码是: <b>%s</b> , 请在15分钟内输入.'];
	const SignUpSucc           = ['subject' => '注册成功', 'body' => ''];
	const SignUpApproved       = ['subject' => '审核通过', 'body' => ''];
	const SignInNotify         = ['subject' => '登录通知', 'body' => '您的账户(%s)于%s在IP: %s(%s) 上登录成功, 如非你本人操作, 请及时更改密码'];
	const AccResetCaptcha      = ['subject' => '重置密码', 'body' => '您的账户(%s)于%s在IP: %s(%s) 申请重置密码,此次操作验证码是: <b>%s</b> ,请在15分钟内输入'];
	const AccResetSucc         = ['subject' => '重置密码成功', 'body' => '您的账户(%s)于%s在IP: %s(%s) 上重置密码成功,如非你本人操作, 请马上联系客服处理'];
	const AccAndIpBanned       = ['subject' => '账户锁定通知', 'body' => '您的账户(%s)于%s在IP: %s(%s) 上输入错误密码次数过多, 账户及IP将锁定30分钟,如非你本人操作, 请及时更改密码'];
	const AccPasswordIncorrect = ['subject' => '登入密码输入错误', 'body' => '您的账户(%s)于%s在IP: %s(%s) 上尝试登录,但密码输入错误,如非你本人操作, 请及时更改密码'];
	const AccPasswordFrozen    = ['subject' => '密码输入次数过多,账户锁定', 'body' => '您的账户(%s) 输入错误密码次数过多,为了保证你的资金安全,账户及IP将锁定30分钟,如非你本人操作, 请及时修改密码'];
	//余额变动类模板
	const TxWithdraw = [];  //提款
	const TxRemit    = []; //代付
	const TxFrozen   = ['subject' => '订单冻结', 'body' => '你的订单于%s接到客户投诉, 现平台将冻结该订单资金, 待该订单解冻后, 该订单资金将返回你余额'];
	//操作类模板
	const PasswordResetRequire = ['subject' => '您现在正在申请重置', 'body' => '']; //密码重置请求
	const PasswordResetInfo    = ['subject' => '', 'body' => '']; //密码重置通知
	//报表类模板
	const ReportLastWeek  = [];
	const ReportLastMonth = [];
}