<?php

namespace BITPAY;

class Msg {
	const ErrCustomCode = 40000;
	// 200 OK
	const Suc = ['statusCode' => 200, 'code' => 0, 'msg' => 'success'];
	//400 BadRequest
	const ErrFailure              = ['statusCode' => 400, 'code' => 40000, 'msg' => '操作失败'];
	const ErrInvalidArgument      = ['statusCode' => 400, 'code' => 40000, 'msg' => '无效参数'];
	const ErrInvalidPassword      = ['statusCode' => 400, 'code' => 40020, 'msg' => '无效密码'];
	const ErrCaptchaSend          = ['statusCode' => 400, 'code' => 40020, 'msg' => '发送验证码失败'];
	const ErrResetPwd             = ['statusCode' => 400, 'code' => 40020, 'msg' => '密码无效，请重置密码'];
	const ErrInvalidEmail         = ['statusCode' => 400, 'code' => 40030, 'msg' => '无效Email'];
	const ErrInvalidAddress       = ['statusCode' => 400, 'code' => 40030, 'msg' => '无效地址'];
	const ErrInvalidEmailAndPhone = ['statusCode' => 400, 'code' => 40030, 'msg' => '无效的电子邮件或电话号码'];
	const ErrInvalidPhone         = ['statusCode' => 400, 'code' => 40030, 'msg' => '无效的电话号码'];
	const ErrInvalidCurrency      = ['statusCode' => 400, 'code' => 40040, 'msg' => '无效的货币'];
	const ErrInvalidAmt        = ['statusCode' => 400, 'code' => 40050, 'msg' => '交易数量必须大于1'];
	const ErrCreateTx             = ['statusCode' => 400, 'code' => 40070, 'msg' => '创建订单失败'];
	const ErrCaptchaInvalid       = ['statusCode' => 400, 'code' => 40080, 'msg' => '无效校验码'];
	const ErrAccState             = ['statusCode' => 400, 'code' => 40080, 'msg' => '账户异常'];
	const ErrAccFrozen            = ['statusCode' => 400, 'code' => 40080, 'msg' => '账户已停用'];
	const ErrLogBackIn            = ['statusCode' => 400, 'code' => 40080, 'msg' => '重新登录'];
	//401 Unauthorized
	const ErrAuth          = ['statusCode' => 401, 'code' => 40100, 'msg' => '未授权'];
	const ErrAccAuth       = ['statusCode' => 401, 'code' => 40100, 'msg' => '用户名或密码错误'];
	const ErrSignature     = ['statusCode' => 401, 'code' => 40100, 'msg' => '签名错误'];
	const ErrSignatureType = ['statusCode' => 401, 'code' => 40100, 'msg' => '签名类型错误'];
	const ErrPayUnverified = ['statusCode' => 401, 'code' => 40110, 'msg' => '请填写支付方式'];
	const ErrOrderAuth     = ['statusCode' => 401, 'code' => 40110, 'msg' => '您无权操作此订单'];
	const ErrSecPwdNoSet   = ['statusCode' => 401, 'code' => 40110, 'msg' => '安全密码未设置'];
	const ErrSecPwd        = ['statusCode' => 401, 'code' => 40110, 'msg' => '安全密码错误，请检查'];
	const ErrIPChange        = ['statusCode' => 401, 'code' => 40110, 'msg' => 'IP已改变,请重新登陆'];
	const ErrOpTimeout     = ['statusCode' => 401, 'code' => 40110, 'msg' => '操作超时'];
	//402 Payment Required
	const ErrPayment             = ['statusCode' => 402, 'code' => 40200, 'msg' => '付款失败'];
	const ErrInsufficientBalance = ['statusCode' => 402, 'code' => 402010, 'msg' => '余额不足'];
	//403 Forbidden
	const ErrPermissionDenied     = ['statusCode' => 403, 'code' => 40300, 'msg' => '没有权限'];
	const ErrOTPAuth              = ['statusCode' => 403, 'code' => 40100, 'msg' => '令牌校验失败'];
	const ErrIPExtent             = ['statusCode' => 403, 'code' => 40100, 'msg' => 'IP不允许进行该操作'];
	const ErrInviterNotExist      = ['statusCode' => 403, 'code' => 40300, 'msg' => '邀请者不存在'];
	const ErrInviterAbnormal      = ['statusCode' => 403, 'code' => 40300, 'msg' => '邀请者状态异常'];
	const ErrRequestsTooFrequency = ['statusCode' => 403, 'code' => 40300, 'msg' => '请求频繁'];
	const ErrOverDailyLimit       = ['statusCode' => 403, 'code' => 40300, 'msg' => '超越每日最大交易限额'];
	const ErrUserUnavailable      = ['statusCode' => 403, 'code' => 40301, 'msg' => '账户未激活, 请联系客服处理'];
	const ErrUserFrozen           = ['statusCode' => 403, 'code' => 40301, 'msg' => '账户冻结, 请联系客服处理'];
	//404 Not Found
	const ErrNotFound         = ['statusCode' => 404, 'code' => 40400, 'msg' => 'Page Not Found'];
	const ErrUserNotFound     = ['statusCode' => 404, 'code' => 40410, 'msg' => '用户不存在'];
	const ErrTxNotFound       = ['statusCode' => 404, 'code' => 40410, 'msg' => '订单不存在'];
	const ErrBankCardNotFound = ['statusCode' => 404, 'code' => 40410, 'msg' => '银行卡不存在'];
	//405 Method Not Allowed
	const ErrMethodNotAllowed = ['statusCode' => 404, 'code' => 40400, 'msg' => 'Method Not Allowed'];
	//406 Not Acceptable
	const ErrPassword              = ['statusCode' => 406, 'code' => 40600, 'msg' => '密码错误'];
	const ErrParameterCannotBeNull = ['statusCode' => 406, 'code' => 40601, 'msg' => '参数不能为空'];
	//409 Conflict
	const ErrExistEmail    = ['statusCode' => 409, 'code' => 40900, 'msg' => '电子邮箱已经存在'];
	const ErrExistUserName = ['statusCode' => 409, 'code' => 40900, 'msg' => '用户名已存在'];
	const ErrExistPhone    = ['statusCode' => 409, 'code' => 40900, 'msg' => '电话号码已经存在'];
	const ErrExistIDCard   = ['statusCode' => 409, 'code' => 40900, 'msg' => '身份证已经存在'];
	const ErrExistChanCode = ['statusCode' => 409, 'code' => 40900, 'msg' => '通道代号已存在'];
	//500
	const ErrServer  = ['statusCode' => 500, 'code' => 50000, 'msg' => '内部服务器错误'];
	const ErrUnknown = ['statusCode' => 500, 'code' => 50001, 'msg' => '未知错误'];

	public static function NewError(int $code, string $msg) {
		return ['statusCode' => (int)substr($code, 0, 3), 'code' => $code, 'msg' => $msg];
	}
}

