<?php
namespace app\home\validate;
use think\Validate;

class BankValidate extends Validate {
	protected $rule    = [
		'name'          => 'require|max:50',
		'c_bank'        => 'require',
		'c_bank_detail' => 'require',
	];
	protected $message = [
		'name.require'          => '银行卡标识不能为空',
		'name.max'              => '银行卡标识最多不能超过50个字符',
		'c_bank.require'        => '开户银行不能为空',
		'c_bank_detail.require' => '开户支行不能为空',
	];
}

?>