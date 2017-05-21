<?php
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php');
#require('./ppf/pdo_mysql.php');
require("../ppf/pdo_template.php"); 
 
$qname=isset($_GET["t"])?$_GET["t"]:"index";     
if($qname=="index"&&GOTO_URL!="") header("Location: ".GOTO_URL);   
 
if(!session_id())session_start();  
$uid=isset($_SESSION['uid'])?$_SESSION['uid']:"";

#check limit
$T=new pdo_template('',true);
//$template=$T->db->query("select tpl from `main` where id=1")->fetchColumn(0);
//if(empty($template))$template="def";
$template="read";
if(file_exists("./tpl_".$template.".php"))
    include("tpl_".$template.".php");    
else
    include("tpl_read.php");         
$T->close();  	
unset($T); 