<?php 
header("Content-type: text/html; charset=utf-8;");
#require('../ppf/360_safe3.php');
require("../../ppf/fun.php"); 
require("../../ppf/pdo_template.php"); 

#自动化模板生成和处理模块 仅处理list页面和AMD页面 tree页面独立处理
session_start(); 
chkLogin('giiuid','./?t=login');     

$tpl=isset($_GET["t"])?$_GET["t"]:"";
$gen=isset($_GET["g"])?$_GET["g"]:"";  
 
if($gen=="list_am"){     
    $T=new pdo_template('./html/tpl/list_am.htm',true,"utf8");
    $tbl=$T->db->query("select tblname from ppf_tpl where tpl='".$tpl."'")->fetchColumn(0);
    $T->ReadDb("select name,tblorder,tblorderby,formatdates from ppf_tpl where tpl='".$tpl."'"); 
    $T->SetBlock("display","select column_name from information_schema.columns where table_schema='".DB_NAME."' and table_name='".$tbl."'");
    $T->SetBlock("display1","select column_name from information_schema.columns where table_schema='".DB_NAME."' and table_name='".$tbl."'");    
    $T->SetBlock("display2","select column_name from information_schema.columns where table_schema='".DB_NAME."' and table_name='".$tbl."'");    

} 
if($gen=="list"){ 
    $T=new pdo_template('./html/tpl/list.htm',true,"utf8");
    $tbl=$T->db->query("select tblname from ppf_tpl where tpl='".$tpl."'")->fetchColumn(0); 
    $T->ReadDb("select name,tblorder,tblorderby,formatdates from ppf_tpl where tpl='".$tpl."'"); 
    $T->SetBlock("display","select column_name from information_schema.columns where table_schema='".DB_NAME."' and table_name='".$tbl."'");
    $T->SetBlock("display1","select column_name from information_schema.columns where table_schema='".DB_NAME."' and table_name='".$tbl."'");    
}

if($gen=="am"){
$T=new pdo_template('./html/tpl/am.htm',true,"utf8");
$tbl=$T->db->query("select tblname from ppf_tpl where tpl='".$tpl."'")->fetchColumn(0);   
$T->ReadDb("select tpl,formatdates from ppf_tpl where tpl='".$tpl."'");   	   	
$T->SetBlock("display","select column_name from information_schema.columns where table_schema='".DB_NAME."' and table_name='".$tbl."'");

$tpl=$tpl."_am";
}
if($gen=="tree"){ 
$T=new pdo_template('./html/tpl/tree.htm',true,"utf8"); 
$T->ReadDb("select tpl,name from ppf_tpl where tpl='".$tpl."'");   	  	 
 
}

if($gen){   
    file_put_contents("html/".$tpl.".htm",$T->content);	
    echo "生成完成";		
}
$T->display();
$T->close();
unset($T); 