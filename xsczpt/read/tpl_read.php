<?php

$T->loadTpl("./html/".$template."/".$qname.".htm");
$T->SetTpl('top','../inc/top.htm');      
$T->SetTpl('foot','../inc/foot.htm');
$T->SetTpl('menu','menu.inc');      
$T->SetTpl('meta','cssjs.inc');
  
switch($qname){
  case "klb_index":        
    break;
	
  case "exit":
      if(isset($_SESSION["uid"])){
    		unset($_SESSION["uid"]);
    		session_destroy();#session_unset(); 相同;
    	}	
    	setcookie('cu',null,time(),"/");         
	break;
  case "reg":
    if(REG_OFF){
      header("location: /?t=error&no=200&name=注册已关闭&msg=系统注册功能已关闭！");
    }
    break;
  case "login":
    if(isset($_GET["url"]))
      $T->Set("tourl",url_base64_decode($_GET["url"]));
    break;
  case "actived":
    $T->SetTpl('menu','../activity/html/def/menu.inc');
    $per = isset($_GET["per"])?$_GET["per"]:0;
	  $sub = isset($_GET["sub"])?$_GET["sub"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
	  $a1 = isset($_GET["a1"])?$_GET["a1"]:0;
    $so = isset($_GET["so"])?$_GET["so"]:"";
	  $wh="";
    if(!empty($per))$wh.=" and v.periods=".$per;
    if(!empty($sub))$wh.=" and v.subjects=".$sub;
    if(!empty($a1)){$truea = "0,".$a1;}else if(!empty($a)){ 
      $truea = $T->db->query("select GROUP_CONCAT(id) from sys_addr where (pidlist like '%,1,%' or pidlist like '1,%' or pidlist='1' or pidlist like '%,1')")->fetchColumn(0);
    }
    if(!empty($truea)){
		  $school = $T->db->query("select GROUP_CONCAT(id) from school where state=2 and  addr in (".$truea.")")->fetchColumn(0);
  		if(!empty($school)){
  			$wh.=" and v.school in (".$school.")";
  		}else{
  			$wh.=" and v.school = 0";
  		}
  	}
	  if(!empty($so))$wh.=$wh." and v.name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `activity` as v where v.state=2 and status=4 ".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=actived&per=".$per."&sub=".$sub."&a=".$a."&a1=".$a1."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select v.id,v.uid,v.name,v.cid,v.lvl,v.enroll,v.visit,v.rstime,v.retime,if(v.membernums,v.membernums,0) amnums,v.status,v.addrs,v.school,a.truename aname from `activity` as v join `act_member` as a on v.uid = a.id where v.state=2 and v.status=4 ".$wh." order by v.timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;  
  case "actives":
    $T->SetTpl('menu','../activity/html/def/menu.inc');
    $per = isset($_GET["per"])?$_GET["per"]:0;
	  $sub = isset($_GET["sub"])?$_GET["sub"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
	  $a1 = isset($_GET["a1"])?$_GET["a1"]:0;
    $so = isset($_GET["so"])?$_GET["so"]:"";
	  $wh="";
    if(!empty($per))$wh.=" and v.periods=".$per;
    if(!empty($sub))$wh.=" and v.subjects=".$sub;
    if(!empty($a1)){$truea = "0,".$a1;}else if(!empty($a)){ 
      $truea = $T->db->query("select GROUP_CONCAT(id) from sys_addr where (pidlist like '%,1,%' or pidlist like '1,%' or pidlist='1' or pidlist like '%,1')")->fetchColumn(0);
    }
    if(!empty($truea)){
		  $school = $T->db->query("select GROUP_CONCAT(id) from school where state=2 and  addr in (".$truea.")")->fetchColumn(0);
  		if(!empty($school)){
  			$wh.=" and v.school in (".$school.")";
  		}else{
  			$wh.=" and v.school = 0";
  		}
  	}
	  if(!empty($so))$wh.=$wh." and v.name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
	  $rc= $T->db->query("select count(1) from `activity` as v where v.state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=actives&per=".$per."&sub=".$sub."&a=".$a."&a1=".$a1."&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select v.id,v.uid,v.name,v.cid,v.lvl,v.enroll,v.visit,v.rstime,v.retime,if(v.membernums,v.membernums,0) amnums,v.status,v.addrs,v.school,a.truename aname from `activity` as v join `act_member` as a on v.uid = a.id where v.state=2".$wh." order by v.timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
  case "activity":
    $T->SetBlock("list","select v.id,v.uid,v.name,v.cid,v.lvl,v.enroll,v.visit,v.rstime,v.retime,if(v.membernums,v.membernums,0) amnums,v.status,v.addrs,v.school,a.truename aname from `activity` as v join `act_member` as a on v.uid = a.id where v.state=2 order by v.visit desc limit 3");
    $T->SetBlock("active_list","select @row := @row +1 AS row,v.id,v.name,v.cid,v.timestamp,if(v.membernums,v.membernums,0) amnums from `activity` as v , (SELECT @row :=0)r where v.state=2 order by v.membernums desc limit 5");
    $T->SetBlock("active_carryOn","select v.*,a.truename aname from `activity` as v join `act_member` as a on v.uid = a.id where v.state=2 and v.status!=4 order by v.timestamp desc limit 6",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock("active_end","select v.*,a.truename aname from `activity` as v join `act_member` as a on v.uid = a.id where v.state=2 and v.status=4 order by v.timestamp desc limit 3");
    $T->SetBlock("comment","select c.*,t.name,a.truename,c.timestamp as timestamp from active_comments c join activity t on c.aid=t.id join act_member a on a.id=c.uid order by c.see desc limit 20",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock("five","select v.*,a.name type from `activity` as v join `active_category` as a on v.cid = a.id where v.state=2 order by v.timestamp desc limit 1",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock("members","select m.uid,a.truename from active_member m join act_member a on a.id=m.uid group by m.uid order by m.timestamp desc limit 16");
   break;
  case "problem":
    $T->readDB("select * from main_problem where id=".$_GET["id"]);
    $timestamp= $T->db->query("select timestamp from `main_problem` where state=2 and id=".$_GET["id"]." ")->fetchColumn(0);
    $T->SetBlock("list","select id,name from `main_problem`  where state=2 and timestamp>".$timestamp." order by timestamp limit 6",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock("listpre","select id,name from `main_problem`  where state=2 and timestamp<".$timestamp." order by timestamp desc limit 1");
    $T->SetBlock("listnext","select id,name from `main_problem`  where state=2 and timestamp>".$timestamp." limit 1");
    break; 
  case "problems": 
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `main_problem` where state=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=problems");
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `main_problem`  where state=2 order by timestamp desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
    break;  
   case "topic":
    $T->readDB("select * from main_topic where id=".$_GET["id"]);
    $timestamp= $T->db->query("select timestamp from `main_topic` where state=2 and id=".$_GET["id"]." ")->fetchColumn(0);
    $T->SetBlock("list","select id,name from `main_topic`  where state=2 and timestamp>".$timestamp." order by timestamp limit 6",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock("listpre","select id,name from `main_topic`  where state=2 and timestamp<".$timestamp." order by timestamp desc limit 1");
    $T->SetBlock("listnext","select id,name from `main_topic`  where state=2 and timestamp>".$timestamp." limit 1");
    break; 
  case "topics": 
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `main_topic` where state=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=topics");
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `main_topic`  where state=2 order by timestamp desc limit ".(($p-1)*15).",15",array('timestamp'),array('%Y-%m-%d %H:%M'));
    break;  
  case "art_info":
    $id = isset($_GET["id"])?$_GET["id"]:0;
    $cid= $T->db->query("select cid from push_list where id=".$id)->fetchColumn(0);
    $pid= $T->db->query("select pid from push_category where id=".$cid)->fetchColumn(0);
    if($pid==0){
      $url="./?t=art_list&pc=".$cid;
    }else{
      $url="./?t=art_list&pc=".$pid."&c=".$cid;
    }
    $T->SetBlock("art","select id,title,des,see,created from `push_list` where id=".$id);
    $T->SetBlock("art_list","select id,title from push_list where cid=".$cid." and id!=".$id." and state=2 order by timestamp desc limit 8");
    $T->Set("url",$url);
    break; 
  case "art_list":
    $pid=(!isset($_GET["pc"]) || $_GET["pc"]==0)?6:$_GET["pc"];
    $c=isset($_GET["c"])?$_GET["c"]:0;
    $display1=$display2="none";
    $pname= $T->db->query("select name from push_category where id=".$pid)->fetchColumn(0);
    $T->SetBlock("art_cate","select id,name,pid from push_category where pid=".$pid." and push_type=1 order by odx desc");
    
    $cnums= $T->db->query("SELECT count(*) FROM push_category where pid=".$pid)->fetchColumn(0);
    if($cnums==0){
      $c = $pid;
    }
    if(empty($c)){
        $display1="block";
        $T->SetBlock2("art_lists","SELECT id,name FROM push_category where pid=".$pid." order by odx desc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,title,created from push_list where cid=? and state=2 order by pic desc limit 8"))
            );
      }else{
        $display2="block";
        $cname= $T->db->query("select name from push_category where id=".$c)->fetchColumn(0);
        $p=(!isset($_GET["p"]) || $_GET["p"]==0)?"1":$_GET["p"]; 
        $rc= $T->db->query("select count(1) from `push_list` where state=2 and cid=".$c)->fetchColumn(0);
        $page=getPageHtml_bt($rc,6,$p,"&t=art_list&pc=".$pid."&c=".$c);
        $T->Set("page",$page);
        $T->SetBlock("art_list","select id,title,created from `push_list` where state=2 and cid=".$c." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
        $T->Set("cname",$cname);
      }
    
    $T->Set("pname",$pname);
    $T->Set("pid",$pid);
    $T->Set("c",$c);
    $T->Set("display1",$display1);
    $T->Set("display2",$display2);
    break;	
  case "art":
    $T->SetBlock("lista","select id,name from `main_art_category` where visible=1 order by odx desc");
    $T->readDB("select * from main_article where id=".$_GET["id"]);                                
    $timestamp= $T->db->query("select timestamp from `main_article` where state=2 and id=".$_GET["id"]." ")->fetchColumn(0);
    $T->SetBlock("list","select id,name from `main_article`  where state=2 and timestamp>".$timestamp." order by timestamp limit 6",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->SetBlock("listpre","select id,name from `main_article`  where state=2 and timestamp<".$timestamp." order by timestamp desc limit 1");
    $T->SetBlock("listnext","select id,name from `main_article`  where state=2 and timestamp>".$timestamp." limit 1");
    break;                   
  case "arts":
    $T->SetBlock("lista","select id,name from `main_art_category` where visible=1 order by odx desc");
    $c=isset($_GET["c"])?$_GET["c"]:"";
    $wh="";
    if(!empty($c)){
      $wh.=" and cid=".$c;
       $T->readDB("select name from main_art_category where id=".$c);
    }
    else{  $T->Set("name","新闻资讯");   }
    $so = isset($_GET["so"])?$_GET["so"]:"";
    if(!empty($so))$wh.=" and name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `main_article` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,6,$p,"&t=arts&so=".$so."&c=".$c);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `main_article`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*6).",6",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
    
  case "famous_search":  
    $T->SetBlock("lista","select id,name from `famous` where state=2 order by id desc limit 8");
    $T->SetBlock("listb","select id,name from `famous` where state=2 order by id desc limit 8,8");
    $so = isset($_GET["so"])?$_GET["so"]:"";
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
    $wh="";
    if(!empty($sc)){
      $wh.=" and school=".$sc;
    }else if(!empty($a)){
      $school = $T->db->query("select GROUP_CONCAT(id) from school where state=2 and addr=".$a)->fetchColumn(0);
      $wh.=" and school in (".$school.")";
    }
    if(!empty($so))$wh.=" and name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `famous` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=famous&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select * from `famous`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break; 
  case "famous":
    $T->SetBlock("new_art","select * from famous_article order by timestamp desc limit 2");
    $T->SetBlock2A("char_famous","select * from famous f join (select fid,count(*) nums from famous_article group by fid limit 4) n on f.id=n.fid order by n.nums desc" 
           ,array("block"=>"rp","sql"=>"select s.name,(select count(*) from famous_member where fid=?)fman from sys_subject s join act_member a on s.id=a.`subject` where a.id=?", 
           "param"=>array("id","uid")) 
           );
    $T->SetBlock("new_active","select id,name,timestamp from activity order by timestamp desc limit 6");
    $T->SetBlock("fam_dynamics","select f.id,f.uid,f.fid,f.title,f.timestamp,a.truename from famous_article f join act_member a on f.uid=a.id order by f.timestamp desc limit 9");
    break;
  case "class":        
    $T->SetBlock("lista","select id,name from `class` where state=2 order by id desc limit 8");
    $T->SetBlock("listb","select id,name from `class` where state=2 order by id desc limit 8,8");
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
  	$wh="";
  	if(!empty($sc)){
      $wh.=" and school=".$sc;
    }else if(!empty($a)){
      $school = $T->db->query("select GROUP_CONCAT(id) from school where state = 2 and addr=".$a)->fetchColumn(0);
      $wh.=" and school in (".$school.")";
    }
    if(!empty($so))$wh.=" and name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `class` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=class&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `class`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
  case "group":
      $T->SetTpl('js','js.inc');
      $T->SetBlock("hot_forum","select f.*,a.truename from grp_forum f join act_member a on a.id=f.uid order by f.see desc limit 3");
      $T->SetBlock("new_forum","select f.id,f.gid,f.title,f.uid,a.truename,f.timestamp from grp_forum f join act_member a on a.id=f.uid order by f.timestamp desc limit 4");
      $T->SetBlock("rcmd_group","select a.id,a.name,a.slogan,IFNULL(mannums,0) as mnums,b.name sname from `group` a join school b on a.school=b.id where a.state=2 and a.rcmd=1 order by a.timestamp desc limit 6");
      $T->SetBlock("group_album","select * from grp_album ORDER BY `timestamp` desc limit 4");
      $T->SetBlock("hot_group","select a.id,a.name,a.slogan,a.mannums,b.name sname,@curRow := @curRow + 1 AS row_number from `group` a join school b on a.school=b.id JOIN (SELECT @curRow := 0) r where a.state=2 order by a.visit desc limit 10");
      $T->SetBlock("active_users","select a.id,a.truename from act_member a join (select uid,count(uid) gnums from grp_forum group by uid order by gnums desc limit 8) g on g.uid=a.id");
      break;	
  case "group_search":  
    $T->SetBlock("lista","select id,name from `group` where state=2 order by id desc limit 8");
    $T->SetBlock("listb","select id,name from `group` where state=2 order by id desc limit 8,8");
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
	$wh="";
	if(!empty($sc)){
      $wh.=" and school=".$sc;
    }else if(!empty($a)){
      $school = $T->db->query("select GROUP_CONCAT(id) from school where state=2 and  addr=".$a)->fetchColumn(0);
      $wh.=" and school in (".$school.")";
    }
    if(!empty($so))$wh.=" and name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `group` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=group&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `group`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
  case "teacher":
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $per=isset($_GET["per"])?$_GET["per"]:0;
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
    $sub =isset($_GET["sub"])?$_GET["sub"]:0;
    $wh="";
    if(!empty($sc))$wh.=" and a.school=".$sc;
    if(!empty($per))$wh.=" and a.period=".$per;
    if(!empty($a))$wh.=" and a.addr=".$a;
    if(!empty($sub))$wh.=" and a.subject=".$sub;
    if(!empty($so))$wh.=$wh." and (username like '%".$so."%' or nick like '%".$so."%' or truename like '%".$so."%')";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `act_member` a left join sys_period p on a.period=p.id left join sys_subject s on a.subject=s.id left join school sc on a.school=sc.id where a.idtype=2 and a.state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=teacher&sc=".$sc."&a=".$a."&per=".$per."&sub=".$sub."&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select a.*,p.name pname,s.name sname,sc.name scname from `act_member` a left join sys_period p on a.period=p.id left join sys_subject s on a.subject=s.id left join school sc on a.school=sc.id where a.idtype=2 and a.state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9");
    $T->Set("so",$so);
    break;
  case "student":
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $per=isset($_GET["per"])?$_GET["per"]:0;
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
    $wh="";
    if(!empty($sc))$wh.=" and a.school=".$sc;
    if(!empty($per))$wh.=" and a.period=".$per;
    if(!empty($a))$wh.=" and a.addr=".$a;
    if(!empty($so))$wh.=$wh." and (username like '%".$so."%' or nick like '%".$so."%' or truename like '%".$so."%')";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `act_member` a left join sys_period p on a.period=p.id left join school sc on a.school=sc.id where a.idtype=1 and a.state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=student&sc=".$sc."&a=".$a."&per=".$per."&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select a.*,p.name pname,sc.name scname from `act_member` a left join sys_period p on a.period=p.id left join school sc on a.school=sc.id where a.idtype=1 and a.state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9");
    $T->Set("so",$so);
    break;
  case "parent":
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $per=isset($_GET["per"])?$_GET["per"]:0;
    $sc = isset($_GET["sc"])?$_GET["sc"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
    $wh="";
    if(!empty($sc))$wh.=" and a.school=".$sc;
    if(!empty($per))$wh.=" and a.period=".$per;
    if(!empty($a))$wh.=" and a.addr=".$a;
    if(!empty($so))$wh.=$wh." and (username like '%".$so."%' or nick like '%".$so."%' or truename like '%".$so."%')";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `act_member` a left join sys_period p on a.period=p.id left join school sc on a.school=sc.id where a.idtype=3 and a.state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=parent&sc=".$sc."&a=".$a."&per=".$per."&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select a.*,p.name pname,sc.name scname from `act_member` a left join sys_period p on a.period=p.id left join school sc on a.school=sc.id where a.idtype=3 and a.state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9");
    $T->Set("so",$so);
    break;
  case "school_waiting":  
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from school where state<2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,15,$p,"&t=school_waiting");
    $T->Set("page",$page); 
    $T->SetBlock("list","select T.*,A.names from school T left join sys_addr A on A.id=T.addr where T.state<2 order by T.timestamp desc limit ".(($p-1)*15).",15"); #,array('timestamp'),array('%Y-%m-%d %H:%M')
    break;   
  case "school":       
    $so=isset($_GET["so"])?$_GET["so"]:"";
    $per=isset($_GET["per"])?$_GET["per"]:0;
    $ot = isset($_GET["ot"])?$_GET["ot"]:0;
    $a = isset($_GET["a"])?$_GET["a"]:0;
    $wh=""; 
    if(!empty($ot))$wh.=" and typeid=".$ot;
    if(!empty($per))$wh.=" and period=".$per;
    if(!empty($a))$wh.=" and addr=".$a;
    else{
       if(!empty($a))$wh.=" and addrs like '0,".$a.",%'";  
    }
    if(!empty($so))$wh.=" and name like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `school` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,14,$p,"&t=school&a=".$a."&per=".$per."&ot=".$ot."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `school`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*14).",14",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
  case "index":
    if(isset($_SESSION["uid"])){
      $T->Set("Login","block"); 
      $T->Set("noLogin","none"); 
    }else{
      $T->Set("Login","none"); 
      $T->Set("noLogin","block"); 
    }
    
	#图片轮播
    $T->SetBlock("pic_arts","select * from push_list where push_type=1 and state=2 and pic is not NULL order by timestamp desc limit 6");                                                                                                                                                         
	$T->SetBlock("pic_arts1","select * from push_list where push_type=1 and state=2 and pic is not NULL order by timestamp desc limit 6");                                                                                                                                                         
	$T->SetBlock("pic_arts2","select * from push_list where push_type=1 and state=2 and pic is not NULL order by timestamp desc limit 6");                                                                                                                                                         
	
	#新闻公告
    $T->SetBlock("new_notice","SELECT @row := @row +1 AS row ,t.* FROM push_list t, (SELECT @row :=0)r where t.cid=16 and t.state=2 order by t.timestamp desc limit 11");                                                                                                                                                         
		#图书推荐
    $T->SetBlock("recommend_cate","SELECT id,name FROM recommend_art_category where pid=26 order by odx asc");                                                                                                                                                         
		$T->SetBlock2("recommend_list","SELECT id,name FROM recommend_art_category where pid=26 order by odx asc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,title,cid,author,pic from recommend_list where cid=? and state=2 limit 6"))
            );
    #读后感
    $T->SetBlock("breview_cate","SELECT id,name,pid FROM push_category where pid=18 order by odx desc");                                                                                                                                                         
		$T->SetBlock2("breview_list","SELECT id,name FROM push_category where pid=18 order by odx desc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,title,cid from push_list where cid=? and state=2 order by pic desc limit 8"))
            );
    #读书达人
    $T->SetBlock2("book_user","select t.id,t.uid,t.title,a.truename from recommend_list as t left join act_member a on a.id=t.uid where a.id<>'' group by t.timestamp having timestamp=(select max(timestamp) from recommend_list where uid=t.uid)  limit 4"
            ,array(array("block"=>"rp","pid"=>"uid","sql"=>"select count(*) as num from recommend_list where uid=?"))
            );
    #读书活动
    $T->SetBlock("active_cate","SELECT id,name FROM active_category order by odx desc");
    $T->SetBlock2("active_list","SELECT id,name FROM active_category order by odx desc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,name,cid,pre,rstime,retime from activity where cid=? limit 5"))
            ); 
    #专题推荐
    $T->SetBlock("recspecial_cate","SELECT id,name FROM recommend_art_category where pid=32 order by odx asc");                                                                                                                                                         
	$T->SetBlock2("recspecial_list","SELECT id,name FROM recommend_art_category where pid=32 order by odx asc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,title,cid,pic,des,see from recommend_list where cid=? and state=2 order by timestamp desc limit 3"))
            ); 
    #排行榜
	$T->SetBlock("ranking_list","select @row := @row +1 AS row,t.id,t.title,t.author,t.see,a.truename,t.pic from recommend_list t join act_member a on t.uid=a.id , (SELECT @row :=0)r  order by t.see desc,t.timestamp desc limit 10");   

	#书香学校
    $T->SetBlock("school_cate","select id,name from sys_period order by id asc");
    $T->SetBlock2("school_list","select id,name from sys_period order by id asc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,name from school where period=? limit 8"))
            );
    #书香视听
    $T->SetBlock("note_first","SELECT t.id,t.uid,t.cid,t.title,t.pre,t.see,a.truename,t.pic,t.created FROM push_list t join act_member a on a.id=t.uid where cid=38 and t.state=2 order by t.timestamp desc limit 1");                                                                                                                                                         
		$T->SetBlock("note_list","SELECT t.id,t.uid,t.cid,t.title,t.pre,t.see,a.truename,t.pic,t.created FROM push_list t join act_member a on a.id=t.uid where cid=38 and t.state=2 order by t.timestamp desc limit 1,3");                                                                                                                                                         
		$T->SetBlock("research_first","SELECT t.id,t.uid,t.cid,t.title,t.pre,t.see,a.truename,t.pic,t.created FROM push_list t join act_member a on a.id=t.uid where cid=39 and t.state=2 order by t.timestamp desc limit 1");                                                                                                                                                         
		$T->SetBlock("research_list","SELECT t.id,t.uid,t.cid,t.title,t.pre,t.see,a.truename,t.pic,t.created FROM push_list t join act_member a on a.id=t.uid where cid=39 and t.state=2 order by t.timestamp desc limit 1,3");                                                                                                                                                         
		$T->SetBlock("comment_first","SELECT t.id,t.uid,t.cid,t.title,t.pre,t.see,a.truename,t.pic,t.created FROM push_list t join act_member a on a.id=t.uid where cid=40 and t.state=2 order by t.timestamp desc limit 1");                                                                                                                                                         
		$T->SetBlock("comment_list","SELECT t.id,t.uid,t.cid,t.title,t.pre,t.see,a.truename,t.pic,t.created FROM push_list t join act_member a on a.id=t.uid where cid=40 and t.state=2 order by t.timestamp desc limit 1,3");                                                                                                                                                         
		
    #热门学校
    $T->SetBlock("hot_school","select s.id,s.name,a.school,a.id uid,sum(r.num) nums from act_member a join (select uid,count(*) num from recommend_list group by uid) r on r.uid=a.id left join school s on s.id=a.school group by id order by r.num desc limit 4");
    #活动剪影
    $T->SetBlock("active_pic","SELECT id,pic FROM push_list where cid=41 and state=2 order by timestamp desc limit 6");                                                                                                                                                         
		
		break;
  case "works":
    #作品推荐
    $T->SetBlock("rec_works","select id,title from push_list where cid=42 and state=2 order by timestamp desc limit 7");
    $T->SetBlock("rec_rand","SELECT t.id,t.title,t.uid,t.pic,a.truename,t.see FROM push_list t join act_member a on a.id=t.uid where t.cid=42 and t.state=2 and t.pic is not null ORDER BY rand() LIMIT 3");
    
    #读后感
    $read_cid= $T->db->query("select GROUP_CONCAT(id) from push_category where pid=18")->fetchColumn(0);
    if(!empty($read_cid)){
      $read_cid=",".$read_cid;
    }
    $T->SetBlock("breview_list","select id,title from push_list where cid in (18".$read_cid.") order by timestamp desc limit 7");
    
    #摄影作品
    $T->SetBlock("photo_list","SELECT t.id,t.title,t.uid,t.pic,a.truename,t.see FROM push_list t join act_member a on a.id=t.uid where t.cid=43 and t.state=2 and t.pic is not null ORDER BY t.timestamp LIMIT 3");
    $T->SetBlock("picture_list","SELECT t.id,t.title,t.uid,t.pic,a.truename,t.see FROM push_list t join act_member a on a.id=t.uid where t.cid=44 and t.state=2 and t.pic is not null ORDER BY t.timestamp LIMIT 3");
    
    $work_cid= $T->db->query("select GROUP_CONCAT(id) from push_category where pid=22")->fetchColumn(0);
    if(!empty($work_cid)){
      $work_cid=",".$work_cid;
    }
    #最新作品
    $T->SetBlock("new_work","select t.id,t.uid,t.title,a.truename,t.see from push_list t join act_member a on a.id=t.uid where cid in (22".$work_cid.") and t.state=2 order by t.timestamp desc limit 6");
    
    #作品排行
    $T->SetBlock("hot_work","select @row := @row +1 AS row,t.id,t.uid,t.title,a.truename,t.see from push_list t join act_member a on a.id=t.uid, (SELECT @row :=0)r where cid in (22".$work_cid.") and t.state=2 order by t.see desc limit 11");
    
    break;
  case "recommend":
	    /*图书推荐-文章列表*/
		$T->SetBlock("list_tj","select *,pl.id as id from `push_list` as pl left join push_category as pc on pc.id=pl.cid where pl.state=2 and pl.push_type=1 and pc.name='图书推荐' and pl.pic<>'' order by pl.timestamp desc limit 5");
		/*图书推荐-点击率最高的*/
		$T->SetBlock("list_hottj","select id,`title`,cid,author,publicer,readafter,pic,see from recommend_list where  pic<>'' and state=2 order by see desc,timestamp desc limit 1");
		/*图书推荐-学生爱看*/
		$T->SetBlock("list_xsak_type","SELECT id,`name` FROM recommend_art_category where pid=36 order by odx asc");
		$T->SetBlock2("list_xsak","SELECT id,`name` FROM recommend_art_category where pid=36 order by odx asc",array(array("block"=>"rp","pid"=>"id","sql"=>"select id,`title`,cid,author,pic from recommend_list where cid=? and pic<>'' and state=2 order by timestamp desc limit 5 ")));
		/*图书推荐-老师爱看*/
		$T->SetBlock("list_lsak_type","SELECT id,`name` FROM recommend_art_category where pid=43 order by odx asc");
		$T->SetBlock2("list_lsak","SELECT id,`name` FROM recommend_art_category where pid=43 order by odx asc",array(array("block"=>"rp","pid"=>"id","sql"=>"select id,`title`,cid,author,pic from recommend_list where cid=? and pic<>'' and state=2 order by timestamp desc limit 5 ")));
		/*图书推荐-家长爱看*/
		$T->SetBlock("list_jzak_type","SELECT id,`name` FROM recommend_art_category where pid=50 order by odx asc");
		$T->SetBlock2("list_jzak","SELECT id,`name` FROM recommend_art_category where pid=50 order by odx asc",array(array("block"=>"rp","pid"=>"id","sql"=>"select id,`title`,cid,author,pic from recommend_list where cid=? and pic<>'' and state=2 order by timestamp desc limit 5 ")));
		/*图书推荐-教育局推荐*/
		$T->SetBlock("list_jyjak","select id,`title`,cid,author,pic from recommend_list where cid=57 and pic<>'' and state=2 order by timestamp desc limit 5");
		/*图书推荐-学校推荐*/
		$T->SetBlock("list_xxak","select id,`title`,cid,author,pic from recommend_list where cid=58 and pic<>'' and state=2 order by timestamp desc limit 5");
		/*读书达人*/
		$T->SetBlock2("book_user","select t.id,t.uid,t.title,a.truename from recommend_list as t left join act_member a on a.id=t.uid where a.id<>'' group by t.timestamp having timestamp=(select max(timestamp) from recommend_list where uid=t.uid)  limit 4",array(array("block"=>"rp","pid"=>"uid","sql"=>"select count(*) as num from recommend_list where uid=?")));
	    /*排行榜*/
		$T->SetBlock("ranking_list","select @row := @row +1 AS row,t.id,t.title,t.author,t.see,a.truename,t.pic from recommend_list t join act_member a on t.uid=a.id , (SELECT @row :=0)r  order by t.see desc,t.timestamp desc limit 10");   
	  break;
  case "recommend_info":
	$id=isset($_GET["id"])?$_GET["id"]:"";
	if($id==""){
		header("location: /?t=error&no=104&name=url传参错误&msg=有未传参数！");
	}else{
		$T->Set("rid",$id);
	}
	if(isset($_SESSION["uid"])){
		$T->Set("uid",$_SESSION["uid"]);
	};
	$T->SetBlock("list","select rl.id,rl.`title`,rl.cid,rl.uid,rl.author,rl.publicer,rl.readafter,rl.pic,rl.see,rl.des,am.truename from recommend_list as rl left join act_member as am on am.id=rl.uid where rl.id=".$id." limit 5");
	#点评
	$T->SetBlock("rea_reply","select am.truename as name,rr.des,rr.uid,rr.timestamp as timestamp,rr.id from recommend_reply as rr left join act_member as am on am.id=rr.uid where rr.rid=".$id." and am.id<>'' order by rr.id desc",array('timestamp'),array('%Y-%m-%d %H:%M'));
	
	  break;
  case "recommend_lists":
	$pid=isset($_GET["pid"])?$_GET["pid"]:"";
	$T->SetBlock2("rea_lists_menu","SELECT id,`name` FROM recommend_art_category where id=".$pid,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,name from recommend_art_category where pid=? order by odx asc")));
    $_pid=$T->db->query("SELECT count(*) FROM recommend_art_category where pid=".$pid)->fetchColumn(0);
	if($_pid==0){
		$T->SetBlock2("rea_list","SELECT id,`name` FROM recommend_art_category where id=".$pid." order by odx asc",array(array("block"=>"rp","pid"=>"id","sql"=>"select id,`title`,cid,author,pic from recommend_list where cid=? and pic<>'' and state=2 order by timestamp desc limit 5 ")));
	}else{
		$T->SetBlock2("rea_list","SELECT id,`name` FROM recommend_art_category where pid=".$pid." order by odx asc",array(array("block"=>"rp","pid"=>"id","sql"=>"select id,`title`,cid,author,pic from recommend_list where cid=? and pic<>'' and state=2 order by timestamp desc limit 5 ")));
	}
	$T->Set("pid",$pid);
	  break;
  case "recommend_list":
	$cid=isset($_GET["cid"])?$_GET["cid"]:"";
    $pid=isset($_GET["pid"])?$_GET["pid"]:"";
	$T->SetBlock2("rea_lists_menu","SELECT id,`name` FROM recommend_art_category where id=".$pid,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,name from recommend_art_category where pid=? order by odx asc")));
	$list_name= $T->db->query("select `name` from recommend_art_category where id=".$cid."")->fetchColumn(0);

	#分页
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `recommend_list` where cid=".$cid)->fetchColumn(0);
    $page=getPageHtml_bt($rc,20,$p,"&t=recommend_list&pid=".$pid."&cid=".$cid);
    $T->Set("page",$page); 
	$T->SetBlock("rea_list","select rl.id,rl.`title`,rl.cid,rl.author,rl.pic,rac.name from recommend_list as rl left join recommend_art_category as rac on rac.id=rl.cid where rl.cid=".$cid." and rl.pic<>'' and rl.state=2 order by rl.timestamp desc limit ".(($p-1)*20).",20");
	#记录重要值
	$T->Set("list_name",$list_name);
	$T->Set("pid",$pid);
	  break;
  case "error":        
      $T->Set("no",$_GET["no"]);
      $T->Set("name",$_GET["name"]);
      $T->Set("des",$_GET["msg"]); 
    break;  			
	default:        					
		break;   
}
$T->Set("gtitle",LR_NAME); 
$T->Set_Assign("ver",LR_VER);
$T->Set_Assign("mail",LR_MAIL);
$T->Set_Assign("tel",LR_TEL); 
$T->clearNoN();
$T->display(); 