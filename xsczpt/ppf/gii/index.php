<?php 
header("Content-type: text/html; charset=gbk;");
require('../fun.php');
require("../pdo_template.php"); 
#登录模板
$qname=isset($_GET["t"])?$_GET["t"]:"index";
$title1='管理系统';

if (!session_id()) session_start();
$T=new pdo_template('./html/'.$qname.'.htm');
$T->SetTpl("cssjs","cssjs.inc");
$T->SetTpl("top","top.inc");
$T->SetTpl("left","left.inc");
$T->SetTpl("foot","foot.inc");      	
$T->Set_Assign("gtitle",LR_NAME);  
$T->Set_Assign("ver",LR_VER);		
switch($qname){
    case "exit":
      	if(isset($_SESSION["giiuid"])){
      		unset($_SESSION["giiuid"]);
      		session_destroy();#	session_unset();		
      	}	
      break;  
		case "login":	        
			break;
	  default:#首页         
  			if(!isset($_SESSION["giiuid"])){
          header("Location: ./?t=login");
  				exit;       
        }        
      	$T->Set_Assign("topadmin",mb_convert_encoding($_SESSION["giiname"], "GBK","UTF-8"));	
        break; 	
}

$T->display();
$T->close();
unset($T);