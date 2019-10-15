<?php
if ($_POST) {
	ini_set('display_errors', '1');
	error_reporting(-1);

	$data   = $_POST;
	$reqUrl = $data['req_url'];
	unset($data['req_url']);

	$appKey = $data['appkey'];
	unset($data['appkey']);

	$data['sign'] = sign($data, $appKey);
	$ch              = curl_init($reqUrl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	//允许请求以文件流的形式返回
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
	$res = curl_exec($ch); //执行发送
	curl_close($ch);
	echo($res);die;
} else {
	$txId = 'E' . date("YmdHis") . rand(100000, 999999);    //订单号
	$user = '1380' . rand(1000000, 9999999);    //订单号
	$url  = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/api/merchant/requestTraderRechargeRmb';
}

function sign($dataArr, $key) {
	ksort($dataArr);
	$str = '';
	foreach ($dataArr as $k => $v) {
		$str .= $k . $v;
	}
	$str = $str . $key;
	return strtoupper(sha1($str));
}

?>
<!Doctype html>
<html>
<head>
	<title>Counter</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet" type="text/css" href="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
	<style></style>
</head>
<body>
<div class="container bg-light py-3">
	<form id="pay-form" method="post" action="" role="form">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label>请求地址</label> <input id="req_url" type="text" name="req_url" class="form-control" placeholder="Please enter request url" required="required" data-error="url is required." value="<?php echo $url; ?>">
					<div class="help-block with-errors"></div>
				</div>
			</div>
		</div>
		<div class="controls">
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						<label>订单号 *</label> <input type="text" name="orderid" class="form-control" placeholder="请输入事务id" required="required" data-error="name is required." value="<?php echo $txId; ?>" readonly>
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label>金额(元) *</label> <input type="number" name="amount" class="form-control" required="required" value="1000">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label>用户名 *</label> <input type="text" name="username" class="form-control" placeholder="Please enter username" value="<?php echo $user; ?>">
						<div class="help-block with-errors"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="controls">
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						<label>币种 *</label> <input type="text" class="form-control" required="required" value="USDT" readonly>
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label>APPID *</label> <input type="text" name="appid" class="form-control" placeholder="" required="required" value="2yMFyGPmo3tmhzXE">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label>APPKEY *</label> <input type="text" name="appkey" class="form-control" value="10ba8b016b722b6f0e906f5d5b052b31">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="form-group">
					<input type="hidden" name="type" class="form-control" value="all">
					<div class="help-block with-errors"></div>
				</div>
				<div class="form-group">
					<input type="hidden" name="address" class="form-control" value="">
					<div class="help-block with-errors"></div>
				</div>
			</div>
		</div>
		<div class="controls">
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<label>同步回调地址 *</label> <input type="text" name="return_url" class="form-control" placeholder="" required="required" value="http://baidu.com">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label>异步回调地址 *</label> <input type="text" name="notify_url" class="form-control" placeholder="" required="required" value="http://baidu.com">
						<div class="help-block with-errors"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="row messages col-md-6"></div>
		<div class="row">
			<div class="messages col-md-6"><a id="msg" href=""></a></div>
			<div class="col-md-6">
				<button type="button" class="btn btn-warning btn-send" onclick="requestPay()">Go</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p class="text-muted"><strong>*</strong> These fields are required.</p>
			</div>
		</div>

	</form>
</div>
<script src="https://cdn.staticfile.org/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.staticfile.org/jquery/3.4.1/jquery.min.js"></script>
<script>
	function requestPay() {
		$.ajax({
			type    : "POST",
			url     : '',
			data    : $('#pay-form').serialize(),
			dataType: "json",
			success : function (res) {
				console.log(res);
				if(res.status === 1){
					$("#msg").html("点我跳转").attr("href",res.data)
				}else{
					$("#msg").html("生成订单错误")
				}

			}, error: function (err) {
				console.log(err);
				$("#msg").html("生成订单错误")
			}
		});
	}
</script>
</body>
</html>