<?php
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use think\Cache;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * 字符串截取，支持中文和其他编码
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = TRUE) {
	if (function_exists("mb_substr")) $slice = mb_substr($str, $start, $length, $charset); elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);
		if (FALSE === $slice) {
			$slice = '';
		}
	} else {
		$re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("", array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice . '...' : $slice;
}

function getTime($time) {
	return date('Y-m-d H:i:s', $time);
}

/**
 * 读取配置
 * @return array
 */
function load_config() {
	$list   = Db::name('config')->select();
	$config = [];
	foreach ($list as $k => $v) {
		$config[trim($v['name'])] = $v['value'];
	}
	return $config;
}

/**
 * 验证手机号是否正确
 * @param number $mobile
 * @author honfei
 */
function isMobile($mobile) {
	if (!is_numeric($mobile)) {
		return FALSE;
	}
	return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? TRUE : FALSE;
}

/**
 * 阿里云云通信发送短息
 * @param string $mobile   接收手机号
 * @param string $tplCode  短信模板ID
 * @param array  $tplParam 短信内容
 * @return array
 */
function sendMsg($mobile, $tplCode, $tplParam) {
	if (empty($mobile) || empty($tplCode)) return ['Message' => '缺少参数', 'Code' => 'Error'];
	if (!isMobile($mobile)) return ['Message' => '无效的手机号', 'Code' => 'Error'];
	require_once '../extend/aliyunsms/vendor/autoload.php';
	Config::load();             //加载区域结点配置
	$accessKeyId     = config('alisms_appkey');
	$accessKeySecret = config('alisms_appsecret');
	if (empty($accessKeyId) || empty($accessKeySecret)) return ['Message' => '请先在后台配置appkey和appsecret', 'Code' => 'Error'];
	$templateParam = $tplParam; //模板变量替换
	//$signName = (empty(config('alisms_signname'))?'阿里大于测试专用':config('alisms_signname'));
	$signName = config('alisms_signname');
	//短信模板ID
	$templateCode = $tplCode;
	//短信API产品名（短信产品名固定，无需修改）
	$product = "Dysmsapi";
	//短信API产品域名（接口地址固定，无需修改）
	$domain = "dysmsapi.aliyuncs.com";
	//暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
	$region = "cn-hangzhou";
	// 初始化用户Profile实例
	$profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
	// 增加服务结点
	DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
	// 初始化AcsClient用于发起请求
	$acsClient = new DefaultAcsClient($profile);
	// 初始化SendSmsRequest实例用于设置发送短信的参数
	$request = new SendSmsRequest();
	// 必填，设置雉短信接收号码
	$request->setPhoneNumbers($mobile);
	// 必填，设置签名名称
	$request->setSignName($signName);
	// 必填，设置模板CODE
	$request->setTemplateCode($templateCode);
	// 可选，设置模板参数
	if ($templateParam) {
		$request->setTemplateParam(json_encode($templateParam));
	}
	//发起访问请求
	$acsResponse = $acsClient->getAcsResponse($request);
	//返回请求结果
	$result = json_decode(json_encode($acsResponse), TRUE);
	return $result;
}

//生成网址的二维码 返回图片地址
function Qrcode($token, $url, $size = 8) {
	$md5   = md5($token);
	$dir   = date('Ymd') . '/' . substr($md5, 0, 10) . '/';
	$patch = 'qrcode/' . $dir;
	if (!file_exists($patch)) {
		mkdir($patch, 0755, TRUE);
	}
	$file     = 'qrcode/' . $dir . $md5 . '.png';
	$fileName = $file;
	if (!file_exists($fileName)) {
		$level = 'L';
		$data  = $url;
		QRcode::png($data, $fileName, $level, $size, 2, TRUE);
	}
	return $file;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name) {
	$result = FALSE;
	if (is_dir($dir_name)) {
		if ($handle = opendir($dir_name)) {
			while (FALSE !== ($item = readdir($handle))) {
				if ($item != '.' && $item != '..') {
					if (is_dir($dir_name . DS . $item)) {
						delete_dir_file($dir_name . DS . $item);
					} else {
						unlink($dir_name . DS . $item);
					}
				}
			}
			closedir($handle);
			if (rmdir($dir_name)) {
				$result = TRUE;
			}
		}
	}
	return $result;
}

//时间格式化1
function formatTime($time) {
	$now_time = time();
	$t        = $now_time - $time;
	$mon      = (int)($t / (86400 * 30));
	if ($mon >= 1) {
		return '一个月前';
	}
	$day = (int)($t / 86400);
	if ($day >= 1) {
		return $day . '天前';
	}
	$h = (int)($t / 3600);
	if ($h >= 1) {
		return $h . '小时前';
	}
	$min = (int)($t / 60);
	if ($min >= 1) {
		return $min . '分钟前';
	}
	return '刚刚';
}

//时间格式化2
function pincheTime($time) {
	$today = strtotime(date('Y-m-d')); //今天零点
	$here  = (int)(($time - $today) / 86400);
	if ($here == 1) {
		return '明天';
	}
	if ($here == 2) {
		return '后天';
	}
	if ($here >= 3 && $here < 7) {
		return $here . '天后';
	}
	if ($here >= 7 && $here < 30) {
		return '一周后';
	}
	if ($here >= 30 && $here < 365) {
		return '一个月后';
	}
	if ($here >= 365) {
		$r = (int)($here / 365) . '年后';
		return $r;
	}
	return '今天';
}

function getRandomString($len, $chars = NULL) {
	if (is_null($chars)) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	}
	mt_srand(10000000 * (double)microtime());
	for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
		$str .= $chars[mt_rand(0, $lc)];
	}
	return $str;
}

