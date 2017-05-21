<?php 
/*调用 pan 数据
*/
header("Content-type: text/html; charset=utf-8;"); 
require '../../cfg/config.inc';
require '../../ppf/fun.php';

if (!session_id()) session_start(); 
chkLoginNoJump("uid"); 
$uid=$_SESSION['uid'];

switch($_POST["t"]){    
  case "get_dir":
    $str=file_get_contents(PAN_URL.'api/yun?t=get_dir&id='.$_POST["id"].'&uid='.$uid);
	   echo $str;
    break;            
  case "vote":
    $pd->exec("update `".$_POST["tbl"]."` set `".$_POST["col"]."`=ifnull(`".$_POST["col"]."`,0)+1 where id='".$_POST["id"]."'");
    echo "成功"; 
    break;   
  case "see":    
      $pd->exec("update `".$_POST["tbl"]."` set see=ifnull(see,0)+1 where id='".$_POST["id"]."'"); 
      echo "成功"; 
    break;
}		