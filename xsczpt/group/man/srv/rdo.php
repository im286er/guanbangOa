<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];
 
$pd=new pdo_mysql();
switch($_POST["tpl"]){
  /*     		

  */  

}		
$pd->close();
unset($pd);
unset($rs);