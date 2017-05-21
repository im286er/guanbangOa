<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

$pd=new pdo_mysql();

switch($_POST["tpl"]){
	case "up_status":
	    
		$rs=$pd->query("update oa_message SET is_read=1 where id=".$_POST["id"]."")->fetchColumn(0);
		echo "OK";
		break;
}		
$pd->close();
unset($pd);
unset($rs);