<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。

//发现了time,请自行验证这套程序是否有时间限制.
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
#取回密码
header("Content-type: text/html; charset=utf-8");
#require('../ppf/360_safe3.php');
require('../ppf/pdo_mysql.php');
require_once('../ppf/mail/class.phpmailer.php');
require('../cfg/mail.inc');
$ip = $_SERVER["REMOTE_ADDR"];
$pd=new pdo_mysql();
switch($_POST["do"]){
case "step1":
$u=$_POST["u"];
$ln=strlen($u);
if(strpos($u,'@'))
$sql="select id,email from act_member where email='".$u."'";
elseif($ln==11&&is_numeric($u))#手机
$sql="select id,email from act_member where mobile='".$u."'";
else
$sql="select id,email from act_member where username='".$u."'";
$rs=$pd->query($sql);
if($r=$rs->fetch(PDO::FETCH_ASSOC)){
$id=$r["id"];
$em=$r["email"];
#$tkey=rand(10000,99999);
$tmp=md5(time());
$pd->exec("update act_member set tmp='".$tmp."',timestamp=UNIX_TIMESTAMP() where id='".$id."'");
$url=RESET_PASS_URI.'&u='.$id.'&k='.$tmp;
$body = "<h2>这是一封系统邮件，请务回复</h2><br/><h3>恭喜您取回密码成功，您的验证码是：".$tmp."</h3><br/>";
$body .= '重设密码页面地址：<a href="'.$url.'" target=_blank>'.$url.'</a>';
$body .= "<br/>邮件来自安全系统";
if(sendEmail('人人通安全系统',$em,'密码取回','密码取回成功',$body)){
echo "ok";
}
else
echo "系统错误:发送邮件出错误";
}
else
echo "帐号不存在";
break;
case "step2":
$psha=md5($_POST["p"]);
$sql="update act_member set pmd5='".$psha."',lastip='".$ip."',tmp=md5(now()) where id='".$_POST["u"]."' and tmp='".$_POST["k"]."'";
if($pd->exec($sql)){
echo "ok";
}
else{
echo "您的验证已过期,请返回重新操作";
}
break;
case "chkreg":
$u=$_POST["u"];
#$t=$_POST["t"];
$m=$_POST["m"];
$e=$_POST["e"];
if($pd->query("select count(1) from act_member where username='".$u."'")->fetchColumn(0))
echo "用户名已存在";
elseif($pd->query("select count(1) from act_member where mobile='".$m."'")->fetchColumn(0))
echo "手机已存在";
elseif($pd->query("select count(1) from act_member where email='".$e."'")->fetchColumn(0))
echo "邮箱已存在";
else 
echo "ok";
break;
}
$pd->close();
unset($rs);
unset($pd);
?>