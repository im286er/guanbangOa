<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];
 
$pd=new pdo_mysql();
switch($_POST["tpl"]){
  case "courseware_qry":#id,title,subject,grade,period,timestamp
    $sql="select * from res_courseware where school=".$_POST["sch"]." and cid=".$_POST["cid"];  #grade
    if(!empty($_POST["subject"]))$sql.=" and subject=".$_POST["subject"];
    if(!empty($_POST["edition"]))$sql.=" and edition=".$_POST["edition"];
    if(!empty($_POST["volume"]))$sql.=" and volume=".$_POST["volume"];
    if(!empty($_POST["chapter"]))$sql.=" and chapter=".$_POST["chapter"];
    if(!empty($_POST["restype"]))$sql.=" and restype=".$_POST["restype"];
    if(!empty($_POST["tle"]))$sql.=" and title like '%".$_POST["tle"]."%'";
    $rs=$pd->query($sql." limit 50"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));     
    break;
  case "get_class_info":#t 
    $rs=$pd->query("SELECT id,period,grade,school from `class`  where id=".$_POST["id"]); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_homework_do":
    if($pd->query("select count(1) from stu_homework where hid=".$_POST["id"]." and uid='".$uid."'")->fetchColumn(0))
      echo "yes".$pd->query("select id from stu_homework where hid=".$_POST["id"]." and uid='".$uid."'")->fetchColumn(0);
    else
      echo "no";
    break;
  case "get_timetable":#t 获取课表 
    $rs=$pd->query("SELECT * from class_timetable where cid=".$_POST["id"]);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
 case "get_class_stu":
  $rs=$pd->query("select id,truename name from act_member where id in (SELECT id from stu_class where cid=".$_POST["id"].")"); 
	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
  break;
 case "get_class_tech":#t 获取班级的教师
    $rs=$pd->query("select id,truename name from act_member where id in (SELECT id from tech_class where cid=".$_POST["id"].")"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
 case "get_class":#t 获取我的班级 
   	$rs=$pd->query("select id,name from class where id in (select cid from stu_class where id='".$uid."')"); 
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break; 
 case "sel_group":#t
     $sql="select id,name from `group` where school=".$_POST['sid']; 
     if(!empty($_POST['name']))
      $sql.=" and name like '%".$_POST["name"]."%'";
    if(!empty($_POST['no']))
       $sql.=" and id=".$_POST['no'];          
    $rs=$pd->query($sql);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "nxt_photo": #c  
	  $rs=$pd->query("SELECT * from grp_photo where aid=".$_POST["a"]." and id>".$_POST["id"]." LIMIT 1");
   	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "pre_photo": #c  
		$rs=$pd->query("SELECT * from grp_photo where aid=".$_POST["a"]." and id<".$_POST["id"]." order by id desc LIMIT 1");
   	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;    
  case "nxt_photo_grp": #c
    $rs=$pd->query("SELECT * from grp_photo where aid=".$_POST["a"]." and id>".$_POST["id"]." LIMIT 7");
   	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;  
  case "pre_photo_grp": #c                                                                       
	  $rs=$pd->query("SELECT * from (SELECT * from grp_photo where aid=".$_POST["a"]." and id<".$_POST["id"]." ORDER BY id desc LIMIT 7) T order by id");
   	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break;
  case "get_photo_last":#g
    $n=$pd->query("SELECT id from grp_photo where aid=".$_POST["a"]." and id<".$_POST["id"]." ORDER BY id DESC LIMIT 3,1")->fetchColumn(0);
    if($n)
      $rs=$pd->query("SELECT * from grp_photo where aid=".$_POST["a"]." and id>".$n." ORDER BY id LIMIT 7");
    else
      $rs=$pd->query("SELECT * from grp_photo where aid=".$_POST["a"]." ORDER BY id LIMIT 7");
   	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
    break; 
  case "chk_cls_limit":
    if($pd->query("select count(1) from tech_class where cid=".$_POST["id"]." and id='".$uid."'")->fetchColumn(0))
          echo "ok";
    else if($pd->query("select count(1) from stu_class where cid=".$_POST["id"]." and id='".$uid."'")->fetchColumn(0))
        echo "ok";
    else
          echo "210您没有权力";
    break;
  case "chk_tech_limit": #c 难测老师权限
     if($_SESSION["idtype"]!=2){echo "201你没有权限";}
     else{ 
        if($pd->query("select count(1) from tech_class where cid=".$_POST["id"]." and id='".$uid."'")->fetchColumn(0))
          echo "ok";
        else
          echo "210您没有权力";
     }	
    break;
  case "get_activity":#c
   	$rs=$pd->query("select * from cls_activity where id=".$_POST['id']);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;	 		
	case "get_notice": #c  
		$rs=$pd->query("select * from cls_notice where id=".$_POST['id']);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;  
}		
$pd->close();
unset($pd);
unset($rs);