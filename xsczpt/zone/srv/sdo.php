<?php 
/*set do
*/
header("Content-type: text/html; charset=utf-8;"); 
#require '../../comm/comm.php';
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start();
#$nick=mb_convert_encoding($_SESSION["nick"],"UTF-8", "GBK");
$uid=isset($_SESSION["uid"])?$_SESSION["uid"]:'';
#chkLoginNoJump("uid"); 
$pd=new pdo_mysql();
switch($_POST["tpl"]){  
 case "upd_leave": 
    $nid=$_POST['id'];   
    $pd->exec("update zone set leavenums=(select count(1) from zone_leave where touid='".$nid."') where id='".$nid."'");   
    echo "ok成功";   
    break;
  case "save_fav": #保存收藏
    $f=$pd->query("select count(*) from zone_fav where url='".$_POST["url"]."' and cid=".$_POST["type"]." and uid='".$uid."'")->fetchColumn(0);
    if($f==0){
      $nid =$pd->gentmrid("zone_fav");
      $rc=$pd->query("select id,uid,title,des from blog_list where id=".$_POST["id"]." and uid='".$uid."'")->fetchAll(PDO::FETCH_ASSOC);
      $pd->exec("insert into zone_fav(id,uid,cid,title,url,des,io,unick) values(".$nid.",'".$uid."',".$_POST['type'].",'".$rc[0]['title']."','".$_POST['url']."','".$rc[0]['des']."',1,'".$_POST['unick']."') ");
      echo "ok收藏成功";
    }else{
      echo "ok您已收藏过该文章";
    }
    break;
  case "set_cover":    
    $p=$pd->query("select pic from zone_album_pic where id=".$_POST["id"]." and uid='".$uid."'")->fetchColumn(0); 
    $pd->exec("update zone_album set cover='".$p."' where id=".$_POST['a']);   
    echo "ok成功";   
    break;
  case "comment_limit":#评论权限   
      $comment=$pd->query("select comment from zone where id='".$uid."'")->fetchColumn(0);
  		echo $comment;	
      break;
  case "weibo_reship":#转发
      $nid=$pd->gentmrid("weibo");
      $time=time(); 
      $dtime = date("Y-m-d H:i:s",time());
      $pd->exec("insert into `".$_POST["tbl"]."` (id,uid,des,see,up,fav,comments,`timestamp`,share,reshipid,created) SELECT  ".$nid.",'".$uid."',des,0,0,0,0,UNIX_TIMESTAMP(),0,".$_POST['id'].",'".$dtime."' from weibo where id=".$_POST['id']);
      $pd->exec("update `".$_POST["tbl"]."` set `".$_POST["do"]."`=ifnull(`".$_POST["do"]."`,0)+1 where id='".$_POST["id"]."'");
      echo "成功";      
      break;
  case "weibo_comment":#微博回复
      $floor=$pd->query("select count(1)+1 from weibo_comments where wid=".$_POST['wid'])->fetchColumn(0);
      //$nid=$pd->gentmrid("weibo_comments");
      $time=time(); 
      $dtime = date("Y-m-d H:i:s",time());                                 
      $sql="insert into weibo_comments(id,uid,wid,des,floor,timestamp,reid,redo,renick,up,bad,comments,created) values(".$time.",'".$uid."','".$_POST['wid']."','".$_POST['des']."','".$floor."',".$time.",'".$_POST['reid']."','".$_POST['redo']."','".$_POST['renick']."',0,0,0,'".$dtime."')";    
      $pd->exec($sql);
      $pd->exec("update weibo set comments=ifnull(comments,0)+1 where id=".$_POST['wid']);
      echo "ok".$time;
      break; 
  case "get_comment":#返回回复
      $rs=$pd->query("select T.*,M.truename,M.nick from weibo_comments T left join act_member M on M.id=T.uid where T.id=".$_POST['id']."");
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
      break;
  case "del_blog_cate":
    $pd->exec("delete from blog_cate where id=".$_POST["id"]." and uid='".$uid."'");
    $pd->exec("update blog set cid=0 where cid=".$_POST["id"]." and uid='".$uid."'");   
    echo 'ok';
    break;
  case "upd_blog_num": #博客添加修改返回
    $pd->exec("update blog_cate set nums=ifnull(nums,0)+1 where id=".$_POST["id"]." and uid='".$uid."'");      
		$pd->exec("update act_member set blog=(select count(1) from blog_list where uid='".$uid."') where id='".$uid."'");
    echo $pd->query("select id from blog_list where uid='".$uid."' order by timestamp desc limit 1")->fetchColumn(0);
    break;
  case "blog_del": #删除博客
		$pd->exec("delete from blog_list where id=".$_POST["id"]." and uid='".$uid."'");
    $pd->exec("delete from blog_comments where bid=".$_POST["id"]." and uid='".$uid."'");
		echo 'ok';	
		break;
  case "msg_del1":
    $mid = $pd->query("select mid from zone_leave_comments where id=".$_POST["id"]."")->fetchColumn(0);
    $pd->exec("delete from zone_leave_comments where id=".$_POST["id"]);
    $pd->exec("update `zone_leave` set comments=(select count(1) from zone_leave_comments where mid=".$mid.") where id=".$mid);
    echo "ok";
    break;
   case "msg_del":
    $pd->exec("delete from `zone_leave` where id=".$_POST["id"]);
    $pd->exec("delete from zone_leave_comments where mid=".$_POST["id"]);
    $pd->exec("update zone set `zone_leave`=(select count(1) from `zone_leave` where touid='".$uid."') where id='".$uid."'");
    echo "ok";
    break;  
   case "msg_resend":
   $floor=$pd->query("select count(1)+1 from zone_leave_comments where mid=".$_POST['id'])->fetchColumn(0);
    $nid=$pd->gentmrid("zone_leave_comments"); 
    $sql="insert into zone_leave_comments(id,uid,mid,des,floor,timestamp) values(".$nid.",'".$uid
    ."','".$_POST['id']."','".$_POST['data']."','".$floor."',UNIX_TIMESTAMP())";    
    $pd->exec($sql);
    $pd->exec("update `zone_leave` set comments=ifnull(comments,0)+1 where id=".$_POST['id']);
    echo "ok".$nid;
    break; 
  case "msg_send":
    $floor=$pd->query("select count(1)+1 from `zone_leave` where touid='".$uid."'")->fetchColumn(0); 
    $nid=$pd->gentmrid("zone_leave");         
    $sql="insert into `zone_leave`(id,touid,uid,des,floor,comments,timestamp,audit) values(".$nid.",'".$uid."','".$uid."','".$_POST['data']
    ."','".$floor."',0,UNIX_TIMESTAMP(),0)";    
    $pd->exec($sql);
    $pd->exec("update zone set `zone_leave`=(select count(1) from `zone_leave` where touid='".$uid."') where id='".$uid."'");
    echo "ok成功";
    break;
  case "albumcomment":
    $pd->exec("insert into album_pic_comments(id,uid,unick,pid,des,timestamp) values(UNIX_TIMESTAMP(),'".$uid
    ."','".$nick."','".$_POST['id']."','".$_POST['data']."',UNIX_TIMESTAMP())");
    $pd->exec("update album_pic set comments=ifnull(comments,0)+1 where id=".$_POST['id']); 
    echo "成功";
    break;
  /*case 'del_photo':
    $p=$pd->query("select pic from zone_album_pic where id=".$_POST["id"]." and uid='".$uid."'")->fetchColumn(0);
    $pid = $pd->query("select id from zone_album_pic where id<".$_POST["id"]." and aid=".$_POST["a"]." order by id desc limit 1")->fetchColumn(0);
    if($p){
      unlink(DIR_ROOT.'/upd/album/'.$uid.'/'.$_POST["a"].'/'.$p);
      unlink(DIR_ROOT.'/upd/album/'.$uid.'/'.$_POST["a"].'/t_'.$p);      
      $pd->exec("delete from zone_album_pic where id=".$_POST["id"]);
      $pd->exec("update zone_album set nums=(select count(1) from zone_album_pic where aid=".$_POST["a"].") from where id=".$_POST["a"]);
      echo "ok".$pid;
    }
    else
      echo "err105:删除出错";    
    break;  
  case "msg_send":
    $floor=$pd->query("select count(1)+1 from `zone_leave` where touid='".$uid."'")->fetchColumn(0); 
    $nid=$pd->gentmrid("zone_leave");         
    $sql="insert into `zone_leave`(id,touid,uid,des,floor,comments,timestamp,audit) values(".$nid.",'".$uid."','".$uid."','".$_POST['data']
    ."','".$floor."',0,UNIX_TIMESTAMP(),0)";    
    $pd->exec($sql);
    $pd->exec("update zone set `zone_leave`=(select count(1) from `zone_leave` where touid='".$uid."') where id='".$uid."'");
    echo "ok成功";
    break; */ 
  /* case "setmsg1": 
    $pd->exec("update zone set leave_open='".$_POST['data']."',leave_limit='".$_POST['data1']."'  where id='".$uid."'");
    echo "成功";
    break;
   
   
	case "signin":#签到 3
     $today=ceil(time()/60/60/24);
    if($pd->query("select count(1) from act_signin where id='".$uid."' and d=".$today)->fetchColumn(0)){
        echo "您今天已签到";
    }
    else{
      $rs=$pd->query("select * from act_signin where id='".$uid."'");        
      $num=INTEGRAL_NUM;
      if($r=$rs->fetch(PDO::FETCH_ASSOC)){#签到过
         $d=$r["d"];
         $d1=$r["d1"];
         $d2=$r["d2"];
         if($d==$today-1){
            $num+=INTEGRAL_NUM;
            if($d1==$today-2){
              $num+=INTEGRAL_NUM;
              if($d2==$today-3)$num+=INTEGRAL_NUM;          
            }
         }
         integralP($pd,$uid,$num,'签到+'.$num); 
         $pd->exec("update act_signin set d2=d1,d1=d,d=".$today."  where id='".$uid."'"); 
         echo "签到成功+".$num;        
      }else{#未签到过
         integralP($pd,$uid,INTEGRAL_NUM,'签到+'.INTEGRAL_NUM); 
         $pd->exec("insert into act_signin values('".$uid."',".$today.",0,0)");
         echo "签到成功+".INTEGRAL_NUM; 
      }
    }   
		break;
 case "upd_blog_comment": # 3
    $pd->exec("update blog_list set comments=(select count(1) from blog_comments where bid=".$_POST["bid"].") where id=".$_POST["bid"]);
    echo "ok"; 		
    break;*/ 
 
 	/*    
 
  case "praise":
    if($pd->query("select count(1) from log_up where id='".$uid."' and tuid='".$_POST["data"]."'")->fetchColumn(0)){ 
      echo "已赞";  
    }
    else {
      $pd->exec("insert into log_up values('".$uid."','".$nick."','".$_POST["data"]."',UNIX_TIMESTAMP())");
      $pd->exec("update zone set up=ifnull(up,0)+1 where id='".$uid."'");
      echo "成功";
    }
    break;*/    
    case "upd_blog_comments":
      $id=$_POST["id"];$bid=$_POST["bid"];    
      $pd->exec("update blog_list set comments=(select count(1) from blog_comments where bid=".$bid.") where id=".$bid);
      break; 
    case "upd_blog_comments_comments":
      $id=$_POST["id"];$reid=$_POST["reid"];$bcid=$_POST["bcid"];    
      $pd->exec("update blog_comments set comments=(select count(1) from blog_comment_comments where bcid=".$bcid.") where id=".$bcid);
      break;  
    case "upd_weibo_comments":
      $id=$_POST["id"];$reid=$_POST["reid"];$wid=$_POST["wid"];    
      $pd->exec("update weibo set comments=(select count(1) from weibo_comments where wid=".$wid.") where id=".$wid);
 		       
      break;
}		
$pd->close();
unset($pd);
unset($rs);