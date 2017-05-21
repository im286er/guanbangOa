<?php
header("Content-type: text/html; charset=utf8;");
require('../ppf/fun.php');
require("../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index";     

$T=new pdo_template('./html/'.$qname.'.htm'); 
$T->SetTpl('cssjs','cssjs.inc');   
$T->SetTpl('head','head.inc');
$T->SetTpl('left','left.inc'); 
$T->Set("gtitle","学生成长系统"); 


#释放资源
$T->clearNoN();
$T->clearNaN();
$T->display();
$T->close();	
unset($T);