<?php
/**前端模板调用默认库*/
#检测photo pass
if($qname=="photo"){        
    if($zid!=$uid){
        $aid=$_GET["aid"];   
        $visit=$T->db->query("select visit from zone_album where id='".$aid."'")->fetchColumn(0);    
        if($visit==3){
            header("location: /?t=error&no=Z113&name=相册禁止访问&msg=禁止所有人访问");
        }
        if($visit==1){
            if(!$T->db->query("select count(1) from act_friend where uid='".$zid."' and fid='".$uid."'")->fetchColumn(0))
                header("location: /?t=error&no=Z111&name=相册无权访问&msg=您不是对方好友");        
        }
        if($visit==2){#输入密码         
            if(!isset($_SESSION["albumpwd"])||!$T->db->query("select count(1) from zone_album where id='".$aid."' and pwd='".$_SESSION["albumpwd"]."'")->fetchColumn(0)){           
                $T->loadTpl("./html/login_album.htm");
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
    
}

$T->loadTpl("./html/".$model."/".$qname.".htm");
$T->SetTpl('cssjs','cssjs.inc'); 
$T->SetTpl('top','../inc/top.htm');      
$T->SetTpl('foot','../inc/foot.htm');       
$T->SetTpl('head','head.inc');    

$T->Set("zid",$zid);
$T->Set("mynick",isset($_SESSION["nick"])?$_SESSION["nick"]:'');
$isme = ($zid==$uid)?1:0;
$T->Set("isme",$isme);
switch($qname){
    
    case "index":                                                                                            
        $T->SetBlock("b_list","SELECT id,title,uid from blog_list where uid = '".$zid."' order by id desc limit 3");  
        $T->SetBlock("f_list","select T.fid,M.truename,M.nick from act_friend T left join act_member M on M.id=T.fid where T.uid='".$zid."' order by fid desc limit 9");
        $T->SetBlock2("list","select * from `weibo` where uid='".$zid."' order by id desc limit 0,15"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.nick from weibo_comments T left join act_member M on M.id=T.uid where wid=? order by T.id desc limit 10"))
      	); 
        $T->Set("PAN_URL",PAN_URL); 
        $T->Set("goldname",GOLD_NAME);          
        break; 
     case "weibo":
        $p=isset($_GET["p"])?$_GET["p"]:"1";  
        $rc= $T->db->query("select count(1) from `weibo` where uid='".$zid."'")->fetchColumn(0);
        $page=getPageHtml_bt($rc,15,$p,"&t=weibo&id=".$zid);
        $T->Set("page",$page); 
        $T->SetBlock2("list","select * from `weibo` where uid='".$zid."' order by id desc limit ".(($p-1)*15).",15"
             	,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.nick from weibo_comments T left join act_member M on M.id=T.uid where wid=? order by T.id desc limit 10"))
        );
        break;
    case "bloga":
          $T->SetBlock("cate","SELECT * from blog_cate where uid ='".$zid."' order by odx desc");
          $c=isset($_GET["c"])?$_GET["c"]:""; 
          $p=isset($_GET["p"])?$_GET["p"]:"1"; 
          $wh=" where uid='".$zid."'";
          if(!empty($c))$wh.=" and cid=".$c;
          $rc= $T->db->query("select count(1) from `blog_list` ".$wh)->fetchColumn(0);
          $page=getPageHtml_bt($rc,15,$p,"&t=bloga&id=".$zid."&c=".$c);
          $T->Set("page",$page); 
          $T->SetBlock("list","select id,uid,title,see,comments,timestamp,created from `blog_list` ".$wh." order by timestamp desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
          break;  
    case "blog":
      $bid=$_GET['bid'];
      $pre=$T->db->query("select id from blog_list where id<".$bid." and uid='".$zid."' order by id desc limit 1")->fetchColumn(0);
      $next=$T->db->query("select id from blog_list where id>".$bid." and uid='".$zid."' order by id asc limit 1")->fetchColumn(0);
      if(empty($pre))$pre= $_GET['bid'];
      if(empty($next))$next= $_GET['bid']; 
      $T->Set("previd",$pre); 
      $T->Set("nextid",$next);  
      $T->ReadDB("select * from blog_list where id=".$bid);
      #$floor=$T->db->query("select count(1)+1 from blog_comments where bid=".$bid)->fetchColumn(0);
      #$T->Set("floor",$floor); 
      $p=isset($_GET["p"])?$_GET["p"]:"1"; 
      $wh=" where bid=".$bid;   #uid='".$zid."' and 
      $rc= $T->db->query("select count(1) from `blog_comments` ".$wh)->fetchColumn(0);
      $page=getPageHtml_bt($rc,30,$p,"&t=blog&id=".$zid."&bid=".$bid);
      $T->Set("page",$page); 
      $T->SetBlock2("list","select * from blog_comments ".$wh." order by id desc limit ".(($p-1)*30).",30"
          ,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.nick from blog_comment_comments T left join act_member M on M.id=T.uid where bcid=? order by T.id desc limit 10"))
      );     
	  $T->Set("ouid",isset($_SESSION['uid'])?$_SESSION['uid']:'');
      break;
    case "blog_cate":
        $T->SetBlock("list","SELECT * from blog_cate where uid='".$uid."' order by odx desc");
        break;
    case "board":
        $p=isset($_GET["p"])?$_GET["p"]:"1";
        $rc= $T->db->query("select count(1) from `zone_leave` where touid='".$zid."'")->fetchColumn(0);
        $page=getPageHtml_bt($rc,10,$p,"&t=board&id=".$zid);
        #$T->Set("leave",$leave);
        $T->Set("page",$page); 
        $T->SetBlock2("list","select * from `zone_leave` where touid='".$zid."' order by id desc limit ".(($p-1)*10).",10"
            	,array(array("block"=>"rp","pid"=>"id","sql"=>"select T.*,M.truename from zone_leave_comments T left join act_member M on M.id=T.uid where T.mid=? order by id desc"))
            	);
        break;  
     case "album":
        $p=isset($_GET["p"])?$_GET["p"]:"1"; 
        $rc= $T->db->query("select count(1) from zone_album where uid='".$zid."'")->fetchColumn(0);
        $page=getPageHtml_bt($rc,20,$p,"&t=album&id=".$zid);
        $T->Set("page",$page); 
        $T->SetBlock("list","select * from zone_album where uid='".$zid."' order by timestamp desc limit ".(($p-1)*20).",20",array('timestamp'),array('%Y-%m-%d') );
        break;
    case "photo":                                            
        $aid=$_GET["aid"];        
        $T->ReadDB("select * from zone_album where id=".$aid); 
        $p=isset($_GET["p"])?$_GET["p"]:"1"; 
        $rc= $T->db->query("select count(1) from zone_album_pic where aid=".$aid)->fetchColumn(0);
        $page=getPageHtml_bt($rc,20,$p,"&t=photo&id=".$aid."&id=".$zid);
        $T->Set("page",$page); 
        $T->SetBlock("list","select * from zone_album_pic where aid=".$aid." order by id desc limit ".(($p-1)*20).",20");
        break; 
	case "photo_last":
        $T->ReadDB("select * from zone_album where id=".$_GET["a"]);
        $pic = $T->db->query("select pic from zone_album_pic where id=".$_GET["pid"]." and aid=".$_GET["a"]."")->fetchColumn(0);
		$T->Set("pic",$pic);
        break;
                      
}
$T->ReadDB("select * from zone where id='".$zid."'");
$T->ReadDB("select * from act_member where id='".$zid."'"); 
$T->Set("gtitle",LR_NAME);
$T->Set("uid",$uid);   
$T->clearNaN();                  
$T->clearNoN();     
$T->display();
