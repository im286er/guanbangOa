<?php
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php'); 
require("../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index";  

if(!isset($_GET["g"]))header("location: /?t=error&no=G100&name=��������&msg=�бش�����δ����");     
$gid=$_GET["g"];
if(!session_id())session_start();  
$uid=isset($_SESSION['uid'])?$_SESSION['uid']:"";

#check limit
$T=new pdo_template('',true);
if(!$T->db->query("select count(1) from `group` where id=".$gid." and state=2")->fetchColumn(0))
    header("location: /?t=error&no=G102&name=״̬�����&msg=״̬û����ˣ��޷����ʣ�");
if(!$T->db->query("select count(1) from `group` where id=".$gid)->fetchColumn(0))
    header("location: /?t=error&no=G103&name=�Ƿ�����&msg=�ռ䲻���ڣ��޷����ʣ�");
#����Ȩ��
$visit=$T->db->query("select visit from `group` where id=".$gid)->fetchColumn(0);
if($visit>0&&empty($uid))header("location: /?t=login&url=".url_base64_encode($_SERVER["REQUEST_URI"]));
switch($visit){
   case "3":header("location: /?t=error&no=G104&name=��ֹ����&msg=��ֹ�����˷���");break;
   case "2":
      if(!$T->db->query("select count(1) from `grp_member` where gid=".$gid." and uid='".$uid."'")->fetchColumn(0))
        header("location: /?t=error&no=G106&name=�޷���Ȩ��&msg=����Ⱥ�ڳ�Ա���޷����ʣ�");
      break;
   case "1":
      if(!$T->db->query("select school from `group` where id=".$gid." and school in (select sid from act_school where uid='".$uid."')")->fetchColumn(0))
        header("location: /?t=error&no=G106&name=�޷���Ȩ��&msg=����ͬУ�ڳ�Ա���޷����ʣ�");
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