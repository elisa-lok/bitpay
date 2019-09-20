<?php

namespace app\common\model;
use think\Model;
use think\Db;

class Eth {
    protected $host,$version;
    protected $id = 0;
    public $base = 1000000000000000000;//1e18 wei  基本单位


   public function __construct()
    {
        $this->host = Db::table('think_config')->where('name', 'ethip')->value('value');
        $this->version = "2.0";
    }

    public function index($method, $addr, $money, $index, $count, $skip,$password) {
        if (!isset($method) || empty($method)) {
            return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_NOMETHOD];
            //echo API_ERROR_MESSAGE_NOMETHOD; //方法名不能为空
            exit;
        }

        switch ($method) {

        case 'getnewaddress':
            $result = $this->personal_newAccount($password);
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
        case 'validateaddress':
            if (!isset($addr) || empty($addr)) {
                return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_PARAMETERERROR];
                //echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
                exit;
            }
            $result = $this->vailedAddress($addr);
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
        case 'getbalance':
            if (!isset($addr) || empty($addr)) {
                return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_PARAMETERERROR];
                exit;
            } else {
            }
            $result = $this->getBalance($addr);
            return $result;
            exit;
		case 'getBtcBalance':
            if (!isset($addr) || empty($addr)) {
                return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_PARAMETERERROR];
                exit;
            } else {
            }
            $result = $this->getBtcBalance($addr);
            return $result;
            exit;
        case 'send':
            if (!isset($addr) || empty($addr)
            || !isset($money) || empty($money)
            || !is_numeric($money)
            ) {
                return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_PARAMETERERROR];
                //echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
                exit;
            }

            // $faddr  = $_REQUEST['fromaddr'];
            $taddr  = $addr;
            $result = $this->send($taddr, $money);
            return $result;
            //echo $result;
            exit;
            case 'cover':
            if (!isset($addr) || empty($addr)
            || !isset($money) || empty($money)
            || !is_numeric($money)
            ) {
                return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_PARAMETERERROR];
                //echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
                exit;
            }

            // $faddr  = $_REQUEST['fromaddr'];
            $taddr  = $addr;
            $result = $this->cover($taddr, $money);
            return $result;
            //echo $result;
            exit;
        case 'getbtcbalance':
            $result = $this->getBtcBalace();
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
		case 'getAllAddress':
            $result = $this->getAllAddress();
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
        case 'getblockcount':
            $result = $this->getBlockCount();
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
        case 'transactionslist':

            $addr       = !empty($addr) ? $addr : '*';
            $count      = !empty($count) && is_numeric($count) ? intval($count) : 10;
            $skip       = !empty($skip) && is_numeric($skip) ? intval($skip) : 0;
            $startblock = 0; //$_REQUEST['txid'];
            $endblock   = 999999; //$_REQUEST['txid'];

            $result = $this->transactionsList($addr, $count, $skip, $startblock, $endblock);
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
        case 'blocktransactionslist':
            if (!isset($index) || empty($index) || !is_numeric($index)
            ) {
                return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_PARAMETERERROR];
                //echo API_ERROR_MESSAGE_PARAMETERERROR; // 参数不正确
                exit;
            }
            $result = $this->blockTransactionsList($index);
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;

        case 'getinfo':
            return ['code'=>1, 'msg'=>'', 'data'=>$this->getinfo()];
            //echo $this->getinfo();
            exit;
        case 'getallbalances':
            $result = $this->getAllBalance();
            return ['code'=>1, 'msg'=>'', 'data'=>$result];
            //echo $result;
            exit;
        default:
            return ['code'=>0, 'msg'=>API_ERROR_MESSAGE_NOMETHOD, 'data'=>''];
            //echo API_ERROR_MESSAGE_NOMETHOD; //方法名不能为空
            exit;

        }

    }
    /***
     * 取得钱包相关信息
     * {
    "result": {
    "alerts": [],
    "mastercoreversion": "0.3.0",
    "totaltransactions": 0,
    "blocktime": 1379205517,
    "blocktransactions": 0,
    "totaltrades": 0,
    "omnicoreversion": "0.3.0",
    "bitcoincoreversion": "0.13.2",
    "omnicoreversion_int": 30000000,
    "block": 105194
    },
    "id": "1521096083469"
    }

    若获取失败，result为空，error信息为错误信息的编码
     **/
    private function getInfo() {

        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);//dump($bitcoin);
        $info    = $bitcoin->omni_getinfo();
        $json    = new \com\JSON;
        $res     = $json->encode($info);
        return $res;
    }

