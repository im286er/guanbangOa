<?php
/**前端自动化模板  post处理
*lren-zhs 2013-6-16 10:01
*/  
require('../ppf/pdo_frtdo.php');

session_start(); 
chkLoginNoJump("uid");

$tpl=$_POST["tpl"]; 

if(!isset($tpl)){echo "err:tpl is null";exit;} 
$do=$_POST["do"];
$db=new pdo_frtdo($tpl);

if($do=="a"||$do=="m"||$do=="am"){  
	$data=$_POST["data"]; #获取数组
	$data["uid"]=base64_encode($_SESSION["uid"]);
	if(!is_array($data)){echo "err:data is null";exit;}
	$data["timestamp"]=base64_encode(time());
	$data["created"]=base64_encode(date('Y-m-d H:i',time()));
	$tpl1=$_POST["tpl1"];
	//var_dump($data);exit;
	if($tpl1 == "push"){
		$db1=new pdo_frtdo($tpl1);
		
		$data["push_homepage"] = base64_decode($data["push_homepage"]);
		$data["push_group"] = base64_decode($data["push_group"]);
		$data["push_agency"] = base64_decode($data["push_agency"]);
		$data["push_cls"] = base64_decode($data["push_cls"]);
		$data["push_studio"] = base64_decode($data["push_studio"]);
		$cid_tmp = $data["cid"];
		//var_dump($data);exit;
		if($data["push_homepage"]==1){
			$data["push_type"] = base64_encode(1);
			$data["cid"] = $data["cid1"];
			$db1->Insert($data);
		}
		if($data["push_group"]==1){
			$data["push_type"] = base64_encode(2);
			$data["cid"] = $data["cid2"];
			$db1->Insert($data);
		}
		if($data["push_agency"]==1){
			$data["push_type"] = base64_encode(3);
			$data["cid"] = $data["cid3"];
			$db1->Insert($data);
		}
		if($data["push_cls"]==1){
			$data["push_type"] = base64_encode(4);
			$data["cid"] = $data["cid4"];
			$db1->Insert($data);
		}
		if($data["push_studio"]==1){
			$data["push_type"] = base64_encode(5);
			$data["cid"] = $data["cid5"];
			$db1->Insert($data);
		}
		$data["cid"] = $cid_tmp;
		$db1->close();
		unset($db1);
	}
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
		$db->Del();
		break; 
	case "o":
		$db->Audit();
		break;
}

$db->close();
unset($db);
unset($data);