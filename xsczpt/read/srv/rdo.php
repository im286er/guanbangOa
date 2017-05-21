<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../ppf/fun.php';
require '../ppf/pdo_mysql.php';

#检测登录
session_start(); 
#chkLoginNoJump("jsid");
 
$pd=new pdo_mysql();
switch($_POST["tpl"]){
  case "get_schs":
		$rs=$pd->query("select id,name from school where name like '%".$_POST["data"]."%' limit 20");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
    
    
  case "get_art_cate_1":
		echo $pd->query("select name from main_art_category where id=".$_POST["id"])->fetchColumn(0); 	 
		break;  
  case "get_art_cate":
		$rs=$pd->query("select id,name from main_art_category order by odx desc");
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
	case "getgrade1": #2
		$rs=$pd->query("select * from sys_grade where pid=".$_POST["id"]." order by odx asc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;		
	case "getunits": # 2 wait del
		$rs=$pd->query("select * from org_units where addr=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
  case "get_school": # 2
		$rs=$pd->query("select * from school where state=2 and addr=".$_POST["id"]."");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
  case "get_blog_sch": #博客汇聚页学校列表
    if(!empty($_POST["strid"])){
      $addr = $_POST["strid"];
    }else{
      if(!empty($_POST["addrid"])){
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr where pid =".$_POST["addrid"])->fetchColumn(0);  
        $addr .="0";
        $addr = $_POST["addrid"].",".$addr;
      }else{
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr")->fetchColumn(0);
      }
    }
    $rs=$pd->query("select * from school where state=2 and addr in (".$addr.")");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "get_blog_group": #博客汇聚页群组列表
    $schid = "";
    if(!empty($_POST["schid"])){
      $schid = $_POST["schid"];
    }else{
      if(!empty($_POST["strid"])){
        $addr = $_POST["strid"];
      }else if(!empty($_POST["addrid"])){
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr where pid =".$_POST["addrid"])->fetchColumn(0);  
        $addr = $_POST["addrid"].",".$addr;
      }else{
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr")->fetchColumn(0); 
      }
      $schid = $pd->query("select group_concat(id) from school where state=2 and addr in (".$addr.")")->fetchColumn(0);
    }
    $rs = $pd->query("select *,IFNULL(mannums,0) as mnums from `group` where state=2 and school in (".$schid.") order by id limit 7");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break;
  case "get_blog_famous": #博客汇聚页群组列表
    $schid = "";
    if(!empty($_POST["schid"])){
      $schid = $_POST["schid"];
    }else{
      if(!empty($_POST["strid"])){
        $addr = $_POST["strid"];
      }else if(!empty($_POST["addrid"])){
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr where pid =".$_POST["addrid"])->fetchColumn(0);  
        $addr = $_POST["addrid"].",".$addr;
      }else{
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr")->fetchColumn(0); 
      }
      $schid = $pd->query("select group_concat(id) from school where state=2 and addr in (".$addr.")")->fetchColumn(0);
    }
    $rs = $pd->query("select * from `famous` where state=2 and school in (".$schid.") order by id limit 6");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "getaddrs": 
		echo $pd->query("select pidlist from sys_addr where id=".$_POST["id"])->fetchColumn(0);   		  
		break;      	 		
	case "getaddr2": #地址 2
		$rs=$pd->query("select id,name from sys_addr where pid=".$_POST["id"]." order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
  case "get_class": 
    $schid = "";
    if(!empty($_POST["schid"])){
      $schid = $_POST["schid"];
    }else{
      if(!empty($_POST["strid"])){
        $addr = $_POST["strid"];
      }else if(!empty($_POST["addrid"])){
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr where pid =".$_POST["addrid"])->fetchColumn(0);  
        $addr = $_POST["addrid"].",".$addr;
      }else{
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr")->fetchColumn(0); 
      }
      $schid = $pd->query("select group_concat(id) from school where state=2 and addr in (".$addr.")")->fetchColumn(0);
    }
    $rs = $pd->query("select * from `class` where state=2 and id in (".$schid.") order by id limit 7");
    echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
    break; 
  case "get_schools": 
      if(!empty($_POST["strid"])){
        $addr = $_POST["strid"];
      }else if(!empty($_POST["addrid"])){
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr where pid =".$_POST["addrid"])->fetchColumn(0);  
        $addr = $_POST["addrid"].",".$addr;
      }else{
        $addr = $pd->query("select GROUP_CONCAT(id) from sys_addr")->fetchColumn(0); 
      }
	  $rs = $pd->query("select * from school where state=2 and addr in (".$addr.") order by id limit 4");
	echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
	break;
}		
$pd->close();
unset($pd);
unset($rs);