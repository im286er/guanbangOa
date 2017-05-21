<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
header("Content-type: text/html; charset=utf-8;");
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';
if (!session_id()) session_start();
$uid=$_SESSION['uid'];
chkLoginNoJump("uid");
$pd=new pdo_mysql();
$fid=$_POST["f"];
switch($_POST["tpl"]){
case "famousZone":		 
$pd->exec("update `famous` set `tpl`='".$_POST['data']."' where id='".$fid."'");
echo "ok";
break;
case "set_fms_isman": 
$pd->exec("update famous_member set isman=".$_POST["val"]." where id=".$_POST["id"]."");
echo "ok";
break;
case "add_user":
$u=$_POST["u"];
if(!$pd->query("select count(1) from act_member where username='".$u."'")->fetchColumn(0))
echo "err用户不存在";
else{
$userid=$pd->query("select id from act_member where username='".$u."'")->fetchColumn(0);
if($pd->query("select count(1) from famous_member where uid='".$userid."' and fid=".$_POST["f"])->fetchColumn(0))
echo "err用户已经存在";
else{
$nid=$pd->genid("famous_member");
$pd->exec("insert into famous_member(id,fid,uid,isman,`state`) values(".$nid.",".$_POST["f"].",'".$userid."',0,2)");
echo "ok";
}
}
break;
}
$pd->close();
unset($pd);
unset($rs);
?>