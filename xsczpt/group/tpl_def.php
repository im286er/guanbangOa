<?php
#if(!session_id()) session_start();   
$T->loadTpl("./html/".$template."/".$qname.".htm");
$T->SetTpl('top','../inc/top.htm');      
$T->SetTpl('foot','foot.htm');   
$T->SetTpl('cssjs','cssjs.inc'); 
$T->SetTpl('head','head.inc');       
switch($qname){ 
  /*
  
  */   
case "forum_send":#检测权限
case "photo_ad":
   chkLogin("uid","/?t=login");  
   if(!$T->db->query("select count(1) from grp_member where gid=".$gid." and uid='".$uid."'")->fetchColumn(0))
    header("location: /?t=error&no=G201&name=没有发布权限&msg=您不是本群成员不能进行此操作！");
    if($qname=="photo_ad")    
        $T->Set("id",$_GET["id"]);
    break;    
case "topic":
    $T->readDB("select * from `grp_forum` where id=".$_GET["id"]);   
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_forum_comment where gid=".$gid." and fid=".$_GET["id"])->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=topic&g=".$gid."&id=".$_GET["id"]);
    $T->Set("page",$page); 
	#$T->Set("num",$rc-$p*15+15); 
   # $T->SetBlock("list","select S.*,A.truename,A.username,(select nick from v_act_member where id=uid) as tname,(select nick from v_act_member where id=rid) as rname,wid as rwid,(select replace(des,'\"','\'') as wdes1 from grp_forum_comment where id=rwid) as wdes,replace(des,'\"','\'') as des1 from grp_forum_comment S left join act_member A on A.id=S.id where S.gid=".$gid." and S.fid=".$_GET["id"]." order by id desc limit ".(($p-1)*15).",15");
    $T->SetBlock("list","select S.*,A.nick from grp_forum_comment S left join act_member A on A.id=S.uid where S.fid=".$_GET["id"]." order by timestamp desc limit ".(($p-1)*15).",15");
    break;  
case "forum":      
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_forum where gid=".$gid)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=forum&g=".$gid);
    $T->Set("page",$page); 
    $T->SetBlock("list","select S.*,A.truename,A.nick from grp_forum S left join act_member A on A.id=S.uid where S.gid=".$gid." order by timestamp desc limit ".(($p-1)*15).",15");
    break;
  case "photo_last": #       
    $T->ReadDB("select * from grp_album where id=(select aid from grp_photo where aid=".$_GET["a"]." and id=".$_GET["id"].")");  
    break; 
  case "photo":
    $T->ReadDB("select * from grp_album where id=".$_GET["id"]); 
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_photo where aid=".$_GET["id"])->fetchColumn(0);
    $page=getPageHtml_bt($rc,20,$p,"&t=photo&g=".$gid."&id=".$_GET["id"]);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from grp_photo where aid=".$_GET["id"]." order by id desc limit ".(($p-1)*20).",20");
    break;     
 case "album": 
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_album where gid=".$gid)->fetchColumn(0);
    $page=getPageHtml_bt($rc,20,$p,"&t=album&g=".$gid);
    $T->Set("page",$page); 
    $T->SetBlock("list","select S.*,A.nick from grp_album S left join act_member A on A.id=S.uid where S.gid=".$gid." order by timestamp desc limit ".(($p-1)*20).",20",array('timestamp'),array('%Y-%m-%d') );
    break;        
  /*case "fdl": #g 文件下载 
      $rs=$T->db->query("SELECT * from grp_file where id=".$_GET["id"]);
      if($r=$rs->fetch(PDO::FETCH_ASSOC)){
        if($r["ftype"]){
          $url=PAN_URL."api/yun?t=dl&id=".$r["fmd5"];
        } 
        else
          $url="./file/".$gid."/".$r["file"];
      }
      unset($r);
      unset($rs); 
      $T->close();	
      unset($T);     
      #echo $url;
      header("location: ".$url);
      exit;
      break;
  case "file":#g
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_file where gid=".$gid)->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=file&g=".$gid);
    $T->Set("page",$page); 
    $T->SetBlock("list","select S.*,A.truename from grp_file S left join act_member A on A.id=S.uid where S.gid=".$gid." order by timestamp desc limit ".(($p-1)*15).",15");
     break;  */        
  case "member":#g 
    $T->SetBlock("list","select S.*,A.truename,A.username,A.nick from grp_member S left join act_member A on A.id=S.uid where S.gid=".$gid." and S.state=2 order by id desc");
    break; 
  case "index":          
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from grp_weibo where gid=".$gid." and wid is null")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&g=".$gid);
    $T->Set("page",$page);   
 #   $T->SetBlock2("list","select S.*,A.truename,A.username from grp_weibo S left join act_member A on A.id=S.id where S.gid=".$gid." order by id desc limit ".(($p-1)*15).",15"
 #     	,array(array("block"=>"rp","pid"=>"id","sql"=>"select S.*,A.truename,A.username from grp_weibo_comment S left join act_member A on A.id=S.id where S.wid=? order by id desc limit 15"))
 #    	);
   #$T->SetBlock2("list","select S.*,A.truename,A.username,(select nick from v_act_member where id=uid) as tname,(select nick from v_act_member where id=rid) as rname,wid as rwid,(select replace(des,'\"','\'') as wdes1 from grp_weibo where id=rwid) as wdes,replace(des,'\"','\'') as des1 from grp_weibo S left join act_member A on A.id=S.id where S.gid=".$gid." and S.wid is null order by id desc limit ".(($p-1)*15).",15",array(array("block"=>"rp","pid"=>"id","sql"=>"select S.*,A.truename,A.username,(select nick from v_act_member where id=uid) as tname,(select nick from v_act_member where id=rid) as rname,wid as rwid,(select replace(des,'\"','\'') as wdes1 from grp_weibo where id=rwid) as wdes,replace(des,'\"','\'') as des1 from grp_weibo S left join act_member A on A.id=S.id where S.gid=".$gid." and S.wid =? and S.wid is not null order by id desc")));
	$T->SetBlock2("list","select * from `grp_weibo` where gid=".$gid." order by id desc limit ".(($p-1)*15).",15",
			array(array("block"=>"rp","pid"=>"id","sql"=>"select * from `grp_weibo_comment` where wid=? order by id desc")));
    #$T->Set("schools",$T->db->query("select name from school where id=(select school from `group` where id=".$gid.")")->fetchColumn(0)); 
    break;  
}
#  
$T->readDB("select * from `group` where id=".$gid);
$T->Set("g",$gid);  
$T->Set("gtitle",LR_NAME);   
$T->clearNaN();                  
$T->clearNoN();  
$T->display();