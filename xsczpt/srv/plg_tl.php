<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../ppf/fun.php';
require '../ppf/pdo_mysql.php';

if (!session_id()) session_start(); 

$pd=new pdo_mysql();
if(!isset($_GET["t"])) exit;

switch($_GET["t"]){
case "ologin":#ͳһ��¼��¼��ϵͳ
        //err:1 �������û�ʧ�� err:2 �ǵ�ǰ��վ�û�
        $id=isset($_POST["id"])?$_POST["id"]:"";
		if(empty($id)){echo "err:1";exit;}
        $user_info=file_get_contents(APP_API_URL.'/getuser/?t=extend&ap='.APPID.'&k='.APPKEY.'&u='.$id);
		
		$user_info=json_decode($user_info);
		if($user_info->orgid!=APPORGID){echo "err:2";exit;}
        $ip=getIP();
		$sql="select * from act_member where bid=? limit 1";//ͳһ��¼���û���id
		$rs=$pd->prepare($sql);
		$rs->execute(array($id));
		if($r=$rs->fetch(PDO::FETCH_ASSOC)){	 
			$id=$r["id"];
			setUser($r); //����Session	
            
			$pd->exec("update act_member set lgnums=ifnull(lgnums,1)+1,lastip='".$ip."',lasttime=UNIX_TIMESTAMP() where id=".$id);
			
			echo "ok";
		}
		
	break;  
}

$pd->close();
unset($rs);
unset($pd);



#�����û� _SESSION fun
function setUser($r){
	$_SESSION['uid'] = $r["id"];
	$_SESSION['username'] = $r["username"];
	$_SESSION['idtype'] = $r["idtype"];
	$_SESSION['nick'] = $r['nick']; 
	$_SESSION['school'] = $r['school'];  
	$_SESSION['face'] = '/upd/face/'.$r["id"].'.jpg';
	$_SESSION['gold'] =$r['gold']; 
	$_SESSION['credit'] = $r['credit'];
	$_SESSION['integral'] = $r['integral']; 
	$_SESSION['msg'] = $r['msg'];	
	$_SESSION["bid"]=$r["bid"]; #ͳһ�ʺ�id;
	$_SESSION["user_type"]= '0';
}