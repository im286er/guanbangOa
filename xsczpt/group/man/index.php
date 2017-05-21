<?php
/* 班级空间 管理中心 */
header("Content-type: text/html; charset=gbk;");
require('../../ppf/fun.php');
#require('../../ppf/pdo_mysql.php');
require("../../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index"; 
 
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");   

$uid=$_SESSION['uid'];
$gid=$_GET["g"];#班级id
 
$T=new pdo_template('',true);  
if(!isset($_SESSION["group_man"])){#管理id 教师的班级id列表 group_concat(id separator '|') 默认逗号   
  $_SESSION["group_man"]=" ,".$T->db->query("SELECT group_concat(id) FROM `group` where uid='".$uid."'")->fetchColumn(0).",".
  $T->db->query("SELECT group_concat(gid) FROM `grp_member` where uid='".$uid."' and isman>0")->fetchColumn(0).","; 
}  

if(!strpos($_SESSION["group_man"],",".$gid.",")){ 
   header("location: /?t=error&no=201&name=没有权限&msg=您没有操作权限！");
}


 
$T->loadTpl('./html/'.$qname.'.htm');  
$T->SetTpl('cssjs','cssjs.inc');  
$T->SetTpl('top','../../inc/top.htm');      
$T->SetTpl('footer','../../inc/foot.htm');      
#$T->SetTpl('head','../html/head.htm');     
$T->SetTpl('menu','menu.htm');  

      
$T->Set("face",$_SESSION["face"]); 
$T->Set("nick",$_SESSION["nick"]);           
switch($qname){    
   /*case "audit": #m
	  $p=isset($_GET["p"])?$_GET["p"]:"1"; 
	  $rc= $T->db->query("select count(1) from grp_member where  gid=".$gid)->fetchColumn(0);
	  $page=getPageHtml_bt($rc,15,$p,"&t=audit&g=".$gid);
	  $T->Set("page",$page); 
      $T->SetBlock("list","select T.state,T.isman,T.timestamp,T.id,M.id as uid,M.truename,M.nick from grp_member T left JOIN act_member M on M.id=T.uid where T.gid=".$gid." limit ".(($p-1)*15).",15");
      #$T->SetBlock("list1","select T.state,T.duty,M.id,M.truename,M.nick from stu_class T left JOIN act_member M on M.id=T.id where t.cid=".$gid);
      break; */
  case "album_photo":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh=" where aid=".$_GET["id"];
    if(!empty($so))$wh.=" and name like '%".$so."%'";  
    $rc=$T->db->query("select count(1) from `grp_photo`".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=album_photo&g=".$gid."&id=".$_GET["id"]);
    $T->Set("page",$page);    
    $T->Set("id",$_GET["id"]); 
    $T->SetBlock("list","select T.*,M.truename from `grp_photo` T left join act_member M on M.id=T.uid ".$wh." order by T.id desc limit ".(($p-1)*15).",15");
    break;  
  case "album":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh=" where gid=".$gid;
    if(!empty($so))$wh.=" and name like '%".$so."%'";  
    $rc=$T->db->query("select count(1) from `grp_album`".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=album&g=".$gid."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `grp_album` T left join act_member M on M.id=T.uid ".$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;    
  case "member": #m
	  $p=isset($_GET["p"])?$_GET["p"]:"1"; 
	  $rc= $T->db->query("select count(1) from grp_member where gid=".$gid)->fetchColumn(0);
	  $page=getPageHtml_bt($rc,15,$p,"&t=member&g=".$gid);
	  $T->Set("page",$page); 
    $T->SetBlock("list","select T.id id1,T.state,T.isman,M.id,M.nick,M.truename from grp_member T left JOIN act_member M on M.id=T.uid where T.gid=".$gid." limit ".(($p-1)*15).",15");
      #$T->SetBlock("list1","select T.state,T.duty,M.id,M.truename,M.nick from stu_class T left JOIN act_member M on M.id=T.id where t.cid=".$gid);
      break; 
case "forum":#g       
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_forum where gid=".$gid)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=forum&c=".$gid);
    $T->Set("page",$page); 
    $T->SetBlock("list","select S.*,A.truename,A.nick from grp_forum S left join act_member A on A.id=S.uid where S.gid=".$gid." order by timestamp desc limit ".(($p-1)*15).",15");
      break;
 case "zone_tpl":
      $p=isset($_GET["p"])?$_GET["p"]:"1";                     
        $rc=$T->db->query("select count(1) from sys_tpl_group where state=2")->fetchColumn(0);
        $page=getPageHtml_bt($rc,15,$p,"&t=zone_tpl");
        $T->Set("page",$page); 
        $T->SetBlock("list","select * from sys_tpl_group where state=2 limit ".(($p-1)*15).",15");  
        $T->ReadDB("select tpl from `group` where id='".$gid."'");    
      break;    
  case "index": #g首页          
    $T->ReadDB("SELECT S.`name` schools,P.`name` periods,G.`name` grades from class C left JOIN school S on S.id=C.school LEFT JOIN sys_period P on P.id=C.period LEFT JOIN sys_grade G on G.id=C.grade  where C.id=".$gid); 
       
    break; 
}  
$audit= $T->db->query("select count(1) from grp_member where state=0 and gid=".$gid)->fetchColumn(0);
$T->Set("audit",$audit);  
$T->readDB("select * from `group` where id=".$gid);  
#$T->ReadDB("select * from v_act_member where id='".$uid."'"); 
$T->Set("g",$gid);  
#$T->Set("sitename",LR_NAME); 
$T->Set("gtitle",LR_NAME);   
$T->clearNaN();
$T->clearNoN();     
$T->display();
$T->close();	
unset($T);