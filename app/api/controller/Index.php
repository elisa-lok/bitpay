<?php
namespace app\api\controller;
class Index {
	public function __construct() {
		$this->appid          = 'cIPBDs97dP2iMklV';                                     //商户号
		$this->key            = 'c46c36e3f2dcccd54806a89c373ae856';                     //秘钥
		$this->rechargeUrl    = 'http://www.***.com/api/merchant/requestTraderRecharge';//用户充值接口按数量
		$this->rechargeRmbUrl = 'http://zpay.cc/api/merchant/requestTraderRechargeRmb'; //用户充值接口按人民币
		$this->notifyUrl      = 'http://47.104.23.74/test.php';
		$this->returnUrl      = 'http://47.104.23.74/test.php';
	}

	public function index() {
		return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
	}

	public function pay() {
		return $this->fetch();
	}

	/**
	 * [sign 签名验签]
	 * @author max
	 */
	private function sign($dataArr) {
		ksort($dataArr);
		$str = '';
		foreach ($dataArr as $key => $value) {
			$str .= $key . $value;
		}
		$str = $str . $this->key;
		return strtoupper(sha1($str));
	}

	private function curl($url, $data = []) {
		//使用crul模拟
		$ch = curl_init();
		//禁用https
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		//允许请求以文件流的形式返回
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch); //执行发送
		curl_close($ch);
		return $result;
	}
}