/**
 * USDT产生地址
 * @param  [type] $user [description]
 * @return [type]       [description]
 */
    private function personal_newAccount($password='') {
         $params = array(
            $password,
        );
         if($password==''){
            return false;
         }
        $data = $this->request(__FUNCTION__, $params);
        // dump($data);
        error_log($data['result'].PHP_EOL, 3, './addressdebug.log');
        if (empty($data['error']) && !empty($data['result'])) {
            return $data['result'];//新生成的账号公钥
        } else {
            return false;
        }
    }


/**
 * USDT查询余额
 * @param unknown $port_number 端口号
 * @return Ambigous <number, unknown> 剩余的余额
 */
    private function getBalance($addr) {
        if ($this->vailedAddress($addr)) {
            $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
            $money   = $bitcoin->omni_getbalance($addr, PROPERTY_ID);
            $num     = empty($money['balance']) ? 0 : $money['balance'];
            return ['code'=>1, 'msg'=>'', 'data'=>$num];
        } else {
            // log . error("USDT接受地址不正确");
            return ['code'=>0, 'msg'=>'USDT地址不正确', 'data'=>''];
        }
    }
	private function getBtcBalance($addr) {
        if ($this->vailedAddress($addr)) {
            $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
            $money   = $bitcoin->omni_getbalance($addr, 0);
            $num     = empty($money['balance']) ? 0 : $money['balance'];
            return ['code'=>1, 'msg'=>'', 'data'=>$num];
        } else {
            // log . error("USDT接受地址不正确");
            return ['code'=>0, 'msg'=>'地址不正确', 'data'=>''];
        }
    }
/**
 * 查询钱包BTC余额
 */
    private function getBtcBalace() {
        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
        $balance = $bitcoin->getbalance();
        return $balance;
    }

/**
 * USDT转帐
 * @param faddr
 * @param value
 * @return
 */
    private function send($taddr, $value) {
		$fee = Db::name('config')->where('name', 'usdt_fee')->value('value');
        $pwd = Db::name('config')->where('name', 'usdt_pwd')->value('value');
        if(empty($fee) || empty($pwd)){
            //return ['code'=>0, 'msg'=>"钱包密码或转账手续费未配置", 'data'=>''];
        }
        if ($this->vailedAddress($taddr)) {

            $redeemaddress   = '';
            $referenceamount = '0';

            $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
            //$fee = 0.0008;
            //$pwd = '19910408a';//钱包密码
           if(!empty($pwd) && !empty($fee)){
                $bitcoin->settxfee($fee);
                $bitcoin->walletlock();
                $bitcoin->walletpassphrase($pwd, 20);
            }
            $res     = $bitcoin->omni_funded_send(ADDRESS, $taddr, PROPERTY_ID, $value . '',ADDRESS);//20190819新的调用方法:发送地址,目标地址,资产id,数量,手续费地址
            // $res     = $bitcoin->omni_send(ADDRESS, $taddr, PROPERTY_ID, $value . '');
            //$bitcoin->walletlock();
            if ($res) {
                return ['code'=>1, 'msg'=>'', 'data'=>$res];
            } else {
                // var_dump($bitcoin->response);
                return ['code'=>0, 'msg'=>$bitcoin->error, 'data'=>''];
            }

        } else {
            return ['code'=>0, 'msg'=>"USDT接受地址不正确", 'data'=>''];
        }
    }

    /**
 * USDT汇总
 * @param faddr
 * @param value
 * @return
 */
    private function cover($taddr, $value) {
        $fee = Db::name('config')->where('name', 'usdt_fee')->value('value');
        $pwd = Db::name('config')->where('name', 'usdt_pwd')->value('value');
        if(empty($fee) || empty($pwd)){
            //return ['code'=>0, 'msg'=>"钱包密码或转账手续费未配置", 'data'=>''];
        }
        if ($this->vailedAddress($taddr)) {

            $redeemaddress   = '';
            $referenceamount = '0';

            $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
            //$fee = 0.0008;
            //$pwd = '19910408a';//钱包密码
           if(!empty($pwd) && !empty($fee)){
                $bitcoin->settxfee($fee);
                $bitcoin->walletlock();
                $bitcoin->walletpassphrase($pwd, 20);
            }
            $res     = $bitcoin->omni_funded_send($taddr,ADDRESS,  PROPERTY_ID, $value . '',ADDRESS);//20190819新的调用方法:发送地址,目标地址,资产id,数量,手续费地址
            // $res     = $bitcoin->omni_send(ADDRESS, $taddr, PROPERTY_ID, $value . '');
            //$bitcoin->walletlock();
            if ($res) {
                return ['code'=>1, 'msg'=>'', 'data'=>$res];
            } else {
                // var_dump($bitcoin->response);
                return ['code'=>0, 'msg'=>$bitcoin->error, 'data'=>''];
            }

        } else {
            return ['code'=>0, 'msg'=>"USDT接受地址不正确", 'data'=>''];
        }
    }

