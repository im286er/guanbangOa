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
	case "set_checked":
		$rs=$pd->query("insert into oa_message(uid,geter,leaveword ,content,stime,is_read,flag,menu1,menu2,subject,from_id) values('".$uid."','".$_POST["geter"]."',(select content from oa_zhsz_report_custom where id=".$_POST["id"]."),'".$_POST["content"]."','".date('Y-m-d H:i:s')."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["subject"]."','".$_POST["id"]."')");
		if($_SESSION['role_id']!=4&&$_SESSION['role_id']!=1&&$_SESSION['role_id']!=3){
			$rs1=$pd->query("update oa_zhsz_report_custom SET master_id='".$uid."' where id=".$_POST["id"]."");
		}elseif($_SESSION['role_id']==4){
			$mid=$pd->query("select master_id from oa_zhsz_report_custom where id=".$_POST["id"]." ")->fetchColumn(0);
			if($mid){
				$rs2=$pd->query("insert into oa_message (uid,geter,leaveword,content,stime,is_read,flag,menu1,menu2,from_id) values('".$uid."','".$mid."','".$_POST["content"]."',(select content from oa_zhsz_report_custom where id=".$_POST["id"]."),'".date('Y-m-d H:i:s',time())."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["id"]."')");

			}
		}
		$mid=$pd->query("select master_id from oa_zhsz_report_custom where id=".$_POST["id"]." ")->fetchColumn(0);
		if($mid&&$_SESSION['role_id']==4){
		  $cont2=$pd->query("update oa_zhsz_report_custom SET flag_status='未通过' where id=".$_POST["id"]." ");
		  $cont=$pd->query("update oa_message SET flag='<span style=\'color:rgb(163,87,26)\'>未通过</span><br/><span style=\'color:red\'>(打回)</span>' where from_id=".$_POST["id"]." ");
		}else{
		  $cont2=$pd->query("update oa_zhsz_report_custom SET flag_status='未通过' where id=".$_POST["id"]." ");
		  $cont=$pd->query("update oa_message SET flag='未通过' where from_id=".$_POST["id"]." ");
		}
		
		echo "ok";
		break;
	case "set_tg":
		$rs=$pd->query("insert into oa_message(uid,geter,leaveword,stime,is_read,flag,menu1,menu2,subject,from_id) values('".$uid."','".$_POST["geter"]."',(select content from oa_zhsz_report_custom where id=".$_POST["id"]."),'".date('Y-m-d H:i:s')."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["subject"]."','".$_POST["id"]."')");
		if($_SESSION['role_id']!=4&&$_SESSION['role_id']!=1&&$_SESSION['role_id']!=3){
			$rs1=$pd->query("update oa_zhsz_report_custom SET master_id='".$uid."' where id=".$_POST["id"]."");
		}elseif($_SESSION['role_id']==4){
			$mid=$pd->query("select master_id from oa_zhsz_report_custom where id=".$_POST["id"]." ")->fetchColumn(0);
			if($mid){
				$rs2=$pd->query("insert into oa_message (uid,geter,content,stime,is_read,flag,menu1,menu2,from_id) values('".$uid."','".$mid."',(select content from oa_zhsz_report_custom where id=".$_POST["id"]."),'".date('Y-m-d H:i:s',time())."',0,'".$_POST["flag"]."','".$_POST["menu1"]."','".$_POST["menu2"]."','".$_POST["id"]."')");
			}
		}
		$mid=$pd->query("select master_id from oa_zhsz_report_custom where id=".$_POST["id"]." ")->fetchColumn(0);
		if($mid&&$_SESSION['role_id']==4){
		  $cont2=$pd->query("update oa_zhsz_report_custom SET flag_status='已审核' where id=".$_POST["id"]." ");
		  $cont=$pd->query("update oa_message SET flag='<span style=\'color:rgb(163,87,26)\'>已审核</span><br/><span style=\'color:red\'>(打回)</span>' where from_id=".$_POST["id"]." ");
		}else{
		  $cont2=$pd->query("update oa_zhsz_report_custom SET flag_status='已审核' where id=".$_POST["id"]." ");
		  $cont=$pd->query("update oa_message SET flag='已审核' where from_id=".$_POST["id"]." ");
		}
		echo "ok";
		break;

}		
$pd->close();
unset($pd);
unset($rs);