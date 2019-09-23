<?php

namespace BITPAY\Api\Common;

class Msg {
	const ErrCustomCode = 40000;
	// 200 OK
	const Suc = ['statusCode' => 200, 'code' => 0, 'msg' => 'succ'];
	//400 BadRequest
	const ErrFailure    = ['statusCode' => 400, 'code' => 40000, 'msg' => 'fail'];
	const ErrCreateTx   = ['statusCode' => 400, 'code' => 40070, 'msg' => 'create tx failed'];
	const ErrTimeoutOp  = ['statusCode' => 401, 'code' => 40110, 'msg' => 'operation timeout'];
	const ErrTimeoutSig = ['statusCode' => 401, 'code' => 40110, 'msg' => 'signature expired'];
	//401 Unauthorized
	const ErrUnauthorized = ['statusCode' => 401, 'code' => 40100, 'msg' => 'Unauthorized'];
	//402 Payment Required
	const ErrPaymentISF          = ['statusCode' => 402, 'code' => 40201, 'msg' => 'insufficient funds'];
	const ErrQuotaLimit          = ['statusCode' => 402, 'code' => 40300, 'msg' => 'over quota limit'];
	const ErrPayChanNotSupport   = ['statusCode' => 402, 'code' => 40110, 'msg' => 'pay channel not support'];
	const ErrPayMethodNotSupport = ['statusCode' => 402, 'code' => 40110, 'msg' => 'pay method not support'];
	//403 Forbidden
	const ErrAuthSig              = ['statusCode' => 401, 'code' => 40100, 'msg' => 'signature error'];
	const ErrAuthSigType          = ['statusCode' => 401, 'code' => 40100, 'msg' => 'signature type error'];
	const ErrPermDenied           = ['statusCode' => 403, 'code' => 40300, 'msg' => 'permission denied'];
	const ErrRequestsTooFrequency = ['statusCode' => 403, 'code' => 40300, 'msg' => 'requests frequency'];
	const ErrAccUnavailable       = ['statusCode' => 403, 'code' => 40301, 'msg' => 'account unavailable'];
	//404 Not Found
	const ErrNotFound    = ['statusCode' => 404, 'code' => 40400, 'msg' => 'not found'];
	const ErrNotFoundAcc = ['statusCode' => 404, 'code' => 40410, 'msg' => 'account not found'];
	const ErrNotFoundTx  = ['statusCode' => 404, 'code' => 40410, 'msg' => 'tx not found'];
	//405 Method Not Allowed
	const ErrMethodNotAllowed = ['statusCode' => 404, 'code' => 40400, 'msg' => 'Method Not Allowed'];
	//406 Not Acceptable
	const ErrInvalidArgument = ['statusCode' => 400, 'code' => 40300, 'msg' => 'Invalid Argument'];
	const ErrInvalidAcc      = ['statusCode' => 400, 'code' => 40030, 'msg' => 'Invalid account'];
	const ErrInvalidAmt      = ['statusCode' => 400, 'code' => 40050, 'msg' => 'Invalid amount'];
	const ErrInvalidIP       = ['statusCode' => 403, 'code' => 40330, 'msg' => 'Invalid IP address'];
	const ErrInvalidURL      = ['statusCode' => 403, 'code' => 40330, 'msg' => 'Invalid callback url'];
	const ErrInvalidBankCode = ['statusCode' => 403, 'code' => 40330, 'msg' => 'Invalid bank code'];
	//409 Conflict
	const ErrExistTx = ['statusCode' => 409, 'code' => 40900, 'msg' => 'transaction already exist'];
	//500
	const ErrServer  = ['statusCode' => 500, 'code' => 50000, 'msg' => 'internal error'];
	const ErrUnknown = ['statusCode' => 500, 'code' => 50001, 'msg' => 'unknown error'];

	public static function NewError(int $code, string $msg) {
		return ['statusCode' => (int)substr($code, 0, 3), 'code' => $code, 'msg' => $msg];
	}
}