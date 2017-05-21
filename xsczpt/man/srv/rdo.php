<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];
 
$pd=new pdo_mysql();
switch($_POST["tpl"]){
   case "get_usrs":
      $u=$_POST["u"];
      $t=$_POST["t"];
      $s=$_POST["s"];
      $wh="";
      if(!empty($u))$wh.=" and username='".$u."'";
      if(!empty($t))$wh.=" and truename like '%".$t."%'";
      $rs=$pd->query("select id,truename from act_member where idtype=2 and school=".$s.$wh." limit 30");
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
      break; 
    case "get_artcate":
      $result = $pd->query("select id,name from type_sub order by odx asc")->fetchAll(PDO::FETCH_ASSOC);  
      echo get_str($result,0,-1);
      break;
	case "get_addr": #获取院校所属的省份
      $result = $pd->query("select id,name from type_acade_addr")->fetchAll(PDO::FETCH_ASSOC);  
      echo get_str($result,0,-1);
	  //echo json_encode($result->fetchAll(PDO::FETCH_ASSOC));
      break;
    case "get_artcate_name":
    	$rs=$pd->query("select id,name from main_art_category order by odx desc");
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
      break;
    case "get_videocate":
      $result = $pd->query("select id,name,pid from main_video_category order by odx desc")->fetchAll(PDO::FETCH_ASSOC);  
      echo get_str($result,0,-1);	
      break;
    case "get_videocate_name":
    	$rs=$pd->query("select id,name from main_video_category order by odx desc");
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
      break;
  	case "getaddr2": #地址 2
  		$rs=$pd->query("select id,name,names from sys_addr where pid=".$_POST["id"]." order by odx asc");
  		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
  		break;
    case "get_addr_sch":
  		$rs=$pd->query("select id,name from school where addr=".$_POST["id"]." order by name");
  		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
  		break;  
    case "get_sch_s":
  		echo $pd->query("select name from school where id=".$_POST["id"])->fetchColumn(0);		
  		break;  
    case "get_addr_s":  #nouse
  		echo $pd->query("select names from sys_addr where id=".$_POST["id"])->fetchColumn(0);		
  		break;  
	case "get_blog_cate":
		$rs=$pd->query("SELECT id,name from push_category where push_type='".$_POST["push_type"]."' order by odx desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
}		

function get_str($result,$id = 0,$count = -1) {
    $count++;
    $str = "";
    $disStr = " ";
    if(count($result)>0){//如果有子类 
        foreach($result as $k=>$v) { //循环记录集
            if($v['pid'] != $id) {
               continue;
            }
            foreach($result as $tmpk => $tmp) {
               if($tmp['pid'] == $v['id']) {
                  $disStr = "disabled=\"disabled\" ";
                  break;
               }
            }
            $str.= "<option {$disStr} value=". $v['id'] .">";
            $disStr = " ";
            $str.= str_repeat("&nbsp;&nbsp;&nbsp;",$count);
            $str.="|--". $v['name'] . "</option>"; //构建字符串  
            $str.= get_str($result,$v['id'],$count); //调用get_str()，将记录集中的id参数传入函数中，继续查询下级
        }  
    }      
    return $str;
}

$pd->close();
unset($pd);
unset($rs);