function random_str($length) {
	//生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
	$arr     = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
	$str     = '';
	$arr_len = count($arr);
	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $arr_len - 1);
		$str  .= $arr[$rand];
	}
	return $str;
}

/**
 * 参数检查并写日志
 */
function stopattack($StrFiltKey, $StrFiltValue, $ArrFiltReq) {
	if (is_array($StrFiltValue)) $StrFiltValue = implode($StrFiltValue);
	if (preg_match("/" . $ArrFiltReq . "/is", $StrFiltValue) == 1) {
		writeslog($_SERVER["REMOTE_ADDR"] . "    " . strftime("%Y-%m-%d %H:%M:%S") . "    " . $_SERVER["PHP_SELF"] . "    " . $_SERVER["REQUEST_METHOD"] . "    " . $StrFiltKey . "    " . $StrFiltValue);
		return FALSE;
	}
	return TRUE;
}

function sql_check() {
	$getfilter    = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	$postfilter   = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	$cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	foreach ($_GET as $key => $value) {
		if (!stopattack($key, $value, $getfilter)) return FALSE;
	}
	foreach ($_POST as $key => $value) {
		if (!stopattack($key, $value, $postfilter)) return FALSE;
	}
	foreach ($_COOKIE as $key => $value) {
		if (!stopattack($key, $value, $cookiefilter)) return FALSE;
	}
	return TRUE;
}

/**
 * SQL注入日志
 */
function writeslog($log) {
	$log_path = RUNTIME_PATH . 'sql_log.txt';
	$ts       = fopen($log_path, "a+");
	fputs($ts, $log . "\r\n");
	fclose($ts);
}

function writeslogtibi($log) {
	$log_path = RUNTIME_PATH . 'tibi_log.txt';
	$ts       = fopen($log_path, "a+");
	fputs($ts, $log . "\r\n");
	fclose($ts);
}

