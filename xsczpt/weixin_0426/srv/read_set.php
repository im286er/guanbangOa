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
	case "set_yd":
		$rs=$pd->query("update oa_message set is_read=1 where id=".$_POST["id"]." ");
		echo "ok";
		break;
	case "set_wd":
		$rs=$pd->query("update oa_message set is_read=0 where id=".$_POST["id"]." ");
		echo "ok";
	    break;
}		
$pd->close();
unset($pd);
unset($rs);