/**
 * 检查地址是否是有效地址
 * @param type $addr
 * @return 是有效地址返回1 无效返回0
 */
    private function vailedAddress($addr) {

        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
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

        $bitcoin    = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
        $blockcount = $bitcoin->getblockcount();
        return $blockcount;
        //echo $blockcount;

    }

    private function transactionsList($addr, $count, $skip, $startblock, $endblock) {
        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
        $info    = $bitcoin->omni_listtransactions($addr, $count, $skip, $startblock, $endblock);

        $json = new \com\JSON;
        $res  = $json->encode($info);
        return $res;
    }

    private function getAllBalance() {
        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
        $info    = $bitcoin->omni_getallbalancesforid(PROPERTY_ID);

        $json = new \com\JSON;
        $res  = $json->encode($info);
        return $res;
    }

    private function blockTransactionsList($index) {
        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
        $info    = $bitcoin->omni_listblocktransactions($index);
        if (empty($info)) {
            return "";
        }

        $json = new \com\JSON;
        $res  = $json->encode($info);
        return $res;
    }
	private function getAllAddress() {
        $bitcoin = new \com\Bitcoin(RPC_USER, RPC_PWD, RPC_URL, RPC_PORT);
        $info    = $bitcoin->getaddressesbyaccount('');
        return $info;
    }


  private  function request($method, $params = array())
      {
        $data = array();
        $data['jsonrpc'] = $this->version;
        $data['id'] = $this->id + 1;
        $data['method'] = $method;
        $data['params'] = $params;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->host);
        // curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $ret = curl_exec($ch);
        //返回结果
        if ($ret) {
            curl_close($ch);
            return json_decode($ret, true);
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            // throw new Exception("curl出错，错误码:$error");
            return false;
        }
    }


    function fromWei($weiNumber)
    {
        $ethNumber = hexdec($weiNumber) / $this->base;
        return $ethNumber;
    }


    function toWei($ethNumber)
    {
        $weiNumber = dechex($ethNumber * $this->base);
        // $weiNumber = float($weiNumber);
        return $weiNumber;
    }

    /**
     * 判断是否是16进制
     * @author qiuphp2
     * @since 2017-9-21
     * @param $a
     * @return int
     */
    function assertIsHex($a)
    {
        if (ctype_xdigit($a)) {
            return true;
        } else {
            return false;
        }
    }

    function web3_clientVersion(){
        $data = $this->request(__FUNCTION__, $params);
        if($data['result']){
            return $data['result'];
        }else{
            return false;
        }
        // return $data['result'];
    }
}
