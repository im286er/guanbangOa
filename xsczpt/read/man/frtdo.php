<?php
/**前端自动化模板  post处理
*lren-zhs 2013-6-16 10:01
*/  
require('../../ppf/pdo_frtdo.php');;

if (!session_id()) session_start(); 
chkLoginNoJump("uid");

$tpl=$_POST["tpl"]; 
if(!isset($tpl)){echo "err:tpl is null";exit;} 
$do=$_POST["do"];
$db=new pdo_frtdo($tpl);


if($do=="a"||$do=="am"){  
  $data=$_POST["data"]; #获取数组
  $data["uid"]=base64_encode($_SESSION["uid"]);
  $data["created"]=base64_encode(date('Y-m-d H:i',time()));
  if(!is_array($data)){echo "err:data is null";exit;}
  $data["timestamp"]=base64_encode(time());
}else if($do=="m"){
  $data=$_POST["data"]; #获取数组
  $data["created"]=base64_encode(date('Y-m-d H:i',time()));
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
  case "d":
    $db->Del($_POST["id"]);
    break;

}

$db->close();
unset($db);
unset($data);