function sql_check2() {
	$getfilter    = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	$postfilter   = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	$cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	$key          = 'content';
	$value        = 'SELECT * from merchant_user';
	if (preg_match("/" . $postfilter . "/is", $value) == 1) {
		dump(1111);
		//writeslog($_SERVER["REMOTE_ADDR"]."    ".strftime("%Y-%m-%d %H:%M:%S")."    ".$_SERVER["PHP_SELF"]."    ".$_SERVER["REQUEST_METHOD"]."    ".$StrFiltKey."    ".$StrFiltValue);
		return FALSE;
	} else {
		dump(222);
	}
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param string       $name 格式 [模块名]/接口名/方法名
 * @param array|string $vars 参数
 */
function api($name, $vars = []) {
	$array     = explode('/', $name);
	$method    = array_pop($array);
	$classname = array_pop($array);
	$module    = $array ? array_pop($array) : 'common';
	$callback  = 'app\\' . $module . '\\Api\\' . $classname . 'Api::' . $method;
	if (is_string($vars)) {
		parse_str($vars, $vars);
	}
	return call_user_func_array($callback, $vars);
}

function generate_password($length = 16) {
	// 密码字符集，可任意添加你需要的字符
	$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$password = '';
	for ($i = 0; $i < $length; $i++) {
		// 这里提供两种字符获取方式
		// 第一种是使用 substr 截取$chars中的任意一位字符；
		// 第二种是取字符数组 $chars 的任意元素
		// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
		$password .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	return $password;
}

function getConfig($field) {
	return Db::name('config')->where(['name' => $field])->value('value');
}

/**
 * 返回订单号
 * @param int $paywhere ,1:商户提现
 * @param int $uid
 * @return string
 */
function createOrderNo($paywhere, $uid) {
	if ($paywhere == 1) {
		//商家提币
		$kait = 'M';
	} elseif ($paywhere == 2) {
		//用户提币
		$kait = 'U';
	} elseif ($paywhere == 3) {
		$kait = 'S';
	} elseif ($paywhere == 4) {
		$kait = 'T';
	} else {
		$kait = 'O';
	}//M22768T3085073605350PS198
	return $kait . $uid . 'T' . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . 'P' . 'S' . sprintf(rand(100, 999));
}

function curl_post($url, $post_data = []) {
	$ch = curl_init();
	//禁用https
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	//允许请求以文件流的形式返回
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_URL, $url);
	$resp = curl_exec($ch); //执行发送
	curl_close($ch);
	return $resp;
}

function curl_get($url, $ttl = 5) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	//参数为1表示传输数据，为0表示直接输出显示。
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//参数为0表示不带头文件，为1表示带头文件
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $ttl);
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, $ttl);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

function getTotalInfo($where, $table, $field) {
	$sum = Db::name($table)->where($where)->sum($field);
	return $sum ? $sum : 0;
}

function tradenoa() {
	return substr(str_shuffle('ABCDEFGHIJKLMNPQRSTUVWXYZ'), 0, 6);
}

function agentReward($uid, $duid, $amount, $type, $relateOrderId = '') {
	$rs    = [];
	$rs[0] = balanceChange(FALSE, $uid, $amount, 0, 0, 0, BAL_COMMISSION, $relateOrderId);
	$rs[1] = Db::name('agent_reward')->insert(['uid' => $uid, 'duid' => $duid, 'amount' => $amount, 'type' => $type, 'create_time' => time()]);
	return $rs;
}

function apilog($uid, $duid, $api_name, $request_param, $return_param) {
	Db::name('merchant_apilog')->insert(['uid' => $uid, 'duid' => $duid, 'api_name' => $api_name, 'request_param' => $request_param, 'return_param' => $return_param, 'create_time' => time()]);
}

