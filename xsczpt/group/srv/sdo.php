<?php 
/*set do
*/
header("Content-type: text/html; charset=utf-8;"); 
require '../../comm/comm.php';
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';


if (!session_id()) session_start();  
chkLoginNoJump("uid"); 
$uid=$_SESSION['uid'];
if($_SESSION["idtype"]!=2){echo "err201你没有权限";exit;}

$pd=new pdo_mysql();
switch($_POST["tpl"]){ 
  case "del_forum_comment":
		if($pd->exec("delete from grp_forum_comment where id=".$_POST["id"]." and uid='".$uid."'")){
 
      echo "ok";
    }
    else    
		  echo '当前状态禁止删除';	
		break; 
  case "del_weibo": 
		if($pd->exec("delete from grp_weibo where id=".$_POST["id"]." and uid='".$uid."'")){
      $pd->exec("delete from grp_weibo where wid=".$_POST["id"]." and uid='".$uid."'");
      echo "ok";
    }
    else    
		  echo '当前状态禁止删除';	
		break; 
  case "weibo_comment":#g评论 
	  $nid=$pd->gentmrid("grp_weibo_comment");
	  $pd->exec("insert into grp_weibo_comment(id,gid,wid,`uid`,des,timestamp) values(".$nid.",".$_POST["g"].",".$_POST["wid"].",'".$uid."','".$_POST["data"]."',UNIX_TIMESTAMP())");  
    //$pd->exec("update grp_weibo set comments=(select count(1) from grp_weibo_comment where wid=".$_POST["wid"].") where id=".$_POST["wid"]);         
    echo "ok".$nid;         
		break;   
  case "forum_comment":#g评论 
	  $pd->exec("insert into grp_forum_comment(id,gid,wid,`uid`,des,timestamp) values(".$nid.",".$_POST["g"].",".$_POST["wid"].",'".$uid."','".$_POST["data"]."',UNIX_TIMESTAMP())");        
    echo "ok";         
		break;   
  case "set_album_cover": #g
	 $pd->exec("update grp_album set logo='t_".$_POST["data"]."' where id=".$_POST["id"]);         
    echo "ok";   	
		break;    
  case "del_photo": #g
    $pic=$pd->query("select pic from grp_photo where id=".$_POST["id"])->fetchColumn(0);
		if($pd->exec("delete from grp_photo where id=".$_POST["id"]." and uid='".$uid."'")){
        echo "ok成功";
        unlink("../album/".$_POST["g"]."/".$_POST["a"]."/".$pic);
        unlink("../album/".$_POST["g"]."/".$_POST["a"]."/t_".$pic);
    }
    else{
       if($pd->query("select count(1) from grp_member where gid=".$_POST["g"]." and id='".$uid."' and isman>0")->fetchColumn(0)){
          $pd->exec("delete from grp_photo where id=".$_POST["id"]);
          log_log($pd,$uid,'删除群图片:id-'.$_POST["id"],3);
          echo "ok成功";  
          unlink("../album/".$_POST["g"]."/".$_POST["a"]."/".$pic);
          unlink("../album/".$_POST["g"]."/".$_POST["a"]."/t_".$pic);            
       }
       else 
          echo "err210你没有权限";         
    } 
		break;
  case "add_photo": #g 添加数据到库    
    $nid=$pd->gentmrid("grp_photo");
	  $pd->exec("insert into grp_photo(id,gid,`uid`,aid,`pic`,ofname,`see`,`up`,size) values(".$nid.",".$_POST["g"].",'".$uid."',".$_POST["id"].",'".$_POST["fname"]."','".$_POST["ofname"]."',0,0,".$_POST["size"].")");  
    $pd->exec("update grp_album set nums=(select count(1) from grp_photo where aid=".$_POST["id"].") where id=".$_POST["id"]);         
    echo "ok";   	
		break;
/* 
 case "del_diary": #c
  	 if($_SESSION["idtype"]!=2){echo "err201你没有权限";}
     else{
        if($pd->exec("delete from cls_diary where id=".$_POST["id"]." and uid='".$uid."'"))
            echo "ok";
        else{
         if($pd->query("select count(1) from tech_class where cid=".$_POST["cid"]." and id='".$uid."' and duty=2")->fetchColumn(0)){
          $pd->exec("delete from cls_diary where id=".$_POST["id"]);
          log_log($pd,$uid,'删除日记:id-'.$_POST["id"],3);
          echo "ok";              
         }
         else 
            echo "err210你没有权限";         
        }           
      } 
    break;   */
 case "upd_comment": #g
    $fl=$pd->query("select count(1) from grp_forum_comment where fid=".$_POST["fid"])->fetchColumn(0);
    $pd->exec("update grp_forum set comments=".$fl." where id=".$_POST["fid"]);
    echo "ok";
    break;
  case "del_album":#g 删除相册
    if($pd->exec("delete from grp_album where id=".$_POST["id"]." and uid='".$uid."'")){
        echo "ok";
        if(file_exists('../album/'.$_POST["g"].'/'.$_POST["id"].'/')){ 
          array_map('unlink',glob('../album/'.$_POST["g"].'/'.$_POST["id"].'/*'));
          rmdir('../album/'.$_POST["g"].'/'.$_POST["id"]);
        } 
    }
    else{
       if($pd->query("select count(1) from grp_member where gid=".$_POST["g"]." and id='".$uid."' and isman>0")->fetchColumn(0)){
          $pd->exec("delete from grp_album where id=".$_POST["id"]);
          log_log($pd,$uid,'删除群相册:id-'.$_POST["id"],3);
          echo "ok";  
          if(file_exists('../album/'.$_POST["g"].'/'.$_POST["id"].'/')){ 
            array_map('unlink',glob('../album/'.$_POST["g"].'/'.$_POST["id"].'/*'));
            rmdir('../album/'.$_POST["g"].'/'.$_POST["id"]);
          }           
       }
       else 
          echo "err210你没有权限";         
    }    
		break;  
  case "dl_file": #   
    $pd->exec("update grp_file set dnums=ifnull(dnums,0)+1 where id=".$_POST["id"]);
    break;
  case "del_file": #g        
          if(!$pd->query("select ftype from grp_file where id=".$_POST["id"])->fetchColumn(0)){ 
              $f=$pd->query("select file from grp_file where id=".$_POST["id"])->fetchColumn(0);
              unlink("../file/".$_POST["g"]."/".$f);
          }
          $pd->exec("delete from grp_file where id=".$_POST["id"]);
          log_log($pd,$uid,'删除群文件:id-'.$_POST["id"],3);
          echo "ok";
		    break; 
   case "add_file1": #g 添加文件  
          $ext=strtolower(pathinfo($_POST["name"], PATHINFO_EXTENSION));
          $name=basename($_POST["name"],'.'.$ext);
          $nid=$pd->gentmrid("grp_file");  
          $pd->exec("insert into grp_file(id,uid,gid,name,timestamp,fsize,ftype,fext,fmd5,`file`,dnums) values(".$nid.",'".$uid."',".$_POST["g"].",'".$name
          ."',UNIX_TIMESTAMP(),".$_POST["size"].",".$_POST["type"].",'".$ext."','".$_POST["md5"]."','',1)"); 
          echo "ok"; 
          break; 
  case "add_file": #g 添加文件  
          $nid=$pd->gentmrid("grp_file");  
          $pd->exec("insert into grp_file(id,uid,gid,name,timestamp,fsize,ftype,fext,fmd5,`file`,dnums) values(".$nid.",'".$uid."',".$_POST["g"].",'".$_POST["ofname"]
          ."',UNIX_TIMESTAMP(),".$_POST["size"].",".$_POST["type"].",'".$_POST["ext"]."','".$_POST["md5"]."','".$_POST["fname"]."',0)"); 
          echo "ok";  
    break;       
}		
$pd->close();
unset($pd);
unset($rs);
