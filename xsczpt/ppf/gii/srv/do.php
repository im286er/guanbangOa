<?php 
/**公用操作
*/
session_start(); 
if(empty($_SESSION["uid"])){
	echo "登录已超时";
	exit();
}

require('../../php/db.php'); 

$ty=isset($_POST["t"])?$_POST["t"]:"";
if(empty($ty)){
	echo "参数为空";
	exit();
}

$conn=openmysql();
switch($ty){
	case "gtnums":
		$rs=mysql_query("select count(1) nums from `t` where `pid`=".$_POST['id'],$conn);
		$nums=mysql_result($rs,0,"nums");
		mysql_query("update t set tnums=".$nums." where id=".$_POST['id'],$conn);
		break;
	case "gcnums": 
		mysql_query("update `t` set pnums=(select count(id) from `p` where `ids` like '".$_POST['ids']."%') where id=".$_POST['id'],$conn);
		$rs=mysql_query("select `pid` from  `t` where id=".$_POST['id'],$conn);
		$pid=mysql_result($rs,0,"pid");
		$rs=mysql_query("select sum(pnums) nums from `t` where `pid`=".$pid,$conn);
		$nums=mysql_result($rs,0,"nums");
		mysql_query("update `t` set pnums=".$nums." where id=".$pid,$conn);
		break;	
}
echo "成功";
if($rs)
	mysql_free_result( $rs);
mysql_close($conn);

?>