function getUsdtPrice($ignore = FALSE, $ttl = 5) {
	if(APP_DEBUG) return 7;
	$price = Cache::get('usdt_price');
	if (!$price) {
		$url  = $ignore ? 'https://otc-api.huobi.pro/v1/data/market/detail' : 'https://otc-api.hbg.com/v1/data/market/detail';
		$data = curl_get($url, $ttl);//获取火币价格
		$res  = json_decode($data, TRUE);
		//$sellPrice = $data_arr['data']['detail'][2]['sell'];
		$price = isset($res['success']) &&  $res['success'] == TRUE ? $res['data']['detail'][2]['buy'] : ($ignore ? 7.00 : getUsdtPrice(TRUE));
		($price != 7.00) && Cache::set('usdt_price', $price, 600);
	}
	return $price;
}

function getUsdtPrice_old() {
	/*$v = 'tether';
	$jiekou = "https://api.coinmarketcap.com/v1/ticker/".$v."/?convert=CNY";
	$url=@file_get_contents($jiekou);
	$biarr=json_decode($url,true);
	return $biarr[0]['price_cny'];*/
	$data     = curl_get('https://otc-api.huobi.co/v1/data/market/detail');
	$price    = 0;
	$data_arr = json_decode($data, TRUE);
	if ($data_arr['code'] == 200) {
		$coin_arr = $data_arr['data']['detail'];
		foreach ($coin_arr as $k => $v) {
			if ($v['coinName'] == 'USDT') {
				$price = $v['sell'];
				break;
			}
		}
	}
	return $price;
}

function sendSms($mobile, $content) {
	if (!config('is_send_sms')) {
		return TRUE;
	}
	$url        = Db::name('config')->where('name', 'mobile_url')->value('value');
	$user       = Db::name('config')->where('name', 'mobile_user')->value('value');
	$key        = Db::name('config')->where('name', 'mobile_pwd')->value('value');
	$title      = Db::name('config')->where('name', 'web_site_title')->value('value');
	$content    = '【THT】' . $content;
	$params     = "appid=$user&to=$mobile&content=$content&signature=$key";
	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_POST, 1);
	curl_setopt($curlHandle, CURLOPT_URL, $url);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);
	$resp = curl_exec($curlHandle);
	curl_close($curlHandle);
	return $resp;
}

function sendNotice($userId, $title, $msg) {
	$user = Db::name('merchant')->where($userId)->find();
	if ($user && isset($user['device'])) {
		Db::name('msg')->insert(['device_id' => $user['device'], 'title' => $title, 'msg' => $msg]);
	}
}

function askNotify($data, $url, $key) {
	ksort($data);
	$serverStr = '';
	foreach ($data as $k => $v) {
		$serverStr = $serverStr . $k . $v;
	}
	$reserverStr  = $serverStr . $key;
	$sign         = strtoupper(sha1($reserverStr));
	$data['sign'] = $sign;
	$return       = curl_post($url, $data);
	file_put_contents(RUNTIME_PATH . 'data/notify_' . date('ymd') . '.log', '【' . date('Y-m-d H:i:s', time()) . '】【URL】' . $url . '【返回】' . $return . ',【请求】' . json_encode($data, 320) . PHP_EOL, FILE_APPEND);
}

function go_mobile() {
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if (strpos($agent, "comFront") || strpos($agent, "iPhone") || strpos($agent, "MIDP-2.0") || strpos($agent, "Opera Mini") || strpos($agent, "UCWEB") || strpos($agent, "Android") || strpos($agent, "Windows Phone") || strpos($agent, "Windows CE") || strpos($agent, "SymbianOS")) {
		return TRUE;
	}
	return FALSE;
}

function checkName($name) {
	$ret = TRUE;
	//中文+身份证允许有.
	if (!preg_match('/^[\x{4e00}-\x{9fa5}]+[·?]?[\x{4e00}-\x{9fa5}]+$/u', $name)) {
		return FALSE;
	}
	$strLen = mb_strlen($name, "utf-8");
	if ($strLen < 2 || $strLen > 8) {//字符长度2到8之间
		return FALSE;
	}
	return $ret;
}

