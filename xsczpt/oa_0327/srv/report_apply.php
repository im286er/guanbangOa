<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
date_default_timezone_set("PRC");
$pd=new pdo_mysql();
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid']; 

switch($_POST["tpl"]){
	case "set_apply":
	    $rs=$pd->query("insert into oa_message(uid,geter,content,leaveword,stime,is_read,flag,menu1,menu2,subject,from_id) values('".$uid."','".$_POST["geter"]."',(select content from oa_zhsz_report_custom where id=".$_POST["id"]."),'".$_POST["content"]."','".date('Y-m-d H:i:s')."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["subject"]."','".$_POST["id"]."')");
		$cont=$pd->query("update oa_zhsz_report_custom SET re_submit=1 where id=".$_POST["id"]." ");
		echo "ok";
		break;
}		
$pd->close();
unset($pd);
unset($rs);