<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
#生成缓存
session_start(); 
chkLoginNoJump("uid");

$pd=new pdo_mysql();
switch($_POST["tpl"]){   
case "gen_active_lvl":    
case "gen_active_cate":
    $s="";$jsn='{';
    $rs=$pd->query("select id,name from active_level order by odx desc");
    while($r=$rs->fetch(PDO::FETCH_ASSOC)){#第一级
        $s.='<option value="'.$r['id'].'">'.$r['name'].'</option>';	
        $jsn.='"'.$r['id'].'":"'.$r['name'].'",';		
    }			
    file_put_contents(DIR_ROOT.'/data/active_lvl.txt',$s); 	
    file_put_contents(DIR_ROOT.'/data/active_lvl.json.txt',substr($jsn,0,strlen($jsn)-1).'}');
    

    $s="";$jsn='{';
    $rs=$pd->query("select id,name from active_category order by odx desc");
    while($r=$rs->fetch(PDO::FETCH_ASSOC)){#第一级
        $s.='<option value="'.$r['id'].'">'.$r['name'].'</option>';	
        $jsn.='"'.$r['id'].'":"'.$r['name'].'",';		
    }			
    file_put_contents(DIR_ROOT.'/data/active_cate.txt',$s); 	
    file_put_contents(DIR_ROOT.'/data/active_cate.json.txt',substr($jsn,0,strlen($jsn)-1).'}');
    
    $outs="{";
    $outs.='"cate":'.file_get_contents(DIR_ROOT.'/data/active_cate.json.txt');
    $outs.=',"lvl":'.file_get_contents(DIR_ROOT.'/data/active_lvl.json.txt');  
    $outs.=',"visit":{"0":"公开，所有可以访问","1":"登录，需要登录用户","2":"指定人","3":"秘密活动"}';
    $outs.=',"enroll":{"0":"公开报名","1":"报名需审核","2":"仅限邀请"}'; 
    file_put_contents(DIR_ROOT.'/data/active.object.txt',$outs.'}');  
    unset($outs);	
    echo "ok";
    break;  
  
}		
$pd->close();
unset($pd);
unset($rs);