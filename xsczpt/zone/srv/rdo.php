<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

#检测登录
if (!session_id()) session_start();
#chkLoginNoJump("uid");  
$uid=isset($_SESSION["uid"])?$_SESSION["uid"]:'';

$pd=new pdo_mysql();
switch($_POST["tpl"]){
  case "get_zone_fav_cate":#收藏类别
    $rs=$pd->query("SELECT * from zone_fav_type where uid='".$uid."' order by id desc");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_photo_last":#g
    $n=$pd->query("SELECT id from zone_album_pic where aid=".$_POST["a"]." and id<".$_POST["id"]." ORDER BY id DESC LIMIT 10,1")->fetchColumn(0);
    if($n)
      $rs=$pd->query("SELECT * from zone_album_pic where aid=".$_POST["a"]." and id>".$n." ORDER BY id desc LIMIT 10");
    else
      $rs=$pd->query("SELECT * from zone_album_pic where aid=".$_POST["a"]." ORDER BY id desc LIMIT 10");
   	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;  
  /*case "get_sch_sub_r":
    $rs=$pd->query("select id,name from sub_rate where sid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break; 
  case "get_sch_sub_t":
    $rs=$pd->query("select id,name from sub_type where sid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "get_sch_sub_c":
    $rs=$pd->query("select id,name from sub_cate where sid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "get_sch_art_cate":
    $rs=$pd->query("select id,name from sch_art_cate where sid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "get_my_sch":
    $rs=$pd->query("select id,name from school where id in (select sid from act_school where uid='".$uid."')"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
 case "get_my_grp":
    $rs=$pd->query("select id,name from `group` where id in (select gid from grp_member where uid='".$uid."')"); 
			echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_my_cls":
    $rs=$pd->query("select id,name from class where id in (select cid from cls_member where uid='".$uid."')"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  */
  case "get_limit_do": 
    $zid=$_POST["id"]; 
    if($zid==$uid){
        $comment="0";
        $leave="0";  
    }
    else{
        $comment=$pd->query("select ifnull(`comment`,0) from `zone` where id='".$zid."'")->fetchColumn(0);
        $leave=$pd->query("select ifnull(`leave`,0) from `zone` where id='".$zid."'")->fetchColumn(0);
        if(empty($comment))$comment="0";
        if(empty($leave))$leave="0";  
        #好友可以回复|留言
        if($pd->query("select count(1) from act_friend where uid='".$zid."' and fid='".$uid."'")->fetchColumn(0)){
            if($comment=="1") $comment="0"; 
             if($leave=="1") $leave="0";     
        }
    }
    echo '{"comment":"'.$comment.'","leave":"'.$leave.'"}'; 
    break;       
  case "album_visit": 
    if($pd->query("select count(1) from zone_album where pwd='".$_POST["data"]."' and id=".$_POST["id"])->fetchColumn(0)){
        $_SESSION["albumpwd"]=$_POST["data"];    
        echo "ok";
    }
    else echo "err访问密码错误"; 
    break;       
  case "z_visit": 
    if($pd->query("select count(1) from zone where visitpwd='".$_POST["data"]."' and id='".$_POST["id"]."'")->fetchColumn(0)){
        $_SESSION["zonepwd"]=$_POST["data"];    
        echo "ok";
    }
    else echo "err访问密码错误";
    break;   
  case "get_blog_comment_comments_next":
    $p=$_POST["page"];$id=$_POST["id"];     
    $rs=$pd->query("select T.*,M.nick from blog_comment_comments T left join act_member M on M.id=T.uid where bcid=".$id." order by T.id desc limit ".($p*10).",10");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	     	
    break;    
  case "get_weibo_comments_next":
    $p=$_POST["page"];$id=$_POST["id"];
    $rs=$pd->query("select T.id,T.uid,T.wid,T.des,IFNULL(T.redo,'') nredo,IFNULL(T.renick,'') nrenick,IFNULL(T.created,'') ncreated,M.truename,M.nick from weibo_comments T left join act_member M on M.id=T.uid where wid=".$id." order by T.id desc limit ".($p*10).",10");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	     	
    break;    
  case "getblogcate": #博客类别 3
		$rs=$pd->query("select id,name from blog_cate where uid='".$uid."' order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
}		
$pd->close();
unset($pd);
unset($rs);