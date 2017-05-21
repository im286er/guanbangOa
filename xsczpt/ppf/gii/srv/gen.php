<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';

session_start(); 
chkLoginNoJump("bask");

$pd=new pdo_mysql();
#$_POST["tpl"]='sys_info';
switch($_POST["tpl"]){
   case "clearip":
  $pd->query("delete from sys_ip_error");
  $pd->query("delete from sys_ip_error_bask");
  echo "ok";
  break;
case "clear":
    array_map( "unlink", glob(DIR_ROOT.'/cache/id/*' ) );
    array_map( "unlink", glob(DIR_ROOT.'/cache/index/*' ) );
    array_map( "unlink", glob(DIR_ROOT.'/cache/two/*' ) );
    array_map( "unlink", glob(DIR_ROOT.'/cache/third/*' ) );
    #unlink(DIR_ROOT.'/data/id/'); 
    echo 'ok';
    break; 
case "sys_info":
		$rs=$pd->query("select * from sys_info where id=1");
		if($r=$rs->fetch(PDO::FETCH_ASSOC)){
			#生成config
			$p=file_get_contents(DIR_ROOT.'/cfg/config.txt');
			#utf8转gbk
        #$name=;#mb_convert_encoding($r["name"], "GBK","UTF-8"); 
        $p=str_replace('{debug}',$r["debug"], $p);
        $p=str_replace('{name}',$r["name"], $p);
        $p=str_replace('{regoff}',$r["regoff"], $p);
        $p=str_replace('{ipchk}',$r["ipchk"], $p);   
        $p=str_replace('{ipnum}',$r["ipnum"], $p);
        $p=str_replace('{cache}',$r["cache"], $p);
        $p=str_replace('{cache_time}',$r["cache_time"], $p);
        $p=str_replace('{reseturl}',$r["reseturl"], $p);  

    file_put_contents(DIR_ROOT.'/cfg/config.inc',$p); 	
    unset($p);     		 
    }			
    echo "ok";
    break;	 
}		
$pd->close();
unset($pd);
unset($rs);