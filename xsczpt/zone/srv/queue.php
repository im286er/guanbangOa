<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

if (!session_id()) session_start();
$uid=(!isset($_GET['frid']) || $_GET['frid']== '')?$_SESSION["uid"]:$_GET['frid'];
$pd=new pdo_mysql();
switch($_POST["tpl"]){
    /*case "push_fmos":#///推送学校
      $id=$_POST['id']; 
      $sid=$_POST['sid'];
      if(empty($sid)){echo "err103:请选择必选项";exit;}
    if(!$pd->query("select count(1) from blog_list where id=".$_POST['id']." and uid='".$uid."'")->fetchColumn(0)){
         echo "err:203 您没权限推送这篇文章";
     }
     else{         
          $nid=$pd->gentmrid("famous_article");
          $sql="insert into famous_article(id,uid,sid,fid,title,des,timestamp,state,froms,fromid) select ".$nid.",uid,".$sid.",0,title,des,UNIX_TIMESTAMP(),0,'个人博客',".$_POST["id"]." from blog_list where id=".$_POST['id'];
          $pd->exec($sql);       
        echo 'ok推送成功';
    }
      break;*/
    case "wbvote":#赞
        $wuid=$pd->query("select uid from weibo where id=".$_POST['data'])->fetchColumn(0);
        $nid=$pd->gentmrid("cli_msg");
        $sql="insert into cli_msg(id,typeid,uid,touid,data,state) values(".$nid.",0,'".$uid."','".$wuid."','".$_POST['data']."',0)";
        $pd->exec($sql);  
        break;
}
$pd->close();
unset($pd);
unset($rs);