function financeLog($uid, $amount, $note, $status, $op) {
	$user = Db::name('merchant')->where('id', $uid)->find();
	$rs   = Db::name('financelog')->insert(['uid' => $uid, 'user' => $user['name'], 'note' => $note, 'amount' => $amount, 'status' => $status, 'add_time' => time(), 'op' => $op]);
	return $rs ? $rs : '记录失败';
}

function getMoneyByLevel($totalFee, $platform, $sellerAgent, $buyerAgent, $sellerMoney) {
	if ($platform >= $totalFee) {
		return [$totalFee, 0, 0, 0];
	}
	if ($platform + $sellerAgent >= $totalFee) {
		return [$platform, $totalFee - $platform, 0, 0];
	}
	if ($platform + $sellerAgent + $buyerAgent >= $totalFee) {
		return [$platform, $sellerAgent, $totalFee - $platform - $sellerAgent, 0];
	}
	if ($platform + $sellerAgent + $buyerAgent + $sellerMoney >= $totalFee) {
		return [$platform, $sellerAgent, $buyerAgent, $totalFee - $platform - $sellerAgent - $buyerAgent];
	}
	return [$totalFee - $sellerAgent - $buyerAgent - $sellerMoney, $sellerAgent, $buyerAgent, $sellerMoney];
}

function getStatisticsOfOrder($buyerid, $sellerid, $buyamount, $sellamount) {
	Db::name('merchant')->where('id', $sellerid)->update(['order_sell_success_num' => Db::raw('order_sell_success_num + 1'), 'order_sell_usdt_amount' => Db::raw('order_sell_usdt_amount + ' . $sellamount)]);
	Db::name('merchant')->where('id', $buyerid)->update(['order_buy_success_num' => Db::raw('order_buy_success_num + 1'), 'order_buy_usdt_amount' => Db::raw('order_buy_usdt_amount + ' . $buyamount)]);
}

function showMsg($msg = '', $code = 0, $data = [], $url = '#') {
	header('Content-Type:application/json; charset=utf-8');
	die(json_encode(['code' => $code, 'msg' => $msg, 'data' => $data, 'url' => $url], 320));
}

function showJson($data = [], $code = 200, $header = [], $options = []) {
	header('Content-Type:application/json; charset=utf-8');
	header('HTTP/2.0 ' . $code);
	die(json_encode($data, 320));
}

/**
 * 变动金额
 * @param bool   $enableTrans
 * @param int    $uid
 * @param float  $modAmt
 * @param float  $fee
 * @param float  $frozenModAmt
 * @param float  $frozenFee
 * @param int    $actType
 * @param string $relateId
 * @param string $memo
 * @return bool|string
 * @throws \think\Exception
 * @throws DataNotFoundException
 * @throws ModelNotFoundException
 * @throws DbException
 * @throws \think\exception\PDOException
 */
