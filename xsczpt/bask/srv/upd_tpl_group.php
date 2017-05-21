<?php
//加密方式：php源码混淆类加密。免费版地址:http://www.zhaoyuanma.com/phpjm.html 免费版不能解密,可以使用VIP版本。
//此程序由【找源码】http://Www.ZhaoYuanMa.Com (免费版）在线逆向还原，QQ：7530782 
?>
<?php
#header("Content-type: text/html; charset=gbk;");
require '../../cfg/config.inc';
foreach($_FILES as $f){
if ($f["error"] >0){
echo '{"ret":"err","code":"01","des":"上传文件出错"}';
exit();
}
$fsize=$f["size"];#($f["size"] / 1024) .kb;
$ftmp_name=$f["tmp_name"];
$ftype=$f["type"];
$fname=$f["name"];

$fext=".".strtolower(pathinfo($fname,PATHINFO_EXTENSION));
if(!strpos(" .jpg.gif.png",$fext)){
echo '{"ret":"err","code":"03","des":"禁止上传的文件类型"}';
exit();
}
if($fsize>2097152){#>200k
echo '{"ret":"err","code":"03","des":"logo大于200k禁止上传"}';
exit;
}
$dir=DIR_ROOT."/upds/group/tpl/";
if (move_uploaded_file($ftmp_name,$dir.$_POST["id"].".jpg")){
echo '{"ret":"ok","code":"10","fname":"'.$_POST["id"].'.jpg"}';
}
}
?>