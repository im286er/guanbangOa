<?php
/* 教师空间*/
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php');
#require('../ppf/pdo_mysql.php');
require("../ppf/pdo_template.php"); 

$qname=isset($_GET["t"])?$_GET["t"]:"index"; 
$qmenu=isset($_GET["m"])?$_GET["m"]:"";
 
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];    
 
$T=new pdo_template('./html/'.$qname.'.htm');   
$T->SetTpl('cssjs','cssjs.inc');     
$T->SetTpl('top','../inc/top.htm');      
$T->SetTpl('foot','../inc/foot.htm');  
$T->SetTpl('head1','head.inc');  
$T->SetTpl('menu','menu'.$qmenu.'.inc');   
 
#$T->Set("nick",$_SESSION["nick"]); 
$T->Set("uid",$uid);    
$T->Set("panurl",PAN_URL);   
switch($qname){
  /*case "fdl": 
      unset($T);  
      $url=PAN_URL."api/yun?t=dl&id=".$_GET["id"]; 
      header("location: ".$url);
      exit;
      break;*/    
  case "itembank":
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    if($p<1)$p=1;
    $rc= $T->db->query("select count(1) from itembank where uid='".$uid."'")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=itembank");
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from itembank where uid='".$uid."' order by id desc limit ".(($p-1)*15).",15");
    break;                                    
 case "preparelesson_lesson_word":
    $T->SetTpl('editor','editora.inc');       
    break;
  case "preparelesson_lesson":     
    $T->ReadDB("select * from beike where id=".$_GET["id"]);
    $T->SetBlock("step","select * from beike_step where bid=".$_GET["id"]." and tid=".$_GET["tid"]);
    $wh="";
    if(isset($_GET["step"]))$wh=" and sid=".$_GET["step"];
    $T->SetBlock("list","select * from beike_list where bid=".$_GET["id"]." and tid=".$_GET["tid"].$wh);
    break;
  case "preparelesson_detail":
    $T->ReadDB("select * from beike where id=".$_GET["id"]);
    break;   
  case "preparelesson": #备课
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    if($p<1)$p=1;
    $rc= $T->db->query("select count(1) from beike where uid='".$uid."'")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=preparelesson");
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from beike where uid='".$uid."' order by id desc limit ".(($p-1)*15).",15");
    break;
 /*        
   */
  case "def_property":
     $T->SetBlock("list","select * from `tech_def_property` where uid='".$uid."' order by timestamp desc");
    break;    
    
  case "textbook": 
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    if($p<1)$p=1;
    $rc= $T->db->query("select count(1) from sys_textbook where uid='".$uid."'")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=textbook");
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from sys_textbook where uid='".$uid."' order by id desc limit ".(($p-1)*15).",15");
    break; 
    
 case "deve_list":
    $p=isset($_GET["p"])?$_GET["p"]:"1";  	   
    if($p<1)$p=1;
    $rc= $T->db->query("select count(1) from `tech_grow` where uid='".$uid."'")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=deve_list");
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,M.truename from `tech_grow` T left join act_member M on M.id=T.uid where T.uid='".$uid."' order by T.timestamp desc limit ".(($p-1)*15).",15");
    break;   
  case "deve_cate": 
     $T->SetBlock("list","select * from `tech_grow_type` where uid='".$uid."' order by odx desc");
     #$T->Set("uid",$uid);   
    break;     
case "deve_info": 
   $tid= $T->db->query("select `id` from `tech_grow_myinfo` where uid='".$uid."' limit 1")->fetchColumn(0);
   $T->Set("tid",$tid);  
  break;
   case "blog":
      $id=$_GET['id'];
      $T->ReadDB("select * from blog_list where id=".$_GET['id']);
      $p=isset($_GET["p"])?$_GET["p"]:"1"; 
      $wh=" where  bid=".$id; #uid='".$uid."' and
        $rc= $T->db->query("select count(1) from `blog_comments` ".$wh)->fetchColumn(0);
        $page=getPageHtml_bt($rc,30,$p,"&t=blog&id=".$id);
        $T->Set("page",$page); 
        $T->SetBlock2("list","select * from blog_comments ".$wh." order by id desc limit ".(($p-1)*30).",30"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.truename from blog_comment_comments T left join act_member M on M.id=T.uid where bcid=? order by T.id desc limit 10"))
            );
      
      $floor=$T->db->query("select count(1)+1 from blog_comments where bid=".$_GET["id"])->fetchColumn(0);
      $T->Set("floor",$floor); 
      /*$pre=$T->db->query("select id from blog_list where id<".$_GET['id']." and uid='".$uid."' order by id desc limit 1")->fetchColumn(0);
      $next=$T->db->query("select id from blog_list where id>".$_GET['id']." and uid='".$uid."' order by id asc limit 1")->fetchColumn(0);
      if(empty($pre))$pre= $_GET['id'];
      if(empty($next))$next= $_GET['id'];
      $T->Set_Assign("comment",$comment);
      $T->Set_Assign("previd",$pre); 
      $T->Set_Assign("nextid",$next);
      */break;
  case "blog_cate": 
    $T->SetBlock("list","select * from `blog_cate` where uid='".$uid."' order by odx desc");
    break;   
  case "blogs":      
    $c=isset($_GET["c"])?$_GET["c"]:"";     
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $wh="";
    if(!empty($c))$wh=" and cid=".$c;
    $rc= $T->db->query("select count(1) from `blog_list` where uid='".$uid."'".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=blogs&c=".$c);
    $T->Set("page",$page); 
    $T->SetBlock("list","select id,title,see,comments,timestamp from `blog_list` where uid='".$uid."'".$wh." order by timestamp desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
    break;
  case "album_photo_add":
    $T->Set("id",$_GET["id"]);
    break;
  case "album_photo":            
    $aid=isset($_GET["id"])?$_GET["id"]:"0"; 
    $T->ReadDB("select * from zone_album where id='".$aid."'"); 
    $T->Set("aid",$aid); 
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $wh=" where uid='".$uid."' and aid=".$aid;
    $rc=$T->db->query("select count(1) from `zone_album_pic` ".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=album_photo&id=".$aid);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `zone_album_pic` ".$wh." order by timestamp desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
    break;  
  case "album":            
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $wh=" where uid='".$uid."'";
    $rc=$T->db->query("select count(1) from `zone_album` ".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=album");
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `zone_album` ".$wh." order by timestamp desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
    break;    
  case "weibo":      
    $p=isset($_GET["p"])?$_GET["p"]:"1";  
    $rc= $T->db->query("select count(1) from `weibo` where uid='".$uid."'")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=weibo");
    $T->Set("page",$page); 
    #$T->SetBlock("list","select * from `weibo` where uid='".$uid."' order by id desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock2("list","select * from `weibo` where uid='".$uid."' order by id desc limit ".(($p-1)*15).",15"
          	,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.truename from weibo_comments T left join act_member M on M.id=T.uid where wid=? order by T.id desc limit 10"))
          	);
    break;
  case "leave":      
    $p=isset($_GET["p"])?$_GET["p"]:"1";  
    $rc= $T->db->query("select count(1) from `zone_leave` where touid='".$uid."'")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=leave");
    $T->Set("page",$page); 
    $T->SetBlock2("list","select * from `zone_leave` where touid='".$uid."' order by id desc limit ".(($p-1)*15).",15"
          	,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.truename from zone_leave_comments T left join act_member M on M.id=T.uid where T.mid=? order by T.id desc limit 10"))
          	);
    break;
  case "fav_cate":
        $T->SetBlock("list","SELECT * from zone_fav_type where uid='".$uid."' order by odx desc");
        break;
  case "fav":
    #$T->SetBlock("cate","select * from zone_fav_type where uid='".$frid."' order by odx desc");
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $c=isset($_GET["c"])?$_GET["c"]:"0"; 
    $wh=" where uid='".$uid."'";
    if(!empty($c))$wh.=" and cid=".$c;                     
    $rc=$T->db->query("select count(1) from zone_fav ".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=fav&c=".$c);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from zone_fav ".$wh." order by id desc limit ".(($p-1)*15).",15");     
    break;  
  case "index":
    #if(!$T->db->query("select count(1) from tech where id='".$uid."'")->fetchColumn(0)){
      # echo "insert into zone(id,name,explain,visit,comment,blacklist,msg_open,msg_limit,timestamp) values('".
      # $_SESSION['uid']."','".$_SESSION['nick']."','空间说明',0,0,'',0,0,UNIX_TIMESTAMP())";
       #$T->db->exec("insert into tech(id,timestamp,name,explain) values('".$uid."',UNIX_TIMESTAMP(),'教师".time()."','空间简介')");
    #} 
    $T->SetBlock2("list","select * from `weibo` where uid='".$uid."' order by id desc limit 0,15"
          	,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.truename from weibo_comments T left join act_member M on M.id=T.uid where wid=? order by T.id desc limit 10"))
          	); 
    break;
 
  default:    
    
     break;   
}                     
$T->ReadDB("select * from act_member where id='".$uid."'"); 
$T->ReadDB("select weibo,blog,photo from zone where id='".$uid."'"); 
$T->Set("gtitle",LR_NAME); 
$T->clearNaN();
$T->clearNoN();     
$T->display();
$T->close();	
unset($T);       