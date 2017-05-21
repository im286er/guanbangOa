<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

#检测登录
session_start(); #chkLoginNoJump("uid");
 
$pd=new pdo_mysql();
switch($_POST["tpl"]){
  #	echo $pd->query("select name from main_art_category where id=".$_POST["id"])->fetchColumn(0);      	 		
	case "set_school": 
    $nid=$pd->genid("act_school");
		 $pd->exec("insert into act_school(id,uid,sid,intoyear,`default`,state) values(".$nid.",'".$_POST["id"]
         ."',".$_POST["sid"].",0,1,2)"); 
        echo "ok";
		break;
	case "del_recommend_reply";
    chkLoginNoJump("uid"); 
  	$uid=$_SESSION['uid'];
  	$pd->exec("delete from recommend_reply where id=".$_POST["id"]." and uid='".$uid."'");
  	echo 'ok';
		break;
}		
$pd->close();
unset($pd);
unset($rs);