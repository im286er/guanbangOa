<?php #登录
require('../../pdo_mysql.php'); 
$u=isset($_POST["u"])?$_POST["u"]:"";
$p=isset($_POST["p"])?$_POST["p"]:""; 

if($u==""||$p==""){
	echo 'err';
	exit;
}

$sha=md5($p);
$pd=new pdo_mysql();
$rs=$pd->prepare("select * from ppf_manager where username=? and pmd5=?");
$rs->execute(array($u,$sha));
if($r=$rs->fetch(PDO::FETCH_ASSOC)){
	session_start(); 
	$_SESSION["giiuid"]=$r["id"];
	$_SESSION["giiname"]=$r["name"];
	$pd->exec("update ppf_manager set lgnums=ifnull(lgnums,0)+1,lasttime=UNIX_TIMESTAMP(),lastip='".$_SERVER["REMOTE_ADDR"]."' where id=".$r["id"]);
	echo 'ok';	
}
else{
	echo 'err';
}
#验证连接
$pd->close();
unset($rs);
unset($pd); 