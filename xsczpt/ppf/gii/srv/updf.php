<?php 
#header("Content-type: text/html; charset=utf-8;");
#require '../../cfg/config.inc';
require('../../ppf/pdo_mysql.php'); 

#上传文件检测
#session_start(); 
#if(empty($_SESSION["uid"])){ 
#	echo '{"ret":"err","des":"'. urlencode("登录超时") .'"}';
#	exit();
#} 
 

foreach($_FILES as $f) 
{ 	
	//处理中文名 
	//if (function_exists("iconv"))$f["name"] = iconv("UTF-8","GB2312",$f["name"]); 
	
	if ($f["error"] > 0){
		echo '{"ret":"err","des":"'. urlencode("上传文件出错").'"}';
		exit();
	}
	$fsize=$f["size"]; #($f["size"] / 1024) . kb;
	$ftmp_name=$f["tmp_name"];//临时文件位置  $_files["file"]["tmp_name"];
	$ftype=$f["type"];//文件类型 $_files["file"]["type"]
	$fname=$f["name"];  //原始文件名 $_files["file"]["name"]

	$fext=strtolower(pathinfo($fname, PATHINFO_EXTENSION));
	if(!strpos(" .jpg.gif.png",$fext)){
		echo '{"ret":"err","des":"'. urlencode("禁止上传的文件类型").'"}';
		exit();
	}
	$dir=DIR_ROOT.UP_FILE_DIR;
	$y=date("Y");	 #/m/d
	$d=date("z");
	$w=ceil($d/7);
	$fd=$dir.$y."/".$w."/";
	if(!is_dir($fd)){
		$re=mkdir($fd,0777,true);#第3个参数为创建多级目录
		if(!$re){
			echo '{"ret":"err","des":"'. urlencode("创建目录错误，没有权限").'"}';
			exit();
		}
	}#time().
	$nname=time(); #microtime()毫秒
	//检查是否已经存在同名文件 	
	#if (file_exists($fname))  echo ""; 
	//保存文件 
	if (move_uploaded_file($ftmp_name, $fd.$nname.".".$fext))  
		echo '{"ret":"ok","flag":"1","fname":"'.UP_FILE_DIR.$y."/".$w."/".$nname.".".$fext.'"}';		
	#保存到数据库
	$pd=new pdo_mysql();
	$pd->exec("insert into files(fname,fext,y,w,d,`path`) values(".$nname.",'".$fext."',".$y.",".$w.",".$d.",'".UP_FILE_DIR.$y."/".$w."/".$nname.".".$fext."')");
	$pd->close();
	unset($pd); 
} 
#
//}
?>