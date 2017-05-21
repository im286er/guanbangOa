<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
header("Content-type: text/html; charset=gbk;");
require('../../ppf/fun.php');
require("../../ppf/pdo_template.php");
if (!session_id()) session_start();
chkLogin("uid","/?t=login");
$uid=$_SESSION['uid'];
$fid=$_GET["f"];
if(empty($fid))header("location: /?t=error&no=FM100&name=传参错误&msg=有必要的参数未传！");
$T=new pdo_template('',true);
if(!isset($_SESSION["famous_man"])){#管理id 教师的班级id列表 group_concat(id separator '|') 默认逗号   
$_SESSION["famous_man"]=" ,".$T->db->query("SELECT group_concat(id) FROM `famous` where uid='".$uid."'")->fetchColumn(0).",".
$T->db->query("SELECT group_concat(fid) FROM `famous_member` where uid='".$uid."' and isman>0")->fetchColumn(0).",";
}
if(!strpos($_SESSION["famous_man"],",".$fid.",")){
header("location: /?t=error&no=FM200&name=没有权限&msg=您没有操作权限！");
}
$qname=isset($_GET["t"])?$_GET["t"]:"index";
$T->loadTpl('./html/'.$qname.'.htm');
$T->SetTpl('cssjs','cssjs.inc');
#$T->SetTpl('cssjs_edit','cssjs_edit.inc');
#$T->SetTpl('cssjs_dialog','cssjs_dialog.inc');
$T->SetTpl('top','../../inc/top.htm');
$T->SetTpl('foot','../../inc/foot.htm');
$T->SetTpl('menu','menu.inc');
#$T->Set("face",$_SESSION["face"]);
#$T->Set("nick",$_SESSION["nick"]);
switch($qname){
case "famousZone":    
$p=isset($_GET["p"])?$_GET["p"]:"1";
$rc=$T->db->query("select count(1) from sys_tpl_famous where state=2")->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=famousZone");
$T->Set("page",$page);
$T->SetBlock("list","select * from sys_tpl_famous where state=2 limit ".(($p-1)*15).",15");
$T->ReadDB("select tpl from famous where id='".$fid."'");
break;
case "slide":  
$T->SetBlock("list","select * from famous_slide where fid=".$fid." order by id desc ");
#$T->SET("statename","未审核");
break;
case "art_list":        
$so=isset($_GET["so"])?$_GET["so"]:"";
$cid=isset($_GET["c"])?$_GET["c"]:"0";
$p=isset($_GET["p"])?$_GET["p"]:"1";
if($p<1)$p=1;
$wh=" where fid=".$fid;
if(!empty($cid))$wh.=" and cid=".$cid;
if(!empty($so))$wh.=" and title like '%".$so."%'";
$rc= $T->db->query("select count(1) from famous_article ".$wh)->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=art_list&f=".$fid);
$T->Set("page",$page);
$T->SetBlock("list","select id,title,cid,fid,timestamp,state from famous_article  ".$wh." order by id desc limit ".(($p-1)*15).",15");
$T->Set("so",$so);
break;
case "art_newsList":
$p=isset($_GET["p"])?$_GET["p"]:"1";
$fid=isset($_GET["f"])?$_GET["f"]:"1";
if($p<1)$p=1;
$rc= $T->db->query("select count(1) from famous_article where fid='".$fid."'and cid=-1")->fetchColumn(0);
$page=getPageHtml($rc,15,$p,"&t=art_newsList&f=".$fid);
$T->Set("page",$page);
$T->SetBlock("list","select * from famous_article where fid='".$fid."' and cid='-1' order by id desc limit ".(($p-1)*15).",15");
break;
case "team_mien":
$p=isset($_GET["p"])?$_GET["p"]:"1";
if($p<1)$p=1;
$rc= $T->db->query("select count(1) from famous_pic where fid='".$fid."'")->fetchColumn(0);
$page=getPageHtml_bt($rc,15,$p,"&t=team_mien&f=".$fid."");
$T->Set("page",$page);
$T->SetBlock("list","select * from famous_pic where fid='".$fid."' order by id desc limit ".(($p-1)*15).",15");
break;
case "art_cate": 
$T->SetBlock("list","select * from famous_cate where fid='".$fid."' order by odx asc");
break;
case "member":    
$T->SetBlock("list","SELECT F.*,M.nick,M.truename,M.username FROM famous_member F left join act_member M on M.id=F.uid WHERE F.fid=".$fid);
break;
case "index": #首页 t  
$T->ReadDB("select * from famous where id='".$fid."'");
break;
}
#$fid=isset($_GET["f"])?$_GET["f"]:"1";
#$T->SetBlock("cat_list","select * from famous_cat where fid='".$fid."' order by sortid desc ");
#$T->ReadDB("select * from famous where id='".$fid."'");
$T->Set("gtitle",LR_NAME);
$T->Set("f",$fid);
$T->clearNaN();
$T->clearNoN();
$T->display();
$T->close();
unset($T);
?>