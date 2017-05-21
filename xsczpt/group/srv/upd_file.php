<?php 
#header("Content-type: text/html; charset=gbk;");
require '../../cfg/config.inc';
/*上传群组文件 只允许.zip .rar */

foreach($_FILES as $f) 
{ //if (function_exists("iconv"))$f["name"] = iconv("UTF-8","GB2312",$f["name"]);	
	if ($f["error"] > 0){
		echo '{"ret":"err","errno":"1","des":"'. "文件出错".'"}';
		exit();
	}
	$fsize=$f["size"]; #($f["size"] / 1024) . kb;
	$ftmp_name=$f["tmp_name"];//临时文件位置  $_files["file"]["tmp_name"];
	$ftype=$f["type"];//文件类型 $_files["file"]["type"]
	$fname=$f["name"];  //原始文件名 $_files["file"]["name"]
	$fmd5=md5_file($ftmp_name); 
	$ext=strtolower(pathinfo($fname, PATHINFO_EXTENSION));
	$fext=".".$ext;
	$ofname=basename($fname,$fext);
	if(!strpos(" .zip.rar.7z.jpg",$fext)){
		echo '{"ret":"err","errno":"5","msg":"禁止上传的文件类型"}';
		exit;
	}
	
	$dir=DIR_ROOT."/group/file/".$_GET["g"]."/";	
	if(!is_dir($dir)){
		$re=mkdir($dir,0777,true);#第3个参数为创建多级目录
		if(!$re){
			echo '{"ret":"err","errno":"6","msg":"创建目录错误，没有权限"}';
			exit;
		}
	}	
	$nname=microtime(true);
	//保存文件 
	if (move_uploaded_file($ftmp_name, $dir.$nname.$fext)){ 
		echo '{"ret":"ok","flag":"'.$_GET["flag"].'","md5":"'.$fmd5.'","ext":"'.$ext.'","size":"'.$fsize.'","fname":"'.$nname.$fext.'","ofname":"'.$ofname.'"}';
		#error_log($nname.$fext."|".$ofname.PHP_EOL, 3,"../album/".$_GET["c"]."/".$_GET["id"].".log"); 	
	}
}