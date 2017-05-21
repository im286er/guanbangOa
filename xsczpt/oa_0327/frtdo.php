<?php
/**前端自动化模板  post处理
*lren-zhs 2013-6-16 10:01
*/  
require('../ppf/pdo_frtdo.php');;

session_start(); 
//chkLoginNoJump("uid");

$tpl=$_POST["tpl"]; 
if(!isset($tpl)){echo "err:tpl is null";exit;} 
$do=$_POST["do"];
$db=new pdo_frtdo($tpl);


if($do=="a"||$do=="m"||$do=="am"){  
  $data=$_POST["data"]; #获取数组
  $data["admin_id"]=base64_encode($_SESSION["admin_id"]);
  if(!is_array($data)){echo "err:data is null";exit;}
  $data["timestamp"]=base64_encode(time());
}

switch($do){	
  case "r":       
    if($tpl=="home")
		  $db->Read($_SESSION["admin_id"]);
    else  
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