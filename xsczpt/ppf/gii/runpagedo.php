<?php
/**后台自动化处理  post处理 
*lren-zhs 2013-6-16 10:01
*/ 
require('../pdo_dbpage1.php');
#检测登录
session_start(); 
chkLoginNoJump("giiuid");

if(!isset($_POST["l_tpl"])){echo "Err101:POST DATA IS NULL";exit();}  
/*二级编码发送
$data=$_POST["data"]; 
$data=base64_decode($data);
$data=str_replace('\"','"',$data); #linux json解码用
$d=json_decode($data);
if(!isset($d)){
	echo "json object is null";
	exit;
}*/ 
/*二级对象直播发送
if(!isset($_POST["data"])){echo "Err101:POST DATA IS NULL";exit();}
$data=$_POST["data"]; 
echo (isset($data["a"]));
*/ 

$db=new pdo_dbpage1($_POST["l_tpl"]);
switch ($_POST["l_do"]) {
	case 'page':
		$db->ByPage();
		break;
	case "del":	
		$db->Del();
		break; 
}	
$db->close();
unset($db);
unset($d);