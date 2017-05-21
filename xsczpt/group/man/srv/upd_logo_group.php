<?php 
#header("Content-type: text/html; charset=gbk;");
require '../../../cfg/config.inc';
/*上传学校logo*/
foreach($_FILES as $f){ 
	if ($f["error"] > 0){
		echo '{"ret":"err","code":"01","des":"上传文件出错"}';
		exit();
	}
	$fsize=$f["size"]; #($f["size"] / 1024) . kb;
	$ftmp_name=$f["tmp_name"];//临时文件位置  $_files["file"]["tmp_name"];
	$ftype=$f["type"];//文件类型 $_files["file"]["type"]
	$fname=$f["name"];  //原始文件名 $_files["file"]["name"]

	$fext=".".strtolower(pathinfo($fname, PATHINFO_EXTENSION));
	if(!strpos(" .jpg.gif.png",$fext)){
		echo '{"ret":"err","code":"03","des":"禁止上传的文件类型"}';
		exit();
	}
	if($fsize>204800){#>200k
		echo '{"ret":"err","code":"03","des":"logo大于200k禁止上传"}';
		exit;
	}
	$dir=DIR_ROOT."/upds/group/logo/";	
  if(!is_dir($dir))$re=mkdir($dir,0777,true);	
	//保存文件 
	if (move_uploaded_file($ftmp_name, $dir.$_POST["id"].".jpg")){  
		echo '{"ret":"ok","code":"10","fname":"'.$_POST["id"].'.jpg"}';	
	}
}