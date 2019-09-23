<?php

namespace BITPAY\Home\Controllers;

use Phalcon\Mvc\Controller;
use BITPAY\Msg;

/**
 * Class ControllerBase
 * @package BITPAY\Home\Controllers
 * @property \Phalcon\Queue\Beanstalk        $queue
 * @property \Phalcon\Tag                    $tag
 * @property \Phalcon\Cache\BackendInterface $cache
 * @property \Phalcon\Http\Response\Cookies  $cookies
 * @property \Phalcon\Escaper                $escaper
 * @property \BITPAY\Service                   $s
 * @property \Redis                          $redis
 * @property \Phalcon\Session\Adapter\Redis  $session
 * @property \Phalcon\Config                 $config
 */
class ControllerBase extends Controller {
	protected $userInfo;
	protected $role      = 'merchant'; // 判断是否是代理
	protected $roleAgent = FALSE; // 判断是否是代理
	protected $accId = 10000;

	/**
	 * 强制执行函数, 初始化时候执行, onConstruct > beforeExecuteRoute > initialize
	 */
	public function onConstruct() {
		$this->request->isOptions() && exit;
		$this->view->setRenderLevel(1); //渲染
	}

	public function beforeExecuteRoute() {

	}

	/**
	 * 初始化信息
	 */
	public function initialize() {
		//获取用户信息
		$this->userInfo = $this->s->sign()->getAccInfo();
		//$ctrlName       = $this->router->getControllerName();
		//if(!$this->userInfo && $ctrlName != 'sign'){
		//	$this->goUrl('http://jdpay.com/account/basicinfo');
		//}
		$this->userInfo && $this->userInfo['uid'] <= 0 && $this->api(Msg::ErrAccState);
		isset($this->userInfo['uid']) && $this->uid = $this->userInfo['uid'];
	}

	/**
	 * 输出信息并退出程序
	 * @param mixed $data
	 * @param int   $count
	 * @param bool  $urlDecodeFlag
	 */
	public function api($data = Msg::Suc, $count = 0, $urlDecodeFlag = FALSE) {
		$this->view->disable();
		$data === TRUE && $data = Msg::Suc;
		$data === FALSE && $data = Msg::ErrFailure;
		$res = isset($data['statusCode'], $data['code'], $data['msg']) ? ['code' => $data['code'], 'msg' => $data['msg'], 'data' => []] : ['code' => 0, 'msg' => 'success', 'data' => $data, 'count' => (int)$count];
		header('Content-type:application/json;charset=utf-8');
		isset($data['statusCode']) && header('HTTP/2.0 ' . $data['statusCode']);
		echo $urlDecodeFlag ? urldecode(json_encode($res)) : json_encode($res);
		die;
	}

	/**
	 * 数组改成字符串处理，方便查询
	 * @param $data
	 * @return array|string
	 */
	public function arrayChangeString(array $data) {
		return !empty($data) ? implode(',', $data) : $data;
	}

	public function goUrl($url = '', $isJson = FALSE) {
		$redirectUrl = $url ? $url : $_REQUEST['redirectUrl'] ?? ($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ($_COOKIE['redirectUrl'] ?? $this->config->domain->home));
		$isJson ? $this->api(['redirectUrl' => $redirectUrl]) : (header('location:' . $redirectUrl) || exit);
	}

	public function getRawData($useArray = FALSE) {
		$res = !$this->request->isPost() && !$this->request->isPut() && $this->request->isPatch() && !$this->request->isDelete() ? $this->api(Msg::ErrNotFound) : json_decode(file_get_contents('php://input'), $useArray);
		return $res ?? $this->api(Msg::ErrInvalidArgument);
	}

	/**
	 * 重定向uri
	 * @param string $uri 'controller/action'
	 * @return mixed
	 */
	protected function forward($uri) {
		$uriParts = explode('/', $uri);
		$params   = array_slice($uriParts, 2);
		return $this->dispatcher->forward(['controller' => $uriParts[0], 'action' => $uriParts[1], 'params' => $params,]);
	}

	/**
	 * filter $_GET request
	 * @return array
	 */
	protected function getQuery() {
		$get = $this->request->getQuery();
		unset($get['_url']);
		$data = [];
		foreach ($get as $k => $v) {
			$k        = htmlspecialchars($k);
			$v        = htmlspecialchars($v);
			$data[$k] = $v;
		}
		return $data;
	}

	/**
	 * 生成带参数的url, 包装一层是为了统一生成算法
	 * @param $arr
	 * @return string
	 */
	protected function buildQuery($arr) {
		return http_build_query($arr, '', '&amp;');
	}

	/**
	 * 过滤字符串
	 * @param $str
	 * @return mixed
	 */
	protected function _trim($str) {
		return trim(preg_replace('/^　+|　+$/ ', ' ', preg_replace('/( |　|\r\n|\r|\n)+/', ' ', $str)));
	}

	public function timeBucketToStamp(string $timeStr) {
		if ($timeStr == '') {
			return FALSE;
		}
		// 默认格式 '2019-04-01 00:00:00 - 2019-05-08 00:00:00'
		$time    = explode('-', $timeStr);
		$time[0] = strtotime($time[0]);
		$time[1] = strtotime($time[1]);
		return $time;
	}
}