<?php 
#header("Content-type: text/html; charset=utf-8"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
#班级相册处理

if (!session_id()) session_start();  
chkLoginNoJump("uid"); 
$uid=$_SESSION['uid'];
#检测操作权限
$pd=new pdo_mysql();  
switch($_POST["tpl"]){
 /* case "reblog":
    $nick=mb_convert_encoding($_SESSION["nick"],"UTF-8", "GBK");
    $floor=$pd->query("select count(1) from blog_comments where bid=".$_POST['id'])->fetchColumn(0);
    $nid=time();
    $sql="insert into blog_comments(id,bid,des,reid,floor,uid,unick,timestamp) values(".$nid
    .",".$_POST["id"].",'".$_POST["data"]."',".$_POST["reid"].",".($floor+1).",'".$_SESSION['uid']."','".
    $nick."',UNIX_TIMESTAMP());";      
    if($pd->exec($sql)){
      if(!empty($_POST['reid'])){
        $rs= $pd->query("select * from blog_comments where id=".$_POST['reid']." limit 1");
        $r=$rs->fetch(PDO::FETCH_ASSOC);
        $redes="<fieldset><legend>";
        $redes.= $pd->query("select nick from act where id='" .$r["uid"]."'")->fetchColumn(0);
        $redes.= " 发表于 ". strftime('%Y-%m-%d %H:%M',$r['id']) ."</legend>";
        $redes.= $r["des"]."</fieldset>";
        $redes.=$_POST["data"]; 
        $pd->exec("update blog_comments set des='". $redes ."' where id=".$nid); 
      }
      $pd->exec("update blog_list set comments=ifnull(comments,0)+1 where id=".$_POST["id"]);     
      echo "ok";
    }else
      echo "no ";
   break;*/
   case "add_album":#c
  	if($pd->query("select count(1) from grp_album where gid=".$_POST['gid']." and name='".$_POST["name"]."'")->fetchColumn(0))
  		echo "err102:存在同名相册";
  	else{
  		$nid=$pd->gentmrid("grp_album");
  		$pd->exec("insert into grp_album(id,gid,uid,name,des,timestamp,nums) values(".$nid.",".$_POST["gid"].",'".
  		$uid."','".$_POST["name"]."','".$_POST["data"]."',UNIX_TIMESTAMP(),0)"); 
  	    echo "ok";
  	  }
    break;  
}
$pd->close();
unset($rs);
unset($pd);