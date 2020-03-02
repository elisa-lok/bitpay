<?php
namespace app\api\controller;
// require './extend/com/easybitcoin.php';
// require './extend/com/cls_json.php';
use com\Bitcoin;
use com\JSON;

define('IN_ECS', TRUE);
define('RPC_USER', 'omnicorerpc');
define('RPC_PWD', '5hMTZI9iBGFqKxsWfOUF');
define('RPC_URL', '47.244.112.175');
define('RPC_PORT', '8332');
define('QIANBAO_KEY', 'qweqwe123');
define('API_ERROR_MESSAGE_NOMETHOD', 'Method Name Error');
define('API_ERROR_MESSAGE_PARAMETERERROR', 'Parameters Error');
define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');
define('PROPERTY_ID', 31);
define('ADDRESS', '1PnhPBJ6JiAFuw5HFSpjuugci7SJjo179U');

class Usdt {
	public function index() {
		if (!isset($_REQUEST['method']) || empty($_REQUEST['method'])) {
			echo API_ERROR_MESSAGE_NOMETHOD; //方法名不能为空
			exit;
		}
		$method = $_REQUEST['method'];
		switch ($method) {
			case 'getnewaddress':
				$result = $this->getNewAddress();
				echo $result;
				exit;
			case 'validateaddress':
				if (!isset($_REQUEST['addr']) || empty($_REQUEST['addr'])) {
					echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
					exit;
				}
				$addr   = $_REQUEST['addr'];
				$result = $this->vailedAddress($addr);
				echo $result;
				exit;
			case 'getbalance':
				if (!isset($_REQUEST['addr']) || empty($_REQUEST['addr'])) {
					echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
					exit;
					// $addr = ADDRESS;
				} else {
					$addr = $_REQUEST['addr'];
				}
				$result = $this->getBalance($addr);
				echo $result;
				exit;
			case 'send':
				if (!isset($_REQUEST['addr']) || empty($_REQUEST['addr']) || !isset($_REQUEST['money']) || empty($_REQUEST['money']) || !is_numeric($_REQUEST['money'])) {
					echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
					exit;
				}
				// $faddr  = $_REQUEST['fromaddr'];
				$taddr  = $_REQUEST['addr'];
				$money  = $_REQUEST['money'];
				$result = $this->send($taddr, $money);
				echo $result;
				exit;
			case 'getbtcbalance':
				$result = $this->getBtcBalace();
				echo $result;
				exit;
			case 'getblockcount':
				$result = $this->getBlockCount();
				echo $result;
				exit;
			case 'transactionslist':
				$addr       = !empty($_REQUEST['addr']) ? $_REQUEST['addr'] : '*';
				$count      = !empty($_REQUEST['count']) && is_numeric($_REQUEST['count']) ? intval($_REQUEST['count']) : 10;
				$skip       = !empty($_REQUEST['skip']) && is_numeric($_REQUEST['skip']) ? intval($_REQUEST['skip']) : 0;
				$startblock = 0;      //$_REQUEST['txid'];
				$endblock   = 999999; //$_REQUEST['txid'];
				$result     = $this->transactionsList($addr, $count, $skip, $startblock, $endblock);
				echo $result;
				exit;
			case 'blocktransactionslist':
				if (!isset($_REQUEST['index']) || empty($_REQUEST['index']) || !is_numeric($_REQUEST['index'])) {
					echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
					exit;
				}
				$index  = $_REQUEST['index'];
				$result = $this->blockTransactionsList($index);
				echo $result;
				exit;
			case 'getinfo':
				echo $this->getinfo();
				exit;
			case 'getallbalances':
				$result = $this->getAllBalance();
				echo $result;
				exit;
			default:
				echo API_ERROR_MESSAGE_NOMETHOD; //方法名不能为空
				exit;
		}
	}

	/***
	 * 取得钱包相关信息
	 * {
	 * "result": {
	 * "alerts": [],
	 * "mastercoreversion": "0.3.0",
	 * "totaltransactions": 0,
	 * "blocktime": 1379205517,
	 * "blocktransactions": 0,
	 * "totaltrades": 0,
	 * "omnicoreversion": "0.3.0",
	 * "bitcoincoreversion": "0.13.2",
	 * "omnicoreversion_int": 30000000,
	 * "block": 105194
	 * },
	 * "id": "1521096083469"
	 * }
	 * 若获取失败，result为空，error信息为错误信息的编码
	 **/
	private function getInfo() {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$info    = $bitcoin->omni_getinfo();
		$json    = new JSON;
		$res     = $json->encode($info);
		return $res;
	}

	/**
	 * USDT产生地址
	 * @param  [type] $user [description]
	 * @return [type]       [description]
	 */
	private function getNewAddress() {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$address = $bitcoin->getnewaddress();
		echo $address;
	}

	/**
	 * USDT查询余额
	 * @param unknown $port_number 端口号
	 * @return Ambigous <number, unknown> 剩余的余额
	 */
	private function getBalance($addr) {
		if ($this->vailedAddress($addr)) {
			$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
			$money   = $bitcoin->omni_getbalance($addr, PROPERTY_ID);
			$num     = empty($money['balance']) ? 0 : $money['balance'];
			return $num;
		} else {
			// log . error("USDT接受地址不正确");
			return "USDT地址不正确222";
		}
	}

	/**
	 * 查询钱包BTC余额
	 */
	private function getBtcBalace() {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$balance = $bitcoin->getbalance();
		echo $balance;
	}

	/**
	 * USDT转帐
	 * @param faddr
	 * @param value
	 * @return
	 */
	private function send($taddr, $value) {
		if ($this->vailedAddress($taddr)) {
			$redeemaddress   = '';
			$referenceamount = '0';
			$bitcoin         = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
			$res             = $bitcoin->omni_send(ADDRESS, $taddr, PROPERTY_ID, $value . '');
			if ($res) {
				return $res;
			} else {
				// var_dump($bitcoin->response);
				return $bitcoin->error;
			}
		} else {
			return "USDT接受地址不正确";
		}
	}

	/**
	 * 检查地址是否是有效地址
	 * @param type $addr
	 * @return 是有效地址返回1 无效返回0
	 */
	private function vailedAddress($addr) {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$address = $bitcoin->validateaddress($addr);
		if ($address['isvalid']) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * 区块高度
	 * @return
	 */
	private function getBlockCount() {
		$bitcoin    = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$blockcount = $bitcoin->getblockcount();
		echo $blockcount;
	}

	private function transactionsList($addr, $count, $skip, $startblock, $endblock) {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$info    = $bitcoin->omni_listtransactions($addr, $count, $skip, $startblock, $endblock);
		$json    = new JSON;
		$res     = $json->encode($info);
		return $res;
	}

	private function getAllBalance() {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$info    = $bitcoin->omni_getallbalancesforid(PROPERTY_ID);
		$json    = new JSON;
		$res     = $json->encode($info);
		return $res;
	}

	private function blockTransactionsList($index) {
		$bitcoin = new Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
		$info    = $bitcoin->omni_listblocktransactions($index);
		if (empty($info)) {
			return "";
		}
		$json = new JSON;
		$res  = $json->encode($info);
		return $res;
	}
}
