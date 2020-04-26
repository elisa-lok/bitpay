<?php
namespace app\api\controller;
use think\Controller;

class Redirect extends Controller {
	public function go($url) {
		die('<!DOCTYPE html><html lang="en"><head></head><body><script>window.location.href=\'' . base64_decode($url) . '\';</script></body></html>');
	}

	public function qr() {
		(preg_match('/AlipayDefined[^Ali]+AliApp[^\n]+AlipayClient/', $_SERVER['HTTP_USER_AGENT']) !== 1) && die;
		$uid = '2088002004038885';
		$acc = 'linvasean@gmail.com';
		$amt = '0.1';
		$memo = '';
		$html = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate"/><meta http-equiv="Pragma" content="no-cache"/><meta http-equiv="Expires" content="0"/><meta charset="utf-8"/><meta content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/><title>正在进入支付…</title></head><body><script>window.onload=function(){window.location.href="alipays://platformapi/startapp?appId=20000186&actionType=addfriend&userId='.$uid.'&loginId='.$acc.'&source=by_f_v&alert=false";setTimeout(function(){window.location.href="alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount=&userId=$uid&memo="},888)}</script></body></html>';
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
		die($html);
	}
}