<?php
#清除缓存
header("Content-type: text/html; charset=utf-8"); 
session_start(); 
if(empty($_SESSION["uid"])){
	echo "登录已超时";
	exit();
}

#登录
$ty=isset($_POST["t"])?$_POST["t"]:"";

if(empty($ty)){
	echo "参数为空";
	exit();
}
switch($ty){
	case "index":
		if(file_exists("../../cache/index"))
			unlink("../../cache/index");
	break;
	case "cate":
		if(file_exists("../../cache/"))
			array_map('unlink',glob('../../cache/*.c'));
	break;
	case "all":	
		if(file_exists("../../cache/"))
			array_map('unlink',glob('../../cache/*'));
	break;
}
echo "成功";
?>