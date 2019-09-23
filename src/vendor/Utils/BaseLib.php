<?php

namespace BITPAY\Utils;

use Phalcon\Mvc\User\Component;
use Phalcon\Logger\Adapter\File as FileAdapter;

class BaseLib extends Component {
	/**
	 * 记录日志
	 * @param        $msg
	 * @param string $name
	 * @param bool   $mode 是否循环创建目录
	 */
	public function logger($msg, $name = 'debug', $mode = FALSE) {
		//开启了debug模式才记录日志
		if ($this->config->debug) {
			$time = time();
			$dir  = PROJ_DIR . '/log/';
			if (file_exists($dir)) {
				$path   = $dir . date('Ymd', $time) . '_' . $name . '.log';
				$logger = new FileAdapter($path);
				$msg    = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $msg;
				$ip     = $this->request->getClientAddress() ? $this->request->getClientAddress() : ' IP NULL ';
				$msg    = $ip . ' ' . $msg;
				$logger->log($msg);
			} else {
				if ($mode) {
					return;
				}
				@mkdir($dir, 0755, TRUE);
				$this->logger($msg, $name, TRUE);
			}
		}
	}

	public function checkPasswdSecurity($pwd) {
		$len = mb_strlen($pwd, 'UTF-8');
		return ($len < 8 || $len > 16 || preg_match('/^\d+$/', $pwd) || preg_match('/^[a-z]+$/', $pwd) || preg_match('/^[A-Z]+$/', $pwd)) ? FALSE : TRUE;
	}
}