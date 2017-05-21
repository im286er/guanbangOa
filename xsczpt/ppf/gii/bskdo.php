<?php
/**后台自动化模板  post处理
*lren-zhs 2013-6-16 10:01
*/  
require('../pdo_bskdo.php');;
#检测登录
session_start(); 
chkLoginNoJump("giiuid");

$tpl=$_POST["tpl"]; 
if(!isset($tpl)){echo "err:tpl is null";exit;} 
$do=$_POST["do"];
$db=new pdo_bskdo($tpl);
 
if(in_array($do,array("a","m","am"))){  
$data=$_POST["data"]; #获取数组
if(!is_array($data)){echo "err:data is null";exit;}
$data["timestamp"]=base64_encode(time());
}

switch($do){
	case "r":
		$db->Read($_POST["id"]);
		break;
	case "a": 
		$db->Insert($data);
		break;
	 case "m":	  
		$db->Save($data);
		break; 
	case "am": 
		$db->SaveInsert($data);
		break; 
}

$db->close();
unset($db);
unset($data);