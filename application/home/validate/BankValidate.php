<?php
namespace app\home\validate;
use think\Validate;

class BankValidate extends Validate {
	protected $rule = [
		'name'          => 'require|max:50|unique:merchant_bankcard',
		'c_bank'        => 'require',
		'c_bank_detail' => 'require',
		'c_bank_card'   => 'between:15,23',
		'c_bank_card'   => 'confirm:c_bank_card_again',
	];
	protected $message = [
		'name.require'          => '银行卡标识不能为空',
		'name.max'              => '银行卡标识最多不能超过50个字符',
		'name.unique'           => '银行卡标识已存在',
		'c_bank.require'        => '开户银行不能为空',
		'c_bank_detail.require' => '开户支行不能为空',
		'c_bank_card.between'   => '银行卡号不正确',
		'c_bank_card.confirm'   => '确认银行卡号错误',
	];
}

?>