<?php 
header("Content-type: text/html; charset=gbk;");
#require('../ppf/360_safe3.php');
require("../fun.php"); 
require("../pdo_template.php"); 

#自动化模板生成和处理模块 仅处理list页面和AMD页面 tree页面独立处理
session_start(); 
chkLogin('giiuid');     

$ptid=isset($_GET["t"])?$_GET["t"]:"";
$htm=isset($_GET["htm"])?$_GET["htm"]:"0";
$type=isset($_GET["type"])?$_GET["type"]:"0";
$gen=isset($_GET["gen"])?$_GET["gen"]:"0"; 

if(!empty($htm)){#静态模板	
	$T=new pdo_template('./html/'.$tpl.'.htm');  
	$T->display();
	$T->close();
	unset($T);
} 
else{#模板生成
  if($type=="1"){#list_am
    $T=new pdo_template('./html/tpl_list_am.htm');
    #$T->SetTpl("cssjs","cssjs.inc");
    #$T->SetTpl("top","top.inc");
    #$T->SetTpl("left","left.inc");
    #$T->SetTpl("foot","foot.inc");
    $tpl=$T->db->query("select tpl from ppf_tpl where id=".$ptid)->fetchColumn(0);  
    $tpname=$T->db->query("select name from ppf_tpl where id=".$ptid)->fetchColumn(0);     	
    $T->Set("name",$tpname);
    $T->Set("tpl",$tpl);
  	$T->ReadDb("select name,tblorder,tblorderby,formatdates from ppf_tpl where id=".$ptid);    	
  	$T->SetBlock("query","select * from ppf_tpl_query where ptid=".$ptid." order by odx desc");   		
  	$T->SetBlock("display","select * from ppf_tpl_display where ptid=".$ptid." order by odx desc");
  	$T->SetBlock("displayc","select * from ppf_tpl_display where ptid=".$ptid." order by odx desc");  
    
    $T->SetBlock("edit","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='txt' order by odx desc");
    	$T->SetBlock("edit1","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype in('ck','des') order by odx desc");#ck编译器
    	$T->SetBlock("editupd","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='upd' order by odx desc");#上传
    	$T->SetBlock("editdate","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='date' order by odx desc");#日期
    	$T->SetBlock("editsel","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='sel' order by odx desc");#下拉列表
    	$T->SetBlock("editchk","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='chk' order by odx desc");#多选框
    	$T->SetBlock("edit0","select * from ppf_tpl_edit where ptid=".$ptid." and hidden=1 order by odx desc");#隐藏	
      $T->SetBlock("pass","select ctrlpass from ppf_tpl where   ctrlpass is not null and id=".$ptid);
  }
  if($type=="2"){#list       
    $T=new pdo_template('./html/tpl_list.htm');
    #$T->SetTpl("cssjs","cssjs.inc");
    #$T->SetTpl("top","top.inc");
    #$T->SetTpl("left","left.inc");
    #$T->SetTpl("foot","foot.inc");
    $tpl=$T->db->query("select tpl from ppf_tpl where id=".$ptid)->fetchColumn(0);  
    $tpname=$T->db->query("select name from ppf_tpl where id=".$ptid)->fetchColumn(0);     	
    $T->Set("name",$tpname);
    $T->Set("tpl",$tpl);
  	$T->ReadDb("select name,tblorder,tblorderby,formatdates from ppf_tpl where id=".$ptid);    	
  	$T->SetBlock("query","select * from ppf_tpl_query where ptid=".$ptid." order by odx desc");   		
  	$T->SetBlock("display","select * from ppf_tpl_display where ptid=".$ptid." order by odx desc");
  	$T->SetBlock("displayc","select * from ppf_tpl_display where ptid=".$ptid." order by odx desc");  
  }
  if($type=="3"){#amd
    $T=new pdo_template('./html/tpl_am.htm');
    $T->SetTpl("cssjs","cssjs.inc");
    $T->SetTpl("top","top.inc");
    $T->SetTpl("left","left.inc");
    $T->SetTpl("foot","foot.inc");
     $tpl=$T->db->query("select tpl from ppf_tpl where id=".$ptid)->fetchColumn(0);  
    $tpname=$T->db->query("select name from ppf_tpl where id=".$ptid)->fetchColumn(0);     	   	
    $T->Set("name",$tpname);
    $T->Set("tpl",$tpl);
      $T->SetBlock("edit","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='txt' order by odx desc");
    	$T->SetBlock("edit1","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype in('ck','des') order by odx desc");#ck编译器
    	$T->SetBlock("editupd","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='upd' order by odx desc");#上传
    	$T->SetBlock("editdate","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='date' order by odx desc");#日期
    	$T->SetBlock("editsel","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='sel' order by odx desc");#下拉列表
    	$T->SetBlock("editchk","select * from ppf_tpl_edit where ptid=".$ptid." and hidden<>1 and valtype='chk' order by odx desc");#多选框
    	$T->SetBlock("edit0","select * from ppf_tpl_edit where ptid=".$ptid." and hidden=1 order by odx desc");#隐藏	
    	#密码控件       
    	$T->SetBlock("pass","select ctrlpass from ppf_tpl where   ctrlpass is not null and id=".$ptid);
      $tpl=$tpl."_am";#独立页面
  }
  if($type=="4"){#生成tree
    $T=new pdo_template('./html/tpl_tree.htm');
    #$T->SetTpl("cssjs","cssjs.inc");
    #$T->SetTpl("top","top.inc");
    #$T->SetTpl("left","left.inc");
    #$T->SetTpl("foot","foot.inc");
    $tpl=$T->db->query("select tpl from ppf_tpl where id=".$ptid)->fetchColumn(0);  
    $tpname=$T->db->query("select name from ppf_tpl where id=".$ptid)->fetchColumn(0);  
    $T->Set("name",$tpname);
    $T->Set("tpl",$tpl);
  }
	if($type=="5"){#生成left菜单
    $T=new pdo_template('./html/tpl_left.htm'); 
    $tpl="left"; 
    $T->SetBlock2("left","select * from ppf_tpl where pid=".$ptid." and hidden=0 order by odx desc",  
            		array(array("block"=>"rp","pid"=>"id","sql"=>"select * from ppf_tpl where pid=? and hidden=0 order by odx desc"))
            	);
  }
  

	if($gen){#生成   
		file_put_contents("gen/".$tpl.".htm",$T->content);	
		echo "生成完成";		
	}
	else
		$T->display();
	$T->close();
	unset($T);
} 