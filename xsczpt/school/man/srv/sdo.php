<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
header("Content-type: text/html; charset=utf-8;");
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';
if (!session_id()) session_start();
chkLoginNoJump("uid");
$uid=$_SESSION['uid'];
//$sid=$_GET["s"];#班级id
 $pd=new pdo_mysql();
 $sid=$_POST["s"];
switch($_POST["tpl"]){
case "schoolZone":		 
$pd->exec("update `school` set `tpl`='".$_POST['data']."' where id='".$sid."'");
//$pd->exec("update `school` set `tpl`='".$_POST['data']."' where id= 2 ");
echo "ok";
break;
case "sub_clear": 
array_map( "unlink",glob(DIR_ROOT.'/data/subject/*') );
echo "操作完成";
break;
case "chkreg":
$u=$_POST["u"];
$t=$_POST["t"];
$m=$_POST["m"];
$e=$_POST["e"];
$rs=$pd->prepare("select count(1) from act_member where username=".$u." or mobile=".$m." or email=".$e."");
$rs->execute(array($u,$m,$e));
if($rs->fetchColumn(0))
echo "error";
else  
echo " ok";
break;
case "ad_man":#添加管理员
$u=$pd->query("select id from act_member where username='".$_POST["u"]."'")->fetchColumn(0);
if(empty($u))echo "用户名不存在";
else{
if($pd->query("select count(1) from sch_admin where uid='".$u."' and sid=".$_POST["s"])->fetchColumn(0))
echo "用户已是此学校的管理员";
else{
$nid=$pd->genid("sch_admin");
$pd->exec("insert into sch_admin(id,uid,sid,timestamp) values(".$nid.",'".$u."','".$_POST["s"]."',UNIX_TIMESTAMP())");
echo "ok";
}
}
break;
case "ad_clsman":#添加班级管理员
$u=$_POST["u"];
$c=$_POST["c"];
$uid1=$pd->query("select id from act_member where username='".$u."'")->fetchColumn(0);
if(empty($uid1))echo "用户名不存在";
else{
if($pd->query("select count(1) from cls_member where uid='".$uid1."' and cid=".$c)->fetchColumn(0))
echo "用户已是存在此班级";
else{
$nid=$pd->genid("cls_member");
$idtype=$pd->query("select idtype from act_member where id='".$uid1."'")->fetchColumn(0);
$pd->exec("insert into cls_member(id,uid,cid,idtype,isman,state,timestamp) values(".$nid.",'".$uid1."','".$c
."',".$idtype.",1,2,UNIX_TIMESTAMP())");
echo "ok";
}
}
break;
case "ad_grpman":  
$u=$pd->query("select id from act_member where username='".$_POST["u"]."'")->fetchColumn(0);
if(empty($u))echo "用户名不存在";
else{
if($pd->query("select count(1) from grp_member where uid='".$u."' and gid=".$_POST["id"])->fetchColumn(0))
echo "用户已是存在此群组";
else{
$pd->exec("insert into grp_member(id,uid,gid,isman,state,timestamp) values(UNIX_TIMESTAMP(),'".$u."','".$_POST["id"]
."',1,2,UNIX_TIMESTAMP())");
echo "ok";
}
}
break;
case "add_famman":           
$u=$pd->query("select id from act_member where username='".$_POST["u"]."'")->fetchColumn(0);
if(empty($u))echo "用户名不存在";
else{
if($pd->query("select count(1) from famous_member where uid='".$u."' and fid=".$_POST["id"])->fetchColumn(0))
echo "用户已是存在此工作室";
else{
$pd->exec("insert into famous_member(id,uid,fid,isman,state,timestamp) values(UNIX_TIMESTAMP(),'".$u."','".$_POST["id"]
."',1,2,UNIX_TIMESTAMP())");
echo "ok";
}
}
break;
}
$pd->close();
unset($pd);
unset($rs);
?>