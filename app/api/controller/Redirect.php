<?php
namespace app\api\controller;

class Redirect extends Base {
	public function go($url) {
		die('<!DOCTYPE html><html lang="en"><head></head><body><script>window.location.href=\'' . base64_decode($url) . '\';</script></body></html>');
	}

	// alipays://platformapi/startapp?appId=20000067&url=http://zpay.cc/qr/{s}
	public function qr($s) {
		(preg_match('/AlipayDefined[^\n]+AliApp[^\n]+AlipayClient/', $_SERVER['HTTP_USER_AGENT']) !== 1) && die;
		[$time, $uid, $acc, $amt, $memo, $type] = explode('|', AesDecrypt($s));
		$time < (time() - (config('order_expire') * 60)) && die('订单超时, 请重新发起');
		(strlen($uid) != 16 || (!preg_match('/^1[3456789]\d+$/', $acc) && !preg_match('/^\w+((.\w+)|(-\w+))@[A-Za-z0-9]+((.|-)[A-Za-z0-9]+).[A-Za-z0-9]+$/', $acc))) && die;
		// 转账码
		$html = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<meta charset="utf-8"/>
<meta content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/>
<title>正在进入支付…</title>
</head>
<body>
<script>
window.onload=function(){
	window.location.href="alipays://platformapi/startapp?appId=20000186&actionType=addfriend&source=by_f_v&alert=false&userId=$uid&loginId=$acc";
	setTimeout(function(){
		window.location.href="alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&memo=$memo&amount=$amt&userId=$uid"
	},588);
}
</script>
</body>
</html>
EOT;
// 红包码
		if($type == 1){
			$html = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate" />
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<meta charset="utf-8"/>
<meta content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
<title>正在进入支付…</title>
</head>
<body>
<script>
window.onload=function(){
  window.location.href="alipays://platformapi/startapp?appId=20000186&actionType=addfriend&source=by_f_v&alert=false&userId=$uid&loginId=$acc";
setTimeout(function(){
  window.location.href = "alipays://platformapi/startapp?appId=20000167&forceRequest=0&returnAppId=recent&tUnreadCount=0&tUserType=1&tUserId=$uid&tLoginId=$acc";},588);
setTimeout(function(){
  window.location.href = "alipays://platformapi/startapp?appId=88886666&appLaunchMode=3&canSearch=false&chatUserName=x&chatUserType=1&entryMode=personalStage&prevBiz=chat&schemaMode=portalInside&target=personal&money=$amt&amount=$amt&remark=$memo&chatUserId=$uid&chatLoginId=$acc"},1188);
}
</script>
</body>
</html>
EOT;
		}
		die($html);
	}
}