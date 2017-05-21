<?php 
/*tree AMD do*/
header("Content-type: text/html; charset=utf-8;"); 
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';

session_start(); 
#$nick=mb_convert_encoding($_SESSION["nick"],"UTF-8", "GBK");
#$uid=$_SESSION['uid'];
#chkLoginNoJump("uid");  #多库联合

$pd=new pdo_mysql();
$tbl=$_POST["tbl"];
switch($_POST["tpl"]){     
  case "addnod":#添加节点
  	$nid=$pd->genid($tbl);
  	$pid=$_POST["pid"];
  	$odx=$_POST["odx"];
  	if(empty($_POST["pid"]))
    		$sql="insert into `".$tbl."`(id,pid,name,childnums,odx) values(".$nid.",".$pid.",'".$_POST["name"]."',0,".$odx.")";
  	else	
  		$sql="insert into `".$tbl."`(id,pid,name,childnums,odx) values(".$nid.",".$pid.",'".$_POST["name"]."',0,".$odx.")";	
      $pd->exec($sql);
  	#更新pidlist
  	if(empty($_POST["pid"])){
  		$pd->exec("update `".$tbl."` set pidlist='0,".$nid.",',lvl=1 where id=".$nid);	
  	}
  	else{
  		$pidlist=$pd->query("select pidlist from `".$tbl."` where id=".$pid)->fetchColumn(0); 
  		$lvl=substr_count($pidlist,',');
  		$pd->exec("update `".$tbl."` set pidlist='".$pidlist.$nid.",',lvl=".$lvl." where id=".$nid);	
  		$nums=$pd->query("select count(1) from `".$tbl."` where pid=".$pid)->fetchColumn(0); 
  		$pd->exec("update `".$tbl."` set childnums=".$nums." where id=".$pid);
  	}	
      echo "ok".$nid;
    break;
  case "delnod":
  	$id=$_POST["id"];
  	$pid=$pd->query("select pid from `".$tbl."` where id=".$id)->fetchColumn(0);
  	$pidlist=$pd->query("select pidlist from `".$tbl."` where id=".$id)->fetchColumn(0);	
      $pd->exec("delete from `".$tbl."` where pidlist like '".$pidlist."%'"); 
  	$nums=$pd->query("select count(1) from `".$tbl."` where pid=".$pid)->fetchColumn(0); 
  	$pd->exec("update `".$tbl."` set childnums=".$nums." where id=".$pid);
      echo "ok";
      break;
  case "modnod":
    $pd->exec("update `".$tbl."` set name='".$_POST["name"]."',odx=".$_POST["odx"]." where id=".$_POST["id"]); 
    echo "ok";    
    break;  
  case "getodx":
  	echo $pd->query("select odx from `".$tbl."` where id=".$_POST["id"])->fetchColumn(0); 
  	break;
  case "mvfile":
    #array_map("rename",array_filter(glob('../gen/*'), 'is_file'),("../a1/*")
    #rename("../gen","../a2");
    $todir=$_POST["dir"];
    if(substr($todir, -1)!="/")$todir.="/"; #未尾+ /     
    foreach(glob('../gen/*') as $f){      
      rename($f,DIR_ROOT.$todir.basename($f));      
    }     
    echo "ok";
    break;
    case "updpidlist":
      $id=$_POST["id"];
      $pidlist=$pd->query("select pidlist from `".$tbl."` where id=(select pid from `".$tbl."` where id=".$id.")")->fetchColumn(0);
      $pd->exec("update `".$tbl."` set pidlist='".$pidlist.$id.",' where id=".$id); 
       echo "ok".$pidlist.$id;
      break;
}		
$pd->close();
unset($pd);
unset($rs);