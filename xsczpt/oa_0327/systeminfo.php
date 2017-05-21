<?php
	//判断用户是否登录
	session_start();
	if(empty($_SESSION['admin_name'])){
		header('Location:index.php');
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/css.css" rel="stylesheet" type="text/css" />
<title>系统信息</title>
</head>

<body>
	<div class="main">
	<!--介绍 begin-->
		<div class="box2">
				<div class="abouttittle">系统概述</div>
				<div class="aboutbox">“综合素质在线评价平台”基于PHP+MySQL的技术架构，完全开源加上强大稳定的技术架构。<br />
				</div>
				<div class="abouttittle">系统信息</div>
				<div class="aboutbox">
				<ul>
				<li><span>程序版本</span>v1.0.0 Releas</li>
				<li><span>操作系统及PHP</span><?php echo PHP_OS,'/','PHP',phpversion(); ?></li>
				<li><span>MySQL版本</span><?php echo 'MySQL',mysql_get_client_info();?></li>
				<li><span>最大上传文件</span><?php echo ini_get('upload_max_filesize'); ?></li>
				<li><span>物理路径</span><?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
				</ul>
				</div>
		</div>
	<!--介绍 end-->
	</div>
</body>
</html>
