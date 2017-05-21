<?php
header("Content-type: text/html; charset=utf-8"); 
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';
#导入用户

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];

#$ip = $_SERVER["REMOTE_ADDR"];   
$pd=new pdo_mysql(); 
switch($_POST["tpl"]){
  case "add": #添加学生A  
    $data=str_replace("，",",",base64_decode($_POST["data"]));    
    $arr=explode("\n",$data);
    $pass=md5($_POST["pwd"]);
    $gid=$_POST['gid'];
    $idtype=$_POST['idtype']; 
    $mob=$_POST["mob"];
    $sid=$_POST["sid"];
    $nid=time();#  
    $rs=$pd->query("select * from `school` where id=".$sid); 
		if($r=$rs->fetch(PDO::FETCH_ASSOC)){
        $period=$r["period"];
    }else{
      echo "年级不存在.无法导入";
      break;
    }                                     
    
    if($pd->query("select count(1) from act_member where timestamp=".$nid)->fetchColumn(0))#如果存在id
      $nid=$pd->query("select max(timestamp)+1 from act_member")->fetchColumn(0);
    $rets="";
        
    $num=0;#导入字段数量
    $modid=0;#手机号下标
    switch($idtype){
      case 1:
        $num = 2;
        $modid = 3; 
        break;
      case 3:
        $num = 3;
        $modid = 4;
        break;
      default: 
        $num = 2;
        $modid = 3;
        break;
    }
    
    for($k=0;$k<count($arr);$k++){
       $arr1=explode(",",$arr[$k]);   
       if(count($arr1) > $num){
         $nid1=DATA_PRE.$nid;    #0 1 2 3  
         $id=$pd->genid("act_school");
         if($mob)$mobs=$arr1[$modid];else $mobs="";
         if($pd->query("select count(1) from act_member where cardno='".$arr1[1]."' or email='".$arr1[2]."'")->fetchColumn(0)){
           $rets.=$arr1[0].",".$arr1[1].",导入错误：身份证号或邮箱已存在".PHP_EOL; 
         }else{
           switch($idtype){
                case 1:
                  if($pd->exec("insert into act_member(id,cardno,truename,nick,username,mobile,email,pmd5,idtype,school,period,grade) values('".$nid1."','".$arr1[1]."','".$arr1[0]
                    ."','".$arr1[0]."','".$nid1."','".$mobs."','".$arr1[2]."','".$pass."',1,".$sid.",".$period.",".$gid.")")){
                    $pd->exec("insert into act_school(id,uid,sid,`intoyear`,`default`) values($id,'".$nid1."','".$sid."',".date("Y").",1)");
                    $rets.=$arr1[0].",".$arr1[1].",成功!用户名:".$nid1.PHP_EOL;  
                  }else{
                    $rets.=$arr1[0].",".$arr1[1].",错误：无法添加记录".PHP_EOL;
                  }    
                  break;
                case 2:
                  if($pd->exec("insert into act_member(id,cardno,truename,nick,username,mobile,email,pmd5,idtype,school,period,grade) values('".$nid1."','".$arr1[1]."','".$arr1[0]
                    ."','".$arr1[0]."','".$nid1."','".$mobs."','".$arr1[2]."','".$pass."',2,".$sid.",".$period.",".$gid.")")){
                    $pd->exec("insert into act_school(id,uid,sid,`intoyear`,`default`) values($id,'".$nid1."','".$sid."',".date("Y").",1)");
                    $rets.=$arr1[0].",".$arr1[1].",成功!用户名:".$nid1.PHP_EOL;  
                  }else{
                    $rets.=$arr1[0].",".$arr1[1].",错误：无法添加记录".PHP_EOL;
                  }
                  break;
                case 3:
                  if($pd->exec("insert into act_member(id,cardno,truename,nick,username,mobile,email,pmd5,idtype,school,period,grade) values('".$nid1."','".$arr1[1]."','".$arr1[0]
                    ."','".$arr1[0]."','".$nid1."','".$mobs."','".$arr1[2]."','".$pass."',3,".$sid.",".$period.",".$gid.")")){
                    $pd->exec("insert into act_school(id,uid,sid,`intoyear`,`default`) values($id,'".$nid1."','".$sid."',".date("Y").",1)");
                    #子女身份证号
                    $cuid=$pd->query("select id from act_member where cardno='".$arr1[3]."'")->fetchColumn(0);
                    if($cuid){
                      $gc_id=$pd->genid("gad_children");
                      $pd->exec("insert into gad_children(id,uid,cid,`cardno`,`timestamp`,`state`) values(".$gc_id.",'".$nid1."','".$cuid."','".$arr1[3]."',UNIX_TIMESTAMP(),2)");
                    }
                    $rets.=$arr1[0].",".$arr1[1].",成功!用户名:".$nid1.PHP_EOL;  
                  }else{
                    $rets.=$arr1[0].",".$arr1[1].",错误：无法添加记录".PHP_EOL;
                  }
                  break;
              }
         } 
       }else
          $rets.=$arr[$k].",错误：数据不合法".PHP_EOL;
       
    }  
     
      
             
    
    echo $rets;
   break;
  
   case "addb":#添加家长
    $data=str_replace("，",",",base64_decode($_POST["data"]));    
    $arr=explode("\n",$data);
    $pass=md5($_POST["pwd"]); 
    $mob=$_POST["mob"];
    $cid=$_POST["cid"];
    $nid=time();#  
    $rs=$pd->query("select * from `class` where id=".$cid); 
		if($r=$rs->fetch(PDO::FETCH_ASSOC)){
        $sch=$r["school"];
        $period=$r["period"];
        $grade=$r["grade"];
    }else{
      echo "班级不存在.无法导入";
      break;
    }                                     
      
    if($pd->query("select count(1) from act_member where timestamp=".$nid)->fetchColumn(0))#如果存在id
      $nid=$pd->query("select max(timestamp)+1 from act_member")->fetchColumn(0);  
    $rets="";         
    for($k=0;$k<count($arr);$k++){
       $arr1=explode(",",$arr[$k]);   
       if(count($arr1)>3){#最少4个字段才能导入        
         $nid1=DATA_PRE.$nid;   
         if($mob)$mobs=$arr1[4];else $mobs="";
         
         if($pd->query("select count(1) from act_member where cardno='".$arr1[1]."' or email='".$arr1[2]."'")->fetchColumn(0)){
           $rets.=$arr1[0].",".$arr1[1].",导入错误：身份证号或邮箱已存在".PHP_EOL; 
         }else{
           if($pd->exec("insert into act_member(id,cardno,truename,nick,username,mobile,email,pmd5,idtype,school,period,grade) values('".$nid1."','".$arr1[1]."','".$arr1[0]
              ."','".$arr1[0]."','".$nid1."','".$mobs."','".$arr1[2]."','".$pass."',3,".$sch.",".$period.",".$grade.")")){
              $pd->exec("insert into act_school(id,sid,`intoyear`,`default`) values('".$nid1."','".$sch."',".date("Y").",1)");
              #子女身份证号
              $cuid=$pd->query("select id from act_member where cardno='".$arr1[3]."'")->fetchColumn(0);
              if($cuid)
                  $pd->exec("insert into gad_children(id,cid,`cardno`,`timestamp`,`state`) values('".$nid1."','".$cuid."','".$arr1[3]."',UNIX_TIMESTAMP(),2)");  
              $rets.=$arr1[0].",".$arr1[1].",成功!用户名:".$nid1.PHP_EOL;  
           }else
             $rets.=$arr1[0].",".$arr1[1].",错误：无法添加记录".PHP_EOL; 
            
         } 
       }else
          $rets.=$arr[$k].",错误：数据不合法".PHP_EOL;
       
    }
    echo $rets; 
    break; 
}
$pd->close();
unset($rs);
unset($pd);