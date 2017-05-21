<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
header("Content-type: text/html; charset=gbk;");
require('../../ppf/fun.php');
require('../../ppf/pdo_mysql.php');
require("../../ppf/pdo_template.php");
$qname=isset($_GET["t"])?$_GET["t"]:"index";
if (!session_id()) session_start();
chkLogin("uid","/?t=login");
$uid=$_SESSION['uid'];
$sid=$_GET["s"];#班级id
#检测管理权限
if(!isset($_SESSION["school_man"])){#管理id id列表 group_concat(id separator '|') 默认逗号 
$pd=new pdo_mysql();
$_SESSION["school_man"]=" ,".$pd->query("SELECT group_concat(id) FROM school where uid='".$uid."'")->fetchColumn(0).",".
$pd->query("SELECT group_concat(sid) FROM sch_admin where uid='".$uid."'")->fetchColumn(0).",";
$pd->close();
unset($pd);
}
#echo $_SESSION["school_man"];exit;
if(!strpos($_SESSION["school_man"],",".$sid.",")){
header("location: /?t=error&no=204&name=没有管理权限&msg=您没有管理权限！");
}
$T=new pdo_template('./html/'.$qname.'.htm');
$T->SetTpl('cssjs','cssjs_bt.inc');
$T->SetTpl('top','../../inc/top.htm');
$T->SetTpl('foot','../../inc/foot.htm');
$T->SetTpl('menu','menu.htm');
switch($qname){
case "act_add":
case "act_edt":
case "import_act": 
$T->Set("period",$T->db->query("select period from `school` where id=".$sid)->fetchColumn(0));
break;


case "schoolZone":
$p=isset($_GET["p"])?$_GET["p"]:"1";
$rc=$T->db->query("select count(1) from sys_tpl_school where state=2")->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=schoolZone");
$T->Set("page",$page);
$T->SetBlock("list","select * from sys_tpl_school where state=2 limit ".(($p-1)*15).",15");
$T->ReadDB("select tpl from school where id='".$sid."'");
break;


case "actives":
$p=isset($_GET["p"])?$_GET["p"]:"1";
$so=isset($_GET["so"])?$_GET["so"]:"";
$c=isset($_GET["c"])?$_GET["c"]:"";
$l=isset($_GET["l"])?$_GET["l"]:"";
if($p<1)$p=1;
$wh=" where school=".$sid;
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
case "schoolZone":
$T->SetBlock("list","select * from `sch_department` where sid=".$sid." order by odx desc");
break;
case "admin":
#$p=isset($_GET["p"])?$_GET["p"]:"1";
#if($p<1)$p=1;
#$rc= $T->db->query("select count(1) from `sch_admin` where sid=".$sid)->fetchColumn(0);
#$page=getPageHtml_bt($rc,15,$p,"&t=admin&s=".$sid);
#$T->Set("page",$page);
$T->SetBlock("list","select T.id tid,M.* from `sch_admin` T left join act_member M on M.id=T.uid where T.sid=".$sid." order by T.timestamp desc");
break;
case "act":
$p=isset($_GET["p"])?$_GET["p"]:"1";
$su=isset($_GET["su"])?$_GET["su"]:"";
$st=isset($_GET["st"])?$_GET["st"]:"";
$sn=isset($_GET["sn"])?$_GET["sn"]:"";
$sp=isset($_GET["sp"])?$_GET["sp"]:"";
if($p<1)$p=1;
$wh="";
if(!empty($su))$wh.=" and username like '%".$su."%'";
if(!empty($st))$wh.=" and truename like '%".$st."%'";
if(!empty($sn))$wh.=" and nick like '%".$sn."%'";
if(!empty($sp))$wh.=" and idtype = ".$sp."";
$rc= $T->db->query("select count(1) from act_school S left join act_member M on M.id=S.uid where sid=".$sid.$wh)->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=act&s=".$sid."&su=".$su."&st=".$st."&sn=".$sn."&sp=".$sp);
$T->Set("page",$page);
$T->SetBlock("list","select M.*,S.id id1,S.state state1 from act_school S left join act_member M on M.id=S.uid where S.sid=".$sid.$wh." order by timestamp desc limit ".(($p-1)*15).",15");
break;
case "topics":
$p=isset($_GET["p"])?$_GET["p"]:"1";
$so=isset($_GET["so"])?$_GET["so"]:"";
if($p<1)$p=1;
$wh="";
if(!empty($so))$wh=" and title like '%".$so."%'";
$rc= $T->db->query("select count(1) from `sch_topic` where sid=".$sid.$wh)->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=topics&s=".$sid."&so=".$so);
$T->Set("page",$page);
$T->SetBlock("list","select T.*,M.truename from `sch_topic` T left join act_member M on M.id=T.uid where T.sid=".$sid.$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
break;
case "art_last":
$T->ReadDb("select * from `sch_art` where id=".$_GET["id"]);
break;
case "art_cate":
$T->SetBlock("list","select * from `sch_art_cate` where sid=".$sid." order by odx desc");
break;
case "arts":
$p=isset($_GET["p"])?$_GET["p"]:"1";
$so=isset($_GET["so"])?$_GET["so"]:"";
if($p<1)$p=1;
$wh="";
if(!empty($so))$wh=" and title like '%".$so."%'";
$rc= $T->db->query("select count(1) from `sch_art` where sid=".$sid.$wh)->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=arts&s=".$sid."&so=".$so);
$T->Set("page",$page);
$T->SetBlock("list","select T.*,M.truename from `sch_art` T left join act_member M on M.id=T.uid where T.sid=".$sid.$wh." order by T.timestamp desc limit ".(($p-1)*15).",15");
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
$f=$_GET['f'];
$T->Set("f",$f);
$T->SetBlock("list","select T.id id1,M.* from `famous_member` T left join act_member M on M.id=T.uid where T.fid=".$f." and isman>0");
break;
case "subject":
$period= $T->db->query("select period from `school` where id=".$sid)->fetchColumn(0);
$T->SetBlock("list","select * from `sys_subject` where school=".$sid." order by odx asc");
$T->SetBlock("lista","select name from `sys_subject` where school=0 and period=".$period);
$T->Set("period",$period);
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
$g=$_GET['g'];
$T->Set("g",$g);
$T->SetBlock("list","select T.id id1,M.* from `grp_member` T left join act_member M on M.id=T.uid where T.gid=".$g." and isman>0");
break;
case "class":
$T->Set("period",$T->db->query("select period from `school` where id=".$sid)->fetchColumn(0));
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
$c=$_GET['c'];
$T->Set("c",$c);
$T->SetBlock("list","select T.id id1,M.* from `cls_member` T left join act_member M on M.id=T.uid where T.cid=".$c." and isman>0");
break;
case "member": #m
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
case "slide":  
$T->SetBlock("list","select * from `sch_slide`  where sid=".$sid." order by id desc");
break;
case "index": #g首页          
$T->ReadDB("SELECT * from school where id=".$sid);
break;
}
$T->Set("s",$sid);
$T->Set("gtitle",LR_NAME);
$T->clearNaN();
$T->clearNoN();
$T->display();
$T->close();
unset($T);
?>