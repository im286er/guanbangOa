<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];

$pd=new pdo_mysql();
switch($_POST["tpl"]){ 
 case "get_in_active": 
		echo $pd->query("SELECT count(1) from active_member where aid=".$_POST["id"]." and uid='".$uid."'")->fetchColumn(0);	 
		break;         
 case "acitve_get_visits": 
    $ids=$pd->query("select visits from activity where id=".$_POST["id"])->fetchColumn(0);
    if($ids)
      echo  $pd->query("select group_concat(truename) FROM `act_member` where id in (".$ids.")")->fetchColumn(0);
		else
      echo "null";	
		break;
  case "acitve_get_invites": 
    $ids=$pd->query("select invites from activity where id=".$_POST["id"])->fetchColumn(0);
    if($ids)
      echo  $pd->query("select group_concat(truename) FROM `act_member` where id in (".$ids.")")->fetchColumn(0);
		else
      echo "null";	
		break;
     
  case "get_blog_comment_comments_next":
    $p=$_POST["page"];$id=$_POST["id"];     
    $rs=$pd->query("select T.*,M.truename from blog_comment_comments T left join act_member M on M.id=T.uid where bcid=".$id." order by T.id desc limit ".($p*10).",10");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	     	
    break;     
  case "get_weibo_comments_next":
    $p=$_POST["page"];$id=$_POST["id"];     
    $rs=$pd->query("select T.*,M.truename from weibo_comments T left join act_member M on M.id=T.uid where wid=".$id." order by T.id desc limit ".($p*10).",10");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	     	
    break;    
  case "get_famous_cate":
    $rs=$pd->query("select id,name from famous_cat where fid=".$_POST["id"]." order by sortid desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "get_sch_art_cate":
    $rs=$pd->query("select id,name from sch_art_cate where sid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;  
   case "get_class_stu":
    $rs=$pd->query("select id,truename name from act_member where id in (SELECT id from cls_member where idtype=1 and cid=".$_POST["id"].")"); 
  	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_def_info":
    $rs=$pd->query("select * from tech_def_property where id=".$_POST["id"]);    
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;   
  case "get_def_property":
    $rs=$pd->query("select id,name from tech_def_property where uid='".$uid."' order by timestamp desc");    
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "get_cls_property":
    $rs=$pd->query("select * from `class` where id=".$_POST["id"]);    
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_my_famous":
    $rs=$pd->query("select id,name from famous where id in (select fid from famous_member where uid='".$uid."')");    
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "get_my_sch":
    $rs=$pd->query("select id,name from school where id in (select sid from act_school where uid='".$uid."')");    
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "get_my_cls":#t 获取我的班级 
   	$rs=$pd->query("select id,name from class where id in (select cid from cls_member where uid='".$uid."')"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;
  case "get_my_grp":
    $rs=$pd->query("select id,name from `group` where id in (select gid from grp_member where uid='".$uid."')"); 
	  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;   
  case "sel_work":#t 
    $rs=$pd->query("select id,title from tech_homework where uid='".$uid."' and title like '%".$_POST["title"]."%' limit 20");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "sel_workl":#t
    $rs=$pd->query("select id,title from tech_homework where uid='".$uid."' order by id desc limit 20");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
 /*  case "sel_ware":#t获取课件
    $sql="select id,title from res_courseware where uid='".$uid."'";
    if(!empty($_POST["grade"]))$sql.=" and grade=".$_POST["grade"];
    if(!empty($_POST["subject"]))$sql.=" and subject=".$_POST["subject"];
    if(!empty($_POST["edition"]))$sql.=" and edition=".$_POST["grade"];
    if(!empty($_POST["volume"]))$sql.=" and volume=".$_POST["volume"];
    if(!empty($_POST["chapter"]))$sql.=" and chapter=".$_POST["chapter"];
    if(!empty($_POST["title"]))$sql.=" and title like '%".$_POST["title"]."%'";
    $rs=$pd->query($sql." order by id desc limit 20");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "sel_ware_late": #t 
    $rs=$pd->query("select id,title from res_courseware where uid='".$uid."' order by id desc limit 20");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break; */
  case "get_chapter_textbook":#t获取课
    $rs=$pd->query("select id,title name,no from sys_textbook where grade=".$_POST["grade"]." and subject=".$_POST["subject"]
      ." and edition=".$_POST["edition"]." and volume=".$_POST["volume"]." and chapter=".$_POST["chapter"]);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  /*case "get_schedule":#t
    $rs=$pd->query("select * from tech_schedule where uid='".$uid."' and year(stime)=".$_POST["y"]." and month(stime)=".$_POST["m"]);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break; */   
  case "get_timetable":#t 获取课表 
    $rs=$pd->query("SELECT * from class_timetable where cid=".$_POST["id"]);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  /*case "get_class_my_subject1":#t 
    $rs=$pd->query("SELECT id,subject,edition from cls_member where cid=".$_POST["id"]." and id='".$uid."'"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;   
  */   
  case "get_cls_tech_info": 
    $rs=$pd->query("SELECT subject,edition from cls_member where cid=".$_POST["id"]." and uid='".$uid."'"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "get_class_info":#t 
    $rs=$pd->query("SELECT id,period,grade,school from `class`  where id=".$_POST["id"]); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_class_tech":#t 获取班级的教师
    $rs=$pd->query("select id,truename name from act_member where id in (SELECT uid from cls_member where cid=".$_POST["id"]." and idtype=2)");  #id in (select uid from class where id=".$_POST["id"].") or
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;	    
	case "get_class":#t 获取我的班级 
   	$rs=$pd->query("select id,name from class where id in (select cid from cls_member where uid='".$uid."')"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;
/*	case "get_sch_1": #t
	  $rs=$pd->query("SELECT id,name from class where school=".$_POST['sch']." and grade=".$_POST['grade']);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break; 		  */
   case "get_fav_cate":
		$rs=$pd->query("SELECT id,name from zone_fav_type where uid='".$uid."' order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
  case "get_blog_cate":
		$rs=$pd->query("SELECT id,name from blog_cate where uid='".$uid."' order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
	case "get_push_cate":
		$rs=$pd->query("SELECT id,name from push_category where push_type='".$_POST["push_type"]."' order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
   case "get_addr": 
	   $rs=$pd->query("select id,name,names from sys_addr where id=".$_POST["id"]);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
  case "getaddr2": 
		$rs=$pd->query("select id,name from sys_addr where pid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
  /*school*/     
  case "get_schs_byid": #t
	  echo $pd->query("SELECT name from school where id=".$_POST['id'])->fetchColumn(0); 
    break; 
  case "get_sch": 
		$rs=$pd->query("(SELECT id,name from school where id in (select sid from act_school where uid='".$uid."'))");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;  
  case "get_tech_type":
    $rs=$pd->query("select id,name from teacher_news_type where sid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;   
  case "get_sub_per":
    $rs=$pd->query("select id,name from sys_period where display=1 order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;   
	case "get_sub_subject":
    $rs=$pd->query("select id,name from sys_subject where period=".$_POST["id"]." and school=0 order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;   
	case "get_sub_atype":
    $rs=$pd->query("select id,name from subjecter_article_type order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;   
}		
$pd->close();
unset($pd);
unset($rs);