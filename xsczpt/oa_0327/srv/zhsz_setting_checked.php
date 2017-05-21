<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
date_default_timezone_set("PRC");
$pd=new pdo_mysql();

switch($_POST["tpl"]){
	case "set_checked":
	    //$title=$pd->query("select fea_name from oa_features where id=".$_POST["id"]);
		$rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu1,menu2,subject) values('".$_POST["uid"]."','".$_POST["geter"]."','".$_POST["content"]."','".date('Y-m-d H:i:s')."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["subject"]."')");
		$cont=$pd->query("update oa_zhsz_report_self SET flag_status='未通过' where id=".$_POST["id"]." ");
		//$rs=$pd->query("select count(*) from message where is_read=0 and `uid`='".$_POST["uid"]."'")->fetchColumn(0);
		echo "ok";
		break;
	case "set_tg":
	    //$title=$pd->query("select fea_name from oa_features where id=".$_POST["id"]);
		$rs=$pd->query("insert into oa_message(uid,geter,stime,is_read,flag,menu1,menu2,subject) values('".$_POST["uid"]."','".$_POST["geter"]."','".date('Y-m-d H:i:s')."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["subject"]."')");
		$cont=$pd->query("update oa_zhsz_report_self SET flag_status='已审核' where id=".$_POST["id"]." ");
		//$rs=$pd->query("select count(*) from message where is_read=0 and `uid`='".$_POST["uid"]."'")->fetchColumn(0);
		echo "ok";
		break;

}		
$pd->close();
unset($pd);
unset($rs);