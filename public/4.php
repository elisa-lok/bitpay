<?php
$msg=file_get_contents("./data/".$_GET['orderid'].".txt");
$pay_orderid=$_GET['orderid'];
if(!$msg){
    $msg='等待放行中.';
}
// var_dump($msg);die;
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
            <meta http-equiv="refresh" content="10">
            <h1 class="mui-title">收银台测试</h1>
        </header>
        <div class="mui-content">
            <div class="mui-content-padded" style="margin: 5px;">
                    <form action="" class="mui-input-group" method="post" autocomplete="off">
                        <input type="hidden" name="orderid" value="<?php echo $pay_orderid;?>">
                    <div class="mui-input-row">
                         <label>订单号:</label>
                        <input type="number" class="mui-input" placeholder="<?php echo $pay_orderid;?>"  readonly="">
                    </div>

                    <div class="mui-input-row">
                         <label>充值结果:</label>
                        <input type="text"  class="mui-input" placeholder="" value="<?php echo $msg;?>" readonly="" style="font-size: 1rem;">
                    </div>
                    <div class="mui-button-row">
                        <button  class="mui-btn mui-btn-primary" type="button">确认</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
