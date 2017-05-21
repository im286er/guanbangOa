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
  $data["uid"]=base64_encode($_SESSION["uid"]);
  if(!is_array($data)){echo "err:data is null";exit;}
  $data["timestamp"]=base64_encode(time());
}

switch($do){	
  case "r":       
    if($tpl=="home")
		  $db->Read($_SESSION["uid"]);
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
  case "d":
    $db->Del();
    break; 
  case "o":
    $db->Audit();
    break;  
}

$db->close();
unset($db);
unset($data);