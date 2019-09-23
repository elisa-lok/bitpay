<?php
/**
 * 基础服务类
 * 本服务相等于一个容器,如果要使用到Phalcon框架中的di则继承
 */

namespace BITPAY\Service;

use Phalcon\Mvc\Model\Message as ModelMessage;
use Phalcon\Mvc\User\Component;
use Phalcon\Validation\Message as ValidationMessage;
use Phalcon\Validation\Message\Group as MessageGroup;
use BITPAY\Model\LogUserOp;

/**
 * Class BaseService
 * @package BITPAY\Service
 * @property \Redis                         $redis
 * @property \Phalcon\Session\Adapter\Redis $session
 * @property \Phalcon\Config                $config
 * @property \Phalcon\Config                $this->config
 * @property \Phalcon\Db\AdapterInterface   $dbWrite
 * @property \Phalcon\Db\AdapterInterface   $dbRead
 * @property \Phalcon\Cache\Backend\Redis   $cache
 */
class BaseService extends Component {
	protected $errorMessages;

	/**
	 * 如果有错误信息返回真
	 * @return bool
	 */
	public function validationHasFailed() {
		return ($this->errorMessages instanceof MessageGroup) ? $this->errorMessages->count() > 0 : FALSE;
	}

	/**
	 * 设置错误信息
	 * @param $messages
	 */
	public function setMessages($messages) {
		if (!$this->errorMessages instanceof MessageGroup) {
			$this->errorMessages = new MessageGroup();
		}
		if (count($messages) > 0 && !empty($messages)) {
			foreach ($messages as $msg) {
				if ($msg instanceof ModelMessage) {
					$tmp = new ValidationMessage($msg->getMessage(), $msg->getField(), $msg->getType());
					$this->errorMessages->appendMessage($tmp);
				} else {
					$this->errorMessages->appendMessage($msg);
				}
			}
		}
	}

	/**
	 * 取错误信息
	 * @param string $filter
	 * @return mixed
	 */
	public function getMessages($filter = '') {
		if (is_string($filter) && !empty($filter)) {
			$filtered = new MessageGroup();
			foreach ($this->errorMessages as $message) {
				is_object($message) && method_exists($message, 'getField') && ($message->getField() == $filter) && $filtered->appendMessage($message);
			}
			return $filtered;
		}
		return $this->errorMessages;
	}