function balanceChange($enableTrans = TRUE, $uid = 0, float $modAmt = 0, float $fee = 0, float $frozenModAmt = 0, float $frozenFee = 0, $actType = 0, $relateId = '', $memo = '') {
	if ($modAmt == 0 && $frozenModAmt == 0) {
		return TRUE;
	}
	$enableTrans && Db::startTrans();
	$userInfo = Db::name('merchant')->where('id', $uid)->lock(TRUE)->find();
	if (!$userInfo) {
		$enableTrans && Db::rollback();
		return FALSE;
	}
	// 格式化数字
	$modAmt       = (float)number_format($modAmt, 8, '.', '');
	$fee          = (float)number_format($fee, 8, '.', '');
	$frozenModAmt = (float)number_format($frozenModAmt, 8, '.', '');
	$frozenFee    = (float)number_format($frozenFee, 8, '.', '');
	// 溯源
	$lastLogId = 0;
	$lastLog   = Db::name('merchant_balance_log')->order('bal_log_id DESC')->find();
	// TODO 如果记录存在, 更新记录
	if ($lastLog) {
		$lastLogId = $lastLog['bal_log_id'];
		if ($lastLog['amt_after'] != $userInfo['usdt'] || $lastLog['frozen_amt_after'] != $userInfo['usdtd']) {
			$log = $msg = '【' . date('Y-m-d H:i:s') . "】 资金记录异常: 用户ID: 【 $uid 】, 前记录ID: $lastLogId , 余额" . $lastLog['amt_after'] . ', 冻结:  ' . $lastLog['frozen_amt_after'] . ', 账户现有: 余额: ' . $userInfo['usdt'] . ', 冻结:' . $userInfo['usdt'] . " \r\n";
			file_put_contents(RUNTIME_PATH . 'data/abnormal_flow.log', $log, FILE_APPEND);
		}
	}
	$newLog = [
		'last_bal_log_id'   => $lastLogId,
		'merchant_id'       => $uid,
		'amt_before'        => $userInfo['usdt'],
		'amt_after'         => $userInfo['usdt'] + $modAmt,
		'amt_change'        => $modAmt,
		'amt_fee'           => $fee,
		'frozen_amt_before' => $userInfo['usdtd'],
		'frozen_amt_after'  => $userInfo['usdtd'] + $frozenModAmt,
		'frozen_amt_change' => $frozenModAmt,
		'frozen_amt_fee'    => $frozenFee,
		'cur_code'          => 'usdt',
		'action_type'       => $actType,
		'relate_id'         => $relateId,
		'memo'              => $memo,
	];
	$res1   = Db::name('merchant')->where('id', $uid)->update(['usdt' => $newLog['amt_after'], 'usdtd' => $newLog['frozen_amt_after']]);
	$res2   = Db::name('merchant_balance_log')->insert($newLog);
	if ($res1 && $res2) {
		$enableTrans && Db::commit();
		return TRUE;
	}
	$enableTrans && Db::rollback();
	return FALSE;
}

/**
 * * 修改金额, 直接填入输入后的金额
 * @param bool   $enableTrans    是否启动事务
 * @param int    $uid            用户ID
 * @param float  $afterAmt       变动后金额
 * @param float  $fee            手续费
 * @param float  $afterFrozenAmt 变动后冻结金额
 * @param float  $frozenFee
 * @param int    $actType
 * @param string $relateId       对应订单ID
 * @param string $memo           备注
 * @return bool|string
 * @throws \think\Exception
 * @throws DataNotFoundException
 * @throws ModelNotFoundException
 * @throws DbException
 * @throws \think\exception\PDOException
 */
