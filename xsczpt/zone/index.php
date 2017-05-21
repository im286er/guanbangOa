<?php
/* 个人空间*/
header("Content-type: text/html; charset=gbk;");
require('../ppf/fun.php'); 
require("../ppf/pdo_template.php"); 
  
if (!session_id()) session_start();
#chkLogin("uid","/?t=login");
$qname=isset($_GET["t"])?$_GET["t"]:"index";       
$uid=isset($_SESSION['uid'])?$_SESSION['uid']:"";
$zid=isset($_GET['id'])?$_GET['id']:"";
if(empty($zid)) header("location: /?t=error&no=Z101&name=传入参数错误&msg=传入参数错误");


$T=new pdo_template('',true);
if(!$T->db->query("select count(1) from zone where id='".$zid."'")->fetchColumn(0)){
   $T->db->exec("insert into zone(id,name,`explain`,visit,comment,timestamp) values('".$zid."','默认空间','空间说明',0,0,UNIX_TIMESTAMP())");         
}

#check limit 
if($zid!=$uid){#非自己
    $visit=$T->db->query("select visit from zone where id='".$zid."'")->fetchColumn(0);    
    if($visit==3){
        header("location: /?t=error&no=Z104&name=ZONE禁止访问&msg=禁止所有人访问");
    }
    if($visit==1){
        if(!$T->db->query("select count(1) from act_friend where uid='".$zid."' and fid='".$uid."'")->fetchColumn(0))
            header("location: /?t=error&no=Z103&name=ZONE无权访问&msg=您不是对方好友");        
    }
    if($visit==2){#输入密码         
        if(!isset($_SESSION["zonepwd"])||!$T->db->query("select count(1) from zone where id='".$zid."' and visitpwd='".$_SESSION["zonepwd"]."'")->fetchColumn(0)){           
            $T->loadTpl("./html/login.htm");
            $T->SetTpl('cssjs','cssjs.inc'); 
            $T->SetTpl('top','../inc/top.htm');      
            $T->SetTpl('foot','../inc/foot.htm');     
            $T->Set("gtitle",LR_NAME);        
            $T->display();
            $T->close();	
            unset($T);
            exit;
        }      
    }
}


#模板do operate  
$model=$T->db->query("select tpl from zone where id='".$zid."'")->fetchColumn(0);
if(empty($model))$model="def";
if(file_exists("./idx_model_".$model.".php"))
    include("./idx_model_".$model.".php");    
else
    include("./idx_model_def.php");   
$T->close();	
unset($T);