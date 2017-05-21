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
 /*case "chkreg":
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
    break;  */  
  case "set_tpl":		 
		$pd->exec("update `main` set tpl='". $_POST['data']."' where id=1");	
    echo "ok";
		break;        
 case "aud_active": #
    $pd->exec("update activity set state=2,status=1 where id=".$_POST["id"]);
    echo "ok";  
    break;  
 case "del_sch_depart": #
    $pd->exec("delete from `sch_department` where id='".$_POST["id"]."'");
      echo "ok";  
    break;  
 case "del_topic": #
      $pd->exec("delete from `sch_topic` where id='".$_POST["id"]."'");
      echo "ok";  
    break; 
  case "del_admin": #
      $pd->exec("delete from `main_member` where id='".$_POST["id"]."'");
      echo "ok";  
    break; 
  case "ad_man":#添加管理员
    $u=$_POST["u"];
    $txt=$_POST["txt"];     
    $s=$_POST["s"];
    $nid=$pd->genid("sch_admin");
    $pd->exec("insert into sch_admin(id,sid,uid,intro,timestamp) values(".$nid.",".$s.",'".$u."','".$txt."',UNIX_TIMESTAMP())");
    echo "ok";   
    break;
  case "del_clsman": #
      $pd->exec("delete from `cls_member` where id='".$_POST["id"]."' and isman=2");
      echo "ok";  
    break;
  case "ad_clsman":#添加班级管理员
    $u=$_POST["u"];
    $txt=$_POST["txt"];     
    $c=$_POST["c"];
    $rs=$pd->query("select count(1) from cls_member where isman=2 and cid=".$c." and uid='".$u."'")->fetchColumn(0);
		if($rs==0){
      $nid=$pd->genid("cls_member");
      $pd->exec("insert into cls_member(id,uid,cid,idtype,intro,isman,timestamp,lastdo) values(".$nid.",'".$u."',".$c.",2,'".$txt."',2,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
    }else{
      $pd->exec("update `cls_member` set isman=2  where cid=".$c." and uid='".$u."'");
    }
    echo "ok";   
    break; 
  case "m_clsman":#修改班级管理员
    $id=$_POST["id"];
    $txt=$_POST["txt"];
    $pd->exec("update `cls_member` set intro='".$txt."'  where id=".$id." ");
    echo "ok";   
    break;
  case "del_grpman": #
      $pd->exec("delete from `grp_member` where id='".$_POST["id"]."' and isman=2");
      echo "ok";  
    break;
  case "ad_grpman":#添加群组管理员
    $u=$_POST["u"];
    $txt=$_POST["txt"];     
    $g=$_POST["g"];
    $rs=$pd->query("select count(1) from grp_member where isman=2 and gid=".$g." and uid='".$u."'")->fetchColumn(0);
		if($rs==0){
      $nid=$pd->genid("grp_member");
      $pd->exec("insert into grp_member(id,uid,gid,intro,isman,timestamp,lastsay) values(".$nid.",'".$u."',".$g.",'".$txt."',2,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
    }else{
      $pd->exec("update `grp_member` set isman=2  where gid=".$g." and uid='".$u."'");
    }
    echo "ok";   
    break; 
  case "m_grpman":#修改群组管理员
    $id=$_POST["id"];
    $txt=$_POST["txt"];
    $pd->exec("update `grp_member` set intro='".$txt."'  where id=".$id." ");
    echo "ok";   
    break;
  case "del_famman": #删除名师管理员
      $pd->exec("delete from `famous_member` where id='".$_POST["id"]."' and isman=2");
      echo "ok";  
    break;
  case "ad_famman":#添加名师管理员
    $u=$_POST["u"];
    $txt=$_POST["txt"];     
    $f=$_POST["f"];
    $rs=$pd->query("select count(1) from famous_member where isman=2 and fid=".$f." and uid='".$u."'")->fetchColumn(0);
		if($rs==0){
      $nid=$pd->genid("famous_member");
      $pd->exec("insert into famous_member(id,uid,fid,intro,isman,timestamp) values(".$nid.",'".$u."',".$f.",'".$txt."',2,UNIX_TIMESTAMP())");
    }else{
      $pd->exec("update `famous_member` set isman=2  where fid=".$f." and uid='".$u."'");
    }
    echo "ok";   
    break; 
  case "m_famman":#修改群组管理员
    $id=$_POST["id"];
    $txt=$_POST["txt"];
    $pd->exec("update `famous_member` set intro='".$txt."'  where id=".$id." ");
    echo "ok";   
    break;
   
  
    case "del_art": #
      $pd->exec("delete from `sch_art` where id=".$_POST["id"]);
      echo "ok";  
    break; 
  case "aud_fam": 
      $pd->exec("update `famous` set state=2  where id=".$_POST["id"]);
        echo "ok";  
    break;
    case "del_fam": #
      $pd->exec("update `famous` set state=4  where id=".$_POST["id"]);
      echo "ok";  
    break; 
  case "aud_sub": 
      $pd->exec("update `subject` set state=2  where id=".$_POST["id"]);
        echo "ok";  
    break;
    case "del_sub": #
      $pd->exec("update `subject` set state=4  where id=".$_POST["id"]);
      echo "ok";  
    break; 
  case "aud_grp": 
      $pd->exec("update `group` set state=2  where id=".$_POST["id"]);
        echo "ok";  
    break;
    case "del_grp": #
      $pd->exec("update `group` set state=4  where id=".$_POST["id"]);
      echo "ok";  
    break;           
  case "aud_cls": #
      $pd->exec("update `class` set state=2  where id=".$_POST["id"]);
        echo "ok";  
    break;
    
    
    
    
  case "aud_art": 
      $pd->exec("update `main_article` set state=2  where id=".$_POST["id"]);
        echo "ok";  
    break;  
	
   case "del_act": 
      $pd->exec("delete from `act_member` where id='".$_POST["id"]."'");
      $pd->exec("delete from `cls_member` where uid='".$_POST["id"]."'");
      $pd->exec("delete from `grp_member` where uid='".$_POST["id"]."'");
      $pd->exec("delete from `act_school` where uid='".$_POST["id"]."'");
      $pd->exec("delete from `sch_admin` where uid='".$_POST["id"]."'");
      echo "ok";  
    break;  
  case "admin_user": 
      if($pd->query("select count(1) from main_member where uid='".$_POST["id"]."'")->fetchColumn(0))
        echo "err用户已经是管理员";
      else{
        $nid=$pd->genid("main_member");
        $pd->exec("insert into main_member(id,uid,timestamp) values(".$nid.",'".$_POST["id"]."',UNIX_TIMESTAMP())");
        echo "ok";  
      }
    break;
	case "aud_push_list": 
		$pd->exec("update `push_list` set state=2  where id=".$_POST["id"]);
        echo "ok";  
		break;
  case "del_recommend":		 
    $pd->exec("delete from recommend_list where id=".$_POST["id"]." and uid='".$uid."'");
    //$pd->exec("delete from recommend_comments where bid=".$_POST["id"]);
    echo 'ok';
    break;
  case "cancel_recommend":
    $pd->exec("update `recommend_list` set state=0  where id=".$_POST["id"]);
    echo 'ok';
    break;
  case "del_leave":		 
    $pd->exec("delete from zone_leave where id=".$_POST["id"]);
    $pd->exec("delete from zone_leave_comments where mid=".$_POST["id"]);
    echo 'ok';
    break;
  case "del_leave_comments":		  
    $pd->exec("delete from zone_leave_comments where id=".$_POST["id"]);
    echo 'ok';
    break;
 case "audit_recommend": 
      $pd->exec("update `recommend_list` set state=2  where id=".$_POST["id"]);
        echo "ok";  
    break;
case "recommend_send_end":
$pd->exec("update zone set blog=(select count(1) from recommend_list where uid='".$uid."') where id='".$uid."'");
break;
}		
$pd->close();
unset($pd);
unset($rs);