function balanceMod($enableTrans = TRUE, $uid = 0, float $afterAmt = 0, float $fee = 0, float $afterFrozenAmt = 0, float $frozenFee = 0, $actType = 0, $relateId = '', $memo = '') {
	$enableTrans && Db::startTrans();
	$userInfo = Db::name('merchant')->where('id', $uid)->lock(TRUE)->find();
	if (!$userInfo) {
		$enableTrans && Db::rollback();
		return FALSE;
	}
	// 格式化数字,保留8位小数
	$afterAmt       = (float)number_format($afterAmt, 8, '.', '');
	$afterFrozenAmt = (float)number_format($afterFrozenAmt, 8, '.', '');
	if ($afterAmt == $userInfo['usdt'] && $afterFrozenAmt == $userInfo['usdtd']) {
		$enableTrans && Db::rollback();
		return TRUE;
	}
	$modAmt       = $afterAmt - $userInfo['usdt'];
	$frozenModAmt = $afterFrozenAmt - $userInfo['usdtd'];
	// 溯源
	$lastLogId = 0;
	$lastLog   = Db::name('merchant_balance_log')->order('bal_log_id DESC')->find();
	// TODO 如果记录存在, 更新记录
	if ($lastLog) {
		$lastLogId = $lastLog['bal_log_id'];
		if ($lastLog['amt_after'] != $userInfo['usdt'] || $lastLog['frozen_amt_after'] != $userInfo['usdtd']) {
			$log = $msg = '【' . date('Y-m-d H:i:s') . "】 资金记录异常: 用户ID: 【 $uid 】, 前记录ID: $lastLogId , 余额" . $lastLog['amt_after'] . ', 冻结:  ' . $lastLog['frozen_amt_after'] . ', 账户现有: 余额: ' . $userInfo['usdt'] . ', 冻结:' . $userInfo['usdt'] . " \r\n";
			file_put_contents(RUNTIME_PATH . 'data/abnormal_flow.log', $log, FILE_APPEND);
		}
	}
	$newLog = [
		'last_bal_log_id'   => $lastLogId,
		'merchant_id'       => $uid,
		'amt_before'        => $userInfo['usdt'],
		'amt_after'         => $afterAmt,
		'amt_change'        => $modAmt,
		'amt_fee'           => $fee,
		'frozen_amt_before' => $userInfo['usdtd'],
		'frozen_amt_after'  => $afterFrozenAmt,
		'frozen_amt_change' => $frozenModAmt,
		'frozen_amt_fee'    => $frozenFee,
		'cur_code'          => 'usdt',
		'action_type'       => $actType,
		'relate_id'         => $relateId,
		'memo'              => $memo,
	];
	$res1   = Db::name('merchant')->where('id', $uid)->update(['usdt' => $newLog['amt_after'], 'usdtd' => $newLog['frozen_amt_after']]);
	$res2   = Db::name('merchant_balance_log')->insert($newLog);
	if ($res1 && $res2) {
		$enableTrans && Db::commit();
		return TRUE;
	}
	$enableTrans && Db::rollback();
	return FALSE;
}

function getIp() {
	//strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
	if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$res = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
	return $res;
}

// 获取最上层所赋予的卖单利润
function getTopAgentFeeRate($uid, $timeout = 900) {
	$pidRate = Cache::get('pid_rate_' . $uid);
	if (!$pidRate) {
		$userModel = Db::name('merchant');
		$user      = $userModel->where('id', $uid)->find();
		// 非承兑商不算利
		if (!$user || $user['pid'] < 1 || $user['reg_type'] != 2) {
			$pidRate = Db::name('config')->where('name', 'usdt_price_add')->value('value');
		} else {
			$firstParent = $userModel->where('id', $user['pid'])->find();
			// 上级不存在
			if (!$firstParent) {
				$pidRate = Db::name('config')->where('name', 'usdt_price_add')->value('value');
			} elseif ($firstParent['pid'] < 1) {
				$pidRate = $firstParent['tariff'];
			} else {
				// 上上级别
				$secondParent = $userModel->where('id', $firstParent['pid'])->find();
				if (!$secondParent || $secondParent['pid'] < 1) {
					$pidRate = $firstParent['tariff'];
				} else {
					// 上上上级别
					$thirdParent = $userModel->where('id', $secondParent['pid'])->find();
					$pidRate     = $thirdParent ? $thirdParent['tariff'] : $secondParent['tariff'];
				}
			}
		}
		$pidRate = $pidRate / 100;
		Cache::set('pid_' . $uid, $pidRate, $timeout);
	}
	return $pidRate;
}

function generateVCode($uid){
	$vcode = randString(8);
	$mchModel = Db::name('merchant');
	if(!$mchModel->where('vcode', $vcode)->find()){
		$mchModel->where('id', $uid)->update(['vcode'=> $vcode]);
		return $vcode;
	}
	return generateVCode($uid);
}
function randString(int $len = 16, string $char = '') {
	$c       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$char    = $char == '' ? $c : $char;
	$charLen = strlen($char);
	$str     = '';
	for ($i = 0; $i < $len; $i++) {
		$str .= $char[rand(0, $charLen - 1)];
	}
	return $str;
}