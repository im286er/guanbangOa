<?php
/*主站 管理中心 */
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php');
require('../ppf/pdo_mysql.php');
require("../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index"; 
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");   
$uid=$_SESSION['uid']; 
  #检测管理权限
if(!isset($_SESSION["main_man"])){#管理id id列表 group_concat(id separator '|') 默认逗号 
  $pd=new pdo_mysql();
  $_SESSION["main_man"]=$pd->query("SELECT count(1) FROM main_member where uid='".$uid."'")->fetchColumn(0);   
  $pd->close();
  unset($pd);    
}   
if(empty($_SESSION["main_man"])){    
  header("location: /?t=error&no=204&name=没有管理权限&msg=您没有管理权限！");
}
#echo $uid.$_SESSION["main_man"];
 
$T=new pdo_template('./html/'.$qname.'.htm'); 
//var_dump($T);
$T->SetTpl('cssjs','cssjs_bt.inc');   
$T->SetTpl('top','../inc/top.htm');      
$T->SetTpl('foot','../inc/foot.htm');        
$T->SetTpl('menu','menu.htm');  
          
switch($qname){    
 case "active_status":
     $T->SetBlock("list","select * from `active_status` order by id asc");     
    break; 
  case "active_cate":
     $T->SetBlock("list","select * from `active_category` order by odx desc");        
    break;
  case "active_lvl":
     $T->SetBlock("list","select * from `active_level` order by odx desc");     
    break; 
  case "actives":
      $p=isset($_GET["p"])?$_GET["p"]:"1"; 
      $so=isset($_GET["so"])?$_GET["so"]:"";
      //var_dump($so);	  
      $c=isset($_GET["c"])?$_GET["c"]:""; 
      $l=isset($_GET["l"])?$_GET["l"]:""; 
      if($p<1)$p=1;
      $wh=" where school=0";
      if(!empty($c))$wh.=" and cid=".$c;
      if(!empty($l))$wh.=" and lvl=".$l;
      if(!empty($so))$wh.=" and name like '%".$so."%'";
      $rc= $T->db->query("select count(1) from `activity` ".$wh)->fetchColumn(0);
      $page=getPageHtml_bt($rc,15,$p,"&t=actives&c=".$c."&l=".$l."&so=".$so);
      $T->Set("page",$page);                                                                                                                                    
      $T->SetBlock("list","select * from `activity` ".$wh." order by id desc limit ".(($p-1)*15).",15");
      break;
  case "depart":
     $T->SetBlock("list","select * from `sch_department` where sid=".$sid." order by odx desc");
    
    break;
   
   case "admin_plus":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    if($p<1)$p=1;
    $u=isset($_GET["u"])?$_GET["u"]:"";
    $n=isset($_GET["n"])?$_GET["n"]:"";
    $k=isset($_GET["k"])?$_GET["k"]:"";
    $i=isset($_GET["i"])?$_GET["i"]:"";      
    $wh="";
    if(!empty($u))$wh.=" and username like '%".$u."%'";
    if(!empty($n))$wh.=" and truename like '%".$n."%'";
    if(!empty($k))$wh.=" and nick like '%".$k."%'";
    if(!empty($i))$wh.=" and idtype = ".$i."";      
    $rc= $T->db->query("select count(1) from `act_member` where id!='' ".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=admin_plus&u=".$u."&n=".$n."&k=".$k."&i=".$i);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from act_member where id!='' ".$wh." order by timestamp desc limit ".(($p-1)*15).",15");
    break;  
  case "act_add":
    $addr= $T->db->query("select addr from `school` where id=".$sid."")->fetchColumn(0);
    $T->Set("addr",$addr); 
    break;
   
  
  case "famous":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `famous` where school=".$sid.$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=famous&s=".$sid."&so=".$so);
    $T->Set("page",$page);                                                                                                                                    
    $T->SetBlock("list","select T.*,M.truename from `famous` T left join act_member M on M.id=T.uid where T.school=".$sid.$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;
  case "famous_admin":
    $fid= $T->db->query("select id from `famous` where school=".$sid )->fetchColumn(0);
    $f=(!isset($_GET["f"]) || empty($_GET["f"]))?$fid:$_GET['f'];
    $p=isset($_GET["p"])?$_GET["p"]:"1";  
    if($p<1)$p=1; 
    $rc= $T->db->query("select count(1) from `famous_member` where fid=".$f." and isman=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=famous_admin&s=".$sid."&f=".$f);
    $T->Set("page",$page); 
    $T->Set("f",$f);
    $T->SetBlock("list","select T.*,M.truename from `famous_member` T left join act_member M on M.id=T.uid where T.fid=".$f." and isman=2 order by T.timestamp desc limit ".(($p-1)*15).",15");
    break; 
  case "subject":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `subject` where school=".$sid.$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=subject&s=".$sid."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `subject` T left join act_member M on M.id=T.uid where T.school=".$sid.$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;    
  case "group":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `group` where school=".$sid.$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=group&s=".$sid."&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select T.*,M.truename from `group` T left join act_member M on M.id=T.uid where T.school=".$sid.$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;
  case "grp_admin":
    $gid= $T->db->query("select id from `group` where school=".$sid )->fetchColumn(0);
    $g=(!isset($_GET["g"]) || empty($_GET["g"]))?$gid:$_GET['g'];
    $p=isset($_GET["p"])?$_GET["p"]:"1";  
    if($p<1)$p=1; 
    $rc= $T->db->query("select count(1) from `grp_member` where gid=".$g." and isman=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=grp_admin&s=".$sid."&g=".$g);
    $T->Set("page",$page); 
    $T->Set("g",$g);
    $T->SetBlock("list","select T.*,M.truename from `grp_member` T left join act_member M on M.id=T.uid where T.gid=".$g." and isman=2 order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;
  case "class":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `class` where school=".$sid.$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=class&s=".$sid."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `class` T left join act_member M on M.id=T.uid where T.school=".$sid.$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;
  case "cls_admin":
    $cid= $T->db->query("select id from `class` where school=".$sid )->fetchColumn(0);
    $c=(!isset($_GET["c"]) || empty($_GET["c"]))?$cid:$_GET['c'];
    $p=isset($_GET["p"])?$_GET["p"]:"1";  
    if($p<1)$p=1; 
    $rc= $T->db->query("select count(1) from `cls_member` where cid=".$c." and isman=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=cls_admin&s=".$sid."&c=".$c);
    $T->Set("page",$page); 
    $T->Set("c",$c);
    $T->SetBlock("list","select T.*,M.truename from `cls_member` T left join act_member M on M.id=T.uid where T.cid=".$c." and isman=2 order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;
  /*case "member": #m
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and truename like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `act_member` where school=".$sid.$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=member&s=".$sid."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from act_member where school=".$sid.$wh." order by timestamp desc limit ".(($p-1)*15).",15");
    break; 
  case "art_last":
     $T->ReadDb("select * from `sch_art` where id=".$_GET["id"]);    
    break;   */  
  case "problems":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `main_problem` where id>0".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=problems&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `main_problem` T left join act_member M on M.id=T.uid where T.id>0".$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;  
  case "arts":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $cid=isset($_GET["c"])?$_GET["c"]:"";
    if($p<1)$p=1;
    $wh="";      	
    if(!empty($cid))$wh=" and cid=".$cid;        
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `main_article` where id>0".$wh)->fetchColumn(0);
	//var_dump($rc);
    $page=getPageHtml_bt($rc,15,$p,"&t=arts&c=".$cid."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `main_article` T left join act_member M on M.id=T.uid where T.id>0".$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;  
  case "art_cate":
     $T->SetBlock("list","select * from `main_art_category` order by odx desc");
    
    break;
  case "act":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    if($p<1)$p=1;
    $u=isset($_GET["u"])?$_GET["u"]:"";
    $n=isset($_GET["n"])?$_GET["n"]:"";
    $k=isset($_GET["k"])?$_GET["k"]:"";
    $i=isset($_GET["i"])?$_GET["i"]:"";      
    $wh="";
    if(!empty($u))$wh.=" and username like '%".$u."%'";
    if(!empty($n))$wh.=" and truename like '%".$n."%'";
    if(!empty($k))$wh.=" and nick like '%".$k."%'";
    if(!empty($i))$wh.=" and idtype = ".$i."";      
    $rc= $T->db->query("select count(1) from `act_member` where id!='' ".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=act&u=".$u."&n=".$n."&k=".$k."&i=".$i);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from act_member where id!='' ".$wh." order by timestamp desc limit ".(($p-1)*15).",15");
    break;    
  case "admin":
    $p=isset($_GET["p"])?$_GET["p"]:"1";  
    if($p<1)$p=1; 
    $rc= $T->db->query("select count(1) from `main_member`")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=admin");
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename,M.nick,M.username from `main_member` T left join act_member M on M.id=T.uid order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;  
  case "tpl":
    $p=isset($_GET["p"])?$_GET["p"]:"1";                     
    $rc=$T->db->query("select count(1) from sys_tpl_index where state=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=tpl");
      $T->Set("page",$page); 
      $T->SetBlock("list","select * from sys_tpl_index where state=2 limit ".(($p-1)*15).",15");  
      $T->ReadDB("select tpl from `main` where id=1");    
    break;  
  case "index": #g首页          
    $T->ReadDB("SELECT * from sys_info where id=1"); 	
    break; 
  case "mo_category":
     $T->SetBlock("list","select * from `mo_category` order by odx desc");
    break;
  case "mo_articles":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $cid=isset($_GET["c"])?$_GET["c"]:"";
    if($p<1)$p=1;
    $wh="";      	
    if(!empty($cid))$wh=" and cid=".$cid;        
    if(!empty($so))$wh=" and name like '%".$so."%'";
	
    $rc= $T->db->query("select count(1) from `mo_articles` where id>0".$wh)->fetchColumn(0);
	
    $page=getPageHtml_bt($rc,15,$p,"&t=mo_articles&c=".$cid."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `mo_articles` T left join act_member M on M.id=T.uid where T.id>0".$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;
  case "mo_advers":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $so=isset($_GET["so"])?$_GET["so"]:""; 
    if($p<1)$p=1;
    $wh="";
    if(!empty($so))$wh=" and name like '%".$so."%'";
    $rc= $T->db->query("select count(1) from `mo_advers` where id>0".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=mo_advers&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `mo_advers` T left join act_member M on M.id=T.uid where T.id>0".$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;	
      
}  
#$T->readDB("select * from `class` where id=".$sid);   
#$T->Set("s",$sid);  
$T->Set("gtitle",LR_NAME);   
$T->clearNaN();
$T->clearNoN();     
$T->display();
$T->close();	
unset($T);