	/**
	 * 使用curl的get方法
	 * @param string $url
	 * @param int    $timeout
	 * @return mixed
	 */
	public function curlGet($url, $conv = FALSE, $SSL = 0, $timeout = 30) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSL); // 让CURL支持HTTPS访问
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_ENCODING, 'text');
		$res = curl_exec($ch);
		$res = $conv ? mb_convert_encoding($res, 'utf-8', 'GBK,UTF-8,ASCII') : $res;
		curl_close($ch);
		return $res;
	}

	/**
	 * 使用curl的post方法
	 * @param      $url
	 * @param      $params
	 * @param bool $use_http_build_query
	 * @param int  $SSL
	 * @return mixed
	 */
	public function curlPost($url, $params, $use_http_build_query = TRUE, $SSL = 0) {
		$use_http_build_query && $params = http_build_query($params);

		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_POST, 1);
		curl_setopt($curlHandle, CURLOPT_URL, $url);
		curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $SSL); // 让CURL支持HTTPS访问
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($curlHandle);
		curl_close($curlHandle);

		return $result;
	}

	/**
	 * 字符串转化成SQL语句或者数组
	 * @param string $str
	 * @param bool   $toSqlIn
	 * @return array|bool
	 */
	public function strToArray($str = '', $toSqlIn = FALSE) {
		$str = str_replace([" ", "　", "\t", "\n", "\r"], '', $str);
		$res = preg_match('/^\d(\,[\d]+)+$/', $str) ? ($toSqlIn ? " IN ('" . implode("','", array_filter(explode(',', $str))) . "') " : array_filter(explode(',', $str))) : FALSE;
		return $toSqlIn && preg_match('/^\d+$/', $str) ? " = '$str'" : $res;
	}

	/**
	 * @param array|string $ids
	 * @return string
	 */
	public function arrayToSqlIn($ids) {
		return is_array($ids) ? " IN ('" . implode("','", array_unique($ids)) . "') " : " = '" . addslashes($ids) . "'";
	}

	/**
	 * 此方法由子类的getOne类型方法调用，用于执行查询前生成phalcon原生find/findFirst方法所需要的参数数组
	 * @access protected
	 * @param  array|string $conditions 条件数组（键名为数据库字段名，键值为目标值）或字符串（同原生findFirst方法）
	 * @param  array        $params     find/findFirst方法参数数组（除conditions以外）
	 * @return mixed    phalcon原生find/findFirst方法参数数组
	 * @todo   to be optimized
	 */
	protected function toParams($conditions, array $params = [], $is_sql = 0) {
		/****检查conditions****/
		if (is_string($conditions)) {
			$conditions = trim($conditions);
		} elseif (is_array($conditions)) {
			foreach ($conditions as $key => $val) {
				$conditions[$key] = trim($val);
				if ($conditions[$key] === '') {
					unset($conditions[$key]);
				}
			}
		} else {
			return FALSE;
		}
		if (!$conditions) {
			return FALSE;
		}

		/****过滤参数****/
		foreach ($params as $key => $val) {
			if (empty($val)) {
				unset($params[$key]);
			}
		}
		$params['conditions'] = $conditions;

		/****生成（或覆盖）conditions 和 bind参数****/
		$sql = ' 1 ';
		$old = $conditions;
		if (is_array($conditions)) {
			$params['bind'] = array_values($conditions);
			$conditions     = array_keys($conditions);
			foreach ($conditions as $key => $val) {
				$conditions[$key] = "{$val} = ?{$key}";
			}
			$params['conditions'] = implode(' AND ', $conditions);
		} else {
			$params['conditions'] = $conditions;
		}

		foreach ($old as $k => $v) {
			$sql .= " and " . $k . " = '" . $v . "'";
		}

		return $is_sql == 1 ? $sql : $params;
	}

	/**
	 * 更新一个数据对象及其对应的数据库记录
	 * @param  array     $data    新数据数组（键名为数据库字段名，键值为待更新值）
	 * @param  \stdClass $objData 待更新的对象
	 * @return boolean           是否更新成功
	 */
	protected function updateOne(array $data, $objData) {
		if (!is_object($objData) || !method_exists($objData, 'update')) {
			return FALSE;
		}

		/*****检查新数据数组****/
		foreach ($data as $key => $val) {
			if (!property_exists($objData, $key)) {
				unset($data[$key]);
				continue;
			}
			$data[$key] = trim($val);
		}
		/*****执行更新****/
		return !$data ? FALSE : $objData->update($data);
	}

	/**
	 * 密码加密
	 * @param        $password
	 * @param string $salt
	 * @return string
	 */
	public function hash($password, $salt = '') {
		return md5(md5(trim($password) . $salt) . $salt);
	}

	/**
	 * @param $gAuthKey
	 * @param $code
	 * @return bool
	 */
	public function checkOtpAuth($gAuthKey, $code) {
		$g = new \BITPAY\Utils\OTP();
		return $g->checkCode($gAuthKey, $code);
	}

	public function checkIpBlock() {

	}

	/**
	 * @return string
	 */
	public function getIpAddr() {
		return (string)$_SERVER['REMOTE_ADDR'];
	}

	/** 商户web操作日志
	 * $action_type 0登录,1修改个人资料,2修改密码,3代付,4批量代付,5添加用户
	 * @param int    $uid
	 * @param int    $acType
	 * @param string $acDesc
	 * @param string $ip
	 * @return boolean
	 */
	public function merchantsWEB($uid, $acType, $acDesc, $ip) {
		$log              = new LogUserOp;
		$log->uid         = (int)$uid;
		$log->action_type = (int)$acType;
		$log->action_desc = addslashes($acDesc);
		$log->ctime       = time();
		$log->ip_addr     = $ip;
		return $log->create();
	}

	/**
	 * router
	 * @param string $uid 用户ID
	 * @param string $auth_
	 * @return null
	 */
	public function returnRouter($info) {
		return [
			[
				'path'       => '1111111', //路由url
				'redirect'   => '1111111', //路由重定向
				'component'  => 'layout', //若要菜单控制面板则必须返回layout,children.component:'业务组件'
				'alwaysShow' => FALSE, //可不返回
				'hidden'     => '1111111',//当设置 TRUE 的时候该路由不会在侧边栏出现。如401、login等页面，或者如一些编辑页面：/edit/1
				'children'   => [
					[
						'path'      => '1111111',
						'name'      => '1111111', //router name
						'component' => $info,
						'meta'      => [
							'title'   => '1111111', //设置该路由在侧边栏和面包屑中展示的名字
							'icon'    => '1111111', // .....图标
							'noCache' => TRUE //如果设置为TRUE，则不会被 <keep-alive> 缓存(默认 FALSE) =》 tab-bar 的视图缓存
						]
					]
				]
			],
			[
				'path'     => '*',
				'redirect' => '/404',
				'hidden'   => TRUE
			] //必须最后返回这个
		];
	}

	// 分割时间语句
	public function timeSQL($columnName, $bucket) {
		list($start, $end) = explode('|', $bucket);
		return ' (' . $columnName . ' >= ' . strtotime($start) . ' AND ' . $columnName . ' <= ' . strtotime($end) . ') ';
	}
}
