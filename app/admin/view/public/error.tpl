<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover minimal-ui">
    <title>提示</title>
    <script src="https://cdn.staticfile.org/sweetalert/2.1.2/sweetalert.min.js"></script>
    <style>html{background-image:radial-gradient(#90F7EC, #32CCBC); background-attachment: fixed; background-size: cover; background-repeat: repeat; background-position: center center;}</style>
</head>
<body>
<script type="text/javascript">
	swal("\n",{title : "<?php echo $msg??$message; ?>", buttons: false, icon : "error"});
	<?php if(isset($url)){ echo 'setTimeout(function () {location.href ="'.$url.'";}, 3000);';} ?>
</script>
</body>
</html>
