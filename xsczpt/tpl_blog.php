<?php
header("Content-type: text/html; charset=utf-8;");
$T->loadTpl("./html/".$template."/".$qname.".htm");
$T->SetTpl('top','./inc/top.htm');      
$T->SetTpl('foot','./inc/foot.htm');   
$T->SetTpl('meta','cssjs.inc'); 
$T->SetTpl('head','head.inc');      
$T->SetTpl('menu','menu.inc');   
$T->SetTpl('icon_list','icon_list.inc'); 
$T->SetTpl('sch_logo','sch_logo.inc'); 
#默认
$T->SetTpl('def_menu','def_menu.inc');
$T->SetTpl('def_logo','def_logo.inc');
#blog博客-空间
$T->SetTpl('blog_menu','blog_menu.inc');
$T->SetTpl('blog_logo','blog_logo.inc');
#school机构
$T->SetTpl('school_menu','school_menu.inc');
$T->SetTpl('school_logo','school_logo.inc');
#class班级
$T->SetTpl('class_menu','class_menu.inc');
$T->SetTpl('class_logo','class_logo.inc');
#group群组
$T->SetTpl('group_menu','group_menu.inc');
$T->SetTpl('group_logo','group_logo.inc');
#famous工作室
$T->SetTpl('famous_menu','famous_menu.inc');
$T->SetTpl('famous_logo','famous_logo.inc');

