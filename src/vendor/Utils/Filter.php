<?php

namespace BITPAY\Utils;

/**
 * 过滤器
 * Class Filter
 * @package BITPAY\Utils
 */
class Filter extends \Phalcon\Filter {
	public static function email($mail) {
		return preg_replace('/[^\w\.@-]/', '', $mail);
	}
}