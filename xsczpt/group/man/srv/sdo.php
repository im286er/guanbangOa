<?php 
/*set do
*/
header("Content-type: text/html; charset=utf-8;"); 
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';


if (!session_id()) session_start();  
chkLoginNoJump("uid"); 
$uid=$_SESSION['uid'];

$pd=new pdo_mysql();
switch($_POST["tpl"]){ 
  case "zone_tpl":		 
		$pd->exec("update `group` set tpl='". $_POST['data']."' where id=".$_POST['gid']);	
        echo "ok";
		break; 
  case "add_user":
    $u=$_POST["u"];
    if(!$pd->query("select count(1) from act_member where username='".$u."'")->fetchColumn(0))
      echo "err用户不存在";
    else{
      $userid=$pd->query("select id from act_member where username='".$u."'")->fetchColumn(0);
      if($pd->query("select count(1) from grp_member where uid='".$userid."' and gid=".$_POST["gid"])->fetchColumn(0))
        echo "err用户已经存在";
      else{
       $nid=$pd->genid("grp_member");           
       $pd->exec("insert into grp_member(id,gid,uid,isman,`state`) values(".$nid.",".$_POST["gid"].",'".
       $userid."',0,1)");
       echo "ok" ;
      }
    }
    break;
   case "setcover":#m
     $pd->exec("update `grp_album` set logo='".$_POST["v"]."' where id=".$_POST["aid"]);  
     echo "ok";
		break;  
  /*
	case "del_group":#t
     #$pd->exec("update `group` set del=1 where id=".$_POST["id"]." and uid='".$uid."'"); 	
     $pd->exec("delete from `group` where id=".$_POST["id"]." and uid='".$uid."'");  
     $pd->exec("delete from stu_group where gid=".$_POST["id"].""); 
     echo "ok成功";
		break;  */

   case "set_grp_man": #g
     $pd->exec("update grp_member set  `isman`=".$_POST["val"]."  where id='".$_POST["id"]."' and gid=".$_POST["gid"]); 
      $pd->exec("update `group` set  nums=(select count(1) from grp_member where gid=".$_POST["gid"]." and state=2),mannums=(select count(1) from grp_member where state=2 and isman>0 and gid=".$_POST["gid"].") where id=".$_POST["gid"]); 
     echo "ok";
    break; 
}		
$pd->close();
unset($pd);
unset($rs);