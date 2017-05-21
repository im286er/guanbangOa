<?php 
header("Content-type: text/html; charset=utf-8;");
#require '../../ppf/pdo_mysql.php';
require('../../ppf/pdo_mysql.php');
/*上传文件*/
#检测ip是否和登录的相同
$ip=$_SERVER["REMOTE_ADDR"];
$pd=new pdo_mysql();
if($pd->query("select count(1) from member where lastip='".$ip."' and id=".$_GET["i"])->fetchColumn(0)){ #登录时间在2个小之内 #and lasttime>UNIX_TIMESTAMP()-7200
	foreach($_FILES as $f) 
	{ 		
		//if (function_exists("iconv"))$f["name"] = iconv("UTF-8","GB2312",$f["name"]);	
		if ($f["error"] > 0){
			echo '{"ret":"err","errno":"1","des":"'. urlencode("文件出错").'"}';
			exit();
		}
		$fsize=$f["size"] / 1024; #($f["size"] / 1024) . kb;
		$ftmp_name=$f["tmp_name"];//临时文件位置  $_files["file"]["tmp_name"];
		$ftype=$f["type"];//文件类型 $_files["file"]["type"]
		$fname=$f["name"];  //原始文件名 $_files["file"]["name"]

		$fext=".".strtolower(pathinfo($fname, PATHINFO_EXTENSION));
		if(!strpos(" .jpg.gif.png,.doc,.txt,.zip",$fext)){
			echo '{"ret":"err","errno":"5","des":"'. urlencode("禁止上传的文件类型").'"}';
			exit();
		}
		#检测并生成文件夹
		$dir=DIR_ROOT.UP_FILE_DIR;
		$y=date("Y");	 #/m/d
		$d=date("z");
		$w=ceil($d/7);
		$fd=$dir.$y."/".$w."/";  #以每周为单位
		if(!is_dir($fd)){
			$re=mkdir($fd,0777,true);#第3个参数为创建多级目录
			if(!$re){
				echo '{"ret":"err","errno":"2","des":"'. urlencode("创建目录错误，没有权限").'"}';
				exit();
			}
		}
		#生成文件名称	
		$nname=microtime(true); #毫秒#time().
		
		if (move_uploaded_file($ftmp_name, $fd.$nname.$fext)){  #保存文件 
			echo '{"ret":"ok","flag":"'.$_GET["flag"].'","fname":"'.UP_FILE_DIR.$y."/".$w.'/'.$nname.$fext.'"}';	
			#更新数据库
			$pd->exec("insert into task_file(id,fname,tid,uid,fsize,ext) values(UNIX_TIMESTAMP(),'".UP_FILE_DIR.$y."/".$w."/".$nname.$fext."',0,"
				.$_GET["i"].",'".$fsize."','".$fext."')");
		}
	}
}
else{
	echo '{"ret":"err","errno":"6","des":"'. urlencode("非法地址,禁止上传").'"}';
}