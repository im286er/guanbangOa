<?php 
header("Content-type: text/html; charset=gbk;");
#require('../ppf/360_safe3.php');
require("../fun.php"); 
require("../pdo_template.php"); 

#检测登录
session_start(); 
chkLogin('giiuid');

$qname=isset($_GET["t"])?$_GET["t"]:"";
#退出
if(!empty($qname)){	
	$T=new pdo_template('./html/'.$qname.'.htm');
  $T->SetTpl("cssjs","cssjs.inc");
  $T->SetTpl("top","top.inc");
  #$T->SetTpl("left","left.inc");
  $T->SetTpl("foot","foot.inc");	
	$T->Set_Assign("gtitle",LR_NAME);   
	$T->Set_Assign("topadmin",mb_convert_encoding($_SESSION["giiname"], "GBK","UTF-8"));	
	$T->Set_Assign("ver",LR_VER);		  
	$T->display();
	$T->close();
	unset($T);
} 