if (!session_id()) session_start(); 
$uid=isset($_SESSION['uid'])?$_SESSION['uid']:0;
if(empty($_SESSION["uid"])){    
  $T->Set("inc_man","inc_on");
} 
switch($qname){
  case "klb_index":        
    break;
	
  case "exit":
      if(isset($_SESSION["uid"])){
    		unset($_SESSION["uid"]);
			$T->Set("inc_man","inc_on");
    		session_destroy();#session_unset(); 相同;
    	}	
    	setcookie('cu',null,time(),"/");      
        $T->Set("APP_API_URL",APP_API_URL);		
	break;
  case "reg":
    if(REG_OFF){
      header("location: /?t=error&no=200&name=注册已关闭&msg=系统注册功能已关闭！");
    }
    break;
  case "login":
    
        if(isset($_GET["url"])){
			$tourl=url_base64_decode($_GET["url"]);
			$logout_from_tl='';
			//判断是否来自通过统一登陆后的退出
			if($tourl=='/oa/?logout_from_tl=1'){
				$logout_from_tl='1';
			}
			$T->Set("tourl","/oa/");
			$T->Set("logout_from_tl",$logout_from_tl);
		}       
        $request_uri_arr=explode("?",$_SERVER["REQUEST_URI"]);
		$request_uri=$request_uri_arr[1];
		$request_uri="?".$request_uri;
		//$request_uri="?".explode("?",$_SERVER["REQUEST_URI"])[1];//低版本报错，太长
		$tl_url=APP_URL."/?t=login&url=".url_base64_encode($request_uri);
		$T->Set("tl_url",$tl_url);
		$T->Set("APPID",APPID);
		$T->Set("APPKEY",APPKEY);
		$T->Set("APP_API_URL",APP_API_URL);
		$isbid='';
		//判断是否已经统一登陆过
		if(isset($_SESSION['bid'])){
			$isbid='1';
		}
		$T->Set("isbid",$isbid);
    break;
  case "forget":
  
    break;
  case "actived":
    $T->SetTpl('menu','activity/html/def/menu.inc');
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
    $T->SetTpl('menu','activity/html/def/menu.inc');
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
  case "part_info":
    $T->SetBlock("part_cate","select id,name,pid from main_art_category where pid=0 order by odx desc");
    $id = isset($_GET["id"])?$_GET["id"]:0;
    $cid= $T->db->query("select cid from main_article where id=".$id)->fetchColumn(0);
    $url="/?t=part_list&pc=".$cid;
    $T->SetBlock("art","select id,name,des,see,timestamp from `main_article` where id=".$id);
    $T->SetBlock("art_list","select id,name from main_article where cid=".$cid." and id!=".$id." and state=2 order by timestamp desc limit 5");
    $T->Set("url",$url);
    break; 
  case "part_list":
    $T->SetBlock("part_cate","select id,name,pid from main_art_category where pid=0 order by odx desc");
    $pid1=$pid=(!isset($_GET["pc"]) || $_GET["pc"]==0)?0:$_GET["pc"];
    $display1=$display2="none";
    $cnums= $T->db->query("SELECT count(*) FROM main_art_category where pid=".$pid)->fetchColumn(0);
    if(empty($cnums)){
      $pid= $T->db->query("SELECT pid FROM main_art_category where id=".$pid)->fetchColumn(0);
    }
    
    $pname= $T->db->query("select name from main_art_category where id=".$pid)->fetchColumn(0);
    $T->SetBlock("art_cate","select id,name,pid from main_art_category where pid=".$pid." order by odx desc");
      
    if(!empty($cnums)){
        $display1="block";
        $T->SetBlock2("art_lists","SELECT id,name FROM main_art_category where pid=".$pid." order by odx desc"
            ,array(array("block"=>"rp","pid"=>"id","sql"=>"select id,name,timestamp from main_article where cid=? and state=2 order by timestamp desc limit 8"))
            );
      }else{    
          $display2="block";
          $cname= $T->db->query("select name from main_art_category where id=".$pid1)->fetchColumn(0);
          $p=(!isset($_GET["p"]) || $_GET["p"]==0)?"1":$_GET["p"]; 
          $rc= $T->db->query("select count(1) from `main_article` where state=2 and cid=".$pid1)->fetchColumn(0);
          $page=getPageHtml_bt($rc,6,$p,"&t=part_list&pc=".$pid);
          $T->Set("page",$page);
          $T->SetBlock("art_list","select id,name,timestamp from `main_article` where state=2 and cid=".$pid1." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
          $T->Set("cname",$cname);
      }
    
    $T->Set("pname",$pname);
    $T->Set("pid",$pid);
    $T->Set("display1",$display1);
    $T->Set("display2",$display2);
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
    $page=getPageHtml_bt($rc,9,$p,"&t=famous_search&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select * from `famous`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;  
case "famous":
    $ids = $T->db->query("select GROUP_CONCAT(id) ids from push_category where push_type=5")->fetchColumn(0);
    $T->SetBlock("new_num","select (@rowNO := @rowNo+1) AS num from push_list,(select @rowNO :=0) b where cid in (".$ids.") and state=2 and pic is not null order by timestamp desc limit 4");
    $T->SetBlock("new_pic","select * from push_list where cid in (".$ids.") and state=2 and pic is not null order by timestamp desc limit 4");
    $T->SetBlock("new_art","select * from push_list where cid in (".$ids.") and state=2 order by timestamp desc limit 2");
    
    $T->SetBlock2A("char_famous","select * from famous f join (select fid,count(*) nums from famous_article group by fid limit 4) n on f.id=n.fid where state=2 order by n.nums desc" 
           ,array("block"=>"rp","sql"=>"select s.name,(select count(*) from famous_member where fid=?)fman from sys_subject s join act_member a on s.id=a.`subject` where a.id=?", 
           "param"=>array("id","uid")) 
           );
    $T->SetBlock("new_active","select id,name,timestamp from activity where state=2 order by timestamp desc limit 6");
    $T->SetBlock("fam_notice","select * from push_list where cid=45 order by timestamp desc limit 6");
    $T->SetBlock("fam_dynamics","select f.id,f.uid,f.fid,f.title,f.timestamp,a.truename from famous_article f join act_member a on f.uid=a.id order by f.timestamp desc limit 9");
    break;
  case "famous_list":
    $wh="";
    $so = isset($_GET["so"])?$_GET["so"]:"";
    if(!empty($so))$wh.=" and title like '%".$so."%'";
    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `famous_article` where state=2".$wh)->fetchColumn(0);
    $page=getPageHtml_bt($rc,9,$p,"&t=famous_list&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("famous_list","select * from famous_article where state=2".$wh." order by timestamp desc limit ".(($p-1)*15).",15");
    $T->Set("so",$so);
    break;
  case "art_info":
    $id = isset($_GET["id"])?$_GET["id"]:0;
    $cid= $T->db->query("select cid from push_list where id=".$id)->fetchColumn(0);
    $pid= $T->db->query("select pid from push_category where id=".$cid)->fetchColumn(0);
	if($pid==0){
      $url="/?t=art_list&pc=".$cid;
    }else{
      $url="/?t=art_list&pc=".$pid."&c=".$cid;
    }
    $T->SetBlock("art","select id,title,des,see,created from `push_list` where id=".$id);
    $T->SetBlock("art_list","select id,title from push_list where cid=".$cid." and id!=".$id." and state=2 order by timestamp desc limit 8");
    $T->Set("url",$url);
    break; 
  case "art_list":
    $pid=(!isset($_GET["pc"]) || $_GET["pc"]==0)?0:$_GET["pc"];
    $c=isset($_GET["c"])?$_GET["c"]:0;
    $display1=$display2="none";
    $pname= $T->db->query("select name from push_category where id=".$pid)->fetchColumn(0);
    $T->SetBlock("art_cate","select id,name,pid from push_category where pid=".$pid." and push_type=5 order by odx desc");
    
    $cnums= $T->db->query("SELECT count(*) FROM push_category where pid=".$pid." and push_type=5")->fetchColumn(0);
    if($cnums==0){
      $c = $pid;
    }
    if(empty($c)){
        $display1="block";
        $T->SetBlock2("art_lists","SELECT id,name FROM push_category where pid=".$pid." and push_type=5 order by odx desc"
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
  case "class":
	$T->Set("cla_url","/html/blog/");
    $T->SetBlock("lista","select name,timestamp from `class_article` where state=2 order by id desc limit 8",array('timestamp'),array('%Y-%m-%d'));
	$T->SetBlock("listb","select name,timestamp from `class_article` where state=2 order by id desc limit 8,8",array('timestamp'),array('%Y-%m-%d'));
	$T->SetBlock("list_newclass","select id,name,slogan,nums from `class` where state=2 order by id desc limit 4",array('timestamp'),array('%Y-%m-%d'));
	$T->SetBlock("list_topclass","select id,name,slogan,nums from `class` where state=2 order by nums desc limit 7",array('timestamp'),array('%Y-%m-%d'));
    $T->SetBlock("list_teacher","select a.*,p.name pname,s.name sname,sc.name scname from `act_member` a left join sys_period p on a.period=p.id left join sys_subject s on a.subject=s.id left join school sc on a.school=sc.id where a.idtype=2 and a.state=2 order by timestamp desc limit 3");
    $T->SetBlock("list_student","select a.*,p.name pname,s.name sname,sc.name scname from `act_member` a left join sys_period p on a.period=p.id left join sys_subject s on a.subject=s.id left join school sc on a.school=sc.id where a.idtype=1 and a.state=2 order by timestamp desc limit 3");
	$T->SetBlock("list_photo","select S.*,A.truename from cls_album S left join act_member A on A.id=S.uid  order by timestamp desc limit 6",array('timestamp'),array('%Y-%m-%d') );
	break;
  case "class_search":        
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
    $page=getPageHtml_bt($rc,9,$p,"&t=class_search&so=".$so);
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
    $page=getPageHtml_bt($rc,9,$p,"&t=group_search&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `group`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
  case "teacher_search":
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
    $page=getPageHtml_bt($rc,9,$p,"&t=teacher_search&sc=".$sc."&a=".$a."&per=".$per."&sub=".$sub."&so=".$so);
    $T->Set("page",$page);
    $T->SetBlock("list","select a.*,p.name pname,s.name sname,sc.name scname from `act_member` a left join sys_period p on a.period=p.id left join sys_subject s on a.subject=s.id left join school sc on a.school=sc.id where a.idtype=2 and a.state=2".$wh." order by timestamp desc limit ".(($p-1)*9).",9");
    $T->Set("so",$so);
    break;
  case "student_search":
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
    $page=getPageHtml_bt($rc,9,$p,"&t=student_search&sc=".$sc."&a=".$a."&per=".$per."&so=".$so);
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
	$T->SetBlock("list_news","select * from `push_list`  where id>0 and push_type=3 and state=2 order by timestamp desc limit 8");
	$T->SetBlock("list_schs","select * from `school`  where state=2 order by timestamp desc limit 5");
	$T->SetBlock("list_schs2","select * from `school`  where state=2 order by timestamp desc limit 5,9");
	$T->SetBlock("list_class","select *,(select count(*) from cls_member where cid=class.id and state=2) as count from class where state=2 order by id limit 4");
	$T->SetBlock("list_famous","select * from `famous`  where state=2 order by timestamp desc limit 4");
	$T->SetBlock("list_group","select * from `group`  where state=2 order by timestamp desc limit 4");
	$T->SetBlock("list_actives","select * from `activity`  where state=2 order by timestamp desc limit 10");
	$T->SetBlock("list_hot_schs","select *,(select count(*) from act_school where sid=school.id) as count from school where state=2 order by count desc limit 10");
	$T->SetBlock("list_new_blog","select * from `blog_list`  where id>0  order by timestamp desc limit 20");
    break;
  case "school_news":
	$p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `push_list` where id>0 and push_type=3 and state=2")->fetchColumn(0);
    $page=getPageHtml_bt($rc,20,$p,"&t=school_news");
	$T->Set("page",$page);
	$T->SetBlock("list","select * from `push_list`  where id>0 and push_type=3 and state=2 order by timestamp desc limit ".(($p-1)*20).",20",array('timestamp'),array('%Y-%m-%d %H:%M'));
	break;
  case "school_info":
	$T->SetBlock("list","select * from `push_list`  where id=".$_GET["id"],array('timestamp'),array('%Y-%m-%d %H:%M'));
    $timestamp= $T->db->query("select timestamp from `push_list` where state=2 and id=".$_GET["id"]." ")->fetchColumn(0);
    $T->SetBlock("listpre","select id,title from `push_list`  where  id>0 and push_type=3 and state=2 and timestamp<".$timestamp."  limit 1");
    $T->SetBlock("listnext","select id,title from `push_list`  where  id>0 and push_type=3 and state=2 and timestamp>".$timestamp." limit 1");
	$T->SetBlock("list_about","select id,title from `push_list`  where  id>0 and push_type=3 and state=2 limit 5");
	break;
  case "school_blog":
	$p=isset($_GET["p"])?$_GET["p"]:"1"; 
    $rc= $T->db->query("select count(1) from `blog_list` where id>0 ")->fetchColumn(0);
    $page=getPageHtml_bt($rc,20,$p,"&t=school_blog");
	$T->Set("page",$page);
	$T->SetBlock("list","select * from `blog_list`  where id>0 order by timestamp desc limit ".(($p-1)*20).",20",array('timestamp'),array('%Y-%m-%d %H:%M'));
    break;
  case "school_search":       
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
    $page=getPageHtml_bt($rc,14,$p,"&t=school_search&a=".$a."&per=".$per."&ot=".$ot."&so=".$so);
    $T->Set("page",$page); 
    $T->SetBlock("list","select * from `school`  where state=2".$wh." order by timestamp desc limit ".(($p-1)*14).",14",array('timestamp'),array('%Y-%m-%d %H:%M'));
    $T->Set("so",$so);
    break;
  case "blog":
      if(isset($_SESSION["uid"])){
    		  $T->Set("Login","block"); 
    		  $T->Set("noLogin","none"); 
    	  }else{
    		  $T->Set("Login","none"); 
    		  $T->Set("noLogin","block"); 
    	  }                                                                                                                                                         
		$T->SetBlock("blog_active","select * from `activity`  order by timestamp desc limit 9");           
		$T->SetBlock("blog_recommend","select b.id,b.title,a.truename,b.uid from `blog_list` b left join act_member a on b.uid=a.id order by b.see desc limit 6");           
		$T->SetBlock("blog_rank","select  (@rowNO := @rowNo+1) AS num,b.id,b.title,a.truename,b.uid from `blog_list` b left join act_member a on b.uid=a.id ,(select @rowNO :=0) b order by b.created desc limit 9");           
		$T->SetBlock("blog_ding","select  (@rowNO := @rowNo+1) AS num,b.id,b.title,a.truename,b.uid from `blog_list` b left join act_member a on b.uid=a.id ,(select @rowNO :=0) b order by b.up desc limit 9");
		$T->SetBlock("blo_bloglist","select B.id,title,B.see,B.timestamp,A.truename,A.id uid,S.`name` subname,D.name addr,CASE A.idtype WHEN 1 THEN '学生'  WHEN 2 THEN '老师' ELSE '家长' END as identity from `blog_list` as B LEFT JOIN `act_member` As A On A.id=B.uid left join sys_subject as S on S.id=A.`subject` left join sys_addr D on D.id=A.addr where B.title<>'' and A.idtype=2 order by B.see desc limit 5");
		
		$T->SetBlock2("zblog","select * FROM `famous_member` b left join `zone` a on a.id=b.uid left join `act_member` c on b.uid=c.id GROUP BY b.uid order by a.see desc,b.uid desc limit 2",array(array("block"=>"rp","pid"=>"uid","sql"=>"select * from `blog_list` where uid=? order by timestamp desc limit 6")));			
                                                                                                     
		$T->SetBlock("blog_star","select z.id,z.name,z.`explain` from zone z join (select count(uid) bnums,uid from blog_list GROUP BY uid order by bnums desc) b on z.id=b.uid limit 3");                                                                        
		$T->SetBlock("blog_master","select id,name,see from zone order by see desc limit 8"); 
		
		$T->SetBlock("teach_acti","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1420429875 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("teach_actilist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1420429875 order by b.timestamp desc limit 1,6");                                                                                                               
		$T->SetBlock("sub_study","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1444445298 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("sub_studylist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1444445298 order by b.timestamp desc limit 1,6");                                                                                                            
		$T->SetBlock("edu_tecn","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1420429875 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("edu_tecnlist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1420429875 order by b.timestamp desc limit 1,6");  
		                                                                                                              
		$T->SetBlock("moral_edu","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1444445298 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("moral_edulist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1444445298 order by b.timestamp desc limit 1,6");                                                                                                               
		$T->SetBlock("teach_mang","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1444445298 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("teach_manglist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1444445298 order by b.timestamp desc limit 1,6");                                                                                                            
		$T->SetBlock("teach_reflect","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1420429875 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("teach_reflectlist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1420429875 order by b.timestamp desc limit 1,6");  
		                                                                                                              
		$T->SetBlock("life_t","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1420429875 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("life_tlist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1420429875 order by b.timestamp desc limit 1,6");                                                                                                               
		$T->SetBlock("diary","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1444445298 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("diarylist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1444445298 order by b.timestamp desc limit 1,6");                                                                                                            
		$T->SetBlock("read_note","select b.id,b.title,b.uid,b.created from `blog_list` b where b.cid=1420429875 order by b.timestamp desc limit 1");                                                                                                                  
		$T->SetBlock("read_notelist","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id where b.cid=1420429875 order by b.timestamp desc limit 1,6");                                                     
		$T->SetBlock("blog_list","select b.id,b.title,b.uid,a.truename from `blog_list` b left join act_member a on b.uid=a.id order by b.timestamp desc limit 8");  
		
	
		$T->SetBlock("famous","select a.id,a.name,b.name sname from `famous` a join school b on a.school=b.id where a.state=2 order by a.timestamp desc limit 6");
		$T->SetBlock("group","select a.id,a.name,b.name sname from `group` a join school b on a.school=b.id where a.state=2 order by a.timestamp desc limit 6");
		$T->SetBlock("aclass","select a.id,a.name,b.name sname from `class` a join school b on a.school=b.id where a.state=2 order by a.id desc limit 6");
		$T->SetBlock("bclass","select a.id,a.name,b.name sname from `class` a join school b on a.school=b.id where a.state=2 order by a.id desc limit 6,6");
		$T->SetBlock("school","select id,name,motto from school where state=2 order by timestamp desc limit 9");           
		$T->SetBlock("arts","select * from `main_article`  where state=2 order by timestamp desc limit 5");
    
      break;
  case "index":
      if(isset($_GET['vid']) || isset($_GET['eid']) || isset($_GET['tid'])){ $T->Set("display","none"); }else{ $T->Set("display",""); }
      #新闻热点	                                                                                                                                                                 
      $T->SetBlock("circle_new","select * from `main_article`  where state=2 and cid=8 order by timestamp desc limit 1");
      $T->SetBlock("top_new","select * from `main_article`  where state=2 and cid=9 order by timestamp desc limit 1");
      $T->SetBlock("top_news","select * from `main_article`  where state=2 and cid=9 order by timestamp desc limit 1,5");
      $T->SetBlock("hot_new","select * from `main_article`  where state=2 and cid=10 order by timestamp desc limit 1");
      $T->SetBlock("hot_news","select * from `main_article`  where state=2 and cid=10 order by timestamp desc limit 1,4");
      $T->SetBlock("focus_news","select * from `main_article`  where state=2 and cid=11 order by timestamp desc limit 5");
      
      #精品视频
      $vid = empty($_GET['vid'])?1:$_GET['vid'];
      $T->SetBlock("video_cate","select * from `main_video_category`  order by id asc");
      $T->SetBlock("video_top","select * from `main_video`  where state=2 and cid=".$vid." order by timestamp desc limit 1");
      $T->SetBlock("video_top1","select * from `main_video`  where state=2 and cid=".$vid." order by timestamp desc limit 1,1");
      $T->SetBlock("video_tops","select * from `main_video`  where state=2 and cid=".$vid." order by timestamp desc limit 2,12");
      $T->SetBlock("video_new","select * from `main_video`  where state=2 and cid=".$vid." and label=0 order by timestamp desc limit 1");
      $T->SetBlock("video_hot","select * from `main_video`  where state=2 and cid=".$vid." and label=1 order by timestamp desc limit 1");
      $T->SetBlock("video_recommend","select * from `main_video`  where state=2 and cid=".$vid." and label=2 order by timestamp desc limit 3");
      $T->SetBlock("video_list_top","select * from `main_video`  where state=2 order by timestamp asc limit 1");
      $T->SetBlock("video_list1","select * from `main_video`  where state=2 order by timestamp asc limit 1,4");
      $T->SetBlock("video_list2","select * from `main_video`  where state=2 order by timestamp asc limit 4,4");
      $T->SetBlock("video_recom_list","select * from `main_video`  where state=2 and label=2 order by timestamp desc limit 5");
      
      #教育文摘
      $eid = empty($_GET['eid'])?13:$_GET['eid'];
  	  $T->SetBlock("eric_cate","select * from `main_art_category` where pid=13 order by id asc");
  	  $T->SetBlock("eric_new","select * from `main_article`  where state=2 and cid=".$eid." order by timestamp desc limit 1");
  	  $T->SetBlock("eric_new1","select * from `main_article`  where state=2 and cid=".$eid." order by timestamp desc limit 1,1");
  	  $T->SetBlock("eric_new_list","select * from `main_article`  where state=2 and cid=".$eid." order by id desc limit 1,2");
  	  $T->SetBlock("eric_new_hots","select * from `main_article`  where state=2 and cid=".$eid." order by see desc limit 2");
  	  $T->SetBlock("eric_new_hot","select * from `main_article`  where state=2 and cid=".$eid." order by see desc limit 1");
  	  $T->SetBlock("famous_mem","select m.fid,m.uid,m.intro,a.truename,a.pre from `famous_member` m join act_member a on m.uid=a.id group by m.uid order by m.timestamp desc limit 4");
  
  	  #育儿心得
      $tid = empty($_GET['tid'])?14:$_GET['tid'];
      $T->SetBlock("partips_cate","select * from `main_art_category` where pid=14 order by id asc");
  	  $T->SetBlock("partips_new","select * from `main_article`  where state=2 and cid=".$tid." order by timestamp desc limit 1");
  	  $T->SetBlock("partips_new_hots","select * from `main_article`  where state=2 and cid=".$tid." order by see desc limit 2");
  	  $T->SetBlock("partips_news","select * from `main_article`  where state=2 and cid=".$tid." order by timestamp desc limit 4");
  	  $T->SetBlock("partips_new1","select * from `main_article`  where state=2 and cid=".$tid." order by timestamp desc limit 1,1");
  	  $T->SetBlock("partips_new_hot","select * from `main_article`  where state=2 and cid=".$tid." order by see desc limit 1");
  	  $T->SetBlock("partips_news_arr","select * from `main_article`  where state=2 and cid=".$tid." order by see desc limit 1,4");
      $T->SetBlock("faq_top","select * from `faq`  order by timestamp desc limit 1");
      $T->SetBlock("faqs","select * from `faq`  order by see desc limit 9");
      $T->SetBlock("faq_mem","select * from `faq`  order by see desc limit 3");
      
      #经典案例
      $caid = empty($_GET['caid'])?15:$_GET['caid'];
  	  $T->SetBlock("case_cate","select * from `main_art_category` where pid=15 order by id asc");
  	  $T->SetBlock("case_list","select * from `main_article`  where state=2 and cid=".$caid." order by timestamp desc limit 6");
  	  
  	  
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