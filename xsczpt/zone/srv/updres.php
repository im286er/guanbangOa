<?php 
#header("Content-type: text/html; charset=gbk;");
require '../../cfg/config.inc';
/*上传资源*/

foreach($_FILES as $f) 
{ //if (function_exists("iconv"))$f["name"] = iconv("UTF-8","GB2312",$f["name"]);	
	if ($f["error"] > 0){
		echo '{"ret":"err","errno":"1","des":"'. urlencode("文件出错").'"}';
		exit();
	}
	$fsize=$f["size"] / 1024/1024; #() . kb;
	$ftmp_name=$f["tmp_name"];//临时文件位置  $_files["file"]["tmp_name"];
	$ftype=$f["type"];//文件类型 $_files["file"]["type"]
	$fname=$f["name"];  //原始文件名 $_files["file"]["name"]
  
	$fext=".".strtolower(pathinfo($fname, PATHINFO_EXTENSION));
  $ofname=basename($fname,$fext);
	if(!strpos(" .txt.rtf.doc.xls.csv.ppt.zip.rar.",$fext)){
		echo '{"ret":"err","errno":"5","des":"'. urlencode("禁止上传的文件类型").'"}';
		exit();
	}
	
  $dir=DIR_ROOT.UP_FILE.$_GET["f"]."/"; 
	$fd=$dir."res/";
	if(!is_dir($fd)){
		$re=mkdir($fd,0777,true);#第3个参数为创建多级目录
		if(!$re){
			echo '{"ret":"err","des":"'. urlencode("创建目录错误，没有权限").'"}';
			exit();
		}
	}	
	$nname=time(); #毫秒#time() microtime(true)
	//保存文件 
	if (move_uploaded_file($ftmp_name, $fd.$nname.$fext)){ #UP_FILE.$_GET["f"]."/res/". 
		echo '{"ret":"ok","flag":"'.$_GET["flag"].'","fname":"'.$nname.$fext.'","ofname":"'.$ofname.'","size":"'.$fsize.'"}';	
	}
}