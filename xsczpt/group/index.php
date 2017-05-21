<?php
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php'); 
require("../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index";  

if(!isset($_GET["g"]))header("location: /?t=error&no=G100&name=参数错误&msg=有必传参数未传！");     
$gid=$_GET["g"];
if(!session_id())session_start();  
$uid=isset($_SESSION['uid'])?$_SESSION['uid']:"";

#check limit
$T=new pdo_template('',true);
if(!$T->db->query("select count(1) from `group` where id=".$gid." and state=2")->fetchColumn(0))
    header("location: /?t=error&no=G102&name=状态非审核&msg=状态没有审核，无法访问！");
if(!$T->db->query("select count(1) from `group` where id=".$gid)->fetchColumn(0))
    header("location: /?t=error&no=G103&name=非法操作&msg=空间不存在，无法访问！");
#访问权限
$visit=$T->db->query("select visit from `group` where id=".$gid)->fetchColumn(0);
if($visit>0&&empty($uid))header("location: /?t=login&url=".url_base64_encode($_SERVER["REQUEST_URI"]));
switch($visit){
   case "3":header("location: /?t=error&no=G104&name=禁止访问&msg=禁止所有人访问");break;
   case "2":
      if(!$T->db->query("select count(1) from `grp_member` where gid=".$gid." and uid='".$uid."'")->fetchColumn(0))
        header("location: /?t=error&no=G106&name=无访问权限&msg=不是群内成员，无法访问！");
      break;
   case "1":
      if(!$T->db->query("select school from `group` where id=".$gid." and school in (select sid from act_school where uid='".$uid."')")->fetchColumn(0))
        header("location: /?t=error&no=G106&name=无访问权限&msg=不是同校内成员，无法访问！");
      break;
}    
    
#Template 
$template=$T->db->query("select tpl from `group` where id=".$gid)->fetchColumn(0);
if(empty($template))$template="def";  
if(file_exists("./tpl_".$template.".php"))
    include("./tpl_".$template.".php");    
else
    include("./tpl_def.php");         
$T->close();	
unset($T); 