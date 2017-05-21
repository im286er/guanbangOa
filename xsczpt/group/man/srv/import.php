<?php
header("Content-type: text/html; charset=utf-8"); 
require '../../../ppf/fun.php';
require '../../../ppf/pdo_mysql.php';
#添加用户

if (!session_id()) session_start(); 
chkLoginNoJump("uid");  
$uid= $_SESSION["uid"];

#$ip = $_SERVER["REMOTE_ADDR"];   
$pd=new pdo_mysql(); 
switch($_POST["tpl"]){
  case "adda": #添加用户  
    $data=str_replace("，",",",base64_decode($_POST["data"]));    
    $arr=explode("\n",$data); 
    $type=$_POST["type"];
    $gid=$_POST["gid"];          
    $rets="";         
    for($k=0;$k<count($arr);$k++){
       #$arr1=explode(",",$arr[$k]); 
       switch($type){
         case 1:
            $suid=$pd->query("select id from act_member where username='".$arr[$k]."'")->fetchColumn(0); 
              break;
        case 2:
            $suid=$pd->query("select id from act_member where email='".$arr[$k]."'")->fetchColumn(0);
              break;
        case 3:
            $suid=$pd->query("select id from act_member where mobile='".$arr[$k]."'")->fetchColumn(0);
              break; 
       }
       if($suid){
         if($pd->query("select count(1) from grp_member where id='".$suid."' and gid=".$gid)->fetchColumn(0)){
           $rets.=$arr[$k].",添加错误：已存在".PHP_EOL; 
         }else{
           if($pd->exec("insert into grp_member(id,gid,state,timestamp,isman) values('".$suid."','".$gid."',2,UNIX_TIMESTAMP(),0)"))              
              $rets.=$arr[$k].",成功".PHP_EOL;  
           else
             $rets.=$arr[$k].",错误：无法添加记录".PHP_EOL;           
         } 
       }
       else
          $rets.=$arr[$k].",添加错误：用户不存在".PHP_EOL;       
    }
    echo $rets;
   break;
   case "addb":#添加家长 未用
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