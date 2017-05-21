<?php
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start();
if(isset($_SESSION["uid"])){
  $uid=$_SESSION["uid"];
  $aid=$_GET["id"];
  $gid=$_GET["g"];  
 $uploaddir = "../../upds/group/album/".$gid."/".$aid."/";
 if(!is_dir($uploaddir)){
		$re=mkdir($uploaddir,0777,true);#第3个参数为创建多级目录
		if(!$re){
			echo "err:错误，创建目录错误，没有权限";
			exit();
		}
	} 
  $f=$_FILES['file'];
  $fsize=$f["size"]; #($f["size"] / 1024) . kb;
	$ftmp_name=$f["tmp_name"];//临时文件位置  $_files["file"]["tmp_name"];
	$ftype=$f["type"];//文件类型 $_files["file"]["type"]
	$fname=$f["name"];  //原始文件名 $_files["file"]["name"]
  
	$fext=".".strtolower(pathinfo($fname, PATHINFO_EXTENSION));
    $ofname=basename($fname,$fext);
	if(!strpos(" .jpg.gif.png.bmp",$fext)){
		echo "err:错误，禁止上传的文件类型";
		exit();
	}   
    $nname=microtime(true);
    
    #$uploadfile = $uploaddir. $_FILES['file']['name'];
    $uploadfile =$uploaddir.$nname.$fext;
    #print "<pre>";
    if (move_uploaded_file($ftmp_name, $uploaddir.$nname.$fext)) {
      $ret=mkThumbnail($uploaddir.$nname.$fext, 120,null,$uploaddir.'t_'.$nname.$fext);
      echo $nname.$fext;       
      # print_r($_FILES);
       #print $_FILES['file']['name'];
       $pd=new pdo_mysql();    
        $nid=$pd->gentmrid("grp_photo");
        $pd->exec("insert into grp_photo(id,`uid`,aid,gid,`pic`,ofname,size,`see`,`up`) values(".$nid.",'".$uid."',".$aid
        .",".$gid.",'".$nname.$fext."','".$ofname."',".$fsize.",0,0)");  
       $pd->exec("update grp_album set logo=ifnull(logo,'".$nname.$fext."'),lastupdate=now(),nums=(select count(1) from grp_photo where aid=".$aid.") where id=".$aid);         
       $pd->close();
       unset($pd); 
    } else {
        print "err:错误，上传失败"; 
        #print_r($_FILES);
    }
    #print "</pre>";
}
else{
   echo "err:错误，登录失效请重新登录";     
}



function mkThumbnail($src, $width = null, $height = null, $filename = null) {  
    if (!isset($width) && !isset($height))  
        return false;  
    if (isset($width) && $width <= 0)  
        return false;  
    if (isset($height) && $height <= 0)  
        return false;  
  
    $size = getimagesize($src);  
    if (!$size)  
        return false;  
  
    list($src_w, $src_h, $src_type) = $size;  
    $src_mime = $size['mime'];  
    switch($src_type) {  
        case 1 :  
            $img_type = 'gif';  
            break;  
        case 2 :  
            $img_type = 'jpeg';  
            break;  
        case 3 :  
            $img_type = 'png';  
            break;  
        case 15 :  
            $img_type = 'wbmp';  
            break;  
        default :  
            return false;  
    }  
  
    if (!isset($width))  
        $width = $src_w * ($height / $src_h);  
    if (!isset($height))  
        $height = $src_h * ($width / $src_w);  
  
    $imagecreatefunc = 'imagecreatefrom' . $img_type;  
    $src_img = $imagecreatefunc($src);  
    $dest_img = imagecreatetruecolor($width, $height);  
    imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);  
  
    $imagefunc = 'image' . $img_type;  
    if ($filename) {  
        $imagefunc($dest_img, $filename);  
    } else {  
        header('Content-Type: ' . $src_mime);  
        $imagefunc($dest_img);  
    }  
    imagedestroy($src_img);  
    imagedestroy($dest_img);  
    return true;  
} 