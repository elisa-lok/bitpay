<?php
$pay_orderid = 'E'.date("YmdHis").rand(100000,999999);    //订单号
$user = '1380'.rand(1000000,9999999);    //订单号
$amount = rand(10,999);    //订单号
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title></title>
        <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">

        <!--标准mui.css-->
        <link rel="stylesheet" href="/mui.min.css">
        <!--App自定义的css-->
        <!-- <link rel="stylesheet" type="text/css" href="../css/app.css" /> -->
        <style>
            h5 {
                margin: 5px 7px;
            }
        </style>
    </head>

    <body>
        <header class="mui-bar mui-bar-nav">
            <h1 class="mui-title">收银台测试</h1>
        </header>
        <div class="mui-content">
            <div class="mui-content-padded" style="margin: 5px;">
                    <form action="2.php/rechargeRmb" class="mui-input-group" method="post" autocomplete="off">
                        <input type="hidden" name="orderid" value="<?php echo $pay_orderid;?>">
                    <div class="mui-input-row">
                         <label>订单号:</label>
                        <input type="number" class="mui-input" placeholder="<?php echo $pay_orderid;?>"  readonly="">
                    </div>
                    <div class="mui-input-row">
                         <label>金额(元):</label>
                        <input type="number" name="amount" class="mui-input-clear" placeholder="输入充值金额" value="<?php echo $amount;?>">
                    </div>
                    <div class="mui-input-row">
                         <label>用户名:</label>
                        <input type="text" class="mui-input" placeholder="输入充值用户名" name="user"  value="<?php echo $user;?>">
                    </div>
                    <div class="mui-input-row">
                         <label>充值货币:</label>
                        <input type="number" class="mui-input" placeholder="USDT"  readonly="">
                    </div>
                    <div class="mui-input-row">
                         <label>商户号:</label>
                        <input type="text" name="appid" class="mui-input" value="QndLSfX9Q0uaVuak" >
                    </div>
                    <div class="mui-input-row">
                         <label>商户密钥:</label>
                        <input type="text" name="key" class="mui-input" placeholder="" value="24ccfcdb5d8f5b7a86e9df6348a20347" style="font-size: 1rem;">
                    </div>
                    <br>
                    <div class="mui-button-row">
                        <button  class="mui-btn mui-btn-primary" type="submit">提交</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" class="mui-btn mui-btn-danger" onclick="return false;">取消</button>
                    </div>
                </form>

            </div>
        </div>
        <script src="mui.min.js"></script>
        <script>
            mui.init({
                swipeBack: true //启用右滑关闭功能
            });
             //语音识别完成事件


            var nativeWebview, imm, InputMethodManager;
            var initNativeObjects = function() {
                if (mui.os.android) {
                    var main = plus.android.runtimeMainActivity();
                    var Context = plus.android.importClass("android.content.Context");
                    InputMethodManager = plus.android.importClass("android.view.inputmethod.InputMethodManager");
                    imm = main.getSystemService(Context.INPUT_METHOD_SERVICE);
                } else {
                    nativeWebview = plus.webview.currentWebview().nativeInstanceObject();
                }
            };
            var showSoftInput = function() {
                if (mui.os.android) {
                    imm.toggleSoftInput(0, InputMethodManager.SHOW_FORCED);
                } else {
                    nativeWebview.plusCallMethod({
                        "setKeyboardDisplayRequiresUserAction": false
                    });
                }
                setTimeout(function() {
                    var inputElem = document.querySelector('input');
                    inputElem.focus();
                    inputElem.parentNode.classList.add('mui-active'); //第一个是search，加上激活样式
                }, 200);
            };
            mui.plusReady(function() {
                initNativeObjects();
                showSoftInput();
            });

        </script>
    </body>

</html>
