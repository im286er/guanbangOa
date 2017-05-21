<?php
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php');
require('../ppf/pdo_mysql.php');
require("../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index"; 
 
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");   
$uid=$_SESSION['uid'];     

$T=new pdo_template('',true);
#$template=$T->db->query("select tpl from `main` where id=1")->fetchColumn(0);
#if(empty($template))$template="def";  

$T->loadTpl('./html/'.$qname.'.htm');  
$T->SetTpl('cssjs','./html/cssjs.inc');    
$T->SetTpl('top','../inc/top.htm');      
$T->SetTpl('foot','../inc/foot.htm'); 
#$T->SetTpl('head','../html/head.inc');    
#$T->SetTpl('menu','./html/menu.inc');  

switch($qname){ 
 
 
  
  case "index": 
    $T->SetBlock("lista","select id,name from `group` where state=2 order by id desc limit 8");
    $T->SetBlock("listb","select id,name from `group` where state=2 order by id desc limit 8,8");
  	$T->SetBlock("listc","select id,name,slogan,des,visit from `group` where state=2 order by timestamp desc limit 9");
  	$T->SetBlock("lista","select id,name from `group` where state=2 order by timestamp desc limit 4");
	  $T->SetBlock("liste","select  id,gid,title,des,see,timestamp from `grp_forum`  order by see desc limit 3");
    $T->SetBlock("listf","select f.id,f.gid,f.title,g.name from `group` as g JOIN grp_forum as f where g.id=f.gid group BY g.id ORDER BY g.`timestamp` desc limit 4");
    $T->SetBlock("listg","select id,name,visit from `group` order by visit desc limit 10");    
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
  	$wh="";
  	if(!empty($sc)){
      $wh.=" and school=".$sc;
    }else if(!empty($a)){
      $school = $T->db->query("select GROUP_CONCAT(id) from school where state=2 and  addr=".$a)->fetchColumn(0);
      $wh.=" and school in (".$school.")";
    }
    if(!empty($so))$wh.=" and name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `group` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=group&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `group`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
      
}    
$T->Set("gtitle",LR_NAME); 
$T->clearNaN();
$T->clearNoN();     
$T->display();
$T->close();	
unset($T);       					
	