<?php
	if(!isset($_GET['gets'])||$_GET['gets']==""){
		$tips = array('url'=>'index.php','tips'=>'参数传递错误');
	}else{
		$tips = $_GET['gets'];
		$tips = unserialize(stripslashes(urldecode($tips)));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="5;url=<?php echo $tips['url']; ?>" />
<title>提示信息</title>
<style type="text/css">
*{margin:0; padding:0;}
body{font-family:"宋体"; font-size:12px;}
.wrong{width:600px;  margin:0 auto; padding-top:70px;font-size:2.5rem}
.wrong p{text-align:center;line-height:4rem;}
.NmainR{background:#fff; margin-top:25%;}
</style>
</head>
<body>
<div class="NmainR">
<div class="wrong">
	<p>
		<font color="ff0000" style><b><?php echo $tips['tips']; ?></b></font><br/>
		如果浏览器在5秒内没有返回, 点击这里 <a href="<?php echo $tips['url']; ?>" target="<?php echo empty($tips['target'])?'_self':$tips['target']; ?>" style="color:#0000FF; font-weight:bold;">返回 >></a>
	</p>
</div>
</div>
</body>
</html>
