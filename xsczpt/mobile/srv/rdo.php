<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];
 
$pd=new pdo_mysql();


                 //var_dump($_POST["tpl"]);
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
	//var_dump('??');
    	      $rs=$pd->query("select id,name from main_art_category order by odx desc");
		        //var_dump($rs->fetchAll(PDO::FETCH_ASSOC));
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
       break;
	case "get_mocate":
	//var_dump('??');
    	      $rs=$pd->query("select id,name from mo_category where visible=1 order by odx desc");
		        //var_dump($rs->fetchAll(PDO::FETCH_ASSOC));
		  echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
       break;
  	case "getaddr2": #µØÖ· 2
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
}		
$pd->close();
unset($pd);
unset($rs);