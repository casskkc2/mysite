<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh" xml:lang="zh">
<head>
<title>首页</title>
<base href="" />
<meta content="text/html; charset=utf-8" http-equiv="content-type" />
<link rel="stylesheet" type="text/css" href="/citymanagement/trunk/citymng/Public/stylesheet/style.css" />
<script type="text/javascript" src="/citymanagement/trunk/citymng/Public/javascript/jquery/jquery.min.js"></script>

<link rel="stylesheet" href="/citymanagement/trunk/citymng/Public/javascript/jquery/jquery-easyui-1.4.2/themes/icon.css" />
<link rel="stylesheet" href="/citymanagement/trunk/citymng/Public/javascript/jquery/jquery-easyui-1.4.2/themes/default/easyui.css" />
<script type="text/javascript" src="/citymanagement/trunk/citymng/Public/javascript/jquery/jquery-easyui-1.4.2/jquery.easyui.min.js"></script>

<script type="text/javascript" src="/citymanagement/trunk/citymng/Public/javascript/jquery/browser.js"></script>
<script type="text/javascript">
$(document).ready(function() {

});
 
function getURLVar(urlVarName) {
	var urlHalves = String(document.location).toLowerCase().split('?');
	var urlVarValue = '';
	
	if (urlHalves[1]) {
		var urlVars = urlHalves[1].split('&');

		for (var i = 0; i <= (urlVars.length); i++) {
			if (urlVars[i]) {
				var urlVarPair = urlVars[i].split('=');
				
				if (urlVarPair[0] && urlVarPair[0] == urlVarName.toLowerCase()) {
					urlVarValue = urlVarPair[1];
				}
			}
		}
	}
	
	return urlVarValue;
} 

</script>
</head>
<body>
<div id="container">
	<div id="top">
		<div class="top_left">
			<marquee width="550px" height="28px" direction="left" scrollamount="3"           onmouseover="this.stop()" onmouseout="this.start()">
					公告：<span style="color:red">本网站正在开发中，敬请期待。</span>
			</marquee>
		</div>
		<div class="top_right">
			用户名：<input type="text" name="username" /> 密码：<input type="password" name="password" /> <input type="button" id="btn_login" value="登录" />
		</div>
	</div>
	<div id="content" class="home_content">
		<div class="home_title">数字综合管理平台</div>
	</div>
	<div id="footer">
	版权所有
	</div>
</div>
</body></html>