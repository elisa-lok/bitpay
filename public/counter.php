<?php
if ($_POST) {
	$data   = $_POST;
	$reqUrl = $data['req_url'];
	unset($data['req_url']);

	$appKey = $data['appkey'];
	unset($data['appkey']);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	//允许请求以文件流的形式返回
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_URL, $url);
	$res = curl_exec($ch); //执行发送
	curl_close($ch);
	die($res);
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
						<label>APPID *</label> <input type="text" name="appid" class="form-control" placeholder="" required="required">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label>APPKEY *</label> <input type="text" name="appkey" class="form-control" placeholder="">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="form-group">
					<input type="hidden" name="type" class="form-control" value="all">
					<div class="help-block with-errors"></div>
				</div>
			</div>
		</div>
		<div class="controls">
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<label>同步回调地址 *</label> <input type="text" name="return_url" class="form-control" placeholder="" required="required">
						<div class="help-block with-errors"></div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label>异步回调地址 *</label> <input type="text" name="notify_url" class="form-control" placeholder="" required="required">
						<div class="help-block with-errors"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<button type="button" class="btn btn-warning btn-send">Go</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<p class="text-muted"><strong>*</strong> These fields are required.</p>
			</div>
		</div>
		<div class="messages"></div>
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
				if (res.code === 200) {
					$(".confirm-warning").addClass("text-success").html('确认成功,稍后将关闭网页')
					closeMe();
				} else {
					console.log(res);
					$(".confirm-warning").addClass("text-danger").html('未知錯誤')
				}
			}, error: function (err) {
				console.log(err);
				if (err.responseJSON.msg === '') {
					$(".confirm-warning").addClass("text-danger").html('未知錯誤')
				} else {
					$(".confirm-warning").addClass("text-danger").html(err.responseJSON.msg)
				}
			}
		});
	}
</script>
</body>
</html>