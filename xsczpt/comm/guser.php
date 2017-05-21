<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。

//发现了time,请自行验证这套程序是否有时间限制.
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
header("Content-type: text/html; charset=utf-8;");
require_once 'comm.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ppf/fun.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ppf/pdo_mysql.php';
class  GUser{
protected $pd;
public function __construct($open=true,$charset='gbk'){
if($open)
$this->openconn($charset);
}
public function openConn($charset='gbk'){
if(!isset($this->pd))
$this->pd=new pdo_mysql('',DB_USER,DB_PWD,$charset);
}
public function close(){
if($this->pd)  {
$this->pd->close();
unset($this->pd);
}
}
public function chkLogin(){
if (!session_id()) session_start();
return isset($_SESSION["uid"]);
}
public function login($u,$flag=0){
	//$user_type = 0;
//$user = mb_convert_encoding($u['username'], 'utf-8', 'gbk');
switch($flag){
case 0:
 if(preg_match('/^[A-Za-z0-9]+$/',$u['username'])){
		if($u['user_type']==3){
			$rs=$this->pd->prepare("select * from v_users where username=? and parent_pass=?");
			$rs->execute(array($u["username"],md5($u["password"]))); 
			
		}else{
			$rs=$this->pd->prepare("select * from act_member where username=? and pmd5=?");
			$rs->execute(array($u["username"],md5($u["password"]))); 
		}
		
 }else{
		$user = mb_convert_encoding($u['username'],'gbk','utf-8');
		$rs=$this->pd->prepare("select * from act_member where truename=? and pmd5=?");
		$rs->execute(array($user,md5($u["password"]))); 
 }
 $user_type = $u['user_type'];
 /* $rs=$this->pd->prepare("select * from act_member where username=? and pmd5=?");
 $rs->execute(array($u["username"],md5($u["password"]))); */
break;
case 1:
$user_type = 0;
$rs=$this->pd->prepare("select * from act_member where email=? and pmd5=?");
$rs->execute(array($u["email"],md5($u["password"])));
break;
case 2:
$user_type = 0;
$rs=$this->pd->prepare("select * from act_member where mobile=? and pmd5=?");
$rs->execute(array($u["mobile"],md5($u["password"])));
break;
}

if($r=$rs->fetch(PDO::FETCH_ASSOC)){
	if($r["state"]!="2")return 1;
	$u= $this->setSession($u,$r,$user_type);
	$ckey=md5(time());
	if(!empty($u["save"])) $this->setCookie($u["id"],$ckey);
	$this->pd->exec("update act_member set tmp='".$ckey."',lgnums=ifnull(lgnums+1,1),lasttime=UNIX_TIMESTAMP(),lastip='".$_SERVER["REMOTE_ADDR"]."' where id='".$u["id"]."'");
	log_log($this->pd,$u["id"],$_SERVER["REMOTE_ADDR"],1);
	return $u;
}
else   
	return 0;
}
public function loginNoPass($u,$flag=0){
switch($flag){
case 0:
$rs=$this->pd->prepare("select * from act_member where username=?");
$rs->execute(array($u["username"]));
break;
case 1:
$rs=$this->pd->prepare("select * from act_member where email=? ");
$rs->execute(array($u["email"]));
break;
case 2:
$rs=$this->pd->prepare("select * from act_member where mobile=?");
$rs->execute(array($u["mobile"]));
break;
case 3: #id登录
$rs=$this->pd->prepare("select * from act_member where id=?");
$rs->execute(array($u["id"]));
break;
}
if($r=$rs->fetch(PDO::FETCH_ASSOC)){
$user_type = 0;
$u= $this->setSession($u,$r,$user_type);
$ckey=md5(time());
if(!empty($u["save"])) $this->setCookie($u["id"],$ckey);
$this->pd->exec("update act_member set tmp='".$ckey."',lgnums=ifnull(lgnums+1,1),lasttime=UNIX_TIMESTAMP(),lastip='".$_SERVER["REMOTE_ADDR"]."' where id='".$u["id"]."'");
log_log($this->pd,$u["id"],$_SERVER["REMOTE_ADDR"],1);
return $u;
}
else   
return 0;
}
public function loginCook($id,$k){
$rs=$this->pd->prepare("select * from act_member where id=? and tmp=?");
$rs->execute(array($id,$k));
if($r=$rs->fetch(PDO::FETCH_ASSOC)){
$u=array();
$user_type = 0;
$u= $this->setSession($u,$r,$user_type);
$this->pd->exec("update act_member set lgnums=ifnull(lgnums+1,1),lasttime=UNIX_TIMESTAMP(),lastip='".$_SERVER["REMOTE_ADDR"]."' where id='".$id."'");
log_log($this->pd,$id,$_SERVER["REMOTE_ADDR"],1);
return $u;
}
else   
return 0;
}
private function setSession($u,$r,$t){
if (!session_id()) session_start();#检测session启动    
$u["id"]=$_SESSION["uid"]=$r["id"];
$u["username"]=$_SESSION["username"]=$r["username"];
//$u["truename"]=$_SESSION["truename"]=$r["truename"];
$u["idtype"]=$_SESSION["idtype"]=$r["idtype"];
$u["nick"]=$_SESSION["nick"]=$r["nick"];
$u["school"]=$_SESSION["school"]=$r["school"];
#if($r["face"])	
$u["face"]=$_SESSION["face"]='/upd/face/'.$u["id"].'.jpg';
#else
#  $u["face"]=$_SESSION["face"]='/error/face.jpg';
$u["gold"]=$_SESSION["gold"]=$r["gold"];
$u["credit"]=$_SESSION["credit"]=$r["credit"];
$u["integral"]=$_SESSION["integral"]=$r["integral"];
$u["msg"]=$_SESSION["msg"]=$r["msg"];
$u["user_type"]=$_SESSION["user_type"]=$t;  //角色
return $u;
}
private function setCookie($u,$k){
$expire=time()+8640000;#100天
setcookie('cu',$u,$expire,"/");#cookie按目录存 请带上/ 如果要通用请带上domain
setcookie('ck',$k,$expire,"/");
}
public function logout(){
if (!session_id()) session_start();
if(isset($_SESSION["uid"])){
#更新用户退出
unset($_SESSION["uid"]);
session_destroy();#session_unset();相同;
}
}
public function chkServer($uid=0){
$sid=$this->pd->query("select ssid from org_units where id=(select unit from act_member where id='".$uid."')")->fetchColumn(0);
if(!empty($sid)&&$sid!=LR_SID){
#更新主服务参数给从服务验证
$code=md5(time());
$this->pd->exec("update act_member set tmp='".$code."' where id='".$uid."'");
return $this->pd->query("select url from sys_server where sid='".$sid."'")->fetchColumn(0)
.'/api/layer?t=skip&vi='.$uid.'&code='.$code;
#使用客户端跳转
}
else 
return 0;
}
public function updateInfo($info){
}
}
?>