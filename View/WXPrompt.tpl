<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title>消息提示</title>
    <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入 WeUI -->
    <link rel="stylesheet" href="https://res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css" />
    <link rel="stylesheet" href="__PUBLIC__/assets/css/weMain.css?version=201709012" />
</head>
<body>
    <div class="container" id="container">
<div class="page js_show">
    <div class="weui-msg">
    	<div class="weui-msg__icon-area">
    	<?php if(isset($message)) {?>
        <i class="weui-icon-success weui-icon_msg"></i></div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title"><?php echo($message); ?></h2>
		<?php }else{?>
        <i class="weui-icon-warn weui-icon_msg"></i></div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title"><?php echo($error); ?></h2>
		<?php }?>		
            <p class="weui-msg__desc">如果您的浏览器没有自动跳转，<a id="href" href="<?php echo($jumpUrl); ?>">请点击此链接</a></p>
        </div>
		<div style='display: none'><b id="wait"><?php echo($waitSecond); ?></b></div>
    </div>
</div>
</div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time <= 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>
