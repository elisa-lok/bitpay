<?php

/**
* usdt类
*/
class usdt
{
	public function __construct()
	{
        $this->newAddressUrl 	= 'http://www.***.com/api/merchant/newAddress'; //生成地址接口地址
        $this->rechargeRecordUrl	= 'http://www.***.com/api/merchant/rechargeRecord';		//充值记录接口
		$this->getBalanceUrl	= 'http://www.***.com/api/merchant/getBalance';		//获取usdt余额接口
		$this->makeWithdrawUrl	= 'http://www.***.com/api/merchant/makeWithdraw';		//用户提币接口
		$this->getWithdrawUrl	= 'http://www.***.com/api/merchant/getWithdraw';		//用户提币状态接口
    	$this->appid 	= 'XXXXX';						//商户号
    	$this->key	= 'XXXXX';				//秘钥
		$this->rechargeUrl = 'http://www.***.com/api/merchant/requestTraderRecharge';//用户充值接口
		$this->notifyUrl = '';
		$this->returnUrl = '';
	}

	/**
	 * [newAddress 生成地址]
	 * @author max
	 */
	public  function newAddress()
	{
        $dataArr    = array(
            'username'           => 'max123456',
            'appid'      => $this->appid ,
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->newAddressUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
	}
	/**
	 * [rechargeRecord 充值记录]
	 * @author max
	 */
	public  function rechargeRecord()
	{
        $dataArr    = array(
            'address'           => '1C2gJ0JuVR*****',
            'appid'      => $this->appid ,
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->rechargeRecordUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
	}
	/**
	 * [getBalance usdt余额]
	 * @author max
	 */
	public  function getBalance()
	{
        $dataArr    = array(
            'address'           => '1C2gJ0JuVR*****',
            'appid'      => $this->appid ,
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->rechargeRecordUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
	}
	/**
	 * [makeWithdraw 用户提币]
	 * @author max
	 */
	public  function makeWithdraw()
	{
        $dataArr    = array(
            'address'           => '1C2gJ0JuVR*****',
			'num'			=>50,
			'username'			=>'max123456',
            'appid'      => $this->appid ,
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->makeWithdrawUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
	}
	/**
	 * [getWithdraw 用户提币状态]
	 * @author max
	 */
	public  function getWithdraw()
	{
        $dataArr    = array(
            'ordersn'           => 'U1TB2290*****68PS848',
            'appid'      => $this->appid ,
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->getWithdrawUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
	}
	/**
	 * [recharge 用户充值]
	 * @author max
	 */
	public  function recharge()
	{
        $dataArr    = array(
            'amount'           => 100,
			'address'			=> '',
			'username'			=> '',
			'orderid'			=> '',
            'appid'      => $this->appid ,
			'return_url'	=>$this->returnUrl,
			'notify_url'	=>$this->notifyUrl
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->getWithdrawUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
	}
	/**
	 * [recharge 用户充值回调]
	 * @author max
	 */
	public function notify(){
		$res = $_POST;
        if($res['sign']){
			$sign = $res['sign'];
			unset($res['sign']);
            $sign2 = $this->sign($res);
            if($sign == $sign2 && $res['status'] == 1){
                $amount = $res['amount'];
                $orderid = $res['orderid'];
                $out_trade_no = $orderid;
                /*逻辑处理*/
            }else{
                exit('fail');
            }
        }else{
            exit('fail');
        }
        echo 'success';
        exit;
	}



	/**
	 * [sign 签名验签]
	 * @author max
	 */
	private function sign($dataArr)
	{
        ksort($dataArr);
        $str = '';
        foreach ($dataArr as $key => $value) {
                $str.=$key.$value;
        }

        $str = $str.$this->key;
        
        return strtoupper(sha1($str));
	}


	private function curl($url,$data = array())
	{
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

$res = new usdt();

$res->newAddress();
$res->rechargeRecord();
$res->getBalance();
$res->makeWithdraw();
$res->getWithdraw();
//充值接口
$data = $res->recharge();
header('Location:'.$data['data']);exit;