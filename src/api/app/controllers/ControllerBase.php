<?php

namespace BITPAY\Api\Controllers;

use Phalcon\Mvc\Controller;
use BITPAY\Api\Common\Msg;

/**
 * TODO DI注入的方法函数在下面添加, 以方便IDE识别, 进行代码编写
 * Class ControllerBase
 * @package BITPAY\Api\Controllers
 * @property \Phalcon\Queue\Beanstalk        $queue
 * @property \Phalcon\Tag                    $tag
 * @property \Phalcon\Cache\BackendInterface $cache
 * @property \Phalcon\Http\Response\Cookies  $cookies
 * @property \Phalcon\Escaper                $escaper
 * @property \Redis                          $redis
 * @property \Phalcon\Db\AdapterInterface    $dbm
 * @property \Phalcon\Db\AdapterInterface    $dbs
 * @property \Phalcon\Config                 $config
 * @property \BITPAY\Api\Lib\Service           $s
 * @property \BITPAY\Api\Lib\Pay               $pay
 * property \BITPAY\Api\Common\Msg             $msg
 */
class ControllerBase extends Controller {
	var $params = NULL;

	//强制初始化执行函数
	public function onConstruct() {
		$this->request->isOptions() && exit;
		$params = $this->request->getJsonRawBody(TRUE); // 转化成数组
		// !$params && $this->api(Msg::ErrInvalidArgument);
	}

	public function beforeExecuteRoute() {

	}

	public function initialize() {

	}

	/**
	 * 重定向
	 * @param $uri
	 */
	public function forward($uri) {
		$uriParts = explode('/', $uri);
		$params   = array_slice($uriParts, 2);
		return $this->dispatcher->forward(['controller' => $uriParts[0], 'action' => $uriParts[1], 'params' => $params,]);
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public function result($data) {
		isset($data['statusCode'], $data['code'], $data['msg']) && $this->api($data);
		return $data;
	}

	/**
	 * 输出信息并退出程序
	 * @param mixed $data
	 * @param bool  $urlDecodeFlag
	 */
	public function api($data = Msg::Suc, $urlDecodeFlag = FALSE) {
		$data === TRUE && $data = Msg::Suc;
		!$data && $data = Msg::ErrFailure;
		$res = isset($data['statusCode'], $data['code'], $data['msg']) ? ['code' => $data['code'], 'msg' => $data['msg'], 'data' => ''] : [
			'code' => 0,
			'msg'  => 'success',
			'data' => $data
		];
		// ob_end_clean();
		// header('Content-type:application/json;charset=utf-8');
		isset($data['statusCode']) && header('HTTP/2.0 ' . $data['statusCode']);
		echo $urlDecodeFlag ? urldecode(json_encode($res)) : json_encode($res);
		die;
	}

	/**
	 * 同页面提交数据到另一页面
	 * @param string $method
	 * @param string $link
	 * @param array  $submitData
	 */
	public function resubmit(string $method, string $link, array $submitData) {
		$html = '<html><head><meta charset="utf-8"/></head><body onload="document.xform.submit()"><form style="display:none;" name="xform" method="' . $method . '" action="' . $link . '">';
		foreach ($submitData as $k => $v) {
			$html .= '<input name="' . $k . '" type="text" value="' . $v . '"/>';
		}
		$html .= '</form></body></html>';
		echo $html;
		die;
	}

	/**
	 * @param $text
	 * @return array
	 */
	public function parseHtmlForm($text) {
		$text = stripos($text, 'charset') === FALSE ? '<head><meta charset="utf-8"></head>' . $text : $text;
		$text = stripos($text, '!doctype') === FALSE ? '<!DOCTYPE HTML>' . $text : $text;
		$dom  = new \DOMDocument();
		$dom->loadHTML($text);
		$res        = $data = [];
		$res['url'] = $dom->getElementsByTagName('form')->item(0)->getAttribute('action');
		$tag        = $dom->getElementsByTagName('input');
		foreach ($tag as $k => $v) {
			$data[$tag->item($k)->getAttribute('name')] = $tag->item($k)->getAttribute('value');
		}
		$res['data'] = $data;
		return $res;
	}

	// 检查支付的是否可用
	public function checkPayment(string $className, string $methodName = '') {
		$className = strtolower($className);
		// 在Linux环境下要注意路径的大小写问题
		!method_exists($this->pay, $className) && $this->api(Msg::ErrPayChanNotSupport);
		$methodName && (!method_exists($this->pay->$className(), $methodName) || !in_array($methodName, $this->pay->$className()->appSvc)) && $this->api(Msg::ErrPayMethodNotSupport);
	}
}