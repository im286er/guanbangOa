<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
$pd=new pdo_mysql();
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];  
switch($_POST["tpl"]){
	case "get_msgs":
	    $rs=$pd->query("select count(*) from oa_message where is_read=0 and `geter`='".$uid."'")->fetchColumn(0);
		echo $rs;
		break;
	case "get_status":
	    $status=$pd->query("select flag_status from oa_zhsz_report_self where id='".$_POST["uid"]."'");
		echo $status;
	    break;
}		
$pd->close();
unset($pd);
unset($rs);