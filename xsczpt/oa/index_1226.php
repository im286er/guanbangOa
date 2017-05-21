<?php
header("Content-type: text/html; charset=utf8;");
require('../ppf/fun.php');
require("../ppf/pdo_template.php");
require("../ppf/pdo_mysql.php"); 
require('./common.php');
require './library/Filter.php';
date_default_timezone_set("PRC");
$qname=isset($_GET["t"])?$_GET["t"]:"index"; 
$pd = new pdo_mysql();

ini_set('memory_limit','1000M');  //初始化数据库内存大小

if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];  
if(!isset($_SESSION['role_id'])){
	$rs1=$pd->query("select avatar,gender,dept,truename,role_id,flag_status,flag_type from oa_user_extend a left join act_member b on a.userid=b.id where a.userid='".$uid."'")->fetch(PDO::FETCH_ASSOC);
	if($rs1){
		//login
		$_SESSION['role_id']    = $rs1['role_id'];
		$_SESSION['flag_type']  = $rs1['flag_type'];
		$_SESSION['truename']   = $rs1['truename'];
		$_SESSION['avatar']   = $rs1['avatar'];
		$_SESSION['gender']   = $rs1['gender'];
		$_SESSION['dept']   = $rs1['dept'];
	}
}

$T=new pdo_template('./html/'.$qname.'.htm',true,"utf8");

switch($qname){
	case "index": #oa首页  
		if($_SESSION['role_id']==6){
			$T->Set("top","top_teacher"); 
			//$T->Set("t_main","oa_main_teacher");
			$T->Set("t_main","oa_main");
			
		}
		elseif($_SESSION['role_id']==4){
			$T->Set("top","top_admin"); 
			$T->Set("t_main","oa_main_admin");
		}	
		elseif($_SESSION['role_id']==1){
			$T->Set("top","top"); 
			$T->Set("t_main","oa_main");
		}	
		else{
			$T->Set("top","top_teacher"); 
			$T->Set("t_main","oa_main_admin");
		}	
		break; 
		
	case "teacher": #oa首页   
		break; 
		
	case "admin": #oa首页   
		break; 
		
	case "user_modify": #oa首页  
		$T->ReadDB("select A.*,B.alias_name from act_member as A inner join oa_user_extend as B ON A.id=B.userid  where id='{$_SESSION['uid']}'"); 
		break; 
		
	case "top": #top  
		/*$T->SetBlock("top_list","select * from oa_features where pid=0");	
		$userurl = "./?t=stu_modify&id={$_SESSION['uid']}";
		
		$T->Set("top_home_url","?t=index"); 
		$T->Set("userurl",$userurl); 
		break;*/
		$menus = $pd->query("select * from oa_features where pid=0 and fea_states=0 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].")")->fetchAll(PDO::FETCH_ASSOC);
		//$grade = $pd->query("SELECT c.grade_id from oa_zhsz_users u join oa_zhsz_class c on u.department=c.id where u.id='{$_SESSION['uid']}'")->fetch(PDO::FETCH_ASSOC);

		$str_menu = '<li><a href="?t=index"  target="_parent" class="current">首页</a><span></span></li>';
		foreach($menus as $rs){
			$menu_son = $pd->query("select * from oa_features where m_id={$rs['id']}")->fetchAll(PDO::FETCH_ASSOC);

				if($menu_son){
					$str_menu .= '<li><a href="?t=left&id='.$rs['id'].'" target="lef_m">'.$rs['fea_name'].'</a><span></span></li>';
				}else{
					$str_menu .= '<li><a href="?t='.$rs['fea_url'].'" target="main">'.$rs['fea_name'].'</a><span></span></li>';
				}
		}
		
		$userurl = "./?t=stu_modify&id={$_SESSION['uid']}";
		
		$T->Set("id",$_SESSION['uid']);
		$T->Set("str_menu",$str_menu); 
		$T->Set("userurl",$userurl); 
		$T->Set("uid",$uid);	
		break; 
		
	case "top_admin": #top   
		$menus = $pd->query("select * from oa_features where pid=0 and fea_states=0 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].")")->fetchAll(PDO::FETCH_ASSOC);
		
		$str_menu = '<li><a href="?t=admin" target="_parent" class="current">首页</a><span></span></li>';
		foreach($menus as $rs){
			$menu_son = $pd->query("select * from oa_features where m_id={$rs['id']}")->fetchAll(PDO::FETCH_ASSOC);
			if($menu_son){
				$str_menu .= '<li><a href="?t=left&id='.$rs['id'].'" target="lef_m">'.$rs['fea_name'].'</a><span></span></li>';
			}else{
				$str_menu .= '<li><a href="?t='.$rs['fea_url'].'" target="main">'.$rs['fea_name'].'</a><span></span></li>';
			}
		}
		
		$T->Set("str_menu",$str_menu); 
		$T->Set("uid",$uid);
		break; 
		
	case "top_teacher": #top   
		$menus = $pd->query("select * from oa_features where pid=0 and fea_states=0 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].")")->fetchAll(PDO::FETCH_ASSOC);
		
		//if($_SESSION['role_id']==6)
			//$str_menu = '<li><a href="?t=teacher" target="_parent" class="current">首页</a><span></span></li>';
		//else
			$str_menu = '<li><a href="?t=index" target="_parent" class="current">首页</a><span></span></li>';
		
		foreach($menus as $rs){
			$menu_son = $pd->query("select * from oa_features where m_id={$rs['id']}")->fetchAll(PDO::FETCH_ASSOC);
			if($menu_son){
				$str_menu .= '<li><a href="?t=left&id='.$rs['id'].'" target="lef_m">'.$rs['fea_name'].'</a><span></span></li>';
			}else{
				$str_menu .= '<li><a href="?t='.$rs['fea_url'].'" target="main">'.$rs['fea_name'].'</a><span></span></li>';
			}
		}
		
		$T->Set("str_menu",$str_menu); 
		$T->Set("uid",$uid);
		break; 
		
	/*case "oa_main":
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		$main = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_main','where'=>"user_id='{$_SESSION['uid']}' and term_id={$curTerm['id']}"));
		
		if($main){
			$selfResult = empty($main['self_summary'])?"暂无自我评价":$main['self_summary'];
			$teacherC   = empty($main['comment'])?"暂无班主任评语":$main['comment'];
			$object     = empty($main['object'])?"暂无学期目标":$main['object'];
		}else{
			$selfResult = "暂无自我评价";
			$teacherC   = "暂无班主任评语";
			$object     = "暂无学期目标";
		}
		
		$experience = $pd->query("SELECT * from oa_zhsz_experience where term_id={$curTerm['id']} and create_by='{$_SESSION['username']}'")->fetchAll(PDO::FETCH_ASSOC);
		
		$str_experience = ''; //经历感悟
		if($experience){
			foreach($experience as $rs){
				$str_experience .= '<div><a href="./?t=zhsz_experience" target="main">'.$rs['subject'].'：'.$rs['content'].'</a><span>'.$rs['create_time'].'</span></div>';
			}
		}else{
			$str_experience = '<p style="text-align:center"><img src="images/no_img.jpg" width="202" height="214" /></p>';
		}
		
		$str_result = '';
		$result = $pd->query("SELECT * from oa_zhsz_result where term_id={$curTerm['id']} and create_by='{$_SESSION['username']}'")->fetchAll(PDO::FETCH_ASSOC);
		if($result){
			foreach($result as $rs){
				$str_result .= '<div><a href="./?t=zhsz_result" target="main">'.$rs['subject'].'</a><span>'.$rs['create_time'].'</span></div>';
			}
		}else{
			$str_result = '<p style="text-align:center"><img src="images/no_img.jpg" width="202" height="214" /></p>';
		}
		
		$research = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_research','where'=>"term_year='{$curTerm['year']}' and create_by='{$_SESSION['username']}'"));
		if($research){
			$str_research = '<div><a href="./?t=zhsz_research" target="main">'.$research['subject'].'：'.$research['content'].'</a><span>'.$research['create_time'].'</span></div>';
		}else{
			$str_research = '<p style="text-align:center"><img src="images/no_img.jpg" width="202" height="214" /></p>';
		}
		
		$str_service = '';
		$service = $pd->query("SELECT * from oa_zhsz_service where term_id={$curTerm['id']} and create_by='{$_SESSION['username']}'")->fetchAll(PDO::FETCH_ASSOC);
		if($service){
			foreach($service as $rs){
				$str_service .= '<div><a href="./?t=zhsz_service" target="main">'.$rs['subject'].'：'.$rs['content'].'</a><span>'.$rs['create_time'].'</span></div>';
			}
		}else{
			$str_service = '<p style="text-align:center"><img src="images/no_img.jpg" width="202" height="214" /></p>';
		}
		
		$str_event = '';
		$event = $pd->query("SELECT * from oa_zhsz_event where term_id={$curTerm['id']} and create_by='{$_SESSION['username']}'")->fetchAll(PDO::FETCH_ASSOC);
		if($event){
			foreach($event as $rs){
				$str_event .= '<div><a href="./?t=zhsz_event" target="main">'.$rs['subject'].'：'.$rs['content'].'</a><span>'.$rs['create_time'].'</span></div>';
			}
		}else{
			$str_event = '<p style="text-align:center"><img src="images/no_img.jpg" width="202" height="214" /></p>';
		}
					
		
		$T->Set("object",$object); 
		$T->Set("teacherC",$teacherC); 
		$T->Set("selfResult",$selfResult); 
		$T->Set("str_experience",$str_experience); 
		$T->Set("str_result",$str_result); 
		$T->Set("str_research",$str_research); 
		$T->Set("str_service",$str_service); 
		$T->Set("str_event",$str_event); 
		break;*/
	
	case "oa_main":
		if($_SESSION['role_id']==1){
			$menus = $pd->query("select * from oa_features where fea_states=0 and m_id=1 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].") order by fea_sort ")->fetchAll(PDO::FETCH_ASSOC);
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
			$grade_id = isset($class["grade_id"])?$class["grade_id"]:0; 
			
			$str_menu = '';
			foreach($menus as $rs){
				if($grade_id!=0){
					if($rs['fea_url']!="work_exp"&&$rs['fea_url']!="biye_info"&&$rs['fea_url']!="senior_message"&&$rs['fea_url']!="biye_info_export")
						$str_menu .= '<li><a href="?t='.$rs['fea_url'].'" target="main"><img src="images/'.$rs['fea_icon'].'.jpg" width="75" height="75" /><p>'.$rs['fea_name'].'</p></a></li>';
				}else{
					$str_menu .= '<li><a href="?t='.$rs['fea_url'].'" target="main"><img src="images/'.$rs['fea_icon'].'.jpg" width="75" height="75" /><p>'.$rs['fea_name'].'</p></a></li>';
				}
			}
		}else{
			$menus = $pd->query("select * from oa_features where fea_states=0 and pid!=0 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].") order by fea_sort ")->fetchAll(PDO::FETCH_ASSOC);
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
			$grade_id = isset($class["grade_id"])?$class["grade_id"]:0; 
			
			$str_menu = '';
			foreach($menus as $rs){
					$str_menu .= '<li class=""><a href="?t='.$rs['fea_url'].'" target="main"><img src="images/'.$rs['fea_icon'].'.jpg" width="75" height="75" /><p>'.$rs['fea_name'].'</p></a></li>';
			}
		}
		
		$T->Set("str_menu",$str_menu); 
		break;
		
	case "oa_main_admin":
		$menus = $pd->query("select * from oa_features where pid=0 and fea_states=0 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].")")->fetchAll(PDO::FETCH_ASSOC);
		
		$str_menu = '';
		foreach($menus as $rs){
			$menu_son = $pd->query("select * from oa_features where m_id={$rs['id']}")->fetchAll(PDO::FETCH_ASSOC);
			if($menu_son){
				$str_menu .= '<li><a href="?t=left&id='.$rs['id'].'" target="lef_m"><img src="images/'.$rs['fea_icon'].'.jpg" width="75" height="75" /><p>'.$rs['fea_name'].'</p></a></li>';
			}else{
				$str_menu .= '<li><a href="?t='.$rs['fea_url'].'" target="main"><img src="images/'.$rs['fea_icon'].'.jpg" width="75" height="75" /><p>'.$rs['fea_name'].'</p></a></li>';
			}
		}
		$T->Set("str_menu",$str_menu); 
		break;
		
	 /* 班主任消息汇总页 */
	case "oa_main_teacher":
	    $class_dept=isset($_GET["dept"])?$_GET["dept"]:1;
		$report = $T->query("SELECT id,code_name from  oa_zhsz_report where parent_no=''  and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		//学期下拉框
		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by year DESC,term_type DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rst){
			$str_term .= '<option value="'.$rst['id'].'">'.$rst['year'].'('.$rst['term_name'].')</option>';
		}
		
		$cid   = 0;
		$gid   = 0;
		$user_num = 0;
		$className = '';
		$gradeName = '';
		$tips      = '&nbsp;';
		$strWhere  = "flag_type=1 and flag_status=1";
		//修学或退学
		$noClass = false;
		//毕业
		$noGrade = false;
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class_id = 0;
		//学生
	
		if($_SESSION['flag_type']==1||$_SESSION['flag_type']==3){
			//查询学生信息
			$user  = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
			//查询当前学生年级班级情况
			if(empty($user['dept'])){
				$noClass = true;
			}else{
				//查询班级
				$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
				//查询是否是毕业班
				if($class['grade_id']==0){
					$noGrade = true;
				}else{
					$cid = $class['id'];
					$class_id = $class['id'];
					$gid = $class['grade_id'];
				}
			}
		}
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			
			if(!empty($class)){
				$cid = $class['id'];
				$class_id = $class['id'];
				$gid = $class['grade_id'];
				$isMaster = true;
				$isMaster1 = 1;
			}
			$isMaster = false;
		}
		
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$term_id   = empty($_POST['term_id'])?'':$_POST['term_id'];
		$cid = empty($_POST['cid'])?$cid:Filter::filter_number($_POST['cid']);
		$gid = empty($_POST['gid'])?$gid:Filter::filter_number($_POST['gid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
	
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		
		if(!empty($cid)){
			$strWhere.= " and dept='{$cid}' ";
		}else{
			if($_SESSION['role_id']==4 && $cid==0){
				$grade_id = $pd->fetchOne(array('field'=>'id','table'=>'oa_zhsz_grade','limit'=>"1"));
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$grade_id} and school={$_SESSION['school']}"));
				$classId  = join(',',$classId);
				$strWhere .= " and dept IN ({$classId}) ";
			}else{
				$strWhere.= " and dept>0 ";
			}
				
		}
		$wheret='';
		$where_wsh='';
		if($term_id){
			$wheret .= " and ozrc.term_id=".$term_id." ";
		}
		if($term_id){
			$where_wsh .= " and (ozrc.term_id=".$term_id." or ozrp.term_id=".$term_id." or ozre.user_id=M.uid ) ";
		}
		//当是班主任角色时
		if($_SESSION['role_id']==6){
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			$tips = "当前班级：{$gradeName}{$className}";
			$class_dept_cur = $T->db->query("select id from oa_zhsz_class where class_master='{$_SESSION['uid']}' and grade_id=".$gid." ")->fetchColumn(0);
			$class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
			if($class_dept==''){
				$class_name = $T->db->query("select class_name from oa_zhsz_class where grade_id={$gid} order by update_time limit 1 ")->fetchColumn(0);
			    $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
				$tips = "当前班级：{$gradeName}{$class_name}";						
			}
            if($class_dept&&$class_dept==$class_dept_cur){
				if($truename){
					$T->Set("truename",$truename);
				    $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";
					
					$sql1= "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.truename like '%".$truename."%' ".$wheret." GROUP BY V.truename";

				    $user_num = $T->db->query("select count(1) from v_users where role_id=1 and dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
				}else{
				   $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and role_id=1 GROUP BY truename";
				
				   $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and role_id=1  GROUP BY truename";
				   
				   $user_num = $T->db->query("select count(1) from v_users where role_id=1 and dept=".$class_dept)->fetchColumn(0);
				}
				$student = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
				
				$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			    $str_sh = '';
			    foreach($report as $rsr){
				   $menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=".$class_dept)->fetchColumn(0);
				   $str_sh .= "<li>{$rsr['code_name']}<em><a href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
			    } 
			
			    $str = '';
			    if(empty($students)){
				  foreach($student as $rss){
					$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
				  }
			    }
		        
			    foreach($students as $rs){
				
				  $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_teacher&stu_id={$rs['stu_id']}' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
			    }
			
				
			}elseif($class_dept&&$class_dept!=$class_dept_cur){
				if($truename){
					$T->Set("truename",$truename);
				    $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";
					
					$sql1= "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.truename like '%".$truename."%' ".$wheret." GROUP BY V.truename";

				    $user_num = $T->db->query("select count(1) from v_users where role_id=1 and dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
				}else{
					$sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and role_id=1 GROUP BY truename";
				
				    $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and role_id=1  GROUP BY truename";
					$user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept={$class_dept}"));
				}
				
				$student = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
				$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			    $str_sh = '';
			    foreach($report as $rsr){
				   $menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=".$class_dept)->fetchColumn(0);
				   $str_sh .= "<li>{$rsr['code_name']}<em><a href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
			    } 
			
			    $str = '';
			    if(empty($students)){
				  foreach($student as $rss){
					$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
				  }
			    }
			    foreach($students as $rs){
				  $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
			    }
			}else{
				$user_num = 0;
			}
		}elseif($_SESSION['role_id']==4 || $_SESSION['role_id']==5){
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));

			if($cid==0||$gid==0){
			  if($truename){
				  $T->Set("truename",$truename);
				  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.truename like '%".$truename."%' GROUP BY V.truename";
				  
				  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.truename like '%".$truename."%' GROUP BY V.truename";

				  $user_num = $T->db->query("select count(1) from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where role_id=1 and truename like '%".$truename."%' ".$wheret." ")->fetchColumn(0);
				  //$rc= $T->db->query("select count(1) from oa_features")->fetchColumn(0);
			  }else{
				  if($gid){
					  $class_name = $T->db->query("select class_name from oa_zhsz_class where grade_id={$gid} order by update_time limit 1 ")->fetchColumn(0);
					  
					  $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
					  $tips = "当前班级：{$gradeName}".$class_name."";
				  }else{
					 $class_new  = $pd->fetchRow(array('field'=>array('class_name,grade_id'),'table'=>'oa_zhsz_class','where'=>"grade_id<>0"));
					 $class_name = $class_new['class_name'];
					 $gid = $class_new['grade_id'];
					 $gradeName = $T->db->query("select grade_name from oa_zhsz_grade where id=".$gid)->fetchColumn(0);
					 $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
					 $tips = "当前班级：{$gradeName}{$class_name}";
					 
				  }
			   //默认一个班级
				  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and role_id=1 GROUP BY truename";
				  
				  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and role_id=1 GROUP BY truename";
				  
				  $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept=1"));
			  }
			   //学生信息查询
			  $student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
			  
			  $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			  $str_sh = '';
			  foreach($report as $rsr){
				   $menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=1 ".$wheret."")->fetchColumn(0);
				   $str_sh .= "<li>{$rsr['code_name']}<em><a href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
			  } 
			
			   $str = '';
			   if(empty($students)){
				  foreach($student as $rss){
					$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
				  }
			   }
		
			   foreach($students as $rs){
				
				  $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_admin&stu_id={$rs['stu_id']}' target='' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
			   }
			 
			}elseif($cid!=0&&$gid!=0){
				if($truename){
				  $class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
				  $T->Set("truename",$truename);
				  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh.") as num from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";
				  
				  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";

				  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and  dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
				  
				   $student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
				   $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			       $str_sh = '';
			       foreach($report as $rsr){
				     $menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=".$class_dept)->fetchColumn(0);
				     $str_sh .= "<li>{$rsr['code_name']}<em><a  href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
			       } 
			
			       $str = '';
			       if(empty($students)){
					  foreach($student as $rss){
						$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
					  }
			       }
		
			       foreach($students as $rs){
				
				      $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_admin&stu_id={$rs['stu_id']}' target='' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
			       }
			    }else{
					$tips = "当前班级：{$gradeName}{$className}";
					$where  = $strWhere;
				
					$class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
					
					$sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id ".$where_wsh." ) as num  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and role_id=1 GROUP BY truename";
					
					$sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept=".$class_dept." and role_id=1 GROUP BY truename";
					
					//学生信息查询
					$student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
					$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					$str_sh = '';
					foreach($report as $rsr){
						$menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' ".$wheret." and ozc.id=".$class_dept." ")->fetchColumn(0);
						
						$str_sh .= "<li>{$rsr['code_name']}<em><a href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
					} 
				
					$str = '';
					if(empty($students)){
					  foreach($student as $rss){
						$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
					  }
			        }
				
					foreach($students as $rs){
					   $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_admin&stu_id={$rs['stu_id']}' target='' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
					   
					   $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept={$class_dept}"));
					}
				}
			}
		}elseif($_SESSION['role_id']!=1&&$_SESSION['role_id']!=3&&$_SESSION['role_id']!=4&&$_SESSION['role_id']!=5&&$_SESSION['role_id']!=6){
			$T->Set("var_wcl","display:none");
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));

			if($cid==0||$gid==0){
			  if($truename){
				  $T->Set("truename",$truename);
				  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id and M.geter='".$uid."' ".$where_wsh." ) as num from v_users as V left join oa_message as M on M.geter=V.id where V.truename like '%".$truename."%' GROUP BY V.truename";
				  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id where V.truename like '%".$truename."%' GROUP BY V.truename";

				  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and truename like '%".$truename."%'")->fetchColumn(0);
				  //$rc= $T->db->query("select count(1) from oa_features")->fetchColumn(0);
			  }else{
				  if($gid){
					  $class_name = $T->db->query("select class_name from oa_zhsz_class where grade_id={$gid} order by update_time limit 1 ")->fetchColumn(0);
					  $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
					  $tips = "当前班级：{$gradeName}".$class_name."";
				  }else{
					$class_new  = $pd->fetchRow(array('field'=>array('class_name,grade_id'),'table'=>'oa_zhsz_class','where'=>"grade_id<>0"));
					 $class_name = $class_new['class_name'];
					 $gid = $class_new['grade_id'];
					 $gradeName = $T->db->query("select grade_name from oa_zhsz_grade where id=".$gid)->fetchColumn(0);
					 $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
					 $tips = "当前班级：{$gradeName}{$class_name}";
				  }
			   //默认一个班级
				  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id and M.geter='".$uid."' ".$where_wsh." ) as num  from v_users as V left join oa_message as M on M.geter=V.id where V.dept=".$class_dept." and role_id=1 GROUP BY truename";
				  
				  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id where V.dept=".$class_dept." and role_id=1 GROUP BY truename";
				  
				  $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept=1"));
			  }
			   //学生信息查询
			  $student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC); 
			  $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			  $str_sh = '';
			  foreach($report as $rsr){
				   $menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=1")->fetchColumn(0);
				   $str_sh .= "<li style='display:none;'>{$rsr['code_name']}<em><a href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
			  } 
			
			   $str = '';
			   if(empty($students)){
					  foreach($student as $rss){
						$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
					  }
			   }
		
			   foreach($students as $rs){
				
				  $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_teacher&stu_id={$rs['stu_id']}' target='' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
				  
				  //$user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept=1"));
			   }
			 
			}elseif($cid!=0&&$gid!=0){
				if($truename){
				  $class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
				  $T->Set("truename",$truename);
				  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id and M.geter='".$uid."' ".$where_wsh.") as num from v_users as V left join oa_message as M on M.geter=V.id where V.dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";
				  
				  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id where V.dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";

				  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and  dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
				  
				   $student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
				   $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			       $str_sh = '';
			       foreach($report as $rsr){
				     $menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=".$class_dept)->fetchColumn(0);
				     $str_sh .= "<li style='display:none;'>{$rsr['code_name']}<em><a  href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
			       } 
			
			       $str = '';
			       if(empty($students)){
					  foreach($student as $rss){
						$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
					  }
			       }
		
			       foreach($students as $rs){
				
				      $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_teacher&stu_id={$rs['stu_id']}' target='' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
			       }
			    }else{
					$tips = "当前班级：{$gradeName}{$className}";
					$where  = $strWhere;
				
					$class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
					
					$sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id left join oa_zhsz_remarks as ozre on  ozre.id=M.from_id where M.is_read=0 and M.uid=V.id and M.geter='".$uid."' ".$where_wsh.") as num  from v_users as V left join oa_message as M on M.geter=V.id where dept=".$class_dept." and role_id=1 GROUP BY truename";
					
					$sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id where dept=".$class_dept." and role_id=1 GROUP BY truename";
					//学生信息查询
					$student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
					$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					$str_sh = '';
					foreach($report as $rsr){
						$menu_num = $T->db->query("select count(*) from oa_zhsz_report_custom as ozrc left join oa_zhsz_report as ozr on ozr.code_name=ozrc.subject left join oa_message as om on om.from_id=ozrc.id left join oa_zhsz_class as ozc on ozc.class_master=om.geter where ozrc.subject = '".$rsr["code_name"]."' and ozrc.flag_status='待审核' and ozc.id=".$class_dept."")->fetchColumn(0);
						$str_sh .= "<li style='display:none;'>{$rsr['code_name']}<em><a href='./?t=report_custom_check&type=1&report_type=".$rsr['id']."'>（".$menu_num."）</a></em></li>";
					} 
				
					$str = '';
					if(empty($students)){
					  foreach($student as $rss){
						$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
					  }
			        }
				
					foreach($students as $rs){
					   $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a href='./?t=message_teacher&stu_id={$rs['stu_id']}' target='' class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
					   
					   $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept={$class_dept}"));
					}
				}
			}
		}
		
		if($_SESSION['role_id']==4){
			$is_admin = 1;
		}else{
			$is_admin = 0;
		}
		
		if($_SESSION['role_id']==6){
			$str_import = '<div class="fr" style="float:right;height:26px;line-height:26px;margin:10px 10px 15px 0;padding:0 10px;background:#277fc2;"><a href="./?t=zhsz_comment_batch" class="" style="width:100px;color:#fff;">导入评语</a></div>';
		}else{
			$str_import = '';
		}
		
		
		$T->Set("is_admin",$is_admin);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("tips",$tips);
		$T->Set("isMaster1",$isMaster1);
		$T->Set("class_id",$class_id);
		$T->Set("str",$str);
		$T->Set("str_sh",$str_sh);
		$T->Set("str_term",$str_term);
		$T->Set("user_num",$user_num);
		$T->Set("class_dept",$class_dept);
		$T->Set("str_import",$str_import);
		$T->Set("term_id",$term_id);
	    break;
		
	case "oa_main_student":
	    //学期下拉框
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by year DESC,term_type DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rst){
			$str_term .= '<option value="'.$rst['id'].'">'.$rst['year'].'('.$rst['term_name'].')</option>';
		}
	    $cid   = 0;
		$gid   = 0;
		$user_num = 0;
		$className = '';
		$gradeName = '';
		$tips      = '&nbsp;';
		$strWhere  = "flag_type=1 and flag_status=1";
		
		if($_SESSION['flag_type']==1||$_SESSION['flag_type']==3){
			//查询学生信息
			$user  = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
			//查询当前学生年级班级情况
			if(empty($user['dept'])){
				$noClass = true;
			}else{
				//查询班级
				$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
				//查询是否是毕业班
				if($class['grade_id']==0){
					$noGrade = true;
				}else{
					$cid = $class['id'];
					$class_id = $class['id'];
					$gid = $class['grade_id'];
				}
			}
			
		}
		
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$term_id   = empty($_POST['term_id'])?'':$_POST['term_id'];
		$cid = empty($_POST['cid'])?$cid:Filter::filter_number($_POST['cid']);
		$gid = empty($_POST['gid'])?$gid:Filter::filter_number($_POST['gid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
	
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		
		if(!empty($cid)){
			$strWhere.= " and dept='{$cid}' ";
		}else{
			
			if($_SESSION['role_id']==4 && $cid==0){
				$grade_id = $pd->fetchOne(array('field'=>'id','table'=>'oa_zhsz_grade','limit'=>"1"));
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$grade_id} and school={$_SESSION['school']}"));
				$classId  = join(',',$classId);
				$strWhere .= " and dept IN ({$classId}) ";
			}else{
				$strWhere.= " and dept>0 ";
			}
		}
		
		//选择学期
		$wheret='';
		if($term_id){
			$wheret .= " and ozrc.term_id=".$term_id."";
		}
	    $className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		$class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);

		if($cid==0||$gid==0){
		  if($truename){
			  $T->Set("truename",$truename);
			  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M where M.is_read=0 and M.uid=V.id) as num from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.truename like '%".$truename."%' ".$wheret." GROUP BY V.truename";
			  
			  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.truename like '%".$truename."%' GROUP BY V.truename";

			  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and truename like '%".$truename."%'")->fetchColumn(0);
			  //$rc= $T->db->query("select count(1) from oa_features")->fetchColumn(0);
		  }else{
			  if($gid){
					  $class_name = $T->db->query("select class_name from oa_zhsz_class where grade_id={$gid} order by update_time limit 1 ")->fetchColumn(0);
					  
					  $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
					  $tips = "当前班级：{$gradeName}".$class_name."";
				  }else{
					 $class_new  = $pd->fetchRow(array('field'=>array('class_name,grade_id'),'table'=>'oa_zhsz_class','where'=>"grade_id<>0"));
					 $class_name = $class_new['class_name'];
					 $gid = $class_new['grade_id'];
					 $gradeName = $T->db->query("select grade_name from oa_zhsz_grade where id=".$gid)->fetchColumn(0);
					 $class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
					 $tips = "当前班级：{$gradeName}{$class_name}";
					 
				  }
		   //默认一个班级
			  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M where M.is_read=0 and M.uid=V.id) as num  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and role_id=1 ".$wheret." GROUP BY truename";
			  
			  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and role_id=1 GROUP BY truename";
			  
			  $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept=".$class_dept.""));
		  }
		   
		   
		   //学生信息查询
		  $student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
		  $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		   $str = '';
		   if(empty($students)){
			  foreach($student as $rss){
					$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
			  }
		   }
	
		   foreach($students as $rs){
			
			  $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
		   }
		 
		}elseif($cid!=0&&$gid!=0){
			if($truename){
			  $class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
			  $T->Set("truename",$truename);
			  $sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M where M.is_read=0 and M.uid=V.id) as num from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and V.truename like '%".$truename."%' ".$wheret." GROUP BY V.truename";
			  
			  $sql1 = "select V.truename,V.id as stu_id from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and V.truename like '%".$truename."%' GROUP BY V.truename";

			  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and  dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
			   $student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
			   $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			   
			   $str = '';
			   if(empty($students)){
				  foreach($student as $rss){
					$str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
			      }
			   }
	
			   foreach($students as $rs){
			
				  $str.="<li class='user_list2' num='{$rs['num']}' style='position:relative;'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a class='ha_tipsicon' style='display:none;width:20px;'>{$rs['num']}</a></li>";
			   }
			}else{
				
				$class_dept = $T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='{$className}'")->fetchColumn(0);
				if($class_dept==''){
					$class_name = $T->db->query("select class_name from oa_zhsz_class where grade_id={$gid} order by update_time limit 1 ")->fetchColumn(0);
					$class_dept=$T->db->query("select id from oa_zhsz_class where grade_id=".$gid." and class_name='".$class_name."'")->fetchColumn(0);
				}
				$tips = "当前班级：{$gradeName}{$className}";
				$where  = $strWhere;

				
				$sql = "select V.truename,V.id as stu_id,(select count(*) from oa_message as M where M.is_read=0 and M.uid=V.id) as num  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept='".$class_dept."' and role_id=1 ".$wheret." GROUP BY truename";
				
				$sql1 = "select V.truename,V.id as stu_id  from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where dept='".$class_dept."' and role_id=1  GROUP BY truename";
	
				//学生信息查询
				$student  = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
				$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
				$str = '';
				if(empty($students)){
				   foreach($student as $rss){
					 $str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rss['stu_id']}'>{$rss['truename']}</a></li>";
					 
					 $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept='{$class_dept}'"));
			       }
				}
			
				foreach($students as $rs){
				   $str.="<li class='user_list2'><a href='./?t=zhsz_table_show&uid={$rs['stu_id']}'>{$rs['truename']}</a><a class='ha_tipsicon' style='display:none;width:20px;'></a></li>";
				   
				   $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept='{$class_dept}'"));
				}
			}
		}
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("tips",$tips);
		//$T->Set("isMaster1",$isMaster1);
		//$T->Set("class_id",$class_id);
		$T->Set("str",$str);
		$T->Set("str_term",$str_term);
		$T->Set("user_num",$user_num);
		$T->Set("class_dept",$class_dept);
		$T->Set("term_id",$term_id);
        break;	
		
	case "left":
		$id=isset($_GET["id"])?$_GET["id"]:0; 
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		$grade_id = isset($class["grade_id"])?$class["grade_id"]:0; 
		$str_left = '';
		
		/*if($id==0){
			//$T->SetBlock("menu_left","select * from oa_features where fea_states=0 and m_id=1 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].") order by fea_sort");
			$left = $pd->query("select * from oa_features where fea_states=0 and m_id=1 and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].") order by fea_sort")->fetchAll(PDO::FETCH_ASSOC);
			foreach($left as $rs){
				if($grade_id==3){
					if($rs['fea_menuname']!="工作经历"&&$rs['fea_menuname']!="学习经历"&&$rs['fea_menuname']!="毕业报表导出")
						$str_left .= '<li><a href="./?t='.$rs['fea_url'].'&id='.$rs['id'].'" class="inactive" target="main" onclick="savemenu(\''.$rs['id'].'\')">'.$rs['fea_menuname'].'</a></li>';
				}else{
					$str_left .= '<li><a href="./?t='.$rs['fea_url'].'&id='.$rs['id'].'" class="inactive" target="main" onclick="savemenu(\''.$rs['id'].'\')">'.$rs['fea_menuname'].'</a></li>';
				}
			}
		}else{*/
			//$T->SetBlock("menu_left","select * from oa_features where fea_states=0 and m_id=".$id." and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].") order by fea_sort");
			if($id!=0){
				$left = $pd->query("select * from oa_features where fea_states=0 and m_id=".$id." and id in (select privilege_id from oa_zhsz_role_privileges where role_id=".$_SESSION['role_id'].") order by fea_sort")->fetchAll(PDO::FETCH_ASSOC);
				foreach($left as $rs){
					if($grade_id!=0){
						if($rs['fea_url']!="work_exp"&&$rs['fea_url']!="biye_info"&&$rs['fea_url']!="biye_info_export")
							$str_left .= '<li><a href="./?t='.$rs['fea_url'].'&id='.$rs['id'].'" class="inactive" target="main" onclick="savemenu(\''.$rs['id'].'\')">'.$rs['fea_menuname'].'</a></li>';
					}else{
						$str_left .= '<li><a href="./?t='.$rs['fea_url'].'&id='.$rs['id'].'" class="inactive" target="main" onclick="savemenu(\''.$rs['id'].'\')">'.$rs['fea_menuname'].'</a></li>';
					}
				}
			}
		//}

		if($_SESSION['dept']&&$_SESSION['role_id']==1){
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
			if($class['grade_id']==0){
				$grade = $class['class_end'];
			}else{
				$grade = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
			}
			
			$show_name = $grade.$class['class_name'];
			
		}else{
			$show_name = $pd->fetchOne(array('field'=>'name','table'=>'oa_zhsz_roles','where'=>"id={$_SESSION['role_id']}"));
		}
		
		$T->Set("show_name",$show_name); 
		
		//$avatar = $_SESSION['avatar'];
		$avatar = $pd->fetchOne(array('field'=>'avatar','table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		if(!$avatar){
			if($_SESSION['gender']=="男")
				$avatar = "images/boy.jpg";
			else
				$avatar = "images/girl.jpg";
		}

		if($_SESSION['role_id']==1){
			$T->Set("urlpic","./?t=avatar_modify"); 
			$target = 'main';
			$userurl = "./?t=stu_modify&id={$_SESSION['uid']}";
		}else{
			$target = '';
			$userurl = "./?t=user_modify";
		}
		
		$T->Set("target",$target); 
		$T->Set("userurl",$userurl); 
		$T->Set("truename",$_SESSION['truename']); 
		$T->Set("avatar",$avatar); 
		$T->Set("str_left",$str_left); 
		break;

	case "features_manage": #功能管理
        $p=isset($_GET["p"])?$_GET["p"]:"1"; 
		if($p<1)$p=1;
		$rc= $T->db->query("select count(1) from oa_features")->fetchColumn(0);
		$page=getPageHtml_bt($rc,15,$p,"&t=features_manage");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->SetBlock("qx_list2","select * from oa_features order by id asc limit ".(($p-1)*15).",15");	
		break;
	case "features_modify":
		$id=$_GET["id"];
		$r=$pd->query("select * from oa_features where id=".$id)->fetch(PDO::FETCH_ASSOC);
		$T->Set("fea_name",$r["fea_name"]); 
		$T->Set("fea_des",$r["fea_des"]);
		$T->Set("fea_menuname",$r["fea_menuname"]);
		$T->Set("id",$r["id"]);
		$T->Set("pid",$r["pid"]);
		$T->Set("fea_url",$r["fea_url"]);
		$T->Set("fea_icon",$r["fea_icon"]);
		$T->Set("fea_sort",$r["fea_sort"]);
		$T->Set("fea_states",$r["fea_states"]);
		$T->Set("fea_type",$r["fea_type"]);
		$T->Set("fea_level",$r["fea_level"]);
		$T->Set("m_id",$r["m_id"]);
		break;
	case "web_modify":  
		
		$rs = $pd->query("select * from oa_zhsz_system where school=".$_SESSION["school"])->fetch(PDO::FETCH_ASSOC);
		$T->Set("name",$rs["name"]);
		$T->Set("domain",$rs["domain"]);
		$T->Set("description",$rs["description"]);
		$T->Set("phone",$rs["phone"]);
		$T->Set("fax",$rs["fax"]);
		$T->Set("email",$rs["email"]);
		$T->Set("address",$rs["address"]);
		$T->Set("copyright",$rs["copyright"]);
		$sCheck1 = 'checked="checked"';
		$sCheck2 = '';
		if($rs['flag_log']==0){
			$sCheck1 = '';
			$sCheck2 = 'checked="checked"';
		}
		$T->Set("sck1",$sCheck1);
		$T->Set("sck2",$sCheck2);
		$tCheck1 = 'checked="checked"';
		$tCheck2 = '';
		if($rs['flag_alias']==0){
			$tCheck1 = '';
			$tCheck2 = 'checked="checked"';
		}
		$T->Set("tck1",$tCheck1);
		$T->Set("tck2",$tCheck2);
		$aCheck1 = 'checked="checked"';
		$aCheck2 = '';
		if($rs['flag_avatar']==1){
			$aCheck1 = '';
			$aCheck2 = 'checked="checked"';
		}
		$T->Set("ack1",$aCheck1);
		$T->Set("ack2",$aCheck2);
		$T->Set("self_start",$rs["self_start"]);
		$T->Set("self_end",$rs["self_end"]);
		$T->Set("course_start",$rs["course_start"]);
		$T->Set("course_end",$rs["course_end"]);
		$T->Set("course_select",$rs["course_select"]);
		$bCheck1 = 'checked="checked"';
		$bCheck2 = '';
		if($rs['flag_status']==0){
			$bCheck1 = '';
			$bCheck2 = 'checked="checked"';
		}
		$T->Set("bck1",$bCheck1);
		$T->Set("bck2",$bCheck2);
		$T->Set("email_host",$rs["email_host"]);
		$T->Set("email_port",$rs["email_port"]);
		$T->Set("email_user",$rs["email_user"]);
		$T->Set("email_pass",$rs["email_pass"]);
		$T->Set("email_reply_name",$rs["email_reply_name"]);
		$T->Set("email_send_name",$rs["email_send_name"]);
		break;
	case "code_manage":
		
		$T->SetBlock("all_code","select code_no,code_name from oa_zhsz_code where  parent_no=''");
		$p=isset($_GET["p"])?$_GET["p"]:"1"; 
        $g=isset($_GET["g"])?$_GET["g"]:""; 
		$wh="";
        if(!empty($g))$wh.=" and parent_no='".$g."'"; 
		
		if($p<1)$p=1;
		$rc= $T->db->query("select count(1) from oa_zhsz_code where 1=1 ".$wh)->fetchColumn(0);
		$page=getPageHtml_bt($rc,30,$p,"&t=code_manage");

		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("page",$page); 
		$T->SetBlock("codelist","select * from oa_zhsz_code where 1=1 ".$wh." order by order_value,code_no limit ".(($p-1)*30).",30");	   		
		break;
		
	case "code_modify":
		
		$id=isset($_GET["id"])?$_GET["id"]:""; 
		if(!empty($id)){
			$rs = $pd->query("select * from oa_zhsz_code where id=".$id)->fetch(PDO::FETCH_ASSOC);
			$T->Set("code_no",$rs["code_no"]);
			$T->Set("code_name",$rs["code_name"]);
			$T->Set("order_value",$rs["order_value"]);
			$T->Set("id",$rs["id"]);
			$cCheck1 = 'checked="checked"';
			$cCheck2 = '';
			if($rs['flag_status']==0){
				$cCheck1 = '';
				$cCheck2 = 'checked="checked"';
			}
			$T->Set("ck1",$cCheck1);
			$T->Set("ck2",$cCheck2);
		}
		break;
	case "role_manage": #角色管理
		$role=$pd->query("select * from oa_zhsz_roles order by id")->fetchAll(PDO::FETCH_ASSOC);
		$str_total = '';
		foreach($role as $rs){
			$str_total .= '<tr id="role_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['name'].'</td><td class="tabtd" align="left">&nbsp;&nbsp; <img src="images/037.gif" /> <a href="./?t=role_modify&id='.$rs['id'].'">修改</a> ';
			if($rs['id']>6){
				$str_total .= '<img src="images/010.gif" /> <a href="javascript:delRole('.$rs['id'].');">删除</a>';
			}
			$str_total .= '</td></tr>';
		}
		
		$T->Set("str_total",$str_total);
		break;
		
	case "role_add":
		//$T->SetBlock2("add_list","select * from oa_zhsz_privileges where type>0 and flag_menu=1 and flag_use=1 order by pid,order_value",
		//	array(array("block"=>"rp","pid"=>"id","sql"=>"select * from oa_zhsz_privileges where pid=?")));   
		$T->SetBlock2("add_list","select * from oa_features where fea_states=0 and pid=0 order by pid,fea_sort",
			array(array("block"=>"rp","pid"=>"id","sql"=>"select * from oa_features where pid=?"))); 
		break;
		
	case "role_modify":
		$id=$_GET["id"];
		$r=$pd->query("select * from oa_zhsz_roles where id=".$id)->fetch(PDO::FETCH_ASSOC);
		$T->Set("name",$r["name"]); 
		$T->Set("id",$id); 
		break;
		
	case "dept_manage": #部门管理
		//查询主分类
		$str_term = '';
		$term=$pd->query("select * from oa_zhsz_dept where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($term as $rs){
			$str_term .= '<tr id="dept_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['dept_name'].'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			//if(isset($priv[2])){
				$str_term .= '<img src="images/037.gif" /> <a href="./?t=dept_modify&id='.$rs['id'].'">修改</a> ';
			//}
			//if(isset($priv[3])){
				$str_term .= '<img src="images/010.gif" /> <a href="javascript:delDept('.$rs['id'].');">删除</a>';
			//}
			$str_term .= '</td></tr>';
		}
		
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_dept');
		$rc  = $pd->fetchOne($params);
		
		$T->Set("total",$rc); 
		$T->Set("str_term",$str_term); 
		break; 
		
	case "dept_add": #部门添加
		break;
		
	case "dept_modify": #部门修改
		$id = Filter::filter_number($_GET['id']);
		$T->ReadDB("select * from oa_zhsz_dept where id={$id}"); 
		break;
		
	case "stamina_manage": #体能项目	
		$str = '';
		//if(isset($priv[1])){
			$str = '<img src="images/add.gif" class="padding5"/><a href="./?t=stamina_add">添加体能项目</a>';
		//}
		
		//查询体能项目
		$params    = array('field'=>array('*'),'table'=>'oa_zhsz_stamina','where'=>'school='.$_SESSION["school"],'order'=>'order_value');
		$stamina  = $pd->fetchAll($params);
		$i = 1;
		$str_total = '';
		foreach($stamina as $rs){
			$grade_name = empty($rs['grade_name'])?'所有年级':$rs['grade_name'];
			$term_type = $rs['term_type']==0?'全年':($rs['term_type']==1?'第一学期':'第二学期');
			$str_total .= '<tr id="stamina_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$i++.'</td><td class="tabtd">'.$rs['name'].'</td><td class="tabtd">'.$grade_name.'</td><td class="tabtd">'.$term_type.'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			
			//if(isset($priv[2])){
				$str_total .= '<img src="images/037.gif" /> <a href="./?t=stamina_modify&id='.$rs['id'].'">修改</a> ';
			//}
			
			//if(isset($priv[3])){
				$str_total .= '<img src="images/010.gif" /> <a href="javascript:delStamina('.$rs['id'].');">删除</a>';
			//}
			$str_total .= '</td></tr>';
		}
		$rc = count($stamina);
		$T->Set("total",$rc);
		$T->Set("str",$str);
		$T->Set("str_total",$str_total);
		break;
		
	case "stamina_modify": #添加体能项目
		//查询体能项目排序最大值
		$id = Filter::filter_number($_GET['id']);
		$params = array('field'=>array('*'),'table'=>'oa_zhsz_stamina','where'=>"id={$id}");
		$stamina  = $pd->fetchRow($params);
		if(empty($stamina)){
			header('Location:./?t=stamina_manage');
			exit;
		}
		$T->Set("gid",$stamina['grade_id']);
		break; 
	case "term_manage": #学期管理
		//查询主分类
		$str_term = '';
		$term=$pd->query("select * from oa_zhsz_term where school=".$_SESSION["school"]." order by year Desc")->fetchAll(PDO::FETCH_ASSOC);
		foreach($term as $rs){
			$flag_default = $rs['flag_default']==0?'否':'是';
			$str_term .= '<tr id="term_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['year'].'</td><td class="tabtd">'.$rs['term_name'].'</td><td class="tabtd">'.$rs['start'].'</td><td class="tabtd">'.$rs['end'].'</td><td class="tabtd">'.$flag_default.'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			//if(isset($priv[2])){
				$str_term .= '<img src="images/037.gif" /> <a href="./?t=term_modify&id='.$rs['id'].'">修改</a> ';
			//}
			//if(isset($priv[3])){
				$str_term .= '<img src="images/010.gif" /> <a href="javascript:delTerm('.$rs['id'].');">删除</a> ';
			//}
			$str_term .= ' </td></tr>';
		}
							
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_term','order'=>'flag_default Desc,year');
		$rc  = $pd->fetchOne($params);
		
		$T->Set("str_term",$str_term); 
		$T->Set("total",$rc); 
		break; 
		
	case "term_add": #学期添加
		break;
		
	case "term_modify": #学期修改
		$id = Filter::filter_number($_GET['id']);
		//查询学期信息
		$T->ReadDB("select * from oa_zhsz_term where id={$id}"); 
		break;
		
	case "grade_manage": #年级管理
		$str_term = '';
		$term=$pd->query("select * from oa_zhsz_grade where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($term as $rs){
			$str_term .= '<tr id="grade_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['grade_name'].'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			//if(isset($priv[2])){
				$str_term .= ' <img src="images/037.gif" /> <a href="./?t=grade_modify&id='.$rs['id'].'">修改</a> ';
			//}
			//if(isset($priv[3])){
				$str_term .= ' <img src="images/010.gif" /> <a href="javascript:delGrade('.$rs['id'].');">删除</a>';
			//}
			$str_term .= '</td></tr>';
		}
		
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_grade');
		$rc  = $pd->fetchOne($params);
		
		$T->Set("str_term",$str_term); 
		$T->Set("total",$rc); 
		break; 
		
	case "grade_add": #年级添加
		break;
		
	case "grade_modify": #年级修改
		$id = Filter::filter_number($_GET['id']);
		$T->ReadDB("select * from oa_zhsz_grade where id={$id}"); 
		break;
		
	case "class_manage": #班级管理
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		
		$strWhere = 'A.grade_id<>0 and school='.$_SESSION["school"];
		$curId	  = empty($_POST['grade_id'])?0:$_POST['grade_id'];
		$curId	  = Filter::filter_number($curId);
		
		if($gid_page)
			$curId = $gid_page;
		
		//查询条件
		if(!empty($curId)){
			$strWhere .= " AND A.grade_id={$curId} ";
		}
		
		$where  = $strWhere;
		//查询所有班级
		$params = array('field'=>'count(A.id)','table'=>'oa_zhsz_class AS A','where'=>$where);
		$rc = $pd->fetchOne($params);
		
		$p=isset($_GET["p"])?$_GET["p"]:"0";  	   
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=class_manage&gid_page={$curId}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("page1",$p);
		$T->Set("total",$rc); 
		$T->Set("grade_id",$curId); 
		break;
		
	case "class_add": #班级添加
		break;
		
	case "class_modify": #班级修改
		$id = Filter::filter_number($_GET['id']);
		$T->ReadDB("select * from oa_zhsz_class where id={$id}"); 
		
		$params = array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$id}");
		$class  = $pd->fetchRow($params);
	
		$masterName = $pd->fetchOne(array('field'=>'truename','table'=>'v_users','where'=>"id='{$class['class_master']}'"));
		
		$year = substr($class['class_start'],0,2);
		$year = '20'.$year;
		$year = intval($year);
								
		$T->Set("masterName",$masterName); 
		$T->Set("class_year",$year); 
		$T->Set("grade",$class['grade_id']); 
		break;
		
	case "class_import": #班级导入
		break;
		
	case "master_import": #班主任导入
		break;
		
	case "graduate_class": #毕业班管理
		$start_page=isset($_GET["start_page"])?$_GET["start_page"]:0;
		$end_page=isset($_GET["end_page"])?$_GET["end_page"]:0;
		
		$strWhere = 'A.grade_id=0 and school='.$_SESSION["school"];
		$start	  = empty($_POST['class_start'])?'':$_POST['class_start'];
		$start	  = Filter::filter_html($start);
		$end	  = empty($_POST['class_end'])?'':$_POST['class_end'];
		$end	  = Filter::filter_html($end);
		if($start_page)
			$start = $start_page;
		if($end_page)
			$end = $end_page;
		//查询条件
		if(!empty($start)){
			$strWhere .= " AND A.class_start like '{$start}%' ";
		}
		if(!empty($end)){
			$strWhere .= " AND A.class_end like '{$end}%' ";
		}
		
		
		$where  = $strWhere;

		//查询所有班级
		$params = array('field'=>'count(A.id)','table'=>'oa_zhsz_class AS A','where'=>$where);
		$rc = $pd->fetchOne($params);
		
		$p=isset($_GET["p"])?$_GET["p"]:"0";  	   
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=graduate_class&end_page={$end}&start_page={$start}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("page1",$p);
		$T->Set("total",$rc); 
		$T->Set("start",$start); 
		$T->Set("end",$end); 
		break;
		
	case "select_manage": #选课管理
		//查询主分类
		$str_term = '';
		$term=$pd->query("select * from oa_zhsz_course_select where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($term as $rs){
			$str_term .= '<tr id="select_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['select_name'].'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			//if(isset($priv[2])){
				$str_term .= ' <img src="images/037.gif" /> <a href="./?t=select_modify&id='.$rs['id'].'">修改</a>';
			//}
			//if(isset($priv[3])){
				$str_term .= '<img src="images/010.gif" /> <a href="javascript:delSelect('.$rs['id'].');">删除</a>';
			//}
			$str_term .= '</td></tr>';
		}
		
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_course_select');
		$rc  = $pd->fetchOne($params);
		
		$T->Set("str_term",$str_term); 
		$T->Set("total",$rc); 
		break;
		
	case "select_add": #选课添加
		break;
		
	case "select_modify": #选课修改
		$id = Filter::filter_number($_GET['id']);
		$T->ReadDB("select * from oa_zhsz_course_select where id={$id}"); 
		break;
		
	case "course_manage": #课程管理
		//查询主分类
		$str_term = '';
		$term=$pd->query("select * from oa_zhsz_course where school=".$_SESSION["school"]." order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($term as $rs){
			$str_term .= '<tr id="course_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['course_name'].'</td><td class="tabtd">'.$rs['score'].'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			//if(isset($priv[2])){
				$str_term .= '<img src="images/037.gif" /> <a href="./?t=course_modify&id='.$rs['id'].'">修改</a> ';
			//}
			//if(isset($priv[3])){
				$str_term .= ' <img src="images/010.gif" /> <a href="javascript:delCourse('.$rs['id'].');">删除</a>';
			//}
			$str_term .= '</td></tr>';
		}
		
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_course');
		$rc  = $pd->fetchOne($params);
		
		$T->Set("total",$rc); 
		$T->Set("str_term",$str_term); 
		break; 
		
	case "course_add": #课程添加
		
		//查询课程排序最大值
		$params   = array('field'=>'max(order_value)','table'=>'oa_zhsz_course');
		$maxValue = $pd->fetchOne($params);
		$maxValue += 1;
		//添加模块
		$moduleHtml = '<tr><td class="tabtd2_L" width="5%">&nbsp;</td>';
		$moduleHtml .= '<td class="tabtd2_R">';
		$moduleHtml .= '<b>模块/专题名称：</b><input type="text" name="m_name_{orderValue}" value="" size="10" />';
		$moduleHtml .= '<br /><b>适应年级：</b>';
		//查询年级
		$i = 0;
		$grade  = $pd->query("select * from oa_zhsz_grade where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($grade as $rs){
			$checked = '';	
			if($i++==0){
				$checked = 'checked="checked"';	
			}
			$moduleHtml .= '<input type="radio" name="grade_{orderValue}" value="'.$rs['id'].'" '.$checked.' />&nbsp;'.$rs['grade_name'].'&nbsp;&nbsp;';	
		}
		$moduleHtml .= '<br /><b>适应学期：</b><input type="radio" name="term_{orderValue}" value="0" />&nbsp;全年&nbsp;&nbsp;';
		$moduleHtml .= '<input type="radio" name="term_{orderValue}" value="1" checked="checked" />&nbsp;第一学期&nbsp;&nbsp;';
		$moduleHtml .= '<input type="radio" name="term_{orderValue}" value="2" /> 第二学期<br /><b>适应选课：</b>';
		$moduleHtml .= '<input type="radio" name="select_{orderValue}" value="0" checked="checked" />&nbsp;全部&nbsp;&nbsp;';
		//查询年级
		$select  = $pd->query("select * from oa_zhsz_course_select where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($select as $rs){
			$moduleHtml .= '<input type="radio" name="select_{orderValue}" value="'.$rs['id'].'" />&nbsp;'.$rs['select_name'].'&nbsp;&nbsp;';	
		}
		$moduleHtml2 = $moduleHtml;
		$moduleHtml .= '</td></tr>';
		
		$str='';
		for($i=1;$i<=2;$i++){
			$str.=str_replace('{orderValue}',$i,$moduleHtml); 
		}
							
		$T->Set("str",$str); 
		$T->Set("moduleHtml2",$moduleHtml2); 
		$T->Set("moduleHtml",$moduleHtml); 
		$T->Set("maxValue",$maxValue); 
		break;
		
	case "course_modify": #课程修改
		$id = Filter::filter_number($_GET['id']);
		//查询课程信息
		$params = array('field'=>array('*'),'table'=>'oa_zhsz_course','where'=>"id={$id}");
		$course  = $pd->fetchRow($params);
		$T->ReadDB("select * from oa_zhsz_course where id={$id}"); 
		if(empty($course)){
			header('Location:./?t=course_manage');
			exit;
		}
		//查询课程模块
		
		$module  = $pd->query("select * from oa_zhsz_course_module where course_id={$course['id']} order by grade_id,term_type,select_course_id")->fetchAll(PDO::FETCH_ASSOC);
		
		$i = 1;
		$str_module = '';
        foreach($module as $rs){
			$term_type = $rs['term_type']==0?'全年':($rs['term_type']==1?'第一学期':'第二学期');
			$str_module .= '<tr id="course_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$i++.'</td><td class="tabtd">'.$rs['module_name'].'</td><td class="tabtd">'.$rs['grade_name'].'</td><td class="tabtd">'.$term_type.'</td><td class="tabtd">'.$rs['select_course_name'].'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			 //if(isset($priv[3])){
				 $str_module .= '<img src="images/010.gif" /> <a href="javascript:delDModule('.$rs['id'].','.$rs['course_id'].');">删除</a>';
			 //}
			 $str_module .= '</td></tr>';
		}

		//添加模块
		$moduleHtml = '<tr><td class="tabtd2_L" width="5%">&nbsp;</td>';
		$moduleHtml .= '<td class="tabtd2_R">';
		$moduleHtml .= '<b>模块/专题名称：</b><input type="text" name="m_name_{orderValue}" value="" size="10" />';
		$moduleHtml .= '<br /><b>适应年级：</b>';
		//查询年级
		$i = 0;
		$grade  = $pd->query("select * from oa_zhsz_grade where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($grade as $rs){
			$checked = '';	
			if($i++==0){
				$checked = 'checked="checked"';	
			}
			$moduleHtml .= '<input type="radio" name="grade_{orderValue}" value="'.$rs['id'].'" '.$checked.' />&nbsp;'.$rs['grade_name'].'&nbsp;&nbsp;';	
		}
		$moduleHtml .= '<br /><b>适应学期：</b><input type="radio" name="term_{orderValue}" value="0" />&nbsp;全年&nbsp;&nbsp;';
		$moduleHtml .= '<input type="radio" name="term_{orderValue}" value="1" checked="checked" />&nbsp;第一学期&nbsp;&nbsp;';
		$moduleHtml .= '<input type="radio" name="term_{orderValue}" value="2" /> 第二学期<br /><b>适应选课：</b>';
		$moduleHtml .= '<input type="radio" name="select_{orderValue}" value="0" checked="checked" />&nbsp;全部&nbsp;&nbsp;';
		//查询年级
		$select  = $pd->query("select * from oa_zhsz_course_select where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		foreach($select as $rs){
			$moduleHtml .= '<input type="radio" name="select_{orderValue}" value="'.$rs['id'].'" />&nbsp;'.$rs['select_name'].'&nbsp;&nbsp;';	
		}
		$moduleHtml2 = $moduleHtml;
		$moduleHtml .= '</td></tr>';
		//何种操作
		$op = empty($_GET['op'])?'course':$_GET['op'];
		
		$str='';
		for($i=1;$i<=2;$i++){
			$str.=str_replace('{orderValue}',$i,$moduleHtml); 
		}
		
		if($op=='course'){
			$s1 = '';
			$s2 = 'style="display:none;"';
		}else{
			$s1 = 'style="display:none;"';
			$s2 = '';
		}
							
		$T->Set("str_module",$str_module); 
		$T->Set("s1",$s1); 
		$T->Set("s2",$s2); 
		$T->Set("str",$str); 
		$T->Set("op",$op); 
		$T->Set("moduleHtml2",$moduleHtml2); 
		$T->Set("moduleHtml",$moduleHtml); 
		break;
		
	case "teach_manage": #任课管理
		//$T->SetBlock("list","select * from oa_zhsz_teach ");
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_teach','where'=>'school='.$_SESSION["school"]);
		$rc  = $pd->fetchOne($params);
		$p=isset($_GET["p"])?$_GET["p"]:"0";  	   
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=teach_manage");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		//查询主分类
		$str_term = '';
		$term=$pd->query("select * from oa_zhsz_teach where school=".$_SESSION["school"]." limit ".(($p-1)*20).",20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($term as $rs){
			$str_term .= '<tr id="teach_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$rs['id'].'</td><td class="tabtd">'.$rs['course_name'].'</td><td class="tabtd">'.$rs['truename'].'</td><td class="tabtd">'.$rs['class_name'].'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			//if(isset($priv[3])){
				$str_term .= '  <img src="images/010.gif" /> <a href="javascript:delTeach('.$rs['id'].');">删除</a>';
			//}
			$str_term .= '</td></tr>';
		}
		
		$T->Set("str_term",$str_term); 
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		break; 
		
	case "teach_add": #任课添加
		
		$T->SetBlock("list","select id,dept_name from oa_zhsz_dept where school=".$_SESSION["school"]);
		
		$T->SetBlock("list1","select id,truename,username from act_member a left join oa_user_extend b on a.id=b.userid where b.flag_type=2 and a.school=".$_SESSION["school"]." order by CONVERT(truename USING GBK)");
		
		$T->SetBlock("list2","select id,course_name from oa_zhsz_course where school=".$_SESSION["school"]." order by order_value");
		
		$T->SetBlock("list3","select id,grade_name from oa_zhsz_grade where school=".$_SESSION["school"]);
		
		$course  = $pd->query("select id,course_name from oa_zhsz_course where school=".$_SESSION["school"]." order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		$i = 0;
		$courseId = -1;
		foreach($course as $rs){
			if($i++==0){
				$courseId = $rs['id'];
			}
		}
									
		//查询年级信息
		$grade  = $pd->query("select id,grade_name from oa_zhsz_grade where school=".$_SESSION["school"])->fetchAll(PDO::FETCH_ASSOC);
		$i = 0;
		$gradeId = -1;
		foreach($grade as $rs){
			if($i++==0){
				$gradeId = $rs['id'];
			}
		}
		
		$T->SetBlock("list4","select id,class_name from oa_zhsz_class where grade_id={$gradeId} order by id");
		
		$T->Set("gradeId",$gradeId); 
		$T->Set("courseId",$courseId); 
		break;
		
	case "score_manage": #成绩管理
	    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
        //$p = $_POST["page"]?$_POST["page"]:1;		
		$p = ($p-1)*20;
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$et_page=isset($_GET["et_page"])?$_GET["et_page"]:"";

		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$et  = empty($_POST['exam_type'])?'':$_POST['exam_type'];
		$orderBy  = empty($_GET['order'])?'':$_GET['order'];

		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		if($et_page){
			$et = mb_convert_encoding($et_page,'gbk', 'utf-8' );
		}
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		//是否为任课老师
		if($_SESSION['role_id']==8){
			$class = $pd->query('select class_id,course_id from oa_zhsz_teach where user_id='.$_SESSION['uid'].' and school='.$_SESSION['school'].'')->fetchAll(PDO::FETCH_ASSOC);
			
			$courseid = $class[0]['course_id'];
			$classId = "";
			foreach($class as $v){
			  $classId .= ",".$v['class_id'];
			}        
			$gradeId = $pd->query("select grade_id from oa_zhsz_class where id in (".$classId.")")->fetchAll(PDO::FETCH_ASSOC);
			//测试年级数组组成
			$gradeId=array(array("grade_id"=>1),array("grade_id"=>2),array("grade_id"=>1));
			$grade=array();
			foreach ($gradeId as $k=>$v) {
			   if(!in_array($v['grade_id'],$grade)){
				 $grade[]=$v['grade_id'];
			   }
			   if(count($grade)==0){
				  $gid=0;
			   }else if(count($gradeId)==1){
				  $gid=$grade[0];
			   }else{
				  $gid=implode(',',$grade);
			   }
			}
		}

		//查询记录
		$strQuery = '';
		$strWhere = "  1=1 ";
		
		//查询所属于班级
		if(strpos($gid,",") == false){     
			if($gid>0){
				if($cid==0){
					$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid} and school={$_SESSION['school']}"));
					$classId  = join(',',$classId);
					$strWhere .= " and A.dept IN ({$classId}) ";
			  }else{
				$classId = $cid;
					$strWhere .= " and A.dept={$classId} ";
				}
			  }
		}else{
			$strWhere .= " and A.dept in ({$classId})";
		}
		  
		if(!empty($et)){
			$strWhere .= "  and B.exam_type='{$et}' ";
			$strQuery .= "&exam_type={$et}";
		}
		if(!empty($truename)){
			$strWhere .= "  and D.truename LIKE'{$truename}%'";
			$strQuery .= "&truename={$truename}";
		}
		if(!empty($tid)){
			$strWhere .= "  and B.term_id={$tid} ";
			$strQuery .= "&tid={$tid}";
		}
		$strQuery .= "&cid={$cid}";
		$strQuery .= "&gid={$gid}";
		$strQuery .= "&tid={$tid}";
		
		$order = ' order by CONVERT(D.truename USING GBK) ';
		if(!empty($orderBy)){
			if($orderBy=='sc_order'||$orderBy=='jc_order'){
				$order = " ORDER BY class_id,{$orderBy} ";
			}
			if($orderBy=='sg_order'||$orderBy=='jg_order'){
				$order = " ORDER BY {$orderBy} ";
			}
		}
		
		$where  = $strWhere;
		//查询所有成绩记录
		$sql 	= 'select A.userid ';
		$sql 	.= ' from oa_user_extend AS A ';
		$sql    .= ' inner join oa_zhsz_score as B ON A.userid=B.user_id inner join act_member AS D ON B.user_id=D.id';
		$sql 	.= " where {$where}";
		$scoreN = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($scoreN);

		$sqlcj = 'select D.truename,B.*,C.year,C.term_name ';
		$sqlcj 	.= ' from oa_user_extend AS A ';
		$sqlcj    .= ' inner join oa_zhsz_score as B ON A.userid=B.user_id ';
		$sqlcj    .= ' inner join oa_zhsz_term as C ON C.id=B.term_id  inner join act_member AS D ON B.user_id=D.id';
		$sqlcj 	.= " where {$where} ";
		$sqlcj 	.=  $order;
		$sqlcj 	.= " limit {$p},20";
		//echo $sqlcj;
		
		//成绩记录查询
		$score  = $pd->query($sqlcj)->fetchAll(PDO::FETCH_ASSOC);
		$str="";
		$num=0;
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==4){
			$isadmin=1;
		}else{
			$isadmin=0;
		}
		foreach($score as $k=>$rs){
			$score[$k]['yw'] = $rs['yw']==-1?'-':$rs['yw'];
			$score[$k]['sx'] = $rs['sx']==-1?'-':$rs['sx'];
			$score[$k]['wy'] = $rs['wy']==-1?'-':$rs['wy'];
			$score[$k]['wl'] = $rs['wl']==-1?'-':$rs['wl'];
			$score[$k]['hx'] = $rs['hx']==-1?'-':$rs['hx'];
			$score[$k]['sw'] = $rs['sw']==-1?'-':$rs['sw'];
			$score[$k]['zz'] = $rs['zz']==-1?'-':$rs['zz'];
			$score[$k]['ls'] = $rs['ls']==-1?'-':$rs['ls'];
			$score[$k]['dl'] = $rs['dl']==-1?'-':$rs['dl'];
			$score[$k]['xxjs'] = $rs['xxjs']==-1?'-':$rs['xxjs'];
			$score[$k]['tyjs'] = $rs['tyjs']==-1?'-':$rs['tyjs'];
			$score[$k]['ty'] = $rs['ty']==-1?'-':$rs['ty'];
			$score[$k]['yy'] = $rs['yy']==-1?'-':$rs['yy'];
			$score[$k]['ms'] = $rs['ms']==-1?'-':$rs['ms'];
			$score[$k]['xl'] = $rs['xl']==-1?'-':$rs['xl'];
			$score[$k]['sc_order'] = empty($rs['sc_order'])?'暂无名次':"第{$rs['sc_order']}名";
			$score[$k]['sg_order'] = empty($rs['sg_order'])?'暂无名次':"第{$rs['sg_order']}名";
			
			$str1="";
			$str3="";
			if($isadmin){
				$str1.='<td class="tabtd">'.$score[$k]['sc_order'].'</td><td class="tabtd">'.$score[$k]['sg_order'].'</td>';
			}
			
			if($isadmin){
				$str3.='<td class="tabtd" style="text-align:left; padding-left:10px;"><img src="images/037.gif" /> <a href="./?t=score_modify&id=".$id."">修改</a> <img src="images/010.gif" /> <a href="javascript:delScore(".$id.");">删除</a></td>';
			}
			
			$str .= '<tr><td class="tabtd">'.$rs['year'].'('.$rs['term_name'].')</td><td class="tabtd">'.$rs['exam_type'].'</td><td class="tabtd">'.$rs['truename'].'</td><td class="tabtd">'.$rs['class_name'].'</td><td class="tabtd">'.$score[$k]['yw'].'</td><td class="tabtd">'.$score[$k]['sx'].'</td><td class="tabtd">'.$score[$k]['wy'].'</td><td class="tabtd">'.$score[$k]['wl'].'</td><td class="tabtd">'.$score[$k]['hx'].'</td><td class="tabtd">'.$score[$k]['sw'].'</td><td class="tabtd">'.$score[$k]['zz'].'</td><td class="tabtd">'.$score[$k]['ls'].'</td><td class="tabtd">'.$score[$k]['dl'].'</td><td class="tabtd">'.$score[$k]['xxjs'].'</td><td class="tabtd">'.$score[$k]['tyjs'].'</td><td class="tabtd">'.$score[$k]['ty'].'</td><td class="tabtd">'.$score[$k]['yy'].'</td><td class="tabtd">'.$score[$k]['ms'].'</td><td class="tabtd">'.$score[$k]['xl'].'</td><td class="tabtd">'.$rs['szf'].'</td>'.$str1.''.$str3.'</tr>';
		}

		$str2 = "";
		if($isadmin){
			$str2='<td width="5%" class="tabtd"><a href="./?t=score_manage&order=sc_order{strQuery}" style="color:#f00;" title="点击进行排序">班级名次</a></td><td width="5%" class="tabtd"><a href="./?t=score_manage&order=sg_order{strQuery}" style="color:#f00;" title="点击进行排序">年级名次</a></td><td class="tabtd" width="5%">操作选项</td>';
		}
		
		$str_tr='<tr class="tabtitle" height="26"><td class="tabtd" width="6%">学期</td><td class="tabtd" width="4%">类型</td><td class="tabtd" width="5%">姓名</td><td class="tabtd" width="5%">班级</td><td class="tabtd">语文</td><td class="tabtd">数学</td><td class="tabtd">外语</td><td class="tabtd">物理</td><td class="tabtd">化学</td><td class="tabtd">生物</td><td class="tabtd">政治</td><td class="tabtd">历史</td><td class="tabtd">地理</td><td class="tabtd" width="4%">信息技术</td><td class="tabtd" width="4%">通用技术</td><td class="tabtd">体育</td><td class="tabtd">音乐</td><td class="tabtd">美术</td><td class="tabtd">心理</td><td class="tabtd">总分</td>'.$str2.'</tr>';
		
		$str_tr .= $str;
		$p=isset($_GET["p"])?$_GET["p"]:"0";  	   
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=score_manage&gid_page={$gid}&cid_page={$cid}&et_page={$et}&order={$orderBy}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("page1",$p);
		$T->Set("truename",$truename);
		$T->Set("tid",$tid);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("exam_type",$et);
		$T->Set("strQuery",$strQuery);
		$T->Set("orderBy",$orderBy);
		$T->Set("isadmin",$isadmin);
		$T->Set("str_tr",$str_tr);
		break; 
		
	case "report_add":  //自定义 报表添加
		$report = $T->query("SELECT id,code_name from  oa_zhsz_report where parent_no=''  and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		$str = '<option value=\'\'>—顶级—</option>';
		foreach($report as $rs){
			$str .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
		}
		$T->Set("str",$str);
		break; 
		
	case "report_modify": //自定义 报表编辑
		$id=isset($_GET["id"])?$_GET["id"]:""; 
		if(!empty($id)){
			$rs = $pd->query("select * from oa_zhsz_report where id=".$id)->fetch(PDO::FETCH_ASSOC);
			//$T->Set("code_no",$rs["code_no"]);
			$T->Set("code_name",$rs["code_name"]);
			$T->Set("order_value",$rs["order_value"]);
			$T->Set("content",$rs["content"]);
			$T->Set("id",$rs["id"]);
			$cCheck1 = 'checked="checked"';
			$cCheck2 = '';
			if($rs['flag_status']==0){
				$cCheck1 = '';
				$cCheck2 = 'checked="checked"';
			}
			$T->Set("ck1",$cCheck1);
			$T->Set("ck2",$cCheck2);
			
			$rCheck1 = 'checked="checked"';
			$rCheck2 = '';
			if($rs['is_partner']==0){
				$rCheck1 = '';
				$rCheck2 = 'checked="checked"';
			}
			$T->Set("rk1",$rCheck1);
			$T->Set("rk2",$rCheck2);
			
			$rCheck1 = 'checked="checked"';
			$rCheck2 = '';
			if($rs['is_att']==0){
				$rCheck1 = '';
				$rCheck2 = 'checked="checked"';
			}
			$T->Set("ra1",$rCheck1);
			$T->Set("ra2",$rCheck2);
			
			$rCheck1 = 'checked="checked"';
			$rCheck2 = '';
			if($rs['is_teacher']==0){
				$rCheck1 = '';
				$rCheck2 = 'checked="checked"';
			}
			$T->Set("rt1",$rCheck1);
			$T->Set("rt2",$rCheck2);
			
			$str_item = '';
			$item = $T->query("SELECT * from  oa_zhsz_report_item where report_id={$id}")->fetchAll(PDO::FETCH_ASSOC);
			foreach($item as $rs){
				$str_item .= "<tr><td nowrap class='tabtd2_L'>内容项：</td><td nowrap class='tabtd2_R'><input type='text' name='item_name[]' class='item_name' aid='{$rs['id']}' value='{$rs['item_name']}' class='BigInput' size='15' maxlength='100' value='' />&nbsp;&nbsp;<a href='javascript:void(0);' onclick='delItem(this,{$rs['id']});'>删除</a></td></tr>";
			}
			$T->Set("str_item",$str_item);
		
		}
		break;
		
	case "report_manage":
		$T->SetBlock("all_code","select id,code_name from oa_zhsz_report where  parent_no='' and school=".$_SESSION["school"]." order by order_value");

		$p=isset($_GET["p"])?$_GET["p"]:"1"; 
        $g=isset($_GET["g"])?$_GET["g"]:""; 
		$wh=" and school=".$_SESSION["school"];
        if(!empty($g))
			$wh.=" and parent_no='".$g."' or id='".$g."'"; 
		
		if($p<1)$p=1;
		$rc= $T->db->query("select count(1) from oa_zhsz_report where 1=1 ".$wh)->fetchColumn(0);
		$page=getPageHtml_bt($rc,30,$p,"&t=report_manage");

		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("page",$page); 
		$T->SetBlock("codelist","select * from oa_zhsz_report where 1=1 ".$wh." order by order_value limit ".(($p-1)*30).",30");	   		
		break;
		
	case "report_self":  //自我评价
		$term_id   = isset($_POST['grade_id'])?Filter::filter_html($_POST['grade_id']):"";
		$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		//查询该学生所在班级的班主任
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));

		$master_val=$class['class_master'];
		//echo $master_val;
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_report_type = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			if($rs['code_name']!="日常表现"){
				$str_report_type .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
				$str_content .= '<div class="ha_content ha_stext _ha_content"><label>说&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</label><div class="ha_cont"><pre>'.$rs['content'].'</pre></div></div>';
			}
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		
		$where  = "uid='{$_SESSION['uid']}'";
		if($term_id)
			$where  .= " and term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and subject_id='.$report_type.'';
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$str_main = '';
		$main = $pd->query("SELECT * from oa_zhsz_report_self where {$where} order by create_time desc   limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$cur_term = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'id='.$rs['term_id'].' and school='.$_SESSION['school']));
			
			$if_edit = '';  //是否可以编辑
			if($curTerm['id'] == $rs['term_id'] && $rs['flag_status']=="待审核"){
				$if_edit = '&nbsp;&nbsp; <img src="images/037.gif" /> <a aid="'.$rs['id'].'" class="caozuo" href="#" style="display:inline-block;">修改</a>';
			}
			
			$grade = '';
			foreach($term as $rs1){    //判断年级
				if($rs['term_id'] == $rs1['id'])
					$grade = $rs1['term'];
			}
				
			$str_main .= '<tr class="ms_info"><td class="content" width="17%">'.$rs['subject'].'</td><td class="topic_name" width="7%">'.$grade.'</td><td class="content" width="13%">'.$cur_term['year'].'('.$cur_term['term_name'].')</td><td class="content" width="38%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="19%"><div class="ha_tacontent">'.$rs['note'].'</div></td><td  width="7%" class="tabtd" align="left">'.$if_edit.'</td></tr>';
		}
		
		$total = $pd->query("SELECT * from oa_zhsz_report_self where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=report_self");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_main",$str_main);
		$T->Set("str_report",$str_report);
		$T->Set("str_term",$str_term);
		$T->Set("str_report_type",$str_report_type);
		$T->Set("str_content",$str_content);
		$T->Set("report_type",$report_type);
		$T->Set("term_id",$term_id);
		$T->Set("master_val",$master_val);
		$T->Set("uid",$uid);
		break;
		
		case "report_self_add":  //自我评价
		$term_id   = isset($_POST['grade_id'])?Filter::filter_html($_POST['grade_id']):"";
		$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		//查询该学生所在班级的班主任
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));

		$master_val=$class['class_master'];
		//echo $master_val;
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_report_type = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']}")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			if($rs['code_name']!="日常表现"){
				$str_report_type .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
				$str_content .= '<div class="ha_content ha_stext _ha_content"><label>说&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</label><div class="ha_cont"><pre>'.$rs['content'].'</pre></div></div>';
			}
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		
		$where  = "uid='{$_SESSION['uid']}'";
		if($term_id)
			$where  .= " and term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and subject_id='.$report_type.'';
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$str_main = '';
		$main = $pd->query("SELECT * from oa_zhsz_report_self where {$where} order by create_time desc   limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$cur_term = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'id='.$rs['term_id'].' and school='.$_SESSION['school']));
			
			$if_edit = '';  //是否可以编辑
			if($curTerm['id'] == $rs['term_id'] && $rs['flag_status']=="待审核"){
				$if_edit = '&nbsp;&nbsp; <img src="images/037.gif" /> <a aid="'.$rs['id'].'" class="caozuo" href="#" style="display:inline-block;">修改</a>';
			}
			
			$grade = '';
			foreach($term as $rs1){    //判断年级
				if($rs['term_id'] == $rs1['id'])
					$grade = $rs1['term'];
			}
				
			$str_main .= '<tr class="ms_info"><td class="content" width="17%">'.$rs['subject'].'</td><td class="topic_name" width="7%">'.$grade.'</td><td class="content" width="13%">'.$cur_term['year'].'('.$cur_term['term_name'].')</td><td class="content" width="38%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="19%"><div class="ha_tacontent">'.$rs['note'].'</div></td><td  width="7%" class="tabtd" align="left">'.$if_edit.'</td></tr>';
		}
		
		$total = $pd->query("SELECT * from oa_zhsz_report_self where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=report_self");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_main",$str_main);
		$T->Set("str_report",$str_report);
		$T->Set("str_term",$str_term);
		$T->Set("str_report_type",$str_report_type);
		$T->Set("str_content",$str_content);
		$T->Set("report_type",$report_type);
		$T->Set("term_id",$term_id);
		$T->Set("master_val",$master_val);
		$T->Set("uid",$uid);
		break;
		
		
	case "report":  //自定义评价
		$term_id   = isset($_POST['grade_id'])?Filter::filter_html($_POST['grade_id']):"";
		$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		$rid   = isset($_POST['rid'])?Filter::filter_html($_POST['rid']):"";

		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		$term   = showTerm($pd,$uid);	
		
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		$where  = "uid='{$_SESSION['uid']}'";
		if($term_id)
			$where  .= " and term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and subject_id='.$report_type.'';
		if($rid)
			$where  .= ' and subject_son_id='.$rid.'';
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";
		if($from_id){
			$where = "id={$from_id}";
		}
		$str_main = '';
		$main = $pd->query("SELECT * from oa_zhsz_report_custom where {$where} order by create_time desc  limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$cur_term = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'id='.$rs['term_id'].' and school='.$_SESSION['school']));
			
			$if_edit = '';  //是否可以编辑
			if($curTerm['id'] == $rs['term_id'] && ($rs['flag_status']=="待审核"||$rs['flag_status']=="未通过")){
				$if_edit = '&nbsp;&nbsp; <img src="images/037.gif" /> <a aid="'.$rs['id'].'" id="caozuo" href="./?t=report_custom_edit&id='.$rs['id'].'" style="display:inline-block;">修改</a>';
			}
			
			$grade = '';
			foreach($term as $rs1){    //判断年级
				if($rs['term_id'] == $rs1['id'])
					$grade = $rs1['term'];
			}
			
			$str_main .= '<tr class="ms_info"><td class="content" width="17%">'.$rs['subject'].'</td><td class="content" width="17%">'.$rs['subject_son'].'</td><td class="topic_name" width="8%">'.$grade.'</td><td class="content"  width="50%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td width="8%" class="tabtd" align="left">'.$if_edit.'</td></tr>';
		}
		
		$total = $pd->query("SELECT * from oa_zhsz_report_custom where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=report");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_report",$str_report);
		$T->Set("str_main",$str_main);
		$T->Set("report_type",$report_type);
		$T->Set("term_id",$term_id);
		$T->Set("rid",$rid);
		break;
		
	case "report_custom_add":  //自定义评价添加
		if(!$_SESSION['dept']){
			$tips = array(
			   'tips' => '当前学生无班级信息，请重试或联系管理员。',
			    'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		
		$master_val=$class['class_master'];

		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			$str_content .= '<div class="ha_content ha_stext _ha_content s1_tips"><label>说&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</label><div class="ha_cont"><pre>'.$rs['content'].'</pre></div></div>';
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		$str_partner = '';
		$partner = $T->query("SELECT id,truename from act_member WHERE id <> '{$_SESSION['uid']}' and id in(SELECT userid from oa_user_extend WHERE dept=(select dept FROM oa_user_extend WHERE userid='{$uid}'))")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($partner as $rs){
			$str_partner .= '<p><input type="checkbox" name="partner" pname="'.$rs['truename'].'" value="'.$rs['id'].'" id="'.$rs['id'].'" /><label id="'.$rs['id'].'">'.$rs['truename'].'</label></p>';
		}
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_report",$str_report);
		$T->Set("str_content",$str_content);
		$T->Set("str_partner",$str_partner);
		$T->Set("master_val",$master_val);
		$T->Set("uid",$uid);
		break;
		
	case "report_custom_edit":  //自定义评价修改
		$id=$_GET["id"];
		
		if(!$id){
			$tips = array(
			   'tips' => '数据获取错误，请重试。',
			   'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			$str_content .= '<div class="ha_content ha_stext _ha_content s1_tips"><label>说&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</label><div class="ha_cont"><pre>'.$rs['content'].'</pre></div></div>';
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		$str_partner = '';   //查询全部班级同学
		$partner = $T->query("SELECT id,truename from act_member WHERE id <> '{$_SESSION['uid']}' and id in(SELECT userid from oa_user_extend WHERE dept=(select dept FROM oa_user_extend WHERE userid='{$uid}'))")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($partner as $rs){
			$str_partner .= '<p><input type="checkbox" name="partner" pname="'.$rs['truename'].'" value="'.$rs['id'].'" id="'.$rs['id'].'" /><label id="'.$rs['id'].'">'.$rs['truename'].'</label></p>';
		}
		
		$T->ReadDB("SELECT * from oa_zhsz_report_custom WHERE id={$id}"); 
		$custom = $T->query("SELECT * from oa_zhsz_report_custom WHERE id={$id}")->fetchAll(PDO::FETCH_ASSOC);
		
		$check1="";
		$check2="";
		if($custom[0]['is_captain']==1){
			$check1="checked";
		}else{
			$check2="checked";
		}
		
		$if_partner = 0;
		if(!empty($custom[0]['partner_id'])){
			$if_partner = 1;
		}
		
		$teacher = '';
		if(!empty($custom[0]['teacher_id'])){
			$teacher = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$custom[0]['teacher_id']}'"));
		}
		
		$custom_att = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$id} and type=1")->fetchAll(PDO::FETCH_ASSOC);  //查询上传的附件
		
		$path_name = (empty($custom_att[0]['path_name']) || !isset($custom_att[0]['path_name']))?"":$custom_att[0]['path_name'];
		$path_id = (empty($custom_att[0]['id']) || !isset($custom_att[0]['id']))?"":$custom_att[0]['id'];

		$custom_pic = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$id} and type=0")->fetchAll(PDO::FETCH_ASSOC);//查询上传的图片
		$str_pic = "";
		$i = rand();
		foreach($custom_pic as $rs){
			$str_pic .= "<div style='width:100px;float:left;margin-right:20px;text-align:center;' class=\"ha_eimglist\"><input type='hidden' id='PicUrl{$i}' name='PicUrl[]' value=''><input type='hidden' id='PicName{$i}' name='PicName[]' value=''><img id='preview{$i}' style='width:100px;height:100px;margin-top:20px;' src='{$rs['attachment']}'/><a id='del{$i}' onclick='delimg2({$i},1,{$rs['id']})' style='display:block;width:100%;line-height:25px;height:25px;cursor:pointer;'>删除</a></div>";
			$i++;
		}
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_report",$str_report);
		$T->Set("str_content",$str_content);
		$T->Set("str_partner",$str_partner);
		$T->Set("check1",$check1);
		$T->Set("check2",$check2);
		$T->Set("path_id",$path_id);
		$T->Set("path_name",$path_name);
		$T->Set("str_pic",$str_pic);
		$T->Set("if_partner",$if_partner);
		$T->Set("teacher",$teacher);
		break;
		
	case "message":
	    //’$submitMethod = $_SERVER["REQUEST_METHOD"];
		$read_select=isset($_POST["read_select"])?$_POST["read_select"]:"";
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$state=isset($_POST["state"])?$_POST["state"]:"";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		#查询消息状态
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
		//$status="";
		$r = $T->db->query("update oa_message set is_read={$read_select} where id in ({$ids})");
		//判断提交方式
		/*if($submitMethod=='POST'){
			$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
			$where="";
			if(!empty($_POST['status'])){
				$status=($_POST['status']=="1")?"已审核":"未通过";
				$where.="M.flag='".$status."' and ";
				$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where ".$where." M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20");
				$r = $T->db->query("update oa_message set is_read=1 where id in ({$ids})");
				$rc= $T->db->query("select count(1) from oa_message as M where ".$where." geter='{$_SESSION['uid']}'")->fetchColumn(0);
				$page=getPageHtml_bt($rc,20,$p,"&t=message");
		        $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		        $T->Set("page",$page); 
			}else{
				$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20");
				$r = $T->db->query("update oa_message set is_read={$_POST['read_select']} where id in ({$ids})");
				$rc= $T->db->query("select count(1) from oa_message as M where geter='{$_SESSION['uid']}'")->fetchColumn(0);
				$page=getPageHtml_bt($rc,20,$p,"&t=message");
		        $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		        $T->Set("page",$page); 
				Header("Location:./?t=message");
			}

		}else{*/
			$total = $T->query("select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
			$str_total = "";
			
			foreach($total as $rs){
				if($rs['menu2']==26){
					$str_href = "report&from_id={$rs['from_id']}";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_report_custom','where'=>"id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==34){
					$str_href = "remarks&from_id={$rs['from_id']}&jiyu_page=我的寄语";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_remarks','where'=>"id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==68){
					$str_href = "stu_jiangcheng&from_id={$rs['from_id']}";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_reward_punishment','where'=>"id='{$rs['from_id']}'"));
				}
				
				$str_total .= '<tr class="ms_info"><td width="5%"><input type="checkbox" name="id[]" id="'.$rs['id'].'" value="'.$rs['id'].'"/></td><td class="topic_name" width="15%" ><span class="check_cont" v="'.$rs['menuname1'].'">'.$rs['menuname1'].'-</span><span class="check_cont" v="'.$rs['menuname2'].'">'.$rs['menuname2'].'</span></td><td class="content" width="25%" style="text-align:left !important;"><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['subject'].'\'>类型：<span limit="20">'.$rs['subject'].'</span><br/></span></a><a href="./?t='.$str_href.'"></a><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['leaveword'].'\' style="float:left">内容：<span style="float:right;line-height:20px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['leaveword'].'</span><br/></span></a><span class="check_cont" v=\''.$rs['content'].'\' >原因：<span>'.$rs['content'].'</span><br/></span></td><td class="topic_name" width="15%">'.$rs['stime'].'</td><td class="flag_checked" width="10%">'.$flag_true.'</td><td class="read_checked" width="10%" is_read="'.$rs['is_read'].'"></td></tr>';
			}
			$rc= $T->db->query("select count(1) from oa_message as M where geter='{$_SESSION['uid']}'")->fetchColumn(0);
			$page=getPageHtml_bt($rc,20,$p,"&t=message");
		    $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		    $T->Set("page",$page); 
		//}
		
		$T->Set("uid",$_SESSION['uid']);
		$T->Set("str_total",$str_total);
		$T->Set("state",$state);
		break;
		
	case "message_admin":
	    //$submitMethod = $_SERVER["REQUEST_METHOD"];
		$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
		$stu_id=isset($_GET["stu_id"])?$_GET["stu_id"]:"";
		$read_select=isset($_POST["read_select"])?$_POST["read_select"]:"";
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		#查询消息状态
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		
		$where="oue.role_id=1";
			
			$r = $T->db->query("update oa_message set is_read={$read_select} where id in ({$ids})");
			if($stu_id==""){
				$total = $T->query("select cus.flag_status,M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,ozc.class_name,ozg.grade_name,A.truename as xname from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join oa_user_extend as oue on oue.userid=M.uid left join oa_zhsz_class as ozc on ozc.id=oue.dept left join oa_zhsz_grade as ozg on ozg.id=ozc.grade_id left join act_member as A on A.id=M.uid left join oa_zhsz_report_custom as cus on cus.id=M.from_id where ".$where." order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
				$rc= $T->db->query("select count(1) from oa_message as M left join oa_user_extend as oue on oue.userid=M.uid where ".$where." ")->fetchColumn(0);
			}else{
				$total = $T->query("select cus.flag_status,ozre.flag_status,M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,ozc.class_name,ozg.grade_name,A.truename as xname from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join oa_user_extend as oue on oue.userid=M.uid left join oa_zhsz_class as ozc on ozc.id=oue.dept left join oa_zhsz_grade as ozg on ozg.id=ozc.grade_id left join act_member as A on A.id=M.uid left join oa_zhsz_report_custom as cus on cus.id=M.from_id left join oa_zhsz_remarks as ozre on ozre.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id where ".$where." and M.uid='".$stu_id."' and M.flag='待审核' and (cus.flag_status='待审核' or ozre.flag_status='待审核' or ozrp.flag_status='待审核') order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
				$rc= $T->db->query("select count(1) from oa_message as M left join oa_user_extend as oue on oue.userid=M.uid left join oa_zhsz_report_custom as cus on cus.id=M.from_id left join oa_zhsz_remarks as ozre on ozre.id=M.from_id left join oa_zhsz_reward_punishment as ozrp on ozrp.id=M.from_id where ".$where." and M.uid='".$stu_id."' and M.flag='待审核' and (cus.flag_status='待审核' or ozre.flag_status='待审核' or ozrp.flag_status='待审核')")->fetchColumn(0);
			}
			$str_total = "";
			foreach($total as $rs){
				if($rs['menu2']==26){
					$str_href = "report_custom_check&from_id={$rs['from_id']}&type=2";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_report_custom','where'=>"id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==34){
					$str_href = "remarks&from_id={$rs['from_id']}&type=2";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_remarks','where'=>"id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==55){
					$str_href = "jiangcheng&from_id={$rs['from_id']}";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_reward_punishment','where'=>"id='{$rs['from_id']}'"));
				}
				$str_total .= '<tr class="ms_info"><td width="5%"><input type="checkbox" name="id[]" id="'.$rs['id'].'" value="'.$rs['id'].'"/></td><td width="10%">'.$rs['xname'].'<br/>'.$rs['grade_name'].$rs['class_name'].'</td><td class="topic_name" width="15%" ><span class="check_cont" v="'.$rs['menuname1'].'">'.$rs['menuname1'].'-</span><span class="check_cont" v="'.$rs['menuname2'].'">'.$rs['menuname2'].'</span></td><td class="content" width="25%" style="text-align:left !important;"><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['subject'].'\'>类型：'.$rs['subject'].'<br/></span></a><span class="check_cont" v=\''.$rs['leaveword'].'\' limit="20">原因：'.$rs['leaveword'].'<br/></span><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['content'].'\' style="float:left">内容：<span style="float:right;line-height:20px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['content'].'</span></span></a></td><td class="topic_name" width="15%">'.$rs['stime'].'</td><td class="flag_checked" width="10%">'.$flag_true.'</td><td class="read_checked" width="10%" is_read="'.$rs['is_read'].'"></td></tr>';
			}
			$page=getPageHtml_bt($rc,20,$p,"&t=message_admin");
		    $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		    $T->Set("page",$page); 
			
		//}
		$T->Set("uid",$_SESSION['uid']);
		$T->Set("str_total",$str_total);
		
	    break;	
	
   case "message_teacher":
		$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
		$stu_id=isset($_GET["stu_id"])?$_GET["stu_id"]:"";
	    //$submitMethod = $_SERVER["REQUEST_METHOD"];
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$stu_id=isset($_GET["stu_id"])?$_GET["stu_id"]:"";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		#查询消息状态
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		$read_select=isset($_POST["read_select"])?$_POST["read_select"]:"";
		$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		    $r = $T->db->query("update oa_message set is_read={$read_select} where id in ({$ids})");
			
			if($stu_id==""){
				$total = $T->query("select cus.flag_status,M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.uid left join oa_zhsz_report_custom as cus on cus.id=M.from_id where  M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
				$rc= $T->db->query("select count(1) from oa_message as M where geter='{$_SESSION['uid']}'")->fetchColumn(0);
			}else{
				$total = $T->query("select cus.flag_status,ozre.flag_status,M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.uid left join oa_zhsz_report_custom as cus on cus.id=M.from_id left join oa_zhsz_remarks as ozre on ozre.id=M.from_id where  M.geter='{$_SESSION['uid']}' and M.uid='".$stu_id."' and M.flag='待审核' and (cus.flag_status='待审核' or ozre.flag_status='待审核') order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
				$rc= $T->db->query("select count(1) from oa_message as M left join oa_zhsz_report_custom as cus on cus.id=M.from_id left join oa_zhsz_remarks as ozre on ozre.id=M.from_id where  M.geter='{$_SESSION['uid']}' and M.uid='".$stu_id."' and M.flag='待审核' and (cus.flag_status='待审核' or ozre.flag_status='待审核')")->fetchColumn(0);
			}
			$str_total = "";
			
			foreach($total as $rs){
				if($rs['menu2']==26){
					$str_href = "report_custom_check&from_id={$rs['from_id']}&type=2";
					//$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_report_custom','where'=>"id='{$rs['from_id']}'"));
					$flag_true = $pd->fetchOne(array('field'=>'flag','table'=>'oa_message','where'=>"from_id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==34){
					$str_href = "remarks&from_id={$rs['from_id']}&type=2";
					$flag_true = $pd->fetchOne(array('field'=>'flag','table'=>'oa_message','where'=>"from_id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==55){
					$str_href = "jiangcheng&from_id={$rs['from_id']}";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_reward_punishment','where'=>"id='{$rs['from_id']}'"));
				}
				$str_total .= '<tr class="ms_info"> <td width="5%"><input type="checkbox" name="id[]" id="'.$rs['id'].'" value="'.$rs['id'].'"/></td><td width="10%">'.$rs['truename'].'</td><td class="topic_name" width="15%" ><span class="check_cont" v="'.$rs['menuname1'].'">'.$rs['menuname1'].'-</span><span class="check_cont" v="'.$rs['menuname2'].'">'.$rs['menuname2'].'</span></td><td class="content" width="25%" style="text-align:left !important;"><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['subject'].'\'>类型：<span>'.$rs['subject'].'</span><br/></span></a><span class="check_cont" v=\''.$rs['leaveword'].'\' >原因：<span>'.$rs['leaveword'].'</span><br/></span><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['content'].'\'  style="float:left">内容：<span style="float:right;line-height:20px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['content'].'</span></span></a></td><td class="topic_name" width="15%">'.$rs['stime'].'</td><td class="flag_checked" width="10%">'.$flag_true.'</td><td class="read_checked" width="10%" is_read="'.$rs['is_read'].'"></td></tr>';
			}
			//$rc= $T->db->query("select count(1) from oa_message as M where geter='{$_SESSION['uid']}'")->fetchColumn(0);
			$page=getPageHtml_bt($rc,20,$p,"&t=message_teacher");
		    $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		    $T->Set("page",$page); 
			
		//}
		$T->Set("uid",$_SESSION['uid']);
		$T->Set("str_total",$str_total);
		
	    break;
		
	case "mess27":  //自我评价审核详情
		$id=$_GET["id"];
		//$from_id=$_GET["from_id"];
		if(!$id){
			$tips = array(
			   'tips' => '数据获取错误，请重试。',
			   'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_content = '';
		$str_flag = '';
		$report = $T->query("SELECT id,uid,title,content,flag,subject from oa_message where id=".$id)->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "{$rs['subject']}";
			$str_content .= '<textarea rows="3" cols="20" class="ha_einput" name="content" id="content" style="height:90px;">'.$rs['content'].'</textarea>';
			if($rs['flag']=='已审核'){
				$str_flag .='<input type="radio" name="zt" value="1" checked="checked"/> 已审核 &nbsp;&nbsp;<input type="radio" name="zt" value="2" /> 未通过 &nbsp;&nbsp;<input type="radio" name="zt" value="3"/> 待审核';
			}else if($rs['flag']=='未通过'){
				$str_flag .='<input type="radio" name="zt" value="1"/> 已审核 &nbsp;&nbsp;<input type="radio" name="zt" value="2" checked="checked"/> 未通过 &nbsp;&nbsp;<input type="radio" name="zt" value="3"/> 待审核';
			}else{
				$str_flag .='<input type="radio" name="zt" value="1"/> 已审核 &nbsp;&nbsp;<input type="radio" name="zt" value="2"/> 未通过 &nbsp;&nbsp;<input type="radio" name="zt" value="3" checked="checked"/> 待审核';
			}
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		$str_partner = '';   //查询全部班级同学
		$partner = $T->query("SELECT id,truename from act_member WHERE id <> '{$_SESSION['uid']}' and id in(SELECT userid from oa_user_extend WHERE dept=(select dept FROM oa_user_extend WHERE userid='{$uid}'))")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($partner as $rs){
			$str_partner .= '<p><input type="checkbox" name="partner" pname="'.$rs['truename'].'" value="'.$rs['id'].'" id="'.$rs['id'].'" /><label id="'.$rs['id'].'">'.$rs['truename'].'</label></p>';
		}
		
		$T->ReadDB("SELECT * from oa_zhsz_report_custom WHERE id={$id}"); 
		$custom = $T->query("SELECT * from oa_zhsz_report_custom WHERE id={$id}")->fetchAll(PDO::FETCH_ASSOC);
		
		$check1="";
		$check2="";
		/* if($custom[0]['is_captain']==1){
			$check1="checked";
		}else{
			$check2="checked";
		}
		
		$if_partner = 0;
		if(!empty($custom[0]['partner_id'])){
			$if_partner = 1;
		} */
		
		$custom_att = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$id} and type=1")->fetchAll(PDO::FETCH_ASSOC);  //查询上传的附件
		
		$path_name = (empty($custom_att[0]['path_name']) || !isset($custom_att[0]['path_name']))?"":$custom_att[0]['path_name'];
		$path_id = (empty($custom_att[0]['id']) || !isset($custom_att[0]['id']))?"":$custom_att[0]['id'];
		$path_att = (empty($custom_att[0]['attachment']) || !isset($custom_att[0]['attachment']))?"":$custom_att[0]['attachment'];

		$custom_pic = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$id} and type=0")->fetchAll(PDO::FETCH_ASSOC);//查询上传的图片
		$str_pic = "";
		$i = rand();
		foreach($custom_pic as $rs){
			$str_pic .= "<div style='width:100px;float:left;margin-right:20px;text-align:center;' class=\"ha_eimglist\"><input type='hidden' id='PicUrl{$i}' name='PicUrl[]' value=''><input type='hidden' id='PicName{$i}' name='PicName[]' value=''><img id='preview{$i}' style='width:100px;height:100px;margin-top:20px;' src='{$rs['attachment']}'/><a id='del{$i}' onclick='delimg2({$i},1,{$rs['id']})' style='display:block;width:100%;line-height:25px;height:25px;cursor:pointer;'>删除</a></div>";
			$i++;
		}
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_report",$str_report);
		$T->Set("str_content",$str_content);
		$T->Set("str_partner",$str_partner);
		$T->Set("check1",$check1);
		$T->Set("check2",$check2);
		$T->Set("path_id",$path_id);
		$T->Set("path_name",$path_name);
		$T->Set("str_pic",$str_pic);
		$T->Set("str_flag",$str_flag);
		$T->Set("path_att",$path_att);
		break;
	
	case "mess26":  //自定义评价审核详情
		$id=$_GET["id"];
		$from_id=$_GET["from_id"];
		if(!$id){
			$tips = array(
			   'tips' => '数据获取错误，请重试。',
			   'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']}")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			$str_content .= '<div class="ha_content ha_stext _ha_content s1_tips"><label>说&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</label><div class="ha_cont"><pre>'.$rs['content'].'</pre></div></div>';
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		$str_partner = '';   //查询全部班级同学
		$partner = $T->query("SELECT id,truename from act_member WHERE id <> '{$_SESSION['uid']}' and id in(SELECT userid from oa_user_extend WHERE dept=(select dept FROM oa_user_extend WHERE userid='{$uid}'))")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($partner as $rs){
			$str_partner .= '<p><input type="checkbox" name="partner" pname="'.$rs['truename'].'" value="'.$rs['id'].'" id="'.$rs['id'].'" /><label id="'.$rs['id'].'">'.$rs['truename'].'</label></p>';
		}
		
		$T->ReadDB("SELECT * from oa_zhsz_report_custom WHERE id={$from_id}"); 

		$custom = $T->query("SELECT * from oa_zhsz_report_custom WHERE id={$from_id}")->fetchAll(PDO::FETCH_ASSOC);
		
		$check1="";
		$check2="";
		if($custom[0]['is_captain']==1){
			$check1="checked";
		}else{
			$check2="checked";
		}
		
		$if_partner = 0;
		if(!empty($custom[0]['partner_id'])){
			$if_partner = 1;
		}
		
		$custom_att = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$from_id} and type=1")->fetchAll(PDO::FETCH_ASSOC);  //查询上传的附件
		
		$path_name = (empty($custom_att[0]['path_name']) || !isset($custom_att[0]['path_name']))?"":$custom_att[0]['path_name'];
		$path_id = (empty($custom_att[0]['id']) || !isset($custom_att[0]['id']))?"":$custom_att[0]['id'];
		$path_att = (empty($custom_att[0]['attachment']) || !isset($custom_att[0]['attachment']))?"":$custom_att[0]['attachment'];

		$custom_pic = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$from_id} and type=0")->fetchAll(PDO::FETCH_ASSOC);//查询上传的图片
		$str_pic = "";

		$i = rand();
		foreach($custom_pic as $rs){
			$str_pic .= "<div style='width:100px;float:left;margin-right:20px;text-align:center;' class=\"ha_eimglist\"><input type='hidden' id='PicUrl{$i}' name='PicUrl[]' value=''><input type='hidden' id='PicName{$i}' name='PicName[]' value=''><img id='preview{$i}' style='width:100px;height:100px;margin-top:20px;' src='{$rs['attachment']}'/><a id='del{$i}' onclick='delimg2({$i},1,{$rs['id']})' style='display:block;width:100%;line-height:25px;height:25px;cursor:pointer;'>删除</a></div>";
			$i++;
		}
		
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_report",$str_report);
		$T->Set("str_content",$str_content);
		$T->Set("str_partner",$str_partner);
		$T->Set("check1",$check1);
		$T->Set("check2",$check2);
		$T->Set("path_id",$path_id);
		$T->Set("path_name",$path_name);
		$T->Set("str_pic",$str_pic);
		$T->Set("if_partner",$if_partner);
		$T->Set("path_att",$path_att);
		$T->Set("uid",$uid);
		break;
	
	case "report_self_check":  //自我评价审核
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$value = empty($_POST['but_op'])?'':$_POST['but_op'];
		//判断提交方式
		if($submitMethod=='POST' && $value=="确定"){
			$id     = $_POST['id'];
			$op     = $_POST['op'];
			$ids = join(',',$id);
			if($op==1){//审核
				$data = array('flag_status'=>'已审核');
			}
			if($op==2){//取消审核
				$data = array('flag_status'=>'待审核');
			}
			$r = $pd->update(array('data'=>$data,'table'=>"oa_zhsz_report_self",'where'=>"id IN ({$ids})"));
			if($r){
				$tips = array(
					'tips' => '审核操作成功。',
					'url'  => './?t=report_self_check'
				);
			}else{
				$tips = array(
					'tips' => '审核操作失败。',
					'url'  => './?t=report_self_check'
				);
			}
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		$term_id   = isset($_POST['term_id'])?Filter::filter_html($_POST['term_id']):"";
		$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		
		$str_report = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			if($rs['code_name']!="日常表现"){
				$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			}
		}
		
		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by year DESC,term_type DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= '<option value="'.$rs['id'].'">'.$rs['year'].'('.$rs['term_name'].')</option>';
		}
		
		$str_grade = '';
		$grades=$pd->query("select id,grade_name from oa_zhsz_grade where school={$_SESSION['school']}")->fetchAll(PDO::FETCH_ASSOC);
		foreach($grades as $rs){
			$str_grade .= '<option value="'.$rs['id'].'">'.$rs['grade_name'].'</option>';
		}

		$where  = "1=1";
		if($term_id)
			$where  .= " and A.term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and A.subject_id='.$report_type.'';
		
		if($truename)
			$where  .= " and B.truename LIKE'{$truename}%' ";
		
		if($gid>0){
			//查询所属于班级
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid} and school={$_SESSION['school']}"));
				$classId  = join(',',$classId);
				$where .= " and dept IN ({$classId}) ";
			}else{
				
				$classId  = $cid;
				$where .= " and dept={$classId} ";
			}
		}
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$str_main = '';
		$main = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_self as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by create_time desc   limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$grade = $pd->query("SELECT class_name,(SELECT grade_name from oa_zhsz_grade WHERE id=grade_id) as grade_name from oa_zhsz_class WHERE id={$rs['dept']}")->fetchAll(PDO::FETCH_ASSOC);
			$str_main .= '<tr class="ms_info"><td class="tabtd"><input type="checkbox" name="id[]" id="id_'.$rs['id'].'" value="'.$rs['id'].'" /></td><td class="content" width="7%">'.$rs['truename'].'</td><td class="content" width="7%">'.$grade[0]['grade_name'].$grade[0]['class_name'].'</td><td class="content" width="15%">'.$rs['subject'].'</td><td class="content" width="34%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="17%"><div class="ha_tacontent">'.$rs['note'].'</div></td><td class="content" width="6%">'.$rs['flag_status'].'</td><td  width="8%" class="tabtd" align="left"><a 
			id="shtg" href="javascript:void(0);"
			class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['uid'].'\',\''.$rs['subject'].'\');"></a>&nbsp;<a   
			id="shwtg" href="javascript:void(0);"
			class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['uid'].'\',\'未通过\',\''.$rs['subject'].'\');" style="width:15px;overflow:hidden;margin:0 6px 0 5px ;">&nbsp;</a><span class="caozuo" aid="'.$rs['id'].'" title="查看详情"><a class="glyphicon glyphicon-zoom-in" style="width:14px;overflow:hidden;">&nbsp;</a></span></td></tr>';
		}
		
		$total = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_self as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=report_self_check");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		
		$T->Set("str_report",$str_report);
		$T->Set("str_term",$str_term);
		$T->Set("str_grade",$str_grade);
		$T->Set("str_main",$str_main);
		$T->Set("truename",$truename);
		$T->Set("term_id",$term_id);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("uid",$uid);
		$T->Set("report_type",$report_type);
		break;
		
	case "report_custom_check":  //自定义评价审核
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$value = empty($_POST['but_op'])?'':$_POST['but_op'];
		//判断提交方式
		if($submitMethod=='POST' && $value=="确定"){
			$menu1 = 0;
			$menu2 = 26;

			$geter     = $_POST['uid'];
			$id     = $_POST['id'];
			$op     = $_POST['op'];
			$ids = join(',',$id);
			if($op==1){//审核
				$data = array('flag_status'=>'已审核');
			}
			if($op==2){//取消审核
				$data = array('flag_status'=>'待审核');
			}
			$r = $pd->update(array('data'=>$data,'table'=>"oa_zhsz_report_custom",'where'=>"id IN ({$ids})"));
			
			if($r){
				$tips = array(
					'tips' => '审核操作成功。',
					'url'  => './?t=report_custom_check'
				);
				for($i=0;$i<count($id);$i++){
					$pd->query("insert into oa_message(uid,geter,leaveword,stime,menu1,menu2,is_read,flag,subject,from_id) select '".$_SESSION['uid']."'  ,'".$geter[$i]."',(select content from oa_zhsz_report_custom where id=".$id[$i]."),'".date('Y-m-d H:i:s',time())."','".$menu1."','".$menu2."',0,(select flag_status from oa_zhsz_report_custom where id=".$id[$i]."),(select subject from oa_zhsz_report_custom where id=".$id[$i]."),".$id[$i]." from oa_zhsz_report_custom where id=".$id[$i]."");
				}
			}else{
				$tips = array(
					'tips' => '审核操作失败。',
					'url'  => './?t=report_custom_check'
				);
			}
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		
		$where  = "1=1";
		$type=isset($_GET["type"])?$_GET["type"]:"";    //判断班主任页面跳转
		$from_id = "";   //消息页面精确跳转定位变量
		if($type==1){   //班主任首页
			$report_type=isset($_GET["report_type"])?$_GET["report_type"]:"";
			$where .= " and A.flag_status='待审核' ";
		}elseif($type==2){ //消息页面
			$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
			$from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";
		}else{
			$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		}
		
		$term_id   = isset($_POST['term_id'])?Filter::filter_html($_POST['term_id']):"";
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$rid   = isset($_POST['rid'])?Filter::filter_html($_POST['rid']):"";
		
		
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		
		$str_report = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
				$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
		}
		
		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by year DESC,term_type DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= '<option value="'.$rs['id'].'">'.$rs['year'].'('.$rs['term_name'].')</option>';
		}
		
		$str_grade = '';
		$grades=$pd->query("select id,grade_name from oa_zhsz_grade where school={$_SESSION['school']}")->fetchAll(PDO::FETCH_ASSOC);
		foreach($grades as $rs){
			$str_grade .= '<option value="'.$rs['id'].'">'.$rs['grade_name'].'</option>';
		}

		
		if($term_id)
			$where  .= " and A.term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and A.subject_id='.$report_type.'';
		
		if($truename)
			$where  .= " and B.truename LIKE'{$truename}%' ";
		
		if($rid)
			$where  .= ' and A.subject_son_id='.$rid.'';
		
		//是否为班主任
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
		}
		
		if($gid>0){
			//查询所属于班级
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid} and school={$_SESSION['school']}"));
				$classId  = join(',',$classId);
				$where .= " and dept IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$where .= " and dept={$classId} ";
			}
		}
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		if($_SESSION['role_id']!=4&&$_SESSION['role_id']!=5&&$_SESSION['role_id']!=6){
			$where = " A.teacher_id='{$_SESSION['uid']}'";
		}
		
		if($from_id){
			$where = " A.id={$from_id}";
		}

		$str_main = '';
		$main = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_custom as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by create_time desc   limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$grade = $pd->query("SELECT class_name,(SELECT grade_name from oa_zhsz_grade WHERE id=grade_id) as grade_name from oa_zhsz_class WHERE id={$rs['dept']}")->fetchAll(PDO::FETCH_ASSOC);
			if(empty($grade)){
				$grade[0]['grade_name'] = '';
				$grade[0]['class_name'] = '';
			}
			$str_main .= '<tr class="ms_info"><td class="tabtd"><input type="checkbox" name="id[]" id="id_'.$rs['id'].'" value="'.$rs['id'].'" /></td><td class="tabtd" style="display:none;"><input type="checkbox" name="uid[]" uid="uid_'.$rs['uid'].'" value="'.$rs['uid'].'" /></td><td class="content" width="7%">'.$rs['truename'].'</td><td class="content" width="7%">'.$grade[0]['grade_name'].$grade[0]['class_name'].'</td><td class="content" width="15%">'.$rs['subject'].'</td><td class="content" width="15%">'.$rs['subject_son'].'</td><td class="content" width="39%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="6%">'.$rs['flag_status'].'</td><td  width="8%" class="tabtd" align="left"><a 
			id="shtg" href="javascript:void(0);"
			class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['uid'].'\',\''.$rs['subject'].'\');"></a>&nbsp;<a   
			id="shwtg" href="javascript:void(0);"
			class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['uid'].'\',\'未通过\',\''.$rs['subject'].'\');" style="width:15px;overflow:hidden;margin:0 6px 0 5px ;">&nbsp;</a><span class="caozuo" title="查看详情"><a aid="'.$rs['id'].'" href="./?t=report_custom_check_detail&id='.$rs['id'].'" class="glyphicon glyphicon-zoom-in" style="width:14px;overflow:hidden;"  target="_blank">&nbsp;</a></span></td></tr>';
		}
		
		$total = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_custom as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=report_self_check");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		if($_SESSION['role_id']==4){
			$is_admin = 1;
		}else{
			$is_admin = 0;
		}
			
		$T->Set("str_report",$str_report);
		$T->Set("str_term",$str_term);
		$T->Set("str_grade",$str_grade);
		$T->Set("str_main",$str_main);
		$T->Set("truename",$truename);
		$T->Set("term_id",$term_id);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("report_type",$report_type);
		$T->Set("rid",$rid);
		$T->Set("is_admin",$is_admin);
		break;
		
	case "report_custom_check_detail":  //自定义评价审核详情
		$id=$_GET["id"];
		
		if(!$id){
			$tips = array(
			   'tips' => '数据获取错误，请重试。',
			   'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
	   
		$uid=$_SESSION['uid'];
		
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			$str_content .= '<div class="ha_content ha_stext _ha_content s1_tips"><label style="width:8.1%!important;">说&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;明：</label><div class="ha_cont"><pre>'.$rs['content'].'</pre></div></div>';
		}

		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by flag_default DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= "<option value='{$rs['id']}'>{$rs['year']}({$rs['term_name']})</option>";
		}
		
		$str_partner = '';   //查询全部班级同学
		$partner = $T->query("SELECT id,truename from act_member WHERE id <> '{$_SESSION['uid']}' and id in(SELECT userid from oa_user_extend WHERE dept=(select dept FROM oa_user_extend WHERE userid='{$uid}'))")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($partner as $rs){
			$str_partner .= '<p><input type="checkbox" name="partner" pname="'.$rs['truename'].'" value="'.$rs['id'].'" id="'.$rs['id'].'" /><label id="'.$rs['id'].'">'.$rs['truename'].'</label></p>';
		}
		
		$T->ReadDB("SELECT * from oa_zhsz_report_custom WHERE id={$id}"); 
		$report_custom = $pd->query("SELECT * from oa_zhsz_report_custom WHERE id={$id}")->fetchAll(PDO::FETCH_ASSOC);
		if($report_custom){
			$report_item = $report_custom[0]['items'];
			//$report_item = mb_convert_encoding($report_item, 'utf-8', 'gbk');
			$T->Set("report_item",$report_item);
		}
		
		$custom = $T->query("SELECT * from oa_zhsz_report_custom WHERE id={$id}")->fetchAll(PDO::FETCH_ASSOC);
		
		$teacher = '';
		if(!empty($custom[0]['teacher_id'])){
			$teacher = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$custom[0]['teacher_id']}'"));
		}
		
		$check1="";
		$check2="";
		if($custom[0]['is_captain']==1){
			$check1="checked";
		}else{
			$check2="checked";
		}
		
		$if_partner = 0;
		if(!empty($custom[0]['partner_id'])){
			$if_partner = 1;
		}
		
		$custom_att = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$id} and type=1")->fetchAll(PDO::FETCH_ASSOC);  //查询上传的附件
		
		$path_name = (empty($custom_att[0]['path_name']) || !isset($custom_att[0]['path_name']))?"":$custom_att[0]['path_name'];
		$path_id = (empty($custom_att[0]['id']) || !isset($custom_att[0]['id']))?"":$custom_att[0]['id'];
		$path_att = (empty($custom_att[0]['attachment']) || !isset($custom_att[0]['attachment']))?"":$custom_att[0]['attachment'];

		$custom_pic = $T->query("SELECT * from oa_zhsz_attachment WHERE custom_id={$id} and type=0")->fetchAll(PDO::FETCH_ASSOC);//查询上传的图片
		$str_pic = "";
		$i = rand();
		foreach($custom_pic as $rs){
			$str_pic .= "<div style='width:100px;float:left;margin-right:20px;text-align:center;' class=\"ha_eimglist\"><input type='hidden' id='PicUrl{$i}' name='PicUrl[]' value=''><input type='hidden' id='PicName{$i}' name='PicName[]' value=''><img id='preview{$i}' style='width:100px;height:100px;margin-top:20px;' src='{$rs['attachment']}'/><a id='del{$i}' onclick='delimg2({$i},1,{$rs['id']})' style='display:block;width:100%;line-height:25px;height:25px;cursor:pointer;'>删除</a></div>";
			$i++;
		}
		
		$T->Set("str_grade",$str_grade);
		$T->Set("str_report",$str_report);
		$T->Set("str_content",$str_content);
		$T->Set("str_partner",$str_partner);
		$T->Set("check1",$check1);
		$T->Set("check2",$check2);
		$T->Set("path_id",$path_id);
		$T->Set("path_name",$path_name);
		$T->Set("str_pic",$str_pic);
		$T->Set("if_partner",$if_partner);
		$T->Set("path_att",$path_att);
		$T->Set("teacher",$teacher);
		break;
		
	case "stu_modify": #学生信息
	      //平台基本信息
	       $params = array('field'=>array('flag_avatar','course_start','course_end','course_select'),'table'=>'oa_zhsz_system','where'=>"school='{$_SESSION['school']}'");
	       $web    = $pd->fetchRow($params);
		    
		   //print_r($web);
	         	//查询个人信息
           $params = array('field'=>array('*'),'table'=>'v_users','where'=>"userid='{$_SESSION['uid']}'");
           $user   = $pd->fetchRow($params);
		  
		   $class = $pd->query_ext("select a.class_name,b.id as grade_id,b.grade_name from oa_zhsz_class as a inner join oa_zhsz_grade as b on a.grade_id=b.id where a.id={$user['dept']} and b.school={$_SESSION['school']}");
		  
			if(empty($class)){
				$class['grade_name'] = '';
				$class['class_name'] = '暂无';
				$class['grade_id'] = 0;
			}
			//目前仅有角色为学生的才显示该菜单
			$str='';
			if($_SESSION['flag_type']==1){					
				$str='<label><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label>';
			
				//仅有高一学生才显示选课菜单
				if(!empty($user['dept'])){
					//查询年级
					if($class['grade_id']>1){
						//查询是否到选课时间
						if(!empty($web['course_start'])){
							
							$now = date('Y-m-d');
							$start = $web['course_start'];
							$end   = $web['course_end'];
							if(empty($web['course_end'])){
								//查询当前学期
								$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school'].''));
								$end = $curTerm['end'];
							}
							$str = '<label><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label><label><a href="./?t=stu_selectcourse&id='.$_SESSION['uid'].'">选课信息</a></label>';
						}
					}
				}
			}											
			//学生外的其它角色显示简单信息
			$str_total='';	
			$str_total1='';
			$str_total2='';
			if($_SESSION['flag_type']!=1){						
				$str_total='<form action="./srv/user_modify_submit.php" method="post" onsubmit="return checkUser();">
				<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">
					<tr>
						 <td width="20%" class="tabtd2_L">登录名：</td>
						 <td width="80%" class="tabtd2_R">
							 '.$user['username'].'&nbsp;
						</td>
					</tr>
					<tr>
						 <td width="20%" class="tabtd2_L">别名：</td>
						 <td width="80%" class="tabtd2_R"><input type="text" id="alias_name" name="alias_name" size="25" value=" '.$user['alias_name'].'" />&nbsp;&nbsp;<a href="javascript:void(0);" style="color:#990000;" onclick="checkAliasName();">检测别名是否存在</a></td>
					</tr>
					<tr>
						 <td width="20%" class="tabtd2_L">姓名：</td>
						 <td width="80%" class="tabtd2_R"><input type="text" id="truename" name="truename" size="25" value="'.$user['truename'].'" /></td>
					</tr>
				</table>
				<div class="boxfooter2">
					<input type="hidden" name="flag" id="flag" value="1" />
					<input name="but_add" id="but_add" type="submit" value="更新信息" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" /></div>
				</form>';
				
			}else{		
				//查询中考信息
				$middleScore = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_about_primary','where'=>"username='{$_SESSION['username']}'"));
				$examNo = '暂无信息';
				if(isset($middleScore['exam_no'])){
						$examNo = $middleScore['exam_no'];
					}
									
					$checked1 = 'checked="checked"';
					$checked2 = '';
					if($user['gender']=='男'){
						$checked1 = 'checked="checked"';
						$checked2 = '';
							}
					if($user['gender']=='女'){
						$checked1 = '';
						$checked2 = 'checked="checked"';
							}	
					$birthday = $pd->fetchOne(array('field'=>'birthday','table'=>'act_member','where'=>"id='{$user['userid']}'"));
					$str2 = empty($birthday)?'0000-00-00':$birthday;
					$str3 = empty($user['avatar'])?'images/nophoto.png':$user['avatar'];						     
					if(!empty($web['flag_avatar'])){							
					$str4='<br /><a href="./?t=avatar_modify">更改照片</a>';
							}else{
					$str4='';			
							}
							
					$str5='';
					$params = array('field'=>array('code_no','code_name'),'table'=>'oa_zhsz_code','where'=>'parent_no="NATION"');
					$codeCat = $pd->fetchAll($params);
					foreach($codeCat as $rs){
						$selected = '';
					if($rs['code_name']==$user['nation']){
						   $selected = 'selected="selected"';	
										}                                   
								 $str5.='<option value="'.$rs['code_name'].'"  '. $selected.'> '.$rs['code_name'].'</option>';
								
									}										
					$str6='';
					$params2 = array('field'=>array('code_no','code_name'),'table'=>'oa_zhsz_code','where'=>'parent_no="POLITICAL"');
					$codeCat2 = $pd->fetchAll($params2);
					foreach($codeCat2 as $rs2){
						$selected = '';
						 if($rs2['code_name']==$user['political_status']){
							$selected = 'selected="selected"';	
						}                          
						$str6.='<option value="'.$rs2['code_name'].'"  '.$selected.'> '.$rs2['code_name'].'</option>';                         
					}
										 
						//查询联系人信息
						$str7='';
						$linkmans = $pd->fetchAll(array('field'=>array('*'),'table'=>'oa_zhsz_relative','where'=>"user_id='{$user['userid']}'"));
						
						foreach($linkmans as $rs){						
							$str7.='<tr>
							<td width="8%" class="tabtd2_L">'.$rs['relation'].'</td>
							<td width="15%" class="tabtd2_R">'.$rs['truename'].'</td>
							<td width="8%" class="tabtd2_L">工作单位</td>
							<td class="tabtd2_R"> '.$rs['company'].'</td>
							<td width="8%" class="tabtd2_L">联系电话</td>
							<td width="15%" class="tabtd2_R">'.$rs['telphone'].'</td>
						  </tr>';
						}
						$str8='';
					
				  //查询中考信息
				if(empty($middleScore)){
					$str8='<tr><td class="tabtd2_R" colspan="10">暂无中考信息（<font color="#990000">请提交初中相关信息-准考证号不为空时才保存初中信息</font>）</td></tr>		
					<tr>
						<td colspan="10">
						  <table width="100%" border="0">
							  <tr>
								<td rowspan="2" class="tabtd2_L" style="text-align:center;">初中信息</td>
								<td class="tabtd2_L" style="text-align:center;">准考证号</td>
								<td class="tabtd2_L" style="text-align:center;">语文</td>
								<td class="tabtd2_L" style="text-align:center;">数学</td>
								<td class="tabtd2_L" style="text-align:center;">外语</td>
								<td class="tabtd2_L" style="text-align:center;">理化</td>
								<td class="tabtd2_L" style="text-align:center;">政史</td>
								<td class="tabtd2_L" style="text-align:center;">体育</td>
							</tr>
							  <tr>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_no" name="middle_no" size="15" value="" /></td>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_yw" name="middle_yw" size="5" value="" /></td>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_sx" name="middle_sx" size="5" value="" /></td>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_wy" name="middle_wy" size="5" value="" /></td>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_lh" name="middle_lh" size="5" value="" /></td>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_zs" name="middle_zs" size="5" value="" /></td>
								<td class="tabtd2_L" style="text-align:center;"><input type="text" id="middle_ty" name="middle_ty" size="5" value="" /></td>
							</tr>
							  <tr>
								<td class="tabtd2_L" style="text-align:center;">奖惩情况</td>
								<td colspan="7" class="tabtd2_L"  style="text-align:left;">
								<textarea cols="30" rows="5" id="middle_intro" name="middle_intro"></textarea>
								</td>
							</tr>
						</table></td>
					</tr>';          
				}else{						
					$str8='<tr>
						<td colspan="10">
						  <table width="100%" border="0">
							  <tr>
								<td rowspan="2" class="tabtd2_L" style="text-align:center;">中考成绩</td>
								<td class="tabtd2_L" style="text-align:center;">语文</td>
								<td class="tabtd2_L" style="text-align:center;">数学</td>
								<td class="tabtd2_L" style="text-align:center;">外语</td>
								<td class="tabtd2_L" style="text-align:center;">理化</td>
								<td class="tabtd2_L" style="text-align:center;">政史</td>
								<td class="tabtd2_L" style="text-align:center;">体育</td>
								<td class="tabtd2_L" style="text-align:center;">总分</td>
							</tr>
							  <tr>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['yw'].' </td>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['sx'].' </td>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['wy'].' </td>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['lh'].' </td>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['zs'].' </td>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['ty'].' </td>
								<td class="tabtd2_L" style="text-align:center;"> '.$middleScore['zf'].' </td>
							</tr>
							  <tr>
								<td class="tabtd2_L" style="text-align:center;">奖惩情况</td>
								<td colspan="7" class="tabtd2_L"  style="text-align:left;"> '.$middleScore['intro'].'</td>
							</tr>
						</table></td>
					</tr>'; 
				}
				$mobile = $pd->fetchOne(array('field'=>'mobile','table'=>'act_member','where'=>"id='{$user['userid']}'"));
				$email = $pd->fetchOne(array('field'=>'email','table'=>'act_member','where'=>"id='{$user['userid']}'"));
					
				$str_total='<form id="form1" action="./srv/user_modify_submit.php" method="post">
							<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">
							  <tr>
								<td width="8%" class="tabtd2_L">姓名</td>
								<td width="15%" class="tabtd2_R">'.$_SESSION['truename'].'&nbsp;</td>
								<td width="12%" class="tabtd2_L">性别</td>
								<td width="15%" class="tabtd2_R">                          		
									<input type="radio" name="gender" value="男" '.$checked1.' /> 男 
									<input type="radio" name="gender" value="女" '.$checked2.' /> 女 
								</td>
								<td width="8%" class="tabtd2_L">出生年月</td>
					<td width="15%" class="tabtd2_R"><input name="birth_date" id="birth_date" type="text" readonly="readonly" onClick="WdatePicker();" class="Wdate" style="height:30px" value="'.$str2.'" /></td>							
					<td colspan="2" rowspan="3" class="tabtd2_R" style="text-align:center; width:224px;height:196px"><img src="'.$str3.'" width="130" height="160" border="0" align="absmiddle" />
							'.$str4.'&nbsp;</td>
							  </tr>
							  <tr>
								<td width="8%" class="tabtd2_L">民族</td>
								<td width="15%" class="tabtd2_R">
									<select name="nation" id="nation">                 
											 '.$str5.'               
									</select>
								</td>
								<td width="12%" class="tabtd2_L">政治面貌</td>
								<td width="15%" class="tabtd2_R">
									<select name="political_status" id="political_status">
											  '.$str6.'                                  
									</select>
								</td>
								<td width="8%" class="tabtd2_L">毕业学校</td>
								<td width="15%" class="tabtd2_R"><input type="text" id="graduate_school" name="graduate_school" size="15" value = "'.$user['graduate_school'].'" /></td>
							  </tr>
							  <tr>
								<td width="8%" class="tabtd2_L">生源性质</td>
								<td width="15%" class="tabtd2_R"> '.$user['from_type'].'&nbsp;</td>
								<td width="12%" class="tabtd2_L">中考准考证号码</td>
								<td class="tabtd2_R"> '.$examNo.' &nbsp;</td>
								<td class="tabtd2_R">曾用名</td>
								<td class="tabtd2_R"><input type="text" id="used_name" name="used_name" size="15" value="'.$user['used_name'].'" /></td>
							  </tr>
							  <tr>
								<td width="8%" class="tabtd2_L">学籍号</td>
								<td width="15%" class="tabtd2_R">
									 '.$user['person_no'].'
								</td>
								<td width="12%" class="tabtd2_L">既往病史</td>
								<td colspan="3" class="tabtd2_R">
									<textarea cols="48" rows="1" name="medical_history" style="line-height:20px;"> '.$user['medical_history'].'</textarea>
								</td>
							  </tr>
							  <tr>
								<td width="8%" class="tabtd2_L">当前班级</td>
								<td width="15%" class="tabtd2_R">&nbsp; '.$class['grade_name'].','.$class['class_name'].'</td>
								<td width="12%" class="tabtd2_L">联系电话</td>
								<td width="15%" class="tabtd2_R"><input type="text" id="telphone" name="telphone" size="15" value="'.$mobile.'" /></td>
								<td width="8%" class="tabtd2_L">Email</td>
								<td width="15%" class="tabtd2_R"><input type="text" id="email" name="email" size="15" value="'.$email.'" /></td>
							  </tr>
							  <tr>
								<td width="8%" class="tabtd2_L">班级职务</td>
								<td class="tabtd2_R"><input type="text" id="duty" name="duty" size="15" value="'.$user['duty'].'" /></td>
								<td class="tabtd2_L">QQ</td>
								<td class="tabtd2_R"><input type="text" id="qq" name="qq" size="15" value="'.$user['qq'].'" /></td>
								<td class="tabtd2_R">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;家庭住址</td>
								<td colspan="4" class="tabtd2_R"><input type="text" id="address" name="address" size="35" value="'.$user['address'].'" />&nbsp;</td>
							  </tr>
							  <tr>
								<td colspan="10">
								<table cellpadding="0" cellspacing="0" border="0" width="100%">                          
									 '.$str7.'                 
							  </table>
								</td>
							</tr>	
						   
							 '.$str8.'
							</table>
							<div class="boxfooter2">
								<input type="hidden" name="flag" id="flag" value="2" />
							  <input name="but_add" id="" type="submit" value="更新信息" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" />
					</div>
				</form>';	
			}	
			$T->Set("str",$str);
			$T->Set("str2",$str2);
			$T->Set("str_total",$str_total);
			//$T->Set("str_total2",$str_total2);		
			break; 
	case "stu_contact": #学生_联系
		//平台基本信息
	       $params = array('field'=>array('flag_avatar','course_start','course_end','course_select'),'table'=>'oa_zhsz_system','where'=>"school='{$_SESSION['school']}'");
	       $web    = $pd->fetchRow($params);
		   //print_r($web);
		   
	         	//查询个人信息
           $params = array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
           $user   = $pd->fetchRow($params);
		   $class = $pd->query_ext("select a.class_name,b.id as grade_id,b.grade_name from oa_zhsz_class as a inner join oa_zhsz_grade as b on a.grade_id=b.id where a.id={$user['dept']}");
		 
		if(empty($class)){
			$class['grade_name'] = '';
			$class['class_name'] = '暂无';
			$class['grade_id'] = 0;
		}
	
		//目前仅有角色为学生的才显示该菜单
		$str='';
		if($_SESSION['flag_type']==1){					
		$str='<label class="current"><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label>';
		
			//仅有高一学生才显示选课菜单
			if(!empty($user['dept'])){
				//查询年级
				if($class['grade_id']>1){
					//查询是否到选课时间
					if(!empty($web['course_start'])){
						$str = '<label class="current"><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label><label><a href="./?t=stu_selectcourse&id='.$_SESSION['uid'].'">选课信息</a></label>';
					}
				}
			}
		}			
		 $T->Set("str",$str);
		 $T->Set("uid",$_SESSION['uid']);
		$T->SetBlock("list5","select * from `oa_zhsz_relative` where user_id='{$_SESSION['uid']}'"); 
		break;
		
	case "avatar_modify": #个人基本信息头像修改	
         $T->SetBlock("list2","select * from v_users where username='{$_SESSION['username']}'");		 
		break;
		
	case "stu_contactadd": #学生_联系添加
		$T->SetBlock("list5","select * from `oa_zhsz_relative` where user_id='{$_SESSION['uid']}'"); 
		break;
		
	case "stu_contactmod": #学生_联系修改
		$stu_id=$_GET['id'];
		$T->SetBlock("list1","select * from `oa_zhsz_relative` where id= $stu_id "); 
		break;	
		
	case "stu_selectcourse": #学生_选课
			$str_menu='<label class="current"><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label>';
			 //平台基本信息
	         $params = array('field'=>array('flag_avatar','course_start','course_end','course_select'),'table'=>'oa_zhsz_system','where'=>"school='{$_SESSION['school']}'");
	         $web= $pd->fetchRow($params);
			 //查询个人信息
			$params = array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
			 $user   = $pd->fetchRow($params);	
			$str='';
				//仅有高一学生才显示选课菜单
					if(!empty($user['dept'])){						//查询班级
						$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']} and school={$_SESSION['school']}"));
						//查询年级
						if($class['grade_id']>1){
							//查询是否到选课时间
							if(!empty($web['course_start'])){
								$str_menu = '<label><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label><label class="current"><a href="./?t=stu_selectcourse&id='.$_SESSION['uid'].'">选课信息</a></label>';
								
								$now = date('Y-m-d');
								$start = $web['course_start'];
								$end   = $web['course_end'];
								
								if(empty($web['course_end'])){
									//查询当前学期
									$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
									$end = $curTerm['end'];
								}
								
								$str3=$user['select_course_id']==0?'选课暂未开始':'选课已经结束';
								$str4=$user['select_course_id']==0?'无':$user['select_course_name']; 
								if($now>=$start&&$now<=$end){
									$str2='';
									$scourse = $pd->fetchAll(array('field'=>array('*'),'table'=>'oa_zhsz_course_select','where'=>"school={$_SESSION['school']}"));
							
									foreach($scourse as $rs){
										$selected = '';
										if($rs['id']==$user['select_course_id']){
											$selected = 'selected="selected"';
										}
										$str2.= '<option value='.$rs['id'].' '.$selected.'>'.$rs['select_name'].'</option>';
									}		
                            
							
						$str = '<form action="./srv/user_modify_submit.php" method="post">
                        <table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="tabtd2_L" width="15%"><font color="#990000"><b>选课说明：</b></font></td>
                                <td class="tabtd2_R">
                                    <b>本次选课从 <font color="#990000"> '.$start.'</font> 开始到 <font color="#990000"> '.$end.'</font> 结束。</b><br />
                                    <div>'.$web['course_select'].'</div>
                                 </td>
                            </tr>
                            <tr>
                                <td class="tabtd2_L" width="15%">当前选课</td>
                                <td class="tabtd2_R">                                                                                                               
                                    <select name="course_select">
                                        <option value="0">暂未选择</option>                      
                                           '.$str2.'                        
                                    </select>
                                 </td>
                            </tr>
                        </table>
						
                        <div class="boxfooter2">
                              <input type="hidden" name="flag" value="3" />
                              <input name="but_sel" id="but_sel" type="submit" value="确定选择" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" /></div>
                        </form>';                  
								}else{
					  $str='<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="tabtd2_L" width="15%"><font color="#990000"><b>选课说明：</b></font></td>
                            <td class="tabtd2_R">
							'.$str3.'
                             </td>
                        </tr>
                        <tr>
                            <td class="tabtd2_L" width="15%">当前选课</td>
                            <td class="tabtd2_R">
                           '.$str4.'
                             </td>
                        </tr>
                    </table>';
								}
							}
						}
					}
					
		 $T->Set("str_menu",$str_menu);
		 $T->Set("str",$str);
		 break;	
		 
	case "pass_modify": #修改密码
		$str='';
		if($_SESSION['role_id']==1){
			//平台基本信息
		    $params = array('field'=>array('flag_avatar','course_start','course_end','course_select'),'table'=>'oa_zhsz_system','where'=>"school='{$_SESSION['school']}'");
	       $web    = $pd->fetchRow($params);
		   //print_r($web);
	        //查询个人信息
          $params = array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
           $user   = $pd->fetchRow($params);
		   //print_r($user);
		   $class = $pd->query_ext("select a.class_name,b.id as grade_id,b.grade_name from oa_zhsz_class as a inner join oa_zhsz_grade as b on a.grade_id=b.id where a.id={$user['dept']}");
		  
			if(empty($class)){
				$class['grade_name'] = '';
				$class['class_name'] = '暂无';
				$class['grade_id'] = 0;
			}
		
			//目前仅有角色为学生的才显示该菜单
			
			if($_SESSION['flag_type']==1){					
				$str='<label><a href="./?t=stu_modify">基本信息</a></label><label><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label>';
			
				//仅有高一学生才显示选课菜单
				if(!empty($user['dept'])){
					//查询年级
					if($class['grade_id']>1){
						//查询是否到选课时间
						if(!empty($web['course_start'])){
							$str = '<label><a href="./?t=stu_modify">基本信息</a></label><label><a href="./?t=stu_contact&id='.$_SESSION['uid'].'">联系人</a></label><label><a href="./?t=stu_selectcourse&id='.$_SESSION['uid'].'">选课信息</a></label>';
						}
					}
				}
			}			
		}else{
			$str='<label><a href="./?t=user_modify">基本信息</a></label>';
		}

		$T->Set("str",$str);
		break;
		
	case "senior_message": #学长寄语
	    $class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));

		$master_val=$class['class_master'];
		//echo $master_val;
		$str_event = '';
		$event = $pd->query("SELECT * from oa_zhsz_biye where type=3 and create_by='{$_SESSION['username']}' order by update_time desc")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($event as $rs){
			if($rs['is_check'] == 1){
				$is_check = "<font color=\'green\'>已审核</font>";
			}else if($rs['is_check'] == 2){
				$is_check = "未通过";
			}else{
				$is_check = "<font color=\"red\">待审核</font>";
			}
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tabtd2_L" style="text-align:center;">'.$rs['update_time'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'<a href="javascript:void(0);" onclick="delEvent('.$rs['id'].');"><img src="images/delete.gif" width="14" height="14" border="0"></a></td><td class="tabtd2_L" style="text-align:center;">'.$is_check.'</td></tr>';
		}
		$T->Set("str_event",$str_event);
		$T->Set("master_val",$master_val);
		$T->Set("uid",$uid);
		break;
		
	case "biye_info": #学习经历
		$str_event = '';
		$event = $pd->query("SELECT * from oa_zhsz_biye where type=1 and create_by='{$_SESSION['username']}' order by update_time desc")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($event as $rs){
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tabtd2_L" style="text-align:center;">'.$rs['subject'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['start_date'].'&nbsp;至&nbsp;'.$rs['end_date'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="delEvent('.$rs['id'].');"><img src="images/delete.gif" width="14" height="14" border="0"></a></td></tr>';
		}
		$T->Set("str_event",$str_event);
		break;
		
	case "work_exp": #工作经历
		$str_event = '';
		$event = $pd->query("SELECT * from oa_zhsz_biye where type=2 and create_by='{$_SESSION['username']}' order by update_time desc")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($event as $rs){
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tabtd2_L" style="text-align:center;">'.$rs['start_date'].'&nbsp;至&nbsp;'.$rs['end_date'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['subject'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="delEvent('.$rs['id'].');"><img src="images/delete.gif" width="14" height="14" border="0"></a></td></tr>';
		}
		$T->Set("str_event",$str_event);
		break;
		
	case "senior_message_search": #学长寄语 查询
		$submitMethod = $_SERVER["REQUEST_METHOD"];
        if(!empty($_POST['but_op'])){
			$value=$_POST['but_op'];
		}else if(!empty($_POST['h_but_op'])){
			$value=$_POST['h_but_op'];
		}
		//$value = empty($_POST['but_op'])?'':$_POST['but_op'];
		
		//判断提交方式
		if($submitMethod=='POST' && $value=="确定"){
			$id     = $_POST['id'];
			$op     = $_POST['op'];
			$ids = join(',',$id);

			if($op==1){//审核
				$data = array('flag_status'=>'已审核');
			}
			if($op==2){//取消审核
				$data = array('flag_status'=>'待审核');
			}
			$r = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_biye','where'=>"id IN ({$ids})"));
			if($r){
				$tips = array(
					'tips' => '审核操作成功。',
					'url'  => './?t=senior_message_search'
				);
			}else{
				$tips = array(
					'tips' => '审核操作失败。',
					'url'  => './?t=senior_message_search'
				);
			}
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		
		$p=isset($_GET["p"])?$_GET["p"]:"0";
		if($p==1) $p = 0;
		$strWhere = 'type=3';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);
		//if($_SESSION['role_id']!=4){
		//	$strWhere .= ' and A.flag_status="已审核"';
		//}
		//查询条件
		if(!empty($truename)){
			$strWhere .= " AND C.truename LIKE'{$truename}%'";
		}
		
		$where  = $strWhere;
		$sql 	= 'select A.*,C.truename as bname,B.dept ';
		$sql 	.= ' from oa_zhsz_biye AS A ';
		$sql 	.= ' inner join oa_user_extend AS B ON A.user_id=B.userid inner join act_member AS C ON B.userid=C.id';
		$sql 	.= " where {$where} ";
		
		$study = $T->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$str_event = '';
		$i = 1;
		
		foreach($study as $rs){
			$biye_info = $pd->fetchRow(array('field'=>array('class_name','class_end'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']} and school={$_SESSION['school']}"));	
			if(!$biye_info){
				$class_end = '';
				$class_name = '';
			}else{
				$class_end = $biye_info['class_end'];
				$class_name = $biye_info['class_name'];
			}
			
			$str_total = '';
			//if(isset($priv[3])){
			if($_SESSION['role_id']==4 || $_SESSION['role_id']==6){
				if($rs['flag_status'] == "已审核"){
					$is_check = "<font color=\'green\'>已审核</font>";
				}else if($rs['flag_status'] == "未通过"){
					$is_check = "未通过";
				}else{
					$is_check = "<font color=\"red\">待审核</font>";
				}
			
				$str_total .= '<td class="tabtd" align="left">&nbsp;&nbsp;&nbsp;&nbsp;<a 
			id="shtg" href="javascript:void(0);"
			class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\''.$rs['subject'].'\');"></a>&nbsp;<a   
			id="shwtg" href="javascript:void(0);"
			class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\'未通过\',\''.$rs['subject'].'\');" style="width:15px;overflow:hidden;margin:0 6px 0 5px ;">&nbsp;</a></td><td class="tabtd2_L" style="text-align:center;">'.$is_check.'</td>';
				$str_first = '<td class="tabtd"><input type="checkbox" name="id[]" id="id_'.$rs['id'].'" value="'.$rs['id'].'" />';
			}else{
				$str_first = '<td class="tabtd2_L" style="text-align:center;">'.$i.'</td>';
			}

			$str_event .= '<tr id="exp_'.$rs['id'].'">'.$str_first.'<td class="tabtd2_L" style="text-align:center;">'.$rs['bname'].'</td><td class="tabtd2_L" style="text-align:center;">'.$class_end.$class_name.'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['update_time'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$str_show = '';
		$str_check = '';
		//if(isset($priv[3])){
		if($_SESSION['role_id']==4|| $_SESSION['role_id']==6){
			$str_show = '<td width="5%" class="tabtd" style="text-align:center;">操作选项</td><td width="5%" class="tabtd" style="text-align:center;">状态</td>';
			$str_check = '<div><select name="op" id="op" ><option value="0">请选择操作</option><option value="1">审核通过</option><option value="2">取消审核</option></select><input type="submit" id="but_op" name="but_op" value="确定" /></div>';
			
			$str_top = '<td width="5%" class="tabtd"><input type="checkbox" name="all_select" id="all_select" value="1" onclick="selectAll(\'id[]\');" /></td>';
			
		}else{
			$str_top = '<td width="5%" class="tabtd"> <style="text-align:center;">序号</td>';
		}
		$rc = count($study);

		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=senior_message_search");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("uid",$uid);
		//echo $uid;
		//exit;
		$T->Set("truename",$truename);
		$T->Set("str_event",$str_event);
		$T->Set("str_show",$str_show);
		$T->Set("str_check",$str_check);
		$T->Set("str_top",$str_top);
		break; 
		
	case "work_exp_search": #工作经历 查询
		$p=isset($_GET["p"])?$_GET["p"]:"0";
		if($p==1) $p = 0;
		$strWhere = 'type=2';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);

		//查询条件
		if(!empty($truename)){
			$strWhere .= " AND C.truename LIKE'{$truename}%'";
		}
		
		$where  = $strWhere;
		$sql 	= 'select A.*,C.truename as bname,B.dept ';
		$sql 	.= ' from oa_zhsz_biye AS A ';
		$sql 	.= ' inner join oa_user_extend AS B ON A.user_id=B.userid inner join act_member AS C ON B.userid=C.id';
		$sql 	.= " where {$where} ";
		
		$study = $T->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$str_event = '';
		$i = 1;
		foreach($study as $rs){
			$biye_info = $pd->fetchRow(array('field'=>array('class_name','class_end'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']} and school={$_SESSION['school']}"));	
			if(!$biye_info){
				$class_end = '';
				$class_name = '';
			}else{
				$class_end = $biye_info['class_end'];
				$class_name = $biye_info['class_name'];
			}
			$str_total = '';
			//if(isset($priv[3])){
				$str_total .= '<td class="tabtd2_L" style="text-align:center;"><img src="images/010.gif" /> <a href="javascript:delEvent('.$rs['id'].');">删除</a></td>';
		//	}
			
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tabtd2_L" style="text-align:center;">'.$i.'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['bname'].'</td><td class="tabtd2_L" style="text-align:center;">'.$class_end.$class_name.'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['start_date'].'&nbsp;至&nbsp;'.$rs['end_date'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['subject'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$rc = count($study);
 
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=work_exp_search");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		$T->Set("truename",$truename);
		$T->Set("str_event",$str_event);
		break; 
		
	case "biye_search": #学习经历 查询
		$p=isset($_GET["p"])?$_GET["p"]:"0";
		if($p==1) $p = 0;
		$strWhere = 'type=1';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);

		//查询条件
		if(!empty($truename)){
			$strWhere .= " AND C.truename LIKE'{$truename}%'";
		}
		
		$where  = $strWhere;
		$sql 	= 'select A.*,C.truename as bname,B.dept ';
		$sql 	.= ' from oa_zhsz_biye AS A ';
		$sql 	.= ' inner join oa_user_extend AS B ON A.user_id=B.userid inner join act_member AS C ON B.userid=C.id';
		$sql 	.= " where {$where} ";
		
		$study = $T->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$str_event = '';
		$i = 1;
		foreach($study as $rs){
			$biye_info = $pd->fetchRow(array('field'=>array('class_name','class_end'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']} and school={$_SESSION['school']}"));	
			if(!$biye_info){
				$class_end = '';
				$class_name = '';
			}else{
				$class_end = $biye_info['class_end'];
				$class_name = $biye_info['class_name'];
			}
			$str_total = '';
			//if(isset($priv[3])){
				$str_total .= '<td class="tabtd2_L" style="text-align:center;"><img src="images/010.gif" /> <a href="javascript:delEvent('.$rs['id'].');">删除</a></td>';
			//}
			
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tabtd2_L" style="text-align:center;">'.$i.'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['bname'].'</td><td class="tabtd2_L" style="text-align:center;">'.$class_end.$class_name.'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['subject'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['start_date'].'&nbsp;至&nbsp;'.$rs['end_date'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$rc = count($study);
 
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=biye_search");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		$T->Set("truename",$truename);
		$T->Set("str_event",$str_event);
		break; 
		
	case "exam_manage": #考试类型设置
		$str = '';
		//if(isset($priv[1])){
			$str = '<img src="images/add.gif" class="padding5"/><a href="./?t=exam_add">添加考试类型</a>';
		//}
		
		//查询体能项目
		$params    = array('field'=>array('*'),'table'=>'oa_exam_type','where'=>'school='.$_SESSION["school"],'order'=>'order_value');
		$exams  = $pd->fetchAll($params);
		$i = 1;
		$str_total = '';
		foreach($exams as $rs){
			$grade_name = empty($rs['grade_name'])?'所有年级':$rs['grade_name'];
			$str_total .= '<tr id="stamina_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd">'.$i++.'</td><td class="tabtd">'.$rs['name'].'</td><td class="tabtd">'.$grade_name.'</td><td class="tabtd" align="center">&nbsp;&nbsp;';
			
			if($rs['id']>2){
				//if(isset($priv[2])){
					$str_total .= '<img src="images/037.gif" /> <a href="./?t=exam_modify&id='.$rs['id'].'">修改</a> ';
				//}
				
				//if(isset($priv[3])){
					$str_total .= '<img src="images/010.gif" /> <a href="javascript:delStamina('.$rs['id'].');">删除</a>';
				//}
			}
			$str_total .= '</td></tr>';
		}
		$rc = count($exams);
		$T->Set("total",$rc);
		$T->Set("str",$str);
		$T->Set("str_total",$str_total);
		break;
		
	case "exam_modify": #修改项目
		//查询体能项目排序最大值
		$id = Filter::filter_number($_GET['id']);
		$params = array('field'=>array('*'),'table'=>'oa_exam_type','where'=>"id={$id}");
		$exams  = $pd->fetchRow($params);
		if(empty($exams)){
			header('Location:./?t=exam_manage');
			exit;
		}
		$T->Set("gid",$exams['grade_id']);
		$T->Set("order_value",$exams['order_value']);
		$T->Set("name",$exams['name']);
		$T->Set("id",$exams['id']);
		break; 
		
	case "score_modify": #修改成绩
		$id = empty($_GET['id'])?0:intval($_GET['id']);
		$score = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"id={$id}"));
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$score['user_id']}'"));
		//查询班级
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
		$truename = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$user['userid']}'"));
		$T->Set("truename",$truename);
		$T->Set("gradeName",$gradeName);
		$T->Set("class_name",$class['class_name']);
		$T->Set("exam_type",$score['exam_type']);
		break; 
		
	case "score_import": #学生成绩导入
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0  and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
			$isMaster1 = 1;
		}
		
		$T->Set("isMaster1",$isMaster1);
		break; 
		
	case "score_export": #批量导出成绩
		if($_SESSION['role_id']==4){
			$isadmin=1;
		}else{
			$isadmin=0;
		}
		
		$T->Set("isadmin",$isadmin);
		break; 
		
	case "zhsz_other": #综合评价记录
		$cid   = 0;
		$gid   = 0;
		$className = '';
		$gradeName = '';
		$tips      = '&nbsp;';
		$strWhere  = "flag_type=1 and flag_status=1";
		//修学或退学
		$noClass = false;
		//毕业
		$noGrade = false;
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class_id = 0;
		//学生
		if($_SESSION['flag_type']==1||$_SESSION['flag_type']==3){
			//查询学生信息
			$user  = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
			//查询当前学生年级班级情况
			if(empty($user['dept'])){
				$noClass = true;
			}else{
				//查询班级
				$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
				//查询是否是毕业班
				if($class['grade_id']==0){
					$noGrade = true;
				}else{
					$cid = $class['id'];
					$class_id = $class['id'];
					$gid = $class['grade_id'];
				}
			}
			
		}
		
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			if(!empty($class)){
				$cid = $class['id'];
				$class_id = $class['id'];
				$gid = $class['grade_id'];
				$isMaster = true;
				$isMaster1 = 1;
			}
			$isMaster = false;
		}
		
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		
		$cid = empty($_POST['cid'])?$cid:Filter::filter_number($_POST['cid']);
		$gid = empty($_POST['gid'])?$gid:Filter::filter_number($_POST['gid']);
		
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		
		if(!empty($cid)){
			$strWhere.= " and dept='{$cid}' ";
		}else{
			
			if($_SESSION['role_id']==4 && $cid==0){
				$grade_id = $pd->fetchOne(array('field'=>'id','table'=>'oa_zhsz_grade','limit'=>"1"));
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$grade_id} and school={$_SESSION['school']}"));
				$classId  = join(',',$classId);
				$strWhere .= " and dept IN ({$classId}) ";
			}else{
				$strWhere.= " and dept>0 ";
			}
				
		}
		
		$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		if($cid!=0&&$gid!=0){
			$tips = "当前班级：{$gradeName}{$className}";
		}
		$where  = $strWhere;
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		//查询所有学生
		$params = array('field'=>'count(*)','table'=>'oa_user_extend','where'=>$where);
		$rc = $pd->fetchOne($params);
		$p=isset($_GET["p"])?$_GET["p"]:"0";  

		$order = 'order by total desc';
		
		if($isMaster==true){
			$order = 'order by CONVERT(C.truename USING GBK)';
		}
		
		$sql = "select C.id,C.truename,D.dept,B.exp,B.result,B.research,B.service,B.event_active,(if(B.exp>0,1,0)+if(B.result>0,1,0)+if(B.research>0,1,0)+if(B.service>0,1,0)+if(B.event_active>0,1,0)) as total ";
		$sql 	.= " from act_member AS C  inner join oa_user_extend as D  ON C.id=D.userid ";
		$sql    .= " left join (select * from oa_zhsz_main_statistics where term_id={$curTerm['id']}) as B on C.id=B.user_id where  {$where}  {$order}  limit {$p},150";
		//学生信息查询
		$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$str = '';
		if(empty($students)){
			$str = '<div style="width:200px; height:30px; overflow:hidden; margin:0 auto; text-align:center;">暂无学生信息</div>';
		}
		
		foreach($students as $rs){
			$c = 0;
			if($rs['exp']>0){
				$c++;	
			}
			if($rs['result']>0){
				$c++;	
			}
			if($rs['research']>0){
				$c++;	
			}
			if($rs['service']>0){
				$c++;	
			}
			if($rs['event_active']>0){
				$c++;	
			}
			//未填写
			if($c==0){
				$css1 = 'style="background:#dedede;"';
				$css2 = 'style="color:#000000;"';
			}
			//填写任意一项
			if($c==1){
				$css1 = 'style="background:#000099;"';
				$css2 = 'style="color:#ffff00;"';
			}
			//填写两项以上
			if($c>=2){
				$css1 = 'style="background:#339900;"';
				$css2 = 'style="color:#ffffff;"';
			}
			//$str.="<li class='user_list2' {$css1}><a href='./?t=zhsz_table&uid={$rs['id']}' {$css2}>{$rs['truename']}</a></li>";
			$str.="<li class='user_list2' {$css1}><a href='./?t=zhsz_table_show&uid={$rs['id']}' {$css2}>{$rs['truename']}</a></li>";
		}
					
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,150,$p,"&t=zhsz_other&gid_page={$gid}&cid_page={$cid}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');

		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("tips",$tips);
		$T->Set("isMaster1",$isMaster1);
		$T->Set("class_id",$class_id);
		$T->Set("str",$str);
		break;
		
	case "course_all_statistics": #技能素质统计
		$tyear_page=isset($_GET["tyear_page"])?$_GET["tyear_page"]:0;
		$tyear    = empty($_POST['tyear'])?'':$_POST['tyear'];
		if($tyear_page)
			$tyear = $tyear_page;
		
		$T->SetBlock("list","select id,course_name from oa_zhsz_course where is_score=0 order by id DESC");
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		$params = array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'school='.$_SESSION['school'],'order'=>'flag_default DESC');
		$terms  = $pd->fetchAll($params);
		
		if(empty($tyear)){
			$tyear = $curTerm['id'];
		}
		//查询所有班级
		$strWhere = 'A.grade_id<>0';
		//每页显示数目为目标个数+1
		$where  = $strWhere;
		$sql 	= 'select A.*,(select count(*) from oa_user_extend where dept=A.id and flag_type=1) as nums ';
		$sql 	.= ' from oa_zhsz_class AS A ';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by A.grade_id,A.id ';
		$sql 	.= " limit {$offset},20";
		//班级查询
		$class  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		//查询技能素质的科目
		$course  = $pd->fetchAll(array('field'=>array('id','course_name'),'table'=>'oa_zhsz_course','where'=>'is_score=0 and school='.$_SESSION['school'],'order'=>'id DESC'));

		//统计报表
		foreach($class as $k=>$rs){
			$class[$k]['grade_name']  = $rs['grade_id']==3?'高三':($rs['grade_id']==1?'高一':'高二');
			for($i=0;$i<count($course);$i++){
				$course_num = $pd->query("select count(*) as num from oa_zhsz_course_level A left join oa_user_extend B on A.user_id=B.userid where course_id={$course[$i]['id']} and B.dept={$rs['id']} and A.term_id={$tyear}")->fetchAll(PDO::FETCH_ASSOC);
				$class[$k]["cnums"][$i] = $course_num[0]['num'];
			}  
		}

		$classlen=count($class);
		$str_total = '';
		for($i=0;$i<$classlen;$i++){
			$num = $i+1;
		  $rs=$class[$i];              
		  $str_total .= ' <tr><td class="tabtd">'.$num.'</td><td class="tabtd">'.$rs['grade_name'].$rs['class_name'].'</td><td class="tabtd">'.$rs['nums'].'</td>';
		   for($j=0;$j<count($rs['cnums']);$j++){
			    $str_total .= ' <td class="tabtd">'.$rs['cnums'][$j].'</td>';
		   }
		   $str_total .= '</tr>';
		}
		
		//查询所有班级
		$params = array('field'=>'count(A.id)','table'=>'oa_zhsz_class AS A','where'=>$where);
		$rc = $pd->fetchOne($params);

		$page=getPageHtml_bt($rc,20,$p,"&t=course_all_statistics&tyear_page={$tyear}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("page",$page); 
		$T->Set("str_total",$str_total); 
		$T->Set("tyear",$tyear); 
		break; 
		
	case "xuefen": #学分认定
		$T->SetBlock("list","select * from oa_zhsz_course where is_print=1 and school={$_SESSION['school']} order by order_value");
		break; 
		
	case "stamina_rs_manage": #体能管理
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$tid_page=isset($_GET["tid_page"])?$_GET["tid_page"]:0;

		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
	
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		
		if($tid==0){
			$tid = $curTerm['id'];	
		}

		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		if($tid_page)
			$tid = $tid_page;
		
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
			$isMaster1 = 1;
		}
		$ttid = 0;
		if($tid>0){
			$ttid = $pd->fetchOne(array('field'=>'term_type','table'=>'oa_zhsz_term','where'=>"id={$tid}"));
		}
		
		$where = 'school='.$_SESSION['school'];
		//if($gid>0){
			$where .= " and (grade_id=0 OR grade_id={$gid}) ";	
		//}
		if($ttid>0){
			$where .= " and (term_type=0 OR term_type={$ttid}) ";
		}
		
		$T->SetBlock("list","select id,name from oa_zhsz_stamina where {$where} order by id");
		//体能项目
		$stamina = $pd->fetchAll(array('field'=>array('id','name'),'table'=>'oa_zhsz_stamina','where'=>$where,'order'=>'id'));
		
		//查询记录
		$strWhere = ' A.flag_type=1 ';

		//默认为当前学期高一年级
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and A.dept IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and A.dept={$classId} ";
			}
		}
		
		if(!empty($truename)){
			$strWhere .= "  and C.truename LIKE'{$truename}%' ";
		}
		
		//分页配置
		$where  = $strWhere;
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		$sql 	= 'select A.*,B.class_name,C.truename ';
		$sql 	.= ' from oa_user_extend AS A ';
		$sql    .= ' inner join oa_zhsz_class as B ON A.dept=B.id inner join act_member as C ON A.userid=C.id';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by CONVERT(C.truename USING GBK) ';
		$sql 	.= " limit {$offset},20";
		
		//学生查询
		$student  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		//查询所有学生
		$params = array('field'=>'count(A.userid)','table'=>'oa_user_extend AS A inner join act_member as C ON A.userid=C.id','where'=>$where);
		$rc = $pd->fetchOne($params);
		
		$len = count($stamina);
		$itemA = array();
		foreach($stamina as $rs){
			$itemA[$rs['id']] = $rs['id'];
		}
		
		if($_SESSION['role_id']==4){
			$isadmin=1;
		}else{
			$isadmin=0;
		}
		
		$str_total = '';
		foreach($student as $rs){
			$where = '';
			if($tid>0){
				$where = " and term_id={$tid}";	
			}
			//查询学生体能记录
			if(!$gradeName){
				$get_grade = $pd->query("select grade_name from oa_zhsz_grade where id=(select grade_id from oa_zhsz_class where id={$rs['dept']})")->fetchAll(PDO::FETCH_ASSOC);
				$gradeName = $get_grade[0]['grade_name'];
			}
			$sm = $pd->query("select item_id,item_name,result from oa_zhsz_stamina_record where user_id='{$rs['userid']}' {$where} order by item_id")->fetchAll(PDO::FETCH_ASSOC);
			$str_total .= '<tr><td class="tabtd">'. $rs['truename'].'</td><td class="tabtd">'.$gradeName.$rs['class_name'].'</td>';
			/*foreach($sm as $rsb){
				if(isset($itemA[$rsb['item_id']])){
					$str_total .= "<td class='tabtd'>{$rsb['result']}</td>";	
				}
			}*/
			if($sm){
				foreach($stamina as $rss){
					$is_ok = 0;
					foreach($sm as $rsb){
						if($rss['id']==$rsb['item_id']){
							$str_total .= "<td class='tabtd'>{$rsb['result']}</td>";	
							$is_ok = 1;
						}
					}
					if(!$is_ok){
						$str_total .= "<td class='tabtd'>-</td>";	
					}
				}
			}
			
			if(empty($sm)){
				for($i=0;$i<$len;$i++){
					$str_total .= "<td class='tabtd'>-</td>";	
				}
				$str_total .= '<td class="tabtd" style="text-align:left; padding-left:10px; color:#333;">&nbsp;暂无数据</td>';
			}else{
				if($isadmin)
					$str_total .= '<td class="tabtd" style="text-align:left; padding-left:10px;"><img src="images/037.gif" /> <a href="./?t=stamina_rs_modify&uid='.$rs['userid'].'&tid='.$tid.'">修改</a> <img src="images/010.gif" /> <a href="javascript:delStamina(\''.$rs['userid'].'\','.$tid.');">删除</a></td>';
			}
			$str_total .='</tr>';
		}
		
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=stamina_rs_manage&gid_page={$gid}&cid_page={$cid}&tid_page={$tid}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("truename",$truename);
		$T->Set("gradeName",$gradeName);
		$T->Set("tid",$tid);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("isMaster1",$isMaster1);
		$T->Set("str_total",$str_total);
		$T->Set("isadmin",$isadmin);
		break; 
		
	case "stamina_rs_modify": #学生成绩导入体能管理修改GET['tid']);
		$tid = empty($_GET['tid'])?0:intval($_GET['tid']);
		$uid = empty($_GET['uid'])?"":$_GET['uid'];
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$uid}'"));
		//查询班级
		
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
		//查询体能记录
		$T->SetBlock("list","select item_id,item_name,result from oa_zhsz_stamina_record where user_id='{$uid}' and term_id={$tid} order by item_id");

		//查询体育成绩
		$tyScore = $pd->fetchOne(array('field'=>'ty','table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$tid} and exam_type='期末'"));
		
		$truename = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$user['userid']}'"));
		$T->Set("gradeName",$gradeName);
		$T->Set("tyScore",$tyScore);
		$T->Set("truename",$truename);
		$T->Set("class_name",$class['class_name']);
		$T->Set("id",$user['userid']);
		$T->Set("tid",$tid);
		break; 
		
	case "physique_manage": #体质管理
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$tyear_page=isset($_GET["tyear_page"])?$_GET["tyear_page"]:0;
		
		$gid      = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid      = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tyear    = empty($_POST['tyear'])?'':$_POST['tyear'];
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		if($tyear_page)
			$tyear = $tyear_page;

		if($_SESSION['role_id']==4){
			$isadmin=1;
		}else{
			$isadmin=0;
		}
		
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
			$isMaster1 = 1;
		}
		//查询记录
		$strWhere = "  1=1 ";
		
		//查询所属于班级
		if($gid>0){
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and A.dept IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and A.dept={$classId} ";
			}
		}

		if(!empty($tyear)){
			$strWhere .= "  and B.term_year='{$tyear}' ";
		}
		
		if(!empty($truename)){
			$strWhere .= "  and A.truename LIKE'{$truename}%'";
		}
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$where  = $strWhere;
		$sql 	= 'select A.truename,B.*,C.class_name,D.grade_name ';
		$sql 	.= ' from v_users AS A ';
		$sql    .= ' inner join oa_zhsz_physique as B ON A.id=B.user_id inner join oa_zhsz_class AS C on A.dept=C.id inner join oa_zhsz_grade AS D on C.grade_id=D.id';
		$sql 	.= " where {$where} and A.flag_type=1";
		$sql 	.= ' order by CONVERT(A.truename USING GBK) ';
		$sql 	.= " limit {$offset},20";
		//体质记录查询
		//$physique  = $pd->query($sql)fetchAll(PDO::FETCH_ASSOC);
		$T->SetBlock("list1",$sql);
		//查询所有体质记录
		$sql 	= 'select A.id ';
		$sql 	.= ' from v_users AS A ';
		$sql    .= ' inner join oa_zhsz_physique as B ON A.id=B.user_id';
		$sql 	.= " where {$where} and A.flag_type=1";

		$physiqueN = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($physiqueN);

		$page=getPageHtml_bt($rc,20,$p,"&t=physique_manage&gid_page={$gid}&cid_page={$cid}&tyear_page={$tyear}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 

		$T->Set("truename",$truename);
		$T->Set("tyear",$tyear);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->SetBlock("list","select id,year,term_name from oa_zhsz_term  group by year order by flag_default DESC,year DESC ");
		$T->Set("isMaster1",$isMaster1);
		$T->Set("isadmin",$isadmin);
		break; 
		
	case "physique_modify": #体质修改
		$id = empty($_GET['id'])?0:intval($_GET['id']);
		//查询体质记录
		$physique = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_physique','where'=>"id={$id}"));
		
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$physique['user_id']}'"));
		
		if($user['dept']){
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));

			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
			
			$T->Set("gradeName",$gradeName);
			$T->Set("class_name",$class['class_name']);
		}
		
		$T->Set("truename",$user['truename']);
		break; 
		
	case "zhsz_table_export": #导出综评表
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
		$where = '';
		if(!empty($class)){
			$isMaster = true;
			$isMaster1 = 1;
			$where .= " and class_id={$class['id']}";
		}
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));

		//预览用户
		$uid = $pd->fetchOne(array('field'=>'user_id','table'=>'oa_zhsz_main','where'=>"term_id={$curTerm['id']} {$where}",'order'=>'id DESC'));
		
		if($_SESSION['role_id']==4){
			$is_admin = 1;
		}else{
			$is_admin = 0;
		}
		
		$T->Set("uid",$uid); 
		$T->Set("isMaster1",$isMaster1);
		$T->Set("is_admin",$is_admin);
		break; 
		
	case "zhsz_import":#技能素质导入
		//查询老师实际所教课程
		//$teachArray  = $pd->query("select * from oa_zhsz_teach where user_id='{$_SESSION['uid']}' and course_id not in (1,2,7,8,9) group by course_id")->fetchAll(PDO::FETCH_ASSOC);
		$teachArray  = $pd->query("select A.*,B.is_score from oa_zhsz_teach  as A inner join oa_zhsz_course AS B ON A.course_id=B.id where A.user_id='{$_SESSION['uid']}' and B.is_score=0 group by A.course_id")->fetchAll(PDO::FETCH_ASSOC);

		if(empty($teachArray)){
			$tips = array(
					   'tips' => '抱歉，当前用户没有设置所教班级及技能素质课程信息。',
					   'url'  => './?t=skills_manage'
					  );
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		/*foreach($teachArray as $k=>$v){
			$teachArray[$k]["is_checked"]  = $pd->fetchOne(array('field'=>'is_checked','table'=>'oa_zhsz_course','where'=>"id={$teachArray[$k]['course_id']}"));
			$teachArray[$k]["is_score"]  = $pd->fetchOne(array('field'=>'is_score','table'=>'oa_zhsz_course','where'=>"id={$teachArray[$k]['course_id']}"));
		}*/
		$str = "";
		foreach($teachArray as $rs){
		  $str .= "<option value='{$rs['course_id']}' stype='{$rs['is_score']}'>{$rs['course_name']}</option>";
		}
		$T->Set("str",$str);
		break;
		
	case "zhsz_vote": #自我评价
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];
		
		//查询是否可自评
		//平台基本信息
		$params = array('field'=>array('self_start','self_end'),'table'=>'oa_zhsz_system','where'=>"school='{$_SESSION['school']}'");
		$web    = $pd->fetchRow($params);
		//查询学生及班级信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		//没有所属班级
		if(empty($user['dept'])){
			$tips = '您目前没有加入任何班级，请联系管理人员。';
			echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
			exit;
		}else{
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			//查询是否是毕业班
			if($class['grade_id']==0){
				$tips = '您目前已经毕业了，无法进行操作哦。';
				echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
				exit;
			}
		}
		//没有设置则当前学期结束日期的前10天内可填写
		if(empty($web['self_start'])){
			$start = date('Y-m-d',strtotime("-10 days",strtotime($curTerm['end'])));
			$end   = $curTerm['end'];
		}else{
			$start = $web['self_start'];
			$end   = $web['self_end'];
		}
		//查询当前学期自我评价
		$str_votes = '';
		$votes  = $pd->query("select b.name,b.item,a.item_id from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='自我评价' and a.term_id={$sid} and a.user_id='{$_SESSION['uid']}' order by a.vote_id")->fetchAll(PDO::FETCH_ASSOC);
		$now = date('Y-m-d');

		if($now>=$start&&$now<=$end){
			//在可编辑范围日期内
			if(empty($votes)){
				//如果是父母也不可以评价
				/*if($_SESSION['user_type']==3){
					echo '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
					echo '<tr><td class="tabtd2_L" style="text-align:center;">暂未评价。</td></tr>';
					echo '</table>';
					exit;
				}*/
				//没有评价过，开始评价
				//查询自我评价项
				$voteLib  = $pd->query("select * from oa_zhsz_votelib where vote_cat='自我评价'")->fetchAll(PDO::FETCH_ASSOC);
				$str_votes .= '<form method="post" action="./srv/zhsz_vote_submit.php" onsubmit="return checkVote();">';
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
				$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="65%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				$ids = '';
				foreach($voteLib as $rs){
					$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;"><input type="hidden" name="vote[]" value="'.$rs['id'].'" />';
					$item = explode(',',$rs['item']);
					$j = 1;
					foreach($item as $rsb){
						$str_votes .= "<input type='radio' name='item_{$rs['id']}' value='{$j}' />&nbsp;&nbsp;{$rsb}&nbsp;&nbsp;";	
						$j++;
					}
					$str_votes .= '</td></tr>';
					$ids .= $rs['id'].',';
				}
				$ids = trim($ids,',');
				$str_votes .= '<tr><td class="tabtd2_R" colspan="3" style="text-align:center; padding:5px 0;">';
				$str_votes .= '<input type="hidden" name="vote_cat" value="自我评价" />';
				$str_votes .= '<input type="hidden" name="term_id" value="'.$sid.'" />';
				$str_votes .= '<input type="hidden" name="ids" id="ids" value="'.$ids.'" />';
				$str_votes .= '<input name="but_exp" id="but_exp" type="submit" value="保 存" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" /></td></tr>';
				$str_votes .= '</table></form>';
			}else{//显示结果
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str_votes .= $item[$j].'</td></tr>';
				}
				$str_votes .= '</table>';
			}
		}else{
			if(empty($votes)){//没有评价过，开始评价
				$tips = '暂未开始评价。';
				echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
				exit;
			}else{//显示结果
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str_votes .= $item[$j].'</td></tr>';
				}
				$str_votes .= '</table>';
			}
		}
			
		$T->Set("str_votes",$str_votes);
		break;
		
	case "zhsz_vote_other": #他人评价
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];
		
		//查询是否可自评
		//平台基本信息
		$params = array('field'=>array('self_start','self_end'),'table'=>'oa_zhsz_system','where'=>'school='.$_SESSION['school']);
		$web    = $pd->fetchRow($params);
		//查询学生及班级信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		
		//没有所属班级
		if(empty($user['dept'])){
			$tips = '您目前没有加入任何班级，请联系管理人员。';
			echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
			exit;
		}else{
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			//查询是否是毕业班
			if($class['grade_id']==0){
				$tips = '您目前已经毕业了，无法进行操作哦。';
				echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
				exit;
			}
		}
		//没有设置则当前学期结束日期的前10天内可填写
		if(empty($web['self_start'])){
			$start = date('Y-m-d',strtotime("-10 days",strtotime($curTerm['end'])));
			$end   = $curTerm['end'];
		}else{
			$start = $web['self_start'];
			$end   = $web['self_end'];
		}
		//查询当前学期学生互评
		$str_votes = '';
		$votes  = $pd->query("select b.name,b.item,a.vote_id,a.item_id,count(a.item_id) as cc from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='学生互评' and a.term_id={$sid} and a.user_id='{$_SESSION['uid']}' group by a.vote_id,a.item_id  order by a.vote_id,a.item_id")->fetchAll(PDO::FETCH_ASSOC);
		$now = date('Y-m-d');
		//在可编辑范围日期内
		if(empty($votes)){//没有评价过
			$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
			$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">暂无他人评价。</td></tr>';
			$str_votes .= '</table>';
		}else{//显示结果
			$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
			$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="65%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
			$i = 1;
			$t = 0;
			$voteId = 0;
			foreach($votes as $rs){
				$t++;
				if($voteId!=$rs['vote_id']){
					if($voteId!=0){
						//收尾上一条结束
						$str_votes .= '</td></tr>';
					}
					//新的评价项
					$voteId = $rs['vote_id'];
					$item   = explode(',',$rs['item']);
					$j      = $rs['item_id'] - 1;
					$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					//循环显示当前结果项之前所有项
					for($m=0;$m<$j;$m++){
						$str_votes .= $item[$m].'(0)&nbsp;&nbsp;';
					}
					$str_votes .= $item[$j].'('.$rs['cc'].')&nbsp;&nbsp;';
				}else{
					$item = explode(',',$rs['item']);
					//查询与上一个是否紧挨着
					$index = $votes[$t-2]['item_id'] + 1;
					if($index!=$rs['item_id']){
						for($m=$votes[$t-2]['item_id'];$m<$rs['item_id']-1;$m++){
							$str_votes .= $item[$m].'(0)&nbsp;&nbsp;';	
						}
					}
					$j    = $rs['item_id'] - 1;
					$str_votes .= $item[$j].'('.$rs['cc'].')&nbsp;&nbsp;';
				}
				//判断记录是最后一条
				if(isset($votes[$t])){
					//下一条是否还是当前评价项
					if($votes[$t]['vote_id']!=$rs['vote_id']){
						$item = explode(',',$rs['item']);
						$len  = count($item);
						//循环显示当前结果项之后所有项
						for($m=$rs['item_id'];$m<$len;$m++){
							$str_votes .= $item[$m].'(0)&nbsp;&nbsp;';
						}
					}
				}else{
					//最后一条记录
					$str_votes .= '</td></tr>';
				}							
			}
			$str_votes .= '</table>';
		}		
				
		$T->Set("str_votes",$str_votes);
		break;
		
	case "zhsz_vote_master": #班主任评价
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];

		//查询是否可自评
		//平台基本信息
		$params = array('field'=>array('self_start','self_end'),'table'=>'oa_zhsz_system','where'=>'school='.$_SESSION['school']);
		$web    = $pd->fetchRow($params);
		//查询学生及班级信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		//没有所属班级
		if(empty($user['dept'])){
			$tips = '您目前没有加入任何班级，请联系管理人员。';
			echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
			exit;
		}else{
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			//查询是否是毕业班
			if($class['grade_id']==0){
				$tips = '您目前已经毕业了，无法进行操作哦。';
				echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
				exit;
			}
		}
		//没有设置则当前学期结束日期的前10天内可填写
		if(empty($web['self_start'])){
			$start = date('Y-m-d',strtotime("-10 days",strtotime($curTerm['end'])));
			$end   = $curTerm['end'];
		}else{
			$start = $web['self_start'];
			$end   = $web['self_end'];
		}
		//查询当前学期班主任评价
		$str_votes = '';
		$votes  = $pd->query("select b.name,b.item,a.item_id from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='班主任评价' and a.term_id={$sid} and a.user_id='{$_SESSION['uid']}' order by a.vote_id")->fetchAll(PDO::FETCH_ASSOC);
		$now = date('Y-m-d');
		//在可编辑范围日期内
		if(empty($votes)){//没有评价过
			$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
			$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">暂无班主任评价。</td></tr>';
			$str_votes .= '</table>';
		}else{//显示结果
			$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
			$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
			$i = 1;
			foreach($votes as $rs){
				$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
				$item = explode(',',$rs['item']);
				$j = $rs['item_id'] - 1;
				$str_votes .= $item[$j].'</td></tr>';
			}
			$str_votes .= '</table>';
		}
					
		$T->Set("str_votes",$str_votes);
		break;
		
	case "zhsz_vote_parent": #父母评价
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];

		//查询是否可自评
		//平台基本信息
		$params = array('field'=>array('self_start','self_end'),'table'=>'oa_zhsz_system','where'=>'school='.$_SESSION['school']);
		$web    = $pd->fetchRow($params);
		//查询学生及班级信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		//没有所属班级
		if(empty($user['dept'])){
			//if($_SESSION['user_type']==3){
			//	$tips = '您的孩子目前没有加入任何班级，请联系管理人员。';
			//}else{
				$tips = '您目前没有加入任何班级，请联系管理人员。';
			//}
			echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
			exit;
		}else{
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			//查询是否是毕业班
			if($class['grade_id']==0){
				if($_SESSION['user_type']==3){
					$tips = '您的孩子目前已经毕业了，无法进行操作哦。';
				}else{
					$tips = '您目前已经毕业了，无法进行操作哦。';
				}
				echo '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
				exit;
			}
		}
		//没有设置则当前学期结束日期的前10天内可填写
		if(empty($web['self_start'])){
			$start = date('Y-m-d',strtotime("-10 days",strtotime($curTerm['end'])));
			$end   = $curTerm['end'];
		}else{
			$start = $web['self_start'];
			$end   = $web['self_end'];
		}
		//查询当前学期父母评价
		$str_votes = '';
		$votes  = $pd->query("select b.name,b.item,a.item_id from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='父母评价' and a.term_id={$sid} and a.user_id='{$_SESSION['uid']}' order by a.vote_id")->fetchAll(PDO::FETCH_ASSOC);
		$now = date('Y-m-d');
		/*if($_SESSION['user_type']==3){
			if($now>=$start&&$now<=$end){
				//在可编辑范围日期内
				if(empty($votes)){//没有评价过，开始评价
					//查询自我评价项
					$voteLib  = $pd->query("select * from oa_zhsz_votelib where vote_cat='父母评价'")->fetchAll(PDO::FETCH_ASSOC);
					$str_votes .= '<form method="post" action="./srv/zhsz_vote_submit.php" onsubmit="return checkVote();">';
					$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
					$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="65%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
					$i = 1;
					$ids = '';
					foreach($voteLib as $rs){
						$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;"><input type="hidden" name="vote[]" value="'.$rs['id'].'" />';
						$item = explode(',',$rs['item']);
						$j = 1;
						foreach($item as $rsb){
							$str_votes .= "<input type='radio' name='item_{$rs['id']}' value='{$j}' />&nbsp;&nbsp;{$rsb}&nbsp;&nbsp;";	
							$j++;
						}
						$str_votes .= '</td></tr>';
						$ids .= $rs['id'].',';
					}
					$ids = trim($ids,',');
					$str_votes .= '<tr><td class="tabtd2_R" colspan="3" style="text-align:center; padding:5px 0;">';
					$str_votes .= '<input type="hidden" name="vote_cat" value="父母评价" />';
					$str_votes .= '<input type="hidden" name="term_id" value="'.$sid.'" />';
					$str_votes .= '<input type="hidden" name="ids" id="ids" value="'.$ids.'" />';
					$str_votes .= '<input name="but_exp" id="but_exp" type="submit" value="保 存" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" /></td></tr>';
					$str_votes .= '</table></form>';
				}else{//显示结果
					$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
					$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
					$i = 1;
					foreach($votes as $rs){
						$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
						$item = explode(',',$rs['item']);
						$j = $rs['item_id'] - 1;
						$str_votes .= $item[$j].'</td></tr>';
					}
					$str_votes .= '</table>';
				}
			}else{
				if(empty($votes)){//没有评价过，开始评价
					$tips = '暂未开始评价。';
					$str_votes .= '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
				}else{//显示结果
					$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
					$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
					$i = 1;
					foreach($votes as $rs){
						$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
						$item = explode(',',$rs['item']);
						$j = $rs['item_id'] - 1;
						$str_votes .= $item[$j].'</td></tr>';
					}
					$str_votes .= '</table>';
				}
			}
		}else{*/
			//在可编辑范围日期内
			if(empty($votes)){//没有评价过，开始评价
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
				$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">暂无父母评价。</td></tr>';
				$str_votes .= '</table>';
			}else{//显示结果
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str_votes .= $item[$j].'</td></tr>';
				}
				$str_votes .= '</table>';
			}
		//}
					
		$T->Set("str_votes",$str_votes);
		break;
		
	case "zhsz_vote_history": #评价历史
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];

		//查询学期
		$id = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id']);
		if($id!=0){
			$sid = $id;	
		}
	
		$voteCat = empty($_POST['vote_cat'])?'自我评价':Filter::filter_html($_POST['vote_cat']);
		
		$term   = showTerm($pd,$_SESSION['uid']);

		$str = '';
		foreach($term as $rs){
			$selected = '';
			if($rs['id']==$sid){
				$selected = 'selected="selected"';	
			}
			$str.='<option value="'.$rs['id'].'" '.$selected.'>'.$rs['term'].'</option>';
		}
		
		$str_votes = '';
		if($voteCat=='学生互评'){
			$votes  = $pd->query("select b.name,b.item,a.vote_id,a.item_id,count(a.item_id) as cc from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='{$voteCat}' and a.term_id={$sid} and a.user_id='{$_SESSION['uid']}' group by a.vote_id,a.item_id  order by a.vote_id,a.item_id")->fetchAll(PDO::FETCH_ASSOC);
			if(empty($votes)){
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">暂无评价记录</td></tr></table>';
			}else{
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="65%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				$t = 0;
				$voteId = 0;
				foreach($votes as $rs){
					$t++;
					if($voteId!=$rs['vote_id']){
						if($voteId!=0){
							//收尾上一条结束
							$str_votes .= '</td></tr>';
						}
						//新的评价项
						$voteId = $rs['vote_id'];
						$item   = explode(',',$rs['item']);
						$j      = $rs['item_id'] - 1;
						$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
						//循环显示当前结果项之前所有项
						for($m=0;$m<$j;$m++){
							$str_votes .= $item[$m].'(0)&nbsp;&nbsp;';
						}
						$str_votes .= $item[$j].'('.$rs['cc'].')&nbsp;&nbsp;';
					}else{
						$item = explode(',',$rs['item']);
						//查询与上一个是否紧挨着
						$index = $votes[$t-2]['item_id'] + 1;
						if($index!=$rs['item_id']){
							for($m=$votes[$t-2]['item_id'];$m<$rs['item_id']-1;$m++){
								$str_votes .= $item[$m].'(0)&nbsp;&nbsp;';	
							}
						}
						$j    = $rs['item_id'] - 1;
						$str_votes .= $item[$j].'('.$rs['cc'].')&nbsp;&nbsp;';
					}
					//判断记录是最后一条
					if(isset($votes[$t])){
						//下一条是否还是当前评价项
						if($votes[$t]['vote_id']!=$rs['vote_id']){
							$item = explode(',',$rs['item']);
							$len  = count($item);
							//循环显示当前结果项之后所有项
							for($m=$rs['item_id'];$m<$len;$m++){
								$str_votes .= $item[$m].'(0)&nbsp;&nbsp;';
							}
						}
					}else{
						//最后一条记录
						$str_votes .= '</td></tr>';
					}							
				}
				$str_votes .= '</table>';
			}
		}else{
			//查询当前评价
			$votes  = $pd->query("select b.name,b.item,a.item_id from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='{$voteCat}' and a.term_id={$sid} and a.user_id='{$_SESSION['uid']}' order by a.vote_id")->fetchAll(PDO::FETCH_ASSOC);
			if(empty($votes)){
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">暂无评价记录</td></tr></table>';
			}else{
				$str_votes .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str_votes .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str_votes .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str_votes .= $item[$j].'</td></tr>';
				}
				$str_votes .= '</table>';
			}
		}
		
		$T->Set("str_votes",$str_votes);
		$T->Set("str",$str);
		$T->Set("voteCat",$voteCat);
		break;
		
	case "vote_manage": #评价管理
		$where = ' 1=1 ';
		$voteCat = empty($_POST['vote_cat'])?'':Filter::filter_html($_POST['vote_cat']);
		if(!empty($voteCat)){
			$where .= " and vote_cat='{$voteCat}' ";
		}
		//查询评价项
		$p=isset($_GET["p"])?$_GET["p"]:"0"; 
		if($p<1)$p=1;
		$recordN = $pd->query("select * from oa_zhsz_votelib where {$where} order by vote_cat")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($recordN);
			
		//$rc= $T->pd->query("select count(1) from oa_zhsz_votelib where {$where} order by vote_cat")->fetchColumn(0);
		$page=getPageHtml_bt($rc,20,$p,"&t=vote_manage");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->SetBlock("list","select * from oa_zhsz_votelib where {$where} order by vote_cat limit ".(($p-1)*20).",20",array('timestamp'),array('%Y-%m-%d %H:%M'));
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("voteCat",$voteCat);
		break; 
		
	case "zhsz_performance": #日常表现
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$sid = 0;
		}else{
			$sid = $curTerm['id'];	
		}
		//查询综评表信息
		$id  = empty($_GET['term_id'])?0:Filter::filter_number($_GET['term_id']);
		$uid = empty($_GET['uid'])?"":Filter::filter_html($_GET['uid']);
		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		
		//查询学生信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		if($id!=0){
			$sid = $id;	
		}
		$isMaster = false;
		$isMaster1 = 0;
		$isAdd  = false;
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			if(!empty($class)&&$user['dept']==$class['id']){
				$isMaster = true;
				$isAdd    = true;
				$isMaster1 = 1;
			}
		}
		
		$term   = showTerm($pd,$uid);
		$str = '';
		foreach($term as $rs){
			$selected = '';
			if($rs['id']==$sid){
				$selected = 'class="current"';	
			}
			$str.='<label '.$selected.'><a href="./?t=zhsz_performance&term_id='.$rs['id'].'&uid='.$uid.'">'.$rs['term'].'</a></label>';
		}
		if($isAdd){
			$str.='<a href="./?t=zhsz_performance_add&uid'.$uid.'" style="float:right; width:80px; height:30px; line-height:30px; display:block; overflow:hidden;">添加日常表现</a>';
		}
		
		//查询的学期
		$selectTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>"id={$sid} and school={$_SESSION['school']}"));
		
		//查询日常表现
		$page   = empty($_GET['page'])?1:$_GET['page'];
		if($isMaster == true){
			$where  = "user_id='{$uid}' and term_id={$sid}";
		}else{
			$uclass  = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid = '{$_SESSION['uid']}'"));
			if($user['dept']==$uclass){
				$where  = "user_id='{$uid}' and term_id={$sid} and pertype=0 or pertype=2";  
			}else{
				$where  = "user_id='{$uid}' and term_id={$sid} and pertype=0"; 
			}
		}
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$sql 	= "select * ";
		$sql 	.= " from oa_zhsz_performance where  {$where} ";
		$sql	.= " order by id desc  limit {$offset},20 ";
		//动态信息查询
		$performance  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_performance','where'=>$where);
		$rc  = $pd->fetchOne($params);
		$page=getPageHtml_bt($rc,20,$p,"&t=zhsz_performance");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		if(empty($performance)){
			$tips = " {$user['truename']}同学在 {$selectTerm['year']} {$selectTerm['term_name']} 无日常表现记录";
		}else{
			$tips = " {$user['truename']}同学在 {$selectTerm['year']} {$selectTerm['term_name']} 的日常表现记录";
		}
		
		$str_total = '';
		foreach($performance as $rs){
			$str_total .= '<tr id="tr_c_'.$rs['id'].'"><td class="tabtd">'.$rs['performance'].'</td><td class="tabtd">'.$rs['create_time'].'</td>';
			if($isMaster==true){
				$str1 = '';
				if($rs['pertype']==null)
					$rs['pertype'] = 0;
				if($rs['pertype']==0)
					{$str1 = "公开";}
				elseif($rs['pertype']==1)
					{$str1 = "保密";}
				else
					{$str1 = "全班";}
				
				$str_total .= '<td class="tabtd pertype">'.$str1.'</td><td class="tabtd"><a class="chpertype" href="javascript:void(0);" onclick="chPerformance('.$rs['id'].','.$rs['pertype'].');">修改状态</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="delPerformance('.$rs['id'].');">删除</a></td></tr>';
			}
		}
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("str_total",$str_total); 
		$T->Set("isMaster1",$isMaster1);
		$T->Set("sid",$sid);
		$T->Set("str",$str);
		$T->Set("uid",$uid);
		$T->Set("tips",$tips);
		$T->Set("curTerm_id",$curTerm['id']);
		$T->Set("truename",$user['truename']);
		$T->Set("flag_type",$_SESSION['flag_type']);
		break;
		
	case "zhsz_other_vote": #我要举报
		$uid = empty($_GET['uid'])?0:Filter::filter_html($_GET['uid']);
		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'act_member','where'=>"id='{$uid}'"));
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];
		
		//查询是否可他评
		//平台基本信息
		$params = array('field'=>array('self_start','self_end'),'table'=>'oa_zhsz_system','where'=>'school='.$_SESSION['school']);
		$web    = $pd->fetchRow($params);
		
		//没有设置则当前学期结束日期的前10天内可填写
		if(empty($web['self_start'])){
			$start = date('Y-m-d',strtotime("-10 days",strtotime($curTerm['end'])));
			$end   = $curTerm['end'];
		}else{
			$start = $web['self_start'];
			$end   = $web['self_end'];
		}
		//查询当前学期学生互评
		$votes = $pd->query("select b.name,b.item,a.item_id from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='学生互评' and a.term_id={$sid} and a.user_id='{$uid}' and a.reply_user='{$_SESSION['uid']}' order by a.vote_id")->fetchAll(PDO::FETCH_ASSOC);

		$now = date('Y-m-d');
		
		$str = '';
		if($now>=$start&&$now<=$end){
			//在可编辑范围日期内
			if(empty($votes)){
				//没有评价过，开始评价
				//查询学生互评项
				$voteLib  = $pd->query("select * from oa_zhsz_votelib where vote_cat='学生互评'")->fetchAll(PDO::FETCH_ASSOC);
				$str .= '<form method="post" action="./srv/zhsz_vote_submit.php" onsubmit="return checkVote();">';
				$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
				$str .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="65%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				$ids = '';
				foreach($voteLib as $rs){
					$str .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;"><input type="hidden" name="vote[]" value="'.$rs['id'].'" />';
					$item = explode(',',$rs['item']);
					$j = 1;
					foreach($item as $rsb){
						$str .= "<input type='radio' name='item_{$rs['id']}' value='{$j}' />&nbsp;&nbsp;{$rsb}&nbsp;&nbsp;";	
						$j++;
					}
					$str .= '</td></tr>';
					$ids .= $rs['id'].',';
				}
				$ids = trim($ids,',');
				$str .= '<tr><td class="tabtd2_R" colspan="3" style="text-align:center; padding:5px 0;">';
				$str .= '<input type="hidden" name="vote_cat" value="学生互评" />';
				$str .= '<input type="hidden" name="term_id" value="'.$sid.'" />';
				$str .= '<input type="hidden" name="user_id" value="'.$uid.'" />';
				$str .= '<input type="hidden" name="ids" id="ids" value="'.$ids.'" />';
				$str .= '<input name="but_exp" id="but_exp" type="submit" value="保 存" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" /></td></tr>';
				$str .= '</table></form>';
			}else{//显示结果
				$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str .= $item[$j].'</td></tr>';
				}
				$str .= '</table>';
			}
		}else{
			if(empty($votes)){//没有评价过，开始评价
				$tips = '暂未开始评价。';
				$str .= '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
			}else{//显示结果
				$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str .= $item[$j].'</td></tr>';
				}
				$str .= '</table>';
			}
		}
		
		$T->Set("str",$str);
		$T->Set("uid",$uid);
		$T->Set("truename",$user['truename']);
		break;
		
	case "zhsz_other_comment": #他人评论
		$uid = empty($_GET['uid'])?0:Filter::filter_html($_GET['uid']);
		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];
		//是否在同一个班级（当前登录者和被查看者）
		
		$d1 = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		
		$d2 = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$uid}'"));
		
		if($d1==$d2){
			$where = " (A.flag_checked=1 OR A.flag_checked=2) ";	
		}else{
			$where = " A.flag_checked=1 ";	
		}
		
		//查询历史评论
		$where .= " AND A.user_id='{$uid}'";
		$sql 	= 'select A.*,B.truename as bname,B.dept ';
		$sql 	.= ' from oa_zhsz_comment AS A ';
		$sql 	.= ' inner join v_users AS B ON A.user_id=B.id ';
		$sql 	.= " where {$where} ";

		$sql 	.= ' order by A.id DESC ';
		//评论查询
		$comment  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$isMaster = false;
		$isMaster1 = 0;
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			if(!empty($class)&&$user['dept']==$class['id']){
				$isMaster = true;
				$isMaster1 = 1;
			}
		}
		
		$str = '';
		if(!empty($comment)){
			$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;"><tr class="tabtitle"><td class="tabtd2_L" width="60%" style="text-align:center;">他人评论</td><td class="tabtd2_R" style="text-align:center;">评论日期</td><td class="tabtd2_R" style="text-align:center;">评论人</td><td class="tabtd2_R" style="text-align:center;">审核状态</td></tr>';
			
			foreach($comment as $rs){
				$tips = '不公开';
				if($rs['flag_checked']==1){
					$tips = '全体学生公开';
				}
				if($rs['flag_checked']==2){
					$tips = '只对本班公开';
				}
				if($rs['flag_checked']==3){
					$tips = '只对这个学生公开';
				}
				$user_name = '';
				if($rs['user_type']==1){
					$user_name = '同学';
				}
				if($rs['user_type']==2){
					$user_name = '老师';
					//查询是否为班主任
					$masterId = $pd->fetchOne(array('field'=>'class_master','table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
					if(!empty($masterId)){
						$masterName = $pd->fetchOne(array('field'=>'username','table'=>'oa_zhsz_users','where'=>"id={$masterId}"));
						if($masterName==$rs['create_by']){
							$user_name = '班主任';
						}
					}
				}
				if($rs['user_type']==3){
					$user_name = '家长';
				}
				if($rs['user_type']==4){
					$user_name = '管理人员';
				}
				$str .= "<tr id='tr_c_{$rs['id']}'><td class='tabtd2_R'>{$rs['content']}</td><td class='tabtd2_R' style='text-align:center;'>{$rs['create_time']}</td><td class='tabtd2_R' style='text-align:center;'>{$user_name}</td><td class='tabtd2_R' style='text-align:center;'>{$tips}</td></tr>";	
			}
			$str .= '</table>';
		}
			
		$T->Set("isMaster1",$isMaster1);
		$T->Set("truename",$user['truename']);
		$T->Set("uid",$uid);
		$T->Set("sid",$sid);
		$T->Set("str",$str);
		$T->Set("flag_type",$_SESSION['flag_type']);
		break;
		
	case "zhsz_master_vote": #班主任评价
		$isMaster = false;
		//查询当前代班
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		
		if(!empty($class)){
			$isMaster = true;
		}
		//不是班主任
		if($_SESSION['flag_type']!=2&&$isMaster==false){
			header('Location:systeminfo.php');
			exit;	
		}
		$uid = empty($_GET['uid'])?0:Filter::filter_html($_GET['uid']);
		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		if($uid==$_SESSION['uid']){
			header('Location:./systeminfo.php');
			exit;	
		}
		
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'act_member','where'=>"id='{$uid}'"));
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => './?t=user_modify'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		$sid = $curTerm['id'];
		
		//平台基本信息
		$params = array('field'=>array('self_start','self_end'),'table'=>'oa_zhsz_system','where'=>'school='.$_SESSION['school']);
		$web    = $pd->fetchRow($params);
		
		//没有设置则当前学期结束日期的前10天内可填写
		if(empty($web['self_start'])){
			$start = date('Y-m-d',strtotime("-10 days",strtotime($curTerm['end'])));
			$end   = $curTerm['end'];
		}else{
			$start = $web['self_start'];
			$end   = $web['self_end'];
		}
		//查询当前学期班主任评价
		$votes = $pd->query("select b.name,b.item,a.item_id from oa_zhsz_vote as a inner join oa_zhsz_votelib as b on a.vote_id=b.id where a.vote_cat='班主任评价' and a.term_id={$sid} and a.user_id='{$uid}' and a.reply_user='{$_SESSION['uid']}' order by a.vote_id")->fetchAll(PDO::FETCH_ASSOC);
		$now = date('Y-m-d');

		$str = '';
		//直接评价
		if(true){
			//在可编辑范围日期内
			if(empty($votes)){
				//没有评价过，开始评价
				//查询班主任评价项
				$voteLib  = $pd->query("select * from oa_zhsz_votelib where vote_cat='班主任评价'")->fetchAll(PDO::FETCH_ASSOC);
				
				$str .= '<form method="post" action="./srv/zhsz_vote_submit.php" onsubmit="return checkVote();">';
				$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0">';
				$str .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="65%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				$ids = '';
				foreach($voteLib as $rs){
					$str .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;"><input type="hidden" name="vote[]" value="'.$rs['id'].'" />';
					$item = explode(',',$rs['item']);
					$j = 1;
					foreach($item as $rsb){
						$str .= "<input type='radio' name='item_{$rs['id']}' value='{$j}' />&nbsp;&nbsp;{$rsb}&nbsp;&nbsp;";	
						$j++;
					}
					$str .= '</td></tr>';
					$ids .= $rs['id'].',';
				}
				$ids = trim($ids,',');
				$str .= '<tr><td class="tabtd2_R" colspan="3" style="text-align:center; padding:5px 0;">';
				$str .= '<input type="hidden" name="vote_cat" value="班主任评价" />';
				$str .= '<input type="hidden" name="term_id" value="'.$sid.'" />';
				$str .= '<input type="hidden" name="user_id" value="'.$uid.'" />';
				$str .= '<input type="hidden" name="ids" id="ids" value="'.$ids.'" />';
				$str .= '<input name="but_exp" id="but_exp" type="submit" value="保 存" style="background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;" />
				<input name="but_back" id="but_back" type="button" value="返 回" onclick="history.back();" style="margin-left:15px;background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;"></td></tr>';
				$str .= '</table></form>';
			}else{//显示结果
				$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str .= $item[$j].'</td></tr>';
				}
				$str .= '</table>
				<div style="width:55%;margin:10px auto;float:right;">
				<input name="but_back" id="but_back" type="button" value="返 回" onclick="history.back();" style="margin-left:15px;background:url(images/butbg.gif) no-repeat top; width:75px; height:25px; border:0; cursor:hand;">
			</div>';
			}
		}else{
			if(empty($votes)){//没有评价过，开始评价
				$tips = '暂未开始评价。';
				$str .= '<div class="boxtitle2" style="text-align:center; margin-bottom:10px;">'.$tips.'</div>';
			}else{//显示结果
				$str .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">';
				$str .= '<tr><td class="tabtd2_L" width="5%" style="text-align:center;">序号</td><td class="tabtd2_L" width="85%" style="text-align:center;">评价项</td><td class="tabtd2_L"style="text-align:center;">结果项</td></tr>';
				$i = 1;
				foreach($votes as $rs){
					$str .= '<tr><td class="tabtd2_L" style="text-align:center;">'.$i++.'</td><td class="tabtd2_L" style="text-align:left;">'.$rs['name'].'</td><td class="tabtd2_L" style="text-align:center;">';
					$item = explode(',',$rs['item']);
					$j = $rs['item_id'] - 1;
					$str .= $item[$j].'</td></tr>';
				}
				$str .= '</table>';
			}
		}
				
				
		$T->Set("uid",$uid);
		$T->Set("str",$str);
		$T->Set("truename",$user['truename']);
		break;
		
	case "zhsz_master_comment": #班主任评论
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$sid = 0;
		}else{
			$sid = $curTerm['id'];	
		}
		//查询综评表信息
		$id  = empty($_GET['term_id'])?0:Filter::filter_number($_GET['term_id']);
		$uid = empty($_GET['uid'])?0:Filter::filter_html($_GET['uid']);
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;

		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		//查询学生信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		if($id!=0){
			$sid = $id;	
		}
		$isMaster = false;
		$isMaster1 = 0;
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			if(!empty($class)&&$class['id']==$user['dept']){
				$isMaster = true;
				$isMaster1 = 1;
			}
		}
		
		//查询学期评语
		$page   = empty($_GET['page'])?1:$_GET['page'];
		$where  = "A.user_id='{$uid}'";
		
		$sql 	= "select A.id,A.class_name,A.master_name,A.content,A.create_time,B.year,B.term_name ";
		$sql 	.= " from oa_zhsz_teacher_comment AS A ";
		$sql 	.= " join oa_zhsz_term AS B ON A.term_id=B.id ";
		$sql    .= " where  {$where} ";
		$sql	.= " order by A.id desc  limit {$offset},20 ";
		//动态信息查询
		$performance  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_teacher_comment AS A','where'=>$where);
		$rc  = $pd->fetchOne($params);
		$page=getPageHtml_bt($rc,20,$p,"&t=zhsz_master_comment&uid={uid}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		if(empty($performance)){
			$tips = " {$user['truename']}同学暂无学期评语";
		}else{
			$tips = " {$user['truename']}同学学期评语记录";
		}
				
		$str = '';
		foreach($performance as $rs){
			$str1 = empty($rs['content'])?'暂无学期评语':$rs['content'];
			$str .= '<tr id="tr_c_'.$rs['id'].'"><td class="tabtd">'.$rs['year'].$rs['term_name'].'</td><td class="tabtd">'.$rs['class_name'].'</td><td class="tabtd">'.$rs['master_name'].'</td><td class="tabtd">'.$str1.'</td><td class="tabtd ha_maedit"><img src="images/037.gif"><a onclick="edit('.$rs['id'].')" style="display:inline-block;height: 30px;line-height: 30px;width: 25%">修改</a></td>';
		}
		
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("isMaster1",$isMaster1);
		$T->Set("tips",$tips);
		$T->Set("truename",$user['truename']);
		$T->Set("uid",$uid);
		$T->Set("sid",$sid);
		$T->Set("str",$str);
		$T->Set("curTerm_id",$curTerm['id']);
		$T->Set("flag_type",$_SESSION['flag_type']);
		break;
		
	case "reply_manage": #他人评价
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$isMaster = true;
		}
		
		$strWhere = '1=1';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);
		$userCard = empty($_POST['user_card'])?'':$_POST['user_card'];
		$userCard = Filter::filter_html($userCard);
		//查询条件
		if(!empty($truename)){
			$strWhere .= " AND B.truename LIKE'{$truename}%' ";
		}
		if(!empty($userCard)){
			$strWhere .= " AND B.user_card='{$userCard}' ";
		}
		if($isMaster==true){
			$strWhere .= " AND B.dept='{$class['id']}' ";	
		}
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		//分页配置
		$where  = $strWhere;
		$sql 	= 'select A.*,B.truename as bname,B.dept ';
		$sql 	.= ' from oa_zhsz_comment AS A ';
		$sql 	.= ' inner join v_users AS B ON A.user_id=B.id ';
		$sql 	.= " where {$where} ";
		$sql2   = $sql;
		$sql 	.= ' order by A.id DESC ';
		$sql 	.= " limit {$offset},20";
		//评论查询
		$reply  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		//查询所有评论
		$replyC = $pd->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($replyC);
		
		$str_total = '';
		$i = 1;
		foreach($reply as $rs){
			$tips = '不公开';
			if($rs['flag_checked']==1){
				$tips = '全体学生公开';
			}
			if($rs['flag_checked']==2){
				$tips = '只对本班公开';
			}
			if($rs['flag_checked']==3){
				$tips = '只对这个学生公开';
			}
			$user = '';
			if($rs['user_type']==1){
				$user = '同学';
			}
			if($rs['user_type']==2){
				$user = '老师';
				//查询是否为班主任
				$masterId = $pd->fetchOne(array('field'=>'class_master','table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
				if(!empty($masterId)){
					$masterName = $pd->fetchOne(array('field'=>'username','table'=>'act_member','where'=>"id='{$masterId}'"));
					if($masterName==$rs['create_by']){
						$user = '班主任';
					}
				}
			}
			if($rs['user_type']==3){
				$user = '家长';
			}
			if($rs['user_type']==4){
				$user = '管理人员';
			}
			
			$str_total .= '<tr id="reply_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tabtd" width="5%">'.$i.'</td><td class="tabtd"  width="10%">'.$rs['bname'].'</td><td class="tabtd"  width="55%">'.$rs['content'].'</td><td class="tabtd" width="10%">'.$tips.'</td><td class="tabtd" width="10%">'.$user.'</td><td class="tabtd" align="left">&nbsp;&nbsp;';
			
			//if(isset($priv[1])){
				$str_total .= '<img src="images/037.gif" /> <a href="javascript:showReply('.$rs['id'].','.$rs['flag_checked'].');">审核</a>';
			//}
			//if(isset($priv[3])){
				$str_total .= '<img src="images/010.gif" /> <a href="javascript:delReply('.$rs['id'].');">删除</a>';
			//}
			
			$str_total .= '</td></tr>';
			$i++;
		}
						
		$page=getPageHtml_bt($rc,20,$p,"&t=reply_manage");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("truename",$truename);
		$T->Set("str_total",$str_total);
		break; 
		
	case "jiangcheng": #学生奖惩管理
	    $from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$value = '';
        if(!empty($_POST['but_op'])){
			$value=$_POST['but_op'];
		}else if(!empty($_POST['h_but_op'])){
			$value=$_POST['h_but_op'];
		}
		//判断提交方式
		if($submitMethod=='POST' && $value=="确定"){
			$menu1 = 0;
			$menu2 = 68;
			$geter     = $_POST['uid'];
			
			$id     = $_POST['id'];
			$op     = $_POST['op'];
			$ids = join(',',$id);

			if($op==1){//审核
				$data = array('flag_status'=>'已审核');
			}
			if($op==2){//取消审核
				$data = array('flag_status'=>'待审核');
			}
			$r = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_reward_punishment','where'=>"id IN ({$ids})"));
			if($r){
				$tips = array(
					'tips' => '审核操作成功。',
					'url'  => './?t=jiangcheng'
				);
				for($i=0;$i<count($id);$i++){
					$pd->query("insert into oa_message(uid,geter,leaveword,stime,menu1,menu2,is_read,subject,flag,from_id) select '".$uid."','".$geter[$i]."',(select content from oa_zhsz_reward_punishment where id=".$id[$i]."),'".date('Y-m-d H:i:s',time())."','".$menu1."','".$menu2."',0,(select flag_type from oa_zhsz_reward_punishment where id=".$id[$i]."),(select flag_status from oa_zhsz_reward_punishment where id=".$id[$i]."),".$id[$i]." from oa_zhsz_reward_punishment where id=".$id[$i]);
				}
			}else{
				$tips = array(
					'tips' => '审核操作失败。',
					'url'  => './?t=jiangcheng'
				);
			}
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$tid_page=isset($_GET["tid_page"])?$_GET["tid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$flagType_page=isset($_GET["flagType_page"])?$_GET["flagType_page"]:0;
		
		//查询综评表信息
		$tid  = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id'],0);
		$gid  = empty($_POST['gid'])?0:Filter::filter_number($_POST['gid']);
		$cid  = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
		$flagType  = empty($_POST['flag_type'])?'':Filter::filter_html($_POST['flag_type']);
		$truename  = empty($_POST['truename'])?'':Filter::filter_html($_POST['truename']);
		
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		if($tid_page)
			$tid = $tid_page;
		if($flagType_page)
			$flagType = $flagType_page;
		
		$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
			$isMaster1 = 1;
		}
		
		//查询当前学期
		$curTerm   = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if($tid==0){
			$tid = $curTerm['id'];	
		}
		$strWhere = ' 1=1 ';
		if($tid!=0){
			if($tid!=-1){
				$strWhere .= " and a.term_id={$tid} ";
			}else{
			}
		}
		if($flagType!=''){
			$strWhere .= " and a.flag_type='{$flagType}' ";
		}
		
		if($gid>0){
			//查询所属于班级
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and a.class_id IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and a.class_id={$classId} ";
			}
		}
		
		if(!empty($truename)){
			$strWhere .= " and a.truename LIKE'{$truename}%' ";
		}
		$str_check = '';
		$str_show = '';
		$str_select = '';
		//if(isset($priv[3])){
		if($_SESSION['role_id']==4|| $_SESSION['role_id']==6){
			$is_show = 1;
			//$str_show = '<td width="5%" class="tabtd" style="text-align:center;">状态</td><td width="5%" class="tabtd" style="text-align:center;">操作选项</td>';
			$str_check = '<div style="background:#fff;margin-bottom:10px;"><select name="op" id="op"  class="ui-select"><option value="0">请选择操作</option><option value="1">审核通过</option><option value="2">取消审核</option></select><input type="submit" class="ha_selchoose" id="but_op" name="but_op" value="确定" /></div>';
			
			$str_top = '<td width="5%" class="tabtd"><input type="checkbox" name="all_select" id="all_select" value="1" onclick="selectAll(\'id[]\');" /></td>';
		}else{
			$is_show = 0;
			if(!empty($jiyu)){
			}
			$str_top = '<td width="5%" class="tabtd"> <style="text-align:center;">序号</td>';
		}
		
		//查询日常表现
		$where  = $strWhere;
		if($from_id){  //消息跳转
			$where = "a.id={$from_id}";
		}
		$sql 	= "select a.*,(select concat(year,term_name) from oa_zhsz_term where id=a.term_id) term_name";
		$sql 	.= " from oa_zhsz_reward_punishment as a where  {$where} ";
		$sql	.= " order by a.id desc  limit {$offset},20 ";
		//动态信息查询
		//$performance  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$T->SetBlock("list",$sql);
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_reward_punishment as a','where'=>$where);
		$rc  = $pd->fetchOne($params);

		$page=getPageHtml_bt($rc,20,$p,"&t=jiangcheng&gid_page={$gid}&cid_page={$cid}&flagType_page={$flagType}&tid_page={$tid}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("truename",$truename);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("term_id",$tid);
		$T->Set("str_check",$str_check);
		$T->Set("str_top",$str_top);
		$T->Set("str_show",$str_show);
		$T->Set("str_select",$str_select);
		$T->Set("is_show",$is_show);
		$T->Set("flag_type",$flagType);
		$T->Set("isMaster1",$isMaster1);
		break; 
		
	case "jiangcheng_op": #学生奖惩添加修改
		$id = empty($_GET['id'])?0:Filter::filter_number($_GET['id']);
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));

		if(!empty($class)){
			$master_val=$class['class_master'];
		}else{
			$master_val='';
		}

		//查询信息
		$params  = array('field'=>array('a.*','(select concat(year,term_name) from oa_zhsz_term where id=a.term_id) term_name'),'table'=>'oa_zhsz_reward_punishment as a','where'=>"id={$id}");
		$info = $pd->fetchRow($params);
		if(empty($info)){
			$info['content'] = '';
			$info['term_name'] = '';
			$info['truename'] = '';
			$info['flag_type'] = '';
			$info['class_name'] = '';
			$info['flag_status'] = '';
			$info['id'] = '';
		}
		if($id==0){
			$op   = 'add';
			$butV = '添 加';	
		}else{
			$op   = 'edit';	
			$butV = '修 改';	
		}
		
		if($_SESSION['role_id']==1)
			$role = 1;
		else
			$role = 0;
		
		$T->Set("op",$op);
		$T->Set("butV",$butV);
		$T->Set("truename",$info['truename']);
		$T->Set("class_name",$info['class_name']);
		$T->Set("flag_type",$info['flag_type']);
		$T->Set("id",$info['id']);
		$T->Set("term_name",$info['term_name']);
		$T->Set("content",$info['content']);
		$T->Set("flag_status",$info['flag_status']);
		$T->Set("butV",$butV);
		$T->Set("role",$role);
		$T->Set("master_val",$master_val);
		break;
		
	case "jiangcheng_import": #学生奖惩导入
		$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id','class_name'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$grade = $pd->fetchRow(array('field'=>array('grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$class["grade_id"]}"));
			$cid = $class['id'];
			$cname = $class['class_name'];
			$gid = $class['grade_id'];
			$gname = $grade['grade_name'];
			$isMaster = true;
			$isMaster1 = 1;
			$T->Set("cid",$cid);
			$T->Set("cname",$cname);
			$T->Set("gid",$gid);
			$T->Set("gname",$gname);
		}
		
		$T->Set("isMaster1",$isMaster1);
		break;
		
	case "zhsz_score": #成绩学分查询
		//平台基本信息
		$params = array('field'=>array('flag_avatar','course_start','course_end','course_select'),'table'=>'oa_zhsz_system','where'=>'school='.$_SESSION['school']);
		$web    = $pd->fetchRow($params);
		//查询个人信息
		$params = array('field'=>array('*'),'table'=>'act_member','where'=>"id='{$_SESSION['uid']}'");
		$user   = $pd->fetchRow($params);
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		$sid = $curTerm['id'];
		//查询综评表信息
		$id = empty($_GET['term_id'])?0:Filter::filter_number($_GET['term_id']);
		if($id!=0){
			$sid = $id;	
		}

		$term   = showTerm($pd,$_SESSION['uid']);
		$str = '';
		foreach($term as $rs){
			$selected = '';
			if($rs['id']==$sid){
				$selected = 'class="current"';	
			}
			$str.='<label '.$selected.'><a href="./?t=zhsz_score&term_id='.$rs['id'].'">'.$rs['term'].'</a></label>';
		}
		
		$str_total = '';
		//查询的学期
		$selectTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>"id={$sid}"));
		//查询考试信息
		$exam  = $pd->query("select * from oa_zhsz_score where user_id='{$_SESSION['uid']}' and term_id={$sid} order by id desc")->fetchAll(PDO::FETCH_ASSOC);
		if(empty($exam)){
			$tips = " {$user['truename']}同学在 {$selectTerm['year']} {$selectTerm['term_name']} 无考试成绩";
			$str_total =  '<div style="width;100%; height:30px; line-height:30px; text-align:center; font-size:14px;">'.$tips.'</div>';
			$tips = "";
		}else{
			$tips = " {$user['truename']}同学在 {$selectTerm['year']} {$selectTerm['term_name']} 的考试成绩";
		}
		
		foreach($exam as $rs){
			$str_cj = $rs['yw']==-1?'-':$rs['yw'].' / ';
			$str_xf = $rs['yw_xf']==-1?'-':$rs['yw_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str1 = $str_cj.$str_xf;
			$str_cj = $rs['sx']==-1?'-':$rs['sx'].' / ';
			$str_xf = $rs['sx_xf']==-1?'-':$rs['sx_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str2 = $str_cj.$str_xf;
			$str_cj = $rs['wy']==-1?'-':$rs['wy'].' / ';
			$str_xf = $rs['wy_xf']==-1?'-':$rs['wy_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str3 = $str_cj.$str_xf;
			$str_cj = $rs['wl']==-1?'-':$rs['wl'].' / ';
			$str_xf = $rs['wl_xf']==-1?'-':$rs['wl_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str4 = $str_cj.$str_xf;
			$str_cj = $rs['hx']==-1?'-':$rs['hx'].' / ';
			$str_xf = $rs['hx_xf']==-1?'-':$rs['hx_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str5 = $str_cj.$str_xf;
			$str_cj = $rs['sw']==-1?'-':$rs['sw'].' / ';
			$str_xf = $rs['sw_xf']==-1?'-':$rs['sw_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str6 = $str_cj.$str_xf;
			$str_cj = $rs['zz']==-1?'-':$rs['zz'].' / ';
			$str_xf = $rs['zz_xf']==-1?'-':$rs['zz_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str7 = $str_cj.$str_xf;
			$str_cj = $rs['ls']==-1?'-':$rs['ls'].' / ';
			$str_xf = $rs['ls_xf']==-1?'-':$rs['ls_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str8 = $str_cj.$str_xf;
			$str_cj = $rs['dl']==-1?'-':$rs['dl'].' / ';
			$str_xf = $rs['dl_xf']==-1?'-':$rs['dl_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str9 = $str_cj.$str_xf;
			$str_cj = $rs['szf']==-1?'-':$rs['szf'].' / ';
			$str_xf = $rs['sxf']==-1?'-':$rs['sxf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str10 = $str_cj.$str_xf;
			$str_cj = $rs['xxjs']==-1?'-':$rs['xxjs'].' / ';
			$str_xf = $rs['xxjs_xf']==-1?'-':$rs['xxjs_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str11 = $str_cj.$str_xf;
			$str_cj = $rs['tyjs']==-1?'-':$rs['tyjs'].' / ';
			$str_xf = $rs['tyjs_xf']==-1?'-':$rs['tyjs_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str12 = $str_cj.$str_xf;
			$str_cj = $rs['ty']==-1?'-':$rs['ty'].' / ';
			$str_xf = $rs['ty']==-1?'-':$rs['ty'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str13 = $str_cj.$str_xf;
			$str_cj = $rs['xx']==-1?'-':$rs['xx'].' / ';
			$str_xf = $rs['xx_xf']==-1?'-':$rs['xx_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str14 = $str_cj.$str_xf;
			$str_cj = $rs['yy']==-1?'-':$rs['yy'].' / ';
			$str_xf = $rs['yy_xf']==-1?'-':$rs['yy_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str15 = $str_cj.$str_xf;
			$str_cj = $rs['ms']==-1?'-':$rs['ms'].' / ';
			$str_xf = $rs['ms_xf']==-1?'-':$rs['ms_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str16 = $str_cj.$str_xf;
			$str_cj = $rs['xl']==-1?'-':$rs['xl'].' / ';
			$str_xf = $rs['xl_xf']==-1?'-':$rs['xl_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str17 = $str_cj.$str_xf;

			$str_total .= '<table width="1150px" border="0" class="tab" cellpadding="0" cellspacing="0" style="margin:15px 10px 10px 15px;"><tr><td colspan="10" class="tabtd2_R">'.$rs['exam_type'].'考试成绩</td></tr><tr class="tabtitle"><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">语文</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">数学</td><td class="tabtd2_L" style="width:80px;font-weight:bold; text-align:center;">外语</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">物理</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">化学</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">生物</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">政治</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">历史</td><td class="tabtd2_L" style="width:105px;font-weight:bold; text-align:center;">地理</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">总分</td></tr><tr><td class="tabtd2_L" style="text-align:center;">'.$str1.'</td><td class="tabtd2_L" style="text-align:center;">'.$str2.'</td><td class="tabtd2_L" style="text-align:center;">'.$str3.'</td><td class="tabtd2_L" style="text-align:center;">'.$str4.'</td><td class="tabtd2_L" style="text-align:center;">'.$str5.'</td><td class="tabtd2_L" style="text-align:center;">'.$str6.'</td><td class="tabtd2_L" style="text-align:center;">'.$str7.'</td><td class="tabtd2_L" style="text-align:center;">'.$str8.'</td><td class="tabtd2_L" style="text-align:center;">'.$str9.'</td><td class="tabtd2_L" style="text-align:center;">'.$str10.'</td></tr><tr><td class="tabtitle" style="text-align:center; font-weight:bold;" colspan="2" rowspan="2">其它学科</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">信息技术</td><td class="tabtd2_R" style="text-align:center;">'.$str11.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">通用技术</td><td class="tabtd2_R" style="text-align:center;">'.$str12.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">体育与健康</td><td class="tabtd2_R" style="text-align:center;">'.$str13.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">选修II</td><td class="tabtd2_L" style="text-align:center;">'.$str14.'</td></tr><tr><td class="tabtd2_L" style="text-align:center;font-weight:bold;">音乐</td><td class="tabtd2_R" style="text-align:center;">'.$str15.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">美术</td><td class="tabtd2_R" style="text-align:center;">'.$str16.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">心理</td><td class="tabtd2_R" style="text-align:center;">'.$str17.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;">&nbsp;</td><td class="tabtd2_L" style="text-align:center;">&nbsp;</td></tr></table>';
		}

		$T->Set("str_total",$str_total);
		$T->Set("str",$str);
		$T->Set("tips",$tips);
		break;
		
	//班主任看到的一个学生的消息页面
    /* case "message_teacher_filter":
	    $submitMethod = $_SERVER["REQUEST_METHOD"];
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$stu_id=isset($_GET["stu_id"])?$_GET["stu_id"]:"";
		$offset = ($p-1)*20;
		#查询消息状态
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		
		$status="";
		//判断提交方式
		if($submitMethod=='POST'){
			$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
			$where="";
			if(!empty($_POST['status'])){
				if($_POST['status']=="1")
					$status="待审核";
				elseif($_POST['status']=="2")
					$status="已审核";
				else
					$status="未通过";
				$where.="M.flag='".$status."' and ";
				$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.geter where ".$where." M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20");
				$r = $T->db->query("update oa_message set is_read=1 where id in ({$ids})");
				$rc= $T->db->query("select count(1) from oa_message as M where ".$where." geter='{$_SESSION['uid']}'")->fetchColumn(0);
				$page=getPageHtml_bt($rc,20,$p,"&t=message_teacher");
		        $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		        $T->Set("page",$page); 
			}else{
				$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.geter where  M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20");
				$r = $T->db->query("update oa_message set is_read={$_POST['read_select']} where id in ({$ids})");
				$rc= $T->db->query("select count(1) from oa_message as M where geter='{$_SESSION['uid']}'")->fetchColumn(0);
				$page=getPageHtml_bt($rc,20,$p,"&t=message_teacher");
		        $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		        $T->Set("page",$page); 
				Header("Location:./?t=message_teacher");
			}

		}else{
			$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.uid where M.geter='{$_SESSION['uid']}' and M.uid='".$stu_id."' and M.is_read=0 order by M.is_read,M.stime desc limit {$offset},20");
			//echo "select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.uid where M.geter='{$_SESSION['uid']}' and M.uid='".$stu_id."' order by M.is_read,M.stime desc limit {$offset},20";
			//exit;	
			$rc= $T->db->query("select count(1) from oa_message as M left join act_member as A on A.id=M.uid where geter='{$_SESSION['uid']}' and M.uid= A.id")->fetchColumn(0);
			$page=getPageHtml_bt($rc,20,$p,"&t=message_teacher_filter");
		    $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		    $T->Set("page",$page); 
			
		}
		$T->Set("uid",$_SESSION['uid']);
		
	    break; */
		
	case "remarks": #友朋寄语
		$from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		if(!empty($class)){
			$master_val=$class['class_master'];
		}else{
			$master_val='';
		}
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$value = '';
        if(!empty($_POST['but_op'])){
			$value=$_POST['but_op'];
		}else if(!empty($_POST['h_but_op'])){
			$value=$_POST['h_but_op'];
		}
		$status_page = empty($_GET['status_page'])?'':$_GET['status_page'];
		$jiyu_page = empty($_GET['jiyu_page'])?'':$_GET['jiyu_page'];
		
		$jiyu = empty($_POST['jiyu'])?'':$_POST['jiyu'];
		$status = empty($_POST['status'])?'':$_POST['status'];
		
		if($status_page)
			$status = $status_page;
		if($jiyu_page)
			$jiyu = $jiyu_page;
		
		//判断提交方式
		if($submitMethod=='POST' && $value=="确定"){
			$menu1 = 0;
			$menu2 = 34;
			$geter     = $_POST['uid'];
			
			$id     = $_POST['id'];
			$op     = $_POST['op'];
			$ids = join(',',$id);

			if($op==1){//审核
				$data = array('flag_status'=>'已审核');
			}
			if($op==2){//取消审核
				$data = array('flag_status'=>'待审核');
			}
			$r = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_remarks','where'=>"id IN ({$ids})"));
			if($r){
				$tips = array(
					'tips' => '审核操作成功。',
					'url'  => './?t=remarks'
				);
				for($i=0;$i<count($id);$i++){
					$pd->query("insert into oa_message(uid,geter,leaveword,stime,menu1,menu2,is_read,flag,from_id) select '".$_SESSION['uid']."','".$geter[$i]."',(select content from oa_zhsz_remarks where id=".$id[$i]."),'".date('Y-m-d H:i:s',time())."','".$menu1."','".$menu2."',0,(select flag_status from oa_zhsz_remarks where id=".$id[$i]."),".$id[$i]." from oa_zhsz_remarks where id=".$id[$i]);
				}
			}else{
				$tips = array(
					'tips' => '审核操作失败。',
					'url'  => './?t=remarks'
				);
			}
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		
		$p=isset($_GET["p"])?$_GET["p"]:"0";
		if($p==1) $p = 0;
		$strWhere = '1=1';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);
		//if($_SESSION['role_id']!=4){
		//	$strWhere .= ' and A.flag_status="已审核"';
		//}
		//查询条件
		if(!empty($truename)){
			$strWhere .= " AND C.truename LIKE'{$truename}%'";
		}
		if(!empty($status)){
			$strWhere .= " AND A.flag_status='{$status}'";
		}
		
		if(!empty($jiyu)){
			$strWhere .= " AND A.user_id ='{$_SESSION['uid']}'";
		}else{
			if($_SESSION['role_id']!=4 && $_SESSION['role_id']!=6){
				$strWhere .= " AND A.flag_status =\"已审核\"";
			}
		}
		
		$where  = $strWhere;
		
		if($from_id){  //消息跳转
			$where = "A.id={$from_id}";
		}
		$sql 	= 'select A.*,C.truename as bname,B.dept,B.flag_type ';
		$sql 	.= ' from oa_zhsz_remarks AS A ';
		$sql 	.= ' inner join oa_user_extend AS B ON A.user_id=B.userid inner join act_member AS C ON B.userid=C.id';
		$sql 	.= " where {$where} order by A.create_time DESC,A.flag_status";
		$study = $T->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$str_event = '';
		$i = 1;
		foreach($study as $rs){
			if($rs['flag_type']==1){
				$biye_info = $pd->fetchRow(array('field'=>array('class_name','class_end','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']} and school={$_SESSION['school']}"));	
				if(!$biye_info){
					$class_name = '';
				}else{
					if($biye_info['grade_id']==0){
						$class_end = $biye_info['class_end'];
					}else{
						$class_end = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$biye_info['grade_id']}"));
					}
					$class_name = $class_end.$biye_info['class_name'];
				}
			}else{
				$class_name = $pd->fetchOne(array('field'=>'name','table'=>'oa_zhsz_roles','where'=>"id={$rs['flag_type']}"));
			}
			
			
			if($rs['flag_status'] == "已审核"){
				$is_check = "<font color=\'green\'>已审核</font>";
			}else if($rs['flag_status'] == "未通过"){
				$is_check = "<font color=\"blue\">未通过</font>";
			}else{
				$is_check = "<font color=\"red\">待审核</font>";
			}
			
			$str_total = '';
			//if(isset($priv[3])){
			if($_SESSION['role_id']==4 || $_SESSION['role_id']==6){
				if(!empty($jiyu)){
					$str_total .= '<td class="tabtd2_L" style="text-align:center;">'.$is_check.'</td><td class="tabtd" align="left">&nbsp;&nbsp;&nbsp;&nbsp;<a   id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="删除寄语" onclick="delEvent('.$rs['id'].');" style="width:15px;overflow:hidden;margin:0 6px 0 10px ;">&nbsp;</a></td>';
				}else{
					$str_total .= '<td class="tabtd2_L" style="text-align:center;">'.$is_check.'</td><td class="tabtd" align="left">&nbsp;&nbsp;&nbsp;&nbsp;<a id="shtg" href="javascript:void(0);"class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['user_id'].'\');"></a>&nbsp;<a   id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\'未通过\');" style="width:15px;overflow:hidden;margin:0 6px 0 5px ;">&nbsp;</a></td>';
				}
				$str_first = '<td class="tabtd"><input type="checkbox" name="id[]" id="id_'.$rs['id'].'" value="'.$rs['id'].'" />';
			}else{
				$str_first = '<td class="tabtd2_L" style="text-align:center;">'.$i.'</td>';
				if(!empty($jiyu)){
					$str_total .= '<td class="tabtd2_L" style="text-align:center;">'.$is_check.'</td><td class="tabtd" align="left">&nbsp;&nbsp;&nbsp;&nbsp;<a   id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="删除寄语" onclick="delEvent('.$rs['id'].');" style="width:15px;overflow:hidden;margin:0 6px 0 10px ;">&nbsp;</a></td>';
				}
			}

			$str_event .= '<tr id="exp_'.$rs['id'].'">'.$str_first.'<td class="tabtd" style="display:none;"><input type="checkbox" name="uid[]" uid="uid_'.$rs['user_id'].'" value="'.$rs['user_id'].'" /></td><td class="tabtd2_L" style="text-align:center;">'.$rs['bname'].'</td><td class="tabtd2_L" style="text-align:center;">'.$class_name.'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['create_time'].'</td><td class="tabtd2_L" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$str_check = '';
		$str_show = '';
		$str_select = '';
		//if(isset($priv[3])){
		if($_SESSION['role_id']==4|| $_SESSION['role_id']==6){
			$is_show = 1;
			$str_show = '<td width="5%" class="tabtd" style="text-align:center;">状态</td><td width="5%" class="tabtd" style="text-align:center;">操作选项</td>';
			$str_check = '<div style="background:#fff;margin-bottom:10px;"><select name="op" id="op"  class="ui-select"><option value="0">请选择操作</option><option value="1">审核通过</option><option value="2">取消审核</option></select><input type="submit" class="ha_selchoose" id="but_op" name="but_op" value="确定" /></div>';
			
			$str_top = '<td width="5%" class="tabtd"><input type="checkbox" name="all_select" id="all_select" value="1" onclick="selectAll(\'id[]\');" /></td>';
			$str_select = '<div class="ha_selbox"><span style="margin-left:10px;">状态：</span><select name="status" id="status" class="ui-select"><option value="">全部</option><option value="待审核">待审核</option><option value="已审核">已审核</option><option value="未通过">未通过</option></select></div>';
		}else{
			
			$is_show = 0;
			if(!empty($jiyu)){
				$str_show = '<td width="5%" class="tabtd" style="text-align:center;">状态</td><td width="5%" class="tabtd" style="text-align:center;">操作选项</td>';
			}
			$str_top = '<td width="5%" class="tabtd"> <style="text-align:center;">序号</td>';
		}
		$rc = count($study);

		$status1 = $status;
		$jiyu1 = $jiyu;
		$status1 = mb_convert_encoding($status1, 'gbk', 'utf-8');
		$jiyu1 = mb_convert_encoding($jiyu, 'gbk', 'utf-8');
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=remarks&status_page={$status1}&jiyu_page={$jiyu1}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("uid",$uid);
		//echo $uid;
		//exit;
		$T->Set("truename",$truename);
		$T->Set("str_event",$str_event);
		$T->Set("str_check",$str_check);
		$T->Set("str_top",$str_top);
		$T->Set("str_show",$str_show);
		$T->Set("str_select",$str_select);
		$T->Set("status",$status);
		$T->Set("is_show",$is_show);
		$T->Set("master_val",$master_val);
		break; 
		
	case "physique_stu_modify": #学生体质页面查看详情
	    $tid = empty($_GET['tid'])?0:intval($_GET['tid']);
		$uid = empty($_GET['uid'])?"":$_GET['uid'];
		$id = empty($_GET['id'])?0:intval($_GET['id']);
		//查询体质记录
		$physique = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_physique','where'=>"id={$id}"));
		
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$physique['user_id']}'"));
		
	    if($user['dept']){
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));

			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
			
			$T->Set("gradeName",$gradeName);
			$T->Set("class_name",$class['class_name']);
		}
		
		$T->Set("truename",$user['truename']);
		$T->Set("tid",$tid);
		break; 
		
	case "search_physique":#学生页面的体能管理
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
	
		if($tid==0){
			$tid = $curTerm['id'];	
		}
		
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
		}

		$where = 'school='.$_SESSION['school'];
		//if($gid>0){
			$where .= " and (grade_id=0 OR grade_id={$gid}) ";	
		//}

		$T->SetBlock("list","select id,name from oa_zhsz_stamina where {$where} order by id");
		//体能项目
		$stamina = $pd->fetchAll(array('field'=>array('id','name'),'table'=>'oa_zhsz_stamina','where'=>$where,'order'=>'id'));
		
		//查询记录
		$strWhere = " A.flag_type=1 and A.userid='{$_SESSION['uid']}'";

		//默认为当前学期高一年级
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		}
		//分页配置
		$where  = $strWhere;
		$sql 	= 'select A.*,B.class_name,C.truename ';
		$sql 	.= ' from oa_user_extend AS A ';
		$sql    .= ' inner join oa_zhsz_class as B ON A.dept=B.id inner join act_member as C ON A.userid=C.id';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by CONVERT(C.truename USING GBK) ';

		//学生查询
		$student  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		//查询所有学生
		$params = array('field'=>'count(A.userid)','table'=>'oa_user_extend AS A inner join act_member as C ON A.userid=C.id','where'=>$where);
		$rc = $pd->fetchOne($params);
		
		$len = count($stamina);
		$itemA = array();
		foreach($stamina as $rs){
			$itemA[$rs['id']] = $rs['id'];
		}
		$str_total = '';
		foreach($student as $rs){
			$term  = $pd->query("select DISTINCT(term_id) from oa_zhsz_stamina_record  where user_id='{$uid}' order by term_id desc")->fetchAll(PDO::FETCH_ASSOC);
			
			foreach($term as $rst){
				$where = '';
				if($rst['term_id']>0){
					$where = " and term_id={$rst['term_id']}";	
				}
				//查询学生体能记录
				
				$sm = $pd->query("select item_id,item_name,result from oa_zhsz_stamina_record where user_id='{$rs['userid']}' {$where} order by item_id")->fetchAll(PDO::FETCH_ASSOC);
				
				$getTerm = $pd->fetchRow(array('field'=>array('year','term_name'),'table'=>'oa_zhsz_term','where'=>'id='.$rst['term_id']));
				
				$str_total .= '<tr><td class="tabtd">'.$getTerm['year'].'('.$getTerm['term_name'].')</td><td class="tabtd">'.$rs['truename'].'</td>';
				
				if($sm){
					foreach($stamina as $rss){
						$is_ok = 0;
						foreach($sm as $rsb){
							if($rss['id']==$rsb['item_id']){
								$str_total .= "<td class='tabtd'>{$rsb['result']}</td>";	
								$is_ok = 1;
							}
						}
						if(!$is_ok){
							$str_total .= "<td class='tabtd'>-</td>";	
						}
					}
				}
				/*foreach($sm as $rsb){
					if(isset($itemA[$rsb['item_id']])){
						$str_total .= "<td class='tabtd'>{$rsb['result']}</td>";	
					}
				}*/
				if(empty($sm)){
					for($i=0;$i<$len;$i++){
						$str_total .= "<td class='tabtd'>-</td>";	
					}
					$str_total .= '<td class="tabtd" style="text-align:left; padding-left:10px; color:#333;">&nbsp;暂无数据</td>';
				}
				$str_total .= '<td class="tabtd" style="text-align:center;padding-left:10px;"><img src="images/see.png" style="text-align:left;width:18px;height:12px;"/>&nbsp;<a href="./?t=stamina_stu_modify&uid='.$rs['userid'].'&tid='.$rst['term_id'].'">查看</a></td>';
				
				$str_total .='</tr>';
			}
			
		}
		$T->Set("str_total",$str_total);
		$T->Set("tid",$tid);
	    break;
		
	case "search_stamina": #学生体质管理页面
		//查询记录
		$strWhere = "  1=1 and A.userid='{$_SESSION['uid']}' ";

		$where  = $strWhere;
		$sql 	= 'select A.truename,B.*,C.year,C.term_name,(select class_name from oa_zhsz_class where id=A.dept) AS class_name ';
		$sql 	.= ' from v_users AS A ';
		$sql    .= ' inner join oa_zhsz_physique as B ON A.id=B.user_id';
		$sql    .= ' inner join oa_zhsz_term as C ON C.id=B.term_id';
		$sql 	.= " where {$where} and A.flag_type=1";
		$sql 	.= ' order by CONVERT(A.truename USING GBK) ';
		//体质记录查询
		$T->SetBlock("list1",$sql);
		
		$T->SetBlock("list","select id,year,term_name from oa_zhsz_term  group by year order by flag_default DESC,year DESC ");
		break; 
		
	case "stamina_stu_modify": #学生体能管理查看页面GET['tid']);
		$tid = empty($_GET['tid'])?0:intval($_GET['tid']);
		$uid = empty($_GET['uid'])?"":$_GET['uid'];
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$uid}'"));
		//查询班级
		
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
		//查询体能记录
		$T->SetBlock("list","select item_id,item_name,result from oa_zhsz_stamina_record where user_id='{$uid}' and term_id={$tid} order by item_id");

		//查询体育成绩
		$tyScore = $pd->fetchOne(array('field'=>'ty','table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$tid} and exam_type='期末'"));
		
		$truename = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$user['userid']}'"));
		$T->Set("gradeName",$gradeName);
		$T->Set("tyScore",$tyScore);
		$T->Set("truename",$truename);
		$T->Set("class_name",$class['class_name']);
		$T->Set("id",$user['userid']);
		$T->Set("tid",$tid);
		break;
		
	case "zhsz_table_show": #日常表现
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$sid = 0;
		}else{
			$sid = $curTerm['id'];	
		}
		//查询综评表信息
		$id  = empty($_GET['term_id'])?0:Filter::filter_number($_GET['term_id']);
		$uid = empty($_GET['uid'])?"":Filter::filter_html($_GET['uid']);
		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		
		//查询学生信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		if($id!=0){
			$sid = $id;	
		}
		$isMaster = false;
		$isMaster1 = 0;
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			if(!empty($class)&&$user['dept']==$class['id']){
				$isMaster = true;
				$isMaster1 = 1;
			}
		}
		
		$term   = showTerm($pd,$uid);   //年级标题
		$str = '';
		foreach($term as $rs){
			$selected = '';
			if($rs['id']==$sid){
				$selected = 'class="current"';	
			}
			$str.='<label '.$selected.'><a href="./?t=zhsz_table_show&term_id='.$rs['id'].'&uid='.$uid.'">'.$rs['term'].'</a></label>';
		}

		$str_reply = '';
		//他人评价
		$reply  = $pd->query("select * from oa_zhsz_comment where user_id='{$uid}' and term_id={$sid} and flag_checked=1")->fetchAll(PDO::FETCH_ASSOC);
		if(empty($reply)){
			$str_reply .= '暂无他人评价。';
		}else{
			$i = 1;
			foreach($reply as $rs){
				$str_reply .=  "<div style='border-bottom:1px dotted #45c8dc; margin-bottom:5px;'>{$i}.&nbsp;&nbsp;{$rs['content']}</div>";
				$i++;
			}
		}
		
		//查询自定义一级大分类
		$str_total='';
		$report  = $pd->query("select DISTINCT `subject`,subject_id from oa_zhsz_report_custom where uid='{$uid}' and term_id={$sid}")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_total .= '<ul class="ha_formlist"><table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="border-top:none;background:#a3c0f9"><tr><td class="tabtd2_L" style="text-align:center;font-weight:bold; " width="100%" colspan="1">'.$rs['subject'].'</td></tr></table>';
			$report_all  = $pd->query("select * from oa_zhsz_report_custom where uid='{$uid}' and term_id={$sid} and subject_id={$rs['subject_id']}")->fetchAll(PDO::FETCH_ASSOC);
			foreach($report_all as $rsa){
				if($rsa['subject_son']=="")
					$subject = $rsa['subject'];
				else
					$subject = $rsa['subject_son'];
				$atts = $pd->query("select * from oa_zhsz_attachment where custom_id='{$rsa['id']}'")->fetchAll(PDO::FETCH_ASSOC);
				$att_down = '无';
				$att_pic = '';
				$i=1;
				foreach($atts as $att){
					if($att['type']==1){
						$att_down = '<a href="'.$att['attachment'].'" target="_blank " style="height:28px;line-height:28px;padding:2px 15px;border-radius:5px;background:#277fc2;color:#fff;">下载</a>';
					}else{
						$att_pic .= '<dd><a href="'.$att['attachment'].'" target="_blank ">图片'.$i.'</a></dd>';
						$i++;
					}
				}
				
				if(!$att_pic)
					$att_pic = '无';
				if($rsa['subject_son_id']){
					$subject_id = $rsa['subject_son_id'];
				}else{
					$subject_id = $rsa['subject_id'];
				}
				
				$report_item = $pd->query("select * from oa_zhsz_report_item where report_id='{$subject_id}'")->fetchAll(PDO::FETCH_ASSOC);//自定义内容项
				$str_custom = '';
				$i=1;
				$item = '';
				$item_array = array();
				if(strlen($rsa['items'])>2){
					$item = $rsa['items'];
					$item = substr($item,2,-2);
					$item_array = explode("\",\"",$item);
				}
				foreach($report_item as $rsr){
					$array = array();
					$rsr_value = '';
					if($i%2==1){
						foreach($item_array as $ita){
							$array = explode(":",$ita);
							if($array[0] == $rsr['id']){
								$rsr_value = base64_decode($array[1]);
								$rsr_value = mb_convert_encoding($rsr_value, 'utf-8', 'gbk');
							}
						}
						if($i==count($report_item)){
							$str_custom .= '<tr><td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%" colspan="3" class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td>';
						}else{
							$str_custom .= '<tr><td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%"  class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td>';
						}
					}else{
						foreach($item_array as $ita){
							$array = explode(":",$ita);
							if($array[0] == $rsr['id']){
								$rsr_value = base64_decode($array[1]);
								$rsr_value = mb_convert_encoding($rsr_value, 'utf-8', 'gbk');
							}
						}
						$str_custom .= '<td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%" class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td></tr>';
					}
					$i++;
				}
				if($i%2==0)
					$str_custom .= '</tr>';
				
				$str_total .= '<li><table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="">
						<tr><td class="tabtd2_L" style="text-align:center;font-weight:bold; " width="17.55%">主题</td><td class="tabtd2_L" style="text-align:center;" width="20%">'.$subject.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;" width="10%">附件证明</td><td class="tabtd2_L" style="text-align:center;" width="10%">'.$att_down.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold; " width="10%">图片</td><td class="tabtd2_L" style="text-align:center;" width="20%">
							<dl class="ha_forimgl">'.$att_pic.'</dl></td></tr>
						<tr><td class="tabtd2_L" style="text-align:center;font-weight:bold;" width="17.55%">内容</td>
						<td class="tabtd2_L" colspan="5" style="text-align:left;">'.$rsa['content'].'</td></tr>
					</table>
					<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="border-top:none;">'.$str_custom.'</table>
					<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="border-top:none;">
					  <tr rowspan="2">
						<td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">备注</td>
						<td width="80%" class="tabtd2_L" style="text-align:left;">'.$rsa['note'].'</td>
					  </tr>
					</table></li>';
			}
			$str_total .= '</ul>';
		}
		
		$content =$pd->fetchOne(array('field'=>'content','table'=>'oa_zhsz_teacher_comment','where'=>"user_id='{$uid}' and term_id={$sid}"));

		$class_name = $pd->query("select A.class_name,B.grade_name from oa_zhsz_class as A inner join oa_zhsz_grade as B ON A.grade_id=B.id WHERE A.id={$user['dept']}")->fetchAll(PDO::FETCH_ASSOC);
		$name = '';
		if($class_name){
			$name = $class_name[0]['grade_name'].$class_name[0]['class_name'];
		}
		$term =$pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_term','where'=>"id={$sid}"));
		
		if($report){
			$tips = "{$name} {$user['truename']}同学在{$term['year']}{$term['term_name']}的成长记录";
		}else{
			$tips = "{$name} {$user['truename']}同学在{$term['year']}{$term['term_name']}无成长记录";
		}
		
		$T->Set("isMaster1",$isMaster1);
		$T->Set("sid",$sid);
		$T->Set("str",$str);
		$T->Set("uid",$uid);
		$T->Set("tips",$tips);
		$T->Set("curTerm_id",$curTerm['id']);
		$T->Set("truename",$user['truename']);
		$T->Set("flag_type",$_SESSION['flag_type']);
		$T->Set("str_reply",$str_reply);
		$T->Set("str_total",$str_total);
		$T->Set("content",$content);
		break;
		
	case "zhsz_table_show_self": #成长轨迹
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		if(empty($curTerm)){
			$sid = 0;
		}else{
			$sid = $curTerm['id'];	
		}
		//查询综评表信息
		$id  = empty($_GET['term_id'])?0:Filter::filter_number($_GET['term_id']);
		if(empty($uid)){
			$tips = array(
			   'tips' => '请选择要查看的学生。',
			   'url'  => './?t=zhsz_other'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}
		
		if($id!=0){
			$sid = $id;	
		}
		
		//查询学生信息
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		$T->ReadDB("select * from v_users where id='{$uid}'"); 
		
		//查询体质测试记录
		$physique = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_physique','where'=>"user_id='{$uid}' and term_id={$sid}"));
		
		//查询联系人信息
		$T->SetBlock("list","select * from oa_zhsz_relative where user_id='{$uid}'");
		
		//查询奖惩情况
		$str_reward = '';
		$reward  = $pd->query("select * from oa_zhsz_reward_punishment where user_id='{$uid}' and term_id={$sid}")->fetchAll(PDO::FETCH_ASSOC);
		if(!empty($reward)){
			$i = 1;
			foreach($reward as $rs){
				$str_reward .=  "<div style='border-bottom:1px dotted #45c8dc; margin-bottom:5px;'>{$i}.&nbsp;&nbsp;{$rs['content']}</div>";
				$i++;
			}
		}
		
		$isMaster = false;
		$isMaster1 = 0;
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			if(!empty($class)&&$user['dept']==$class['id']){
				$isMaster = true;
				$isMaster1 = 1;
			}
		}
		
		$term   = showTerm($pd,$uid);   //年级标题
		$str = '';
		foreach($term as $rs){
			$selected = '';
			if($rs['id']==$sid){
				$selected = 'class="current"';	
			}
			$str.='<label '.$selected.'><a href="./?t=zhsz_table_show_self&term_id='.$rs['id'].'&uid='.$uid.'">'.$rs['term'].'</a></label>';
		}

		$str_reply = '';
		//他人评价
		$reply  = $pd->query("select * from oa_zhsz_comment where user_id='{$uid}' and term_id={$sid} and flag_checked=1")->fetchAll(PDO::FETCH_ASSOC);
		if(empty($reply)){
			$str_reply .= '暂无他人评价。';
		}else{
			$i = 1;
			foreach($reply as $rs){
				$str_reply .=  "<div style='border-bottom:1px dotted #45c8dc; margin-bottom:5px;'>{$i}.&nbsp;&nbsp;{$rs['content']}</div>";
				$i++;
			}
		}
		
		//查询自定义一级大分类
		$str_total='';
		$report  = $pd->query("select DISTINCT `subject`,subject_id from oa_zhsz_report_custom where uid='{$uid}' and term_id={$sid}")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_total .= '<ul class="ha_formlist"><table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="border-top:none;background:#a3c0f9"><tr><td class="tabtd2_L" style="text-align:center;font-weight:bold; " width="100%" colspan="1">'.$rs['subject'].'</td></tr></table>';
			$report_all  = $pd->query("select * from oa_zhsz_report_custom where uid='{$uid}' and term_id={$sid} and subject_id={$rs['subject_id']}")->fetchAll(PDO::FETCH_ASSOC);
			foreach($report_all as $rsa){
				if($rsa['subject_son']=="")
					$subject = $rsa['subject'];
				else
					$subject = $rsa['subject_son'];
				$atts = $pd->query("select * from oa_zhsz_attachment where custom_id='{$rsa['id']}'")->fetchAll(PDO::FETCH_ASSOC);
				$att_down = '无';
				$att_pic = '';
				$i=1;
				foreach($atts as $att){
					if($att['type']==1){
						$att_down = '<a href="'.$att['attachment'].'" target="_blank " style="height:28px;line-height:28px;padding:2px 15px;border-radius:5px;background:#277fc2;color:#fff;">下载</a>';
					}else{
						$att_pic .= '<dd><a href="'.$att['attachment'].'" target="_blank ">图片'.$i.'</a></dd>';
						$i++;
					}
				}
				
				if(!$att_pic)
					$att_pic = '无';
				if($rsa['subject_son_id']){
					$subject_id = $rsa['subject_son_id'];
				}else{
					$subject_id = $rsa['subject_id'];
				}
				
				$report_item = $pd->query("select * from oa_zhsz_report_item where report_id='{$subject_id}'")->fetchAll(PDO::FETCH_ASSOC);//自定义内容项
				$str_custom = '';
				$i=1;
				$item = '';
				$item_array = array();
				if(strlen($rsa['items'])>2){
					$item = $rsa['items'];
					$item = substr($item,2,-2);
					$item_array = explode("\",\"",$item);
				}
				
				foreach($report_item as $rsr){
					$array = array();
					$rsr_value = '';
					if($i%2==1){
						foreach($item_array as $ita){
							$array = explode(":",$ita);
							if($array[0] == $rsr['id']){
								$rsr_value = base64_decode($array[1]);
								$rsr_value = mb_convert_encoding($rsr_value, 'utf-8', 'gbk');
							}
						}
						if($i==count($report_item)){
							$str_custom .= '<tr><td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%" colspan="3" class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td>';
						}else{
							$str_custom .= '<tr><td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%"  class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td>';
						}
					}else{
						foreach($item_array as $ita){
							$array = explode(":",$ita);
							if($array[0] == $rsr['id']){
								$rsr_value = base64_decode($array[1]);
								$rsr_value = mb_convert_encoding($rsr_value, 'utf-8', 'gbk');
							}
						}
						$str_custom .= '<td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%" class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td></tr>';
					}
					$i++;
				}
				if($i%2==0)
					$str_custom .= '</tr>';
				
				$str_total .= '<li><table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="">
						<tr><td class="tabtd2_L" style="text-align:center;font-weight:bold; " width="17.55%">主题</td><td class="tabtd2_L" style="text-align:center;" width="20%">'.$subject.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold;" width="10%">附件证明</td><td class="tabtd2_L" style="text-align:center;" width="10%">'.$att_down.'</td><td class="tabtd2_L" style="text-align:center;font-weight:bold; " width="10%">图片</td><td class="tabtd2_L" style="text-align:center;" width="20%">
							<dl class="ha_forimgl">'.$att_pic.'</dl></td></tr>
						<tr><td class="tabtd2_L" style="text-align:center;font-weight:bold;" width="17.55%">内容</td>
						<td class="tabtd2_L" colspan="5" style="text-align:left;">'.$rsa['content'].'</td></tr>
					</table>
					<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="border-top:none;">'.$str_custom.'</table>
					<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" style="border-top:none;">
					  <tr rowspan="2">
						<td width="20%" class="tabtd2_L" style="text-align:center;font-weight:bold;">备注</td>
						<td width="80%" class="tabtd2_L" style="text-align:left;">'.$rsa['note'].'</td>
					  </tr>
					</table></li>';
			}
			$str_total .= '</ul>';
		}
		
		$content =$pd->fetchOne(array('field'=>'content','table'=>'oa_zhsz_teacher_comment','where'=>"user_id='{$uid}' and term_id={$sid}"));

		$class_name = $pd->query("select A.class_name,B.grade_name,A.grade_id from oa_zhsz_class as A inner join oa_zhsz_grade as B ON A.grade_id=B.id WHERE A.id={$user['dept']}")->fetchAll(PDO::FETCH_ASSOC);
		$name = '';
		if($class_name){
			$name = $class_name[0]['grade_name'].$class_name[0]['class_name'];
		}else{
			$class_name[0]['grade_id'] = 0;
		}
		$term =$pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_term','where'=>"id={$sid}"));
		
		if($report){
			$tips = "{$name} {$user['truename']}同学在{$term['year']}{$term['term_name']}的成长记录";
		}else{
			$tips = "{$name} {$user['truename']}同学在{$term['year']}{$term['term_name']}无成长记录";
		}
		
		//查询当前体能项目
		$str_stamina = '';
		$str_teacher = '';
		$str_menu = '';
		$stamina  = $pd->query("select * from oa_zhsz_stamina where (term_type=0 or term_type={$sid}) and (grade_id=0 or grade_id={$class_name[0]['grade_id']}) order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		if(!empty($stamina)){
			foreach($stamina as $rs){
				$teacherName = '';
				$str_menu .= '<td colspan="" class="tabtd2_L" style="width:80px; text-align:center;">'.$rs['name'].'</td>';
				//查询体能数据
				$rsb = $pd->fetchRow(array('field'=>array('teacher_name','result'),'table'=>'oa_zhsz_stamina_record','where'=>"user_id='{$uid}' and term_id={$sid} and item_id={$rs['id']}"));
				if(!empty($rsb)){
					$teacherName = $rsb['teacher_name'];
				}
				$result = isset($rsb['result'])?(empty($rsb['result'])?'&nbsp;':$rsb['result']):'&nbsp;';
				$str_stamina .= '<td colspan="" class="tabtd2_L" style="width:80px; text-align:center;">'.$result.'</td>';
				$str_teacher .= '<td colspan="" class="tabtd2_L" style="width:80px; text-align:center;">'.$teacherName.'</td>';
			}
		}
		
		$T->Set("isMaster1",$isMaster1);
		$T->Set("sid",$sid);
		$T->Set("str",$str);
		$T->Set("uid",$uid);
		$T->Set("tips",$tips);
		$T->Set("curTerm_id",$curTerm['id']);
		$T->Set("truename",$user['truename']);
		$T->Set("flag_type",$_SESSION['flag_type']);
		$T->Set("str_reply",$str_reply);
		$T->Set("str_total",$str_total);
		$T->Set("content",$content);
		$T->Set("str_reward",$str_reward);
		$T->Set("str_menu",$str_menu);
		$T->Set("str_stamina",$str_stamina);
		$T->Set("str_teacher",$str_teacher);
		$T->Set("physique_height",empty($physique['height'])?'&nbsp;':$physique['height'].'CM');
		$T->Set("physique_left_eye",empty($physique['left_eye'])?'&nbsp;':$physique['left_eye']);
		$T->Set("physique_liver",empty($physique['liver'])?'&nbsp;':$physique['liver']);
		$T->Set("physique_xf_ting",empty($physique['xf_ting'])?'&nbsp;':$physique['xf_ting']);
		$T->Set("physique_wkcg",empty($physique['wkcg'])?'&nbsp;':$physique['wkcg']);
		$T->Set("physique_weight",empty($physique['weight'])?'&nbsp;':$physique['weight'].'KG');
		$T->Set("physique_right_eye",empty($physique['right_eye'])?'&nbsp;':$physique['right_eye']);
		$T->Set("physique_guts",empty($physique['guts'])?'&nbsp;':$physique['guts']);
		$T->Set("physique_teacher_name",empty($physique['teacher_name'])?'&nbsp;':$physique['teacher_name']);
		$T->Set("physique_blood",empty($physique['blood'])?'&nbsp;':$physique['blood']);
		$T->Set("physique_vc",empty($physique['vc'])?'&nbsp;':$physique['vc']);
		$T->Set("physique_spleen",empty($physique['spleen'])?'&nbsp;':$physique['spleen']);
		$T->Set("physique_yanke",empty($physique['yanke'])?'&nbsp;':$physique['yanke']);
		$T->Set("physique_mouth",empty($physique['mouth'])?'&nbsp;':$physique['mouth']);
		break;
		
	case "user_manage": 
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?'':$_POST['tid'];    //类型
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$userCard = empty($_POST['user_card'])?'':$_POST['user_card'];
		
		$p=isset($_GET["p"])?$_GET["p"]:"1"; 
		if($p<1)$p=1;
		
		$g_gid=empty($_GET['gid'])?0:intval($_GET['gid']);
		if($g_gid>0){
			$gid=$g_gid;
		}
		$g_tid = empty($_GET['tid'])?'':$_GET['tid']; 
		if(!empty($g_tid)){
			$tid=$g_tid;
		}
		$g_cid=empty($_GET['cid'])?0:intval($_GET['cid']);
		if($g_cid>0){
			$cid=$g_cid;
		}
		
		$strWhere = ' ';
		$sql='';
		  //没有输入姓名查询时
		if(!empty($tid)){
			if($tid=="1"){
				$strWhere .=" and flag_type=1 ";
			}
			if($tid=="2"){
				$strWhere .=" and flag_type=2 ";
			}
			if($tid=="3"){
				$strWhere .=" and flag_type=4 ";
			}
		}
		
		//是否为班主任
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
		}
		
		if($gid>0){
				//查询所属于班级
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and dept IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and dept={$classId} ";
			}
		}
		
		if(!empty($truename)){
			$strWhere .= " and truename='".$truename."' ";
		}
		
		if(!empty($userCard)){
			$strWhere .= " and username='".$userCard."' ";
		}
		
		$rc= $T->db->query("select count(1) from v_users where school=".$_SESSION['school'].$strWhere)->fetchColumn(0);
		$page=getPageHtml_bt($rc,20,$p,"&t=user_manage&tid=".$tid."&gid=".$gid."&cid=".$cid);
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->SetBlock("u_list","select * from v_users where school=".$_SESSION['school'].$strWhere." limit ".(($p-1)*20).",20");	
		
		$str_menu = "";
		$is_show = 0;
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5){
			$str_menu = '<div class="boxtitle"><label class="current"><a href="javascript:void(0);">用户列表</a></label><span class="floatright"><img src="images/add.gif" class="padding5"/><a href="./?t=member_add">添加学生</a> &nbsp;| <img src="images/add.gif" class="padding5"/><a href="./?t=teacher_add">添加教师</a> &nbsp;| <img src="images/add.gif" class="padding5"/><a href="./?t=admin_add">添加管理员</a> &nbsp;| <img src="images/add.gif" class="padding5"/><a href="./?t=member_import">批量导入学生</a> &nbsp;| <img src="images/add.gif" class="padding5"/><a href="./?t=teacher_import">批量导入教师</a> &nbsp;</span></div>';
			$is_show = 1;
		}
		
		$T->Set("tid",$tid);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("truename",$truename); 
		$T->Set("userCard",$userCard); 
		$T->Set("str_menu",$str_menu); 
		$T->Set("is_show",$is_show); 
		break;
		
	case "member_add": #学生添加

		$codeCat=$pd->query("select * from oa_zhsz_code where parent_no='RELATIVE'")->fetchAll(PDO::FETCH_ASSOC);
		$str_code = '';
		foreach($codeCat as $rs){
			$str_code .= '<option value="'.$rs['code_name'].'">'.$rs['code_name'].'</option>';
		}
		$T->Set("str_code",$str_code);
		break;
		
	case "member_modify": #学生修改

		$codeCat=$pd->query("select * from oa_zhsz_code where parent_no='RELATIVE'")->fetchAll(PDO::FETCH_ASSOC);
		$str_code = '';
		foreach($codeCat as $rs){
			$str_code .= '<option value="'.$rs['code_name'].'">'.$rs['code_name'].'</option>';
		}
		
		$id =empty($_GET['id'])?'':$_GET['id'];
		//查询学生记录
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$id}'"));
		
		$T->ReadDB("select * from v_users where id='{$id}'");
		
		//查询初中信息
		$middleScore = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_about_primary','where'=>"username='{$user['username']}'"));
		$isMiddle = 1;
		if(empty($middleScore)){
			$isMiddle = 0;
			$middleScore['exam_no'] = '';
			$middleScore['yw']      = '';
			$middleScore['sx']      = '';
			$middleScore['wy']      = '';
			$middleScore['lh']      = '';
			$middleScore['zs']      = '';
			$middleScore['ty']      = '';
			$middleScore['intro']   = '';
		}
		//查询班级
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));

		if($user['gender']=='男'){
			$str_check = '<input type="radio" name="gender" checked value="男"  /> 男<input type="radio" name="gender" value="女"  /> 女';
		}else{
			$str_check = '<input type="radio" name="gender"  value="男"  /> 男<input type="radio" name="gender" value="女"  checked/> 女';
		}
		
		if($user['flag_status']==1){
			$str_status = '<input type="radio" name="flag_status" value="1"  checked/> 正常<input type="radio" name="flag_status" value="0"  /> 停用';
		}else{
			$str_status = '<input type="radio" name="flag_status" value="1"  /> 正常<input type="radio" name="flag_status" value="0" checked /> 停用';
		}
		

		//查询联系人信息
		$linkmans  = $pd->query("select * from oa_zhsz_relative where user_id='{$id}'")->fetchAll(PDO::FETCH_ASSOC);
		$str_total = '';
		if(empty($linkmans)){
			$str_total .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0"><tr><td width="20%" class="tabtd2_L">&nbsp;</td><td width="80%" class="tabtd2_R"><a href="javascript:void(0);" onclick="addRelation();"><img src="images/add.gif" width="14" height="14" border="0" />&nbsp;增加</a></td></tr><tr><td width="20%" class="tabtd2_L">关系</td><td width="80%" class="tabtd2_R"><select name="relation[]">'.$str_code.'</select> </td></tr><tr><td width="20%" class="tabtd2_L">姓名</td><td width="80%" class="tabtd2_R"><input type="text" name="rel_name[]" size="15" /></td></tr><tr><td width="20%" class="tabtd2_L">工作单位</td><td width="80%" class="tabtd2_R"><input type="text" name="company[]" size="25" /> </td></tr><tr><td width="20%" class="tabtd2_L">联系电话</td><td width="80%" class="tabtd2_R"><input type="text" name="phone[]" size="30" /></td></tr></table><table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0"><tr><td width="20%" class="tabtd2_L">关系</td><td width="80%" class="tabtd2_R"><select name="relation[]">'.$str_code.'</select></td></tr><tr><td width="20%" class="tabtd2_L">姓名</td><td width="80%" class="tabtd2_R"><input type="text" name="rel_name[]" size="15" /></td></tr><tr><td width="20%" class="tabtd2_L">工作单位</td><td width="80%" class="tabtd2_R"><input type="text" name="company[]" size="25" /></td></tr><tr><td width="20%" class="tabtd2_L">联系电话</td> <td width="80%" class="tabtd2_R"><input type="text" name="phone[]" size="30" /></td></tr></table>';
			
		}else{
			$i = 0;
			foreach($linkmans as $rs){
				$str_total .= '<table width="100%" border="0" class="tab" cellpadding="0" cellspacing="0" id="trelation_'.$rs['id'].'">';
				if($i++==0){
					$str_total .= '<tr><td width="20%" class="tabtd2_L">&nbsp;</td><td width="80%" class="tabtd2_R"><a href="javascript:void(0);" onclick="addRelation();"><img src="images/add.gif" width="14" height="14" border="0" />&nbsp;增加</a></td></tr>';
				}
				$str_code = '';
				foreach($codeCat as $rsb){
					$selected = '';
					if($rsb['code_name']==$rs['relation']){
						$selected = 'selected="selected"';
					}
					$str_code .= '<option value="'.$rsb['code_name'].'" '.$selected.'>'.$rsb['code_name'].'</option>';
				}
				$str_total .= '<tr><td width="20%" class="tabtd2_L">关系</td><td width="80%" class="tabtd2_R"><select name="relation_'.$rs['id'].'">'.$str_code.'</select></td></tr><tr><td width="20%" class="tabtd2_L">姓名</td><td width="80%" class="tabtd2_R"><input type="text" name="rel_name_'.$rs['id'].'" size="15" value="'.$rs['truename'].'" /></td></tr><tr><td width="20%" class="tabtd2_L">工作单位</td><td width="80%" class="tabtd2_R"><input type="text" name="company_'.$rs['id'].'" size="25" value="'.$rs['company'].'" /></td></tr><tr><td width="20%" class="tabtd2_L">联系电话</td><td width="80%" class="tabtd2_R"><input type="text" name="phone_'.$rs['id'].'" value="'.$rs['telphone'].'" size="30" />&nbsp;<img src="images/010.gif" /> <a href="javascript:delRelationA('.$rs['id'].');">删除</a></td></tr></table>';
			}
		}
					
		$T->Set("str_code",$str_code);
		$T->Set("str_check",$str_check);
		$T->Set("gid",isset($class['grade_id'])?$class['grade_id']:"");
		$T->Set("cid",isset($class['id'])?$class['id']:"");
		$T->Set("str_status",$str_status);
		$T->Set("exam_no",$middleScore['exam_no']);
		$T->Set("yw",$middleScore['yw']);
		$T->Set("sx",$middleScore['sx']);
		$T->Set("wy",$middleScore['wy']);
		$T->Set("lh",$middleScore['lh']);
		$T->Set("zs",$middleScore['zs']);
		$T->Set("ty",$middleScore['ty']);
		$T->Set("intro",$middleScore['intro']);
		$T->Set("str_total",$str_total);
		$T->Set("isMiddle",$isMiddle);
		break;
	case "admin_modify": #管理员修改
		$id = $_GET['id'];
		//查询管理人员记录
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$id}'"));
		$T->ReadDB("select * from v_users where id='{$id}'");
		
		if($user['flag_status']==1){
			$str_status = '<input type="radio" name="flag_status" value="1"  checked/> 正常<input type="radio" name="flag_status" value="0"  /> 停用';
		}else{
			$str_status = '<input type="radio" name="flag_status" value="1"  /> 正常<input type="radio" name="flag_status" value="0" checked /> 停用';
		}
		
		$T->Set("str_status",$str_status); 
		$T->Set("rid",$user['role_id']); 
		break;
	case "teacher_modify": #老师修改
		$id = $_GET['id'];
		//查询老师记录
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$id}'"));
		$T->ReadDB("select * from v_users where id='{$id}'");
		
		if($user['gender']=='男'){
			$str_sex = '<input type="radio" name="gender" value="男"  checked/> 男<input type="radio" name="gender" value="女"  /> 女';
		}else{
			$str_sex = '<input type="radio" name="gender" value="男"  /> 男<input type="radio" name="gender" value="女"  checked/> 女';
		}
		
		if($user['flag_status']==1){
			$str_status = '<input type="radio" name="flag_status" value="1"  checked/> 正常<input type="radio" name="flag_status" value="0"  /> 停用';
		}else{
			$str_status = '<input type="radio" name="flag_status" value="1"  /> 正常<input type="radio" name="flag_status" value="0" checked /> 停用';
		}
		
		$T->Set("str_status",$str_status); 
		$T->Set("str_sex",$str_sex); 
		$T->Set("department",$user['dept']); 
		$T->Set("role_id",$user['role_id']); 
		$T->Set("nation",$user['nation']); 
		$T->Set("political_status",$user['political_status']); 
		break;
		
	case "member_import_list": #导入学生列表
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$where = "create_by='{$_SESSION['username']}'";
		//分页配置
		$sql 	= 'select * ';
		$sql 	.= ' from oa_zhsz_users_temp ';
		$sql 	.= " where {$where}";
		$sql 	.= ' order by CONVERT(truename USING GBK) ';
		$sql 	.= " limit {$offset},40";
		//学生查询
		$member  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		//查询所有学生
		$params = array('field'=>'count(id)','table'=>'oa_zhsz_users_temp','where'=>$where);
		$rc = $pd->fetchOne($params);
		$memberC = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_users_temp','where'=>$where));

		$page=getPageHtml_bt($rc,40,$p,"&t=member_import_list");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$str_total = '';
		foreach($member as $rs){
			$str_total .= '<tr><td class="tabtd">'.$rs['username'].'</td><td class="tabtd">'.$rs['truename'].'('.$rs['user_card'].')'.'</td><td class="tabtd">'.$rs['gender'].'</td><td class="tabtd">';
			
			if($rs['flag_status']!=1&&$rs['flag_status']!=6){
				$str_total .= '<font color="#ff0000">'.$rs['reason'].'</font>';
			}
			if($rs['flag_status']==1||$rs['flag_status']==6){
				$str_total .= '<font color="#009900">'.$rs['reason'].'</font>';
			}
			$str_total .= '</td></tr>';
		}
		
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("memberC",$memberC); 
		$T->Set("str_total",$str_total); 
		break;
	case "teacher_import_list": #老师导入列表
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$where = "create_by='{$_SESSION['username']}'";
		//分页配置
		$sql 	= 'select * ';
		$sql 	.= ' from oa_zhsz_users_temp ';
		$sql 	.= " where {$where}";
		$sql 	.= ' order by CONVERT(truename USING GBK) ';
		$sql 	.= " limit {$offset},40";
		//老师查询
		$member  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		//查询所有老师
		$params = array('field'=>'count(id)','table'=>'oa_zhsz_users_temp','where'=>$where);
		$rc = $pd->fetchOne($params);
		$memberC = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_users_temp','where'=>'flag_status=1 and '.$where));

		$page=getPageHtml_bt($rc,40,$p,"&t=teacher_import_list");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$str_total = '';
		foreach($member as $rs){
			$str_total .= '<tr><td class="tabtd">'.$rs['username'].'</td><td class="tabtd">'.$rs['truename'].'</td><td class="tabtd">'.$rs['gender'].'</td><td class="tabtd">';
			if($rs['flag_status']!=1){
				$str_total .= '<font color="#ff0000">'.$rs['reason'].'</font>';
			}
			if($rs['flag_status']==1){
				$str_total .= '<font color="#009900">'.$rs['reason'].'</font>';
			}
			
			$str_total .= '</td></tr>';
		}
		
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("memberC",$memberC); 
		$T->Set("str_total",$str_total); 
		break;
		
	case "zhsz_all_statistics": #综评统计总表
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		$term_page=isset($_GET["term_page"])?$_GET["term_page"]:0;
		$report_page=isset($_GET["report_page"])?$_GET["report_page"]:"";
		$report_id = empty($_POST['report_id'])?"":$_POST['report_id'];
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		$termid = isset($_POST['term_id'])?$_POST['term_id']:$curTerm["id"];
		
		if($term_page)
			$termid = $term_page;
		if($report_page)
			$report_id = $report_page;
		
		$report  = $pd->query("SELECT * from  oa_zhsz_report where parent_no='{$report_id}' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);     //页面标题栏目
		$str_report = '';
		foreach($report as $rs){
			$str_report .= '<td  class="tabtd">'.$rs['code_name'].'</td>';
		}
		
		$report_menu  = $pd->query("SELECT * from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);//下拉框
		$str_option = '<option value="">全部</option>';
		
		foreach($report_menu as $rs){
			$report_menu_son  = $pd->query("SELECT * from  oa_zhsz_report where parent_no={$rs['id']}")->fetchAll(PDO::FETCH_ASSOC);    //若没有下一级的不显示
			if(!empty($report_menu_son))
				$str_option .= '<option value="'.$rs['id'].'">'.$rs['code_name'].'</option>';
		}
		
		$strWhere = 'A.grade_id<>0';
		$where  = $strWhere;
		$sql 	= 'select A.id,A.class_name,A.grade_id,(select count(*) from oa_user_extend where dept=A.id and flag_type=1) as nums ';
		$sql 	.= ' from oa_zhsz_class AS A ';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by A.grade_id,A.id ';
		$sql 	.= " limit {$offset},20";
		//班级查询
		$class  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);

		if($report_id){
			$report_where = 'subject_son_id';
		}else{
			$report_where = 'subject_id';
		}
		//统计报表
		$str = '';
		$i=1;
		foreach($class as $rs){
			$rs['grade_name']  = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$rs['grade_id']}"));
			
			$report  = $pd->query("SELECT * from  oa_zhsz_report where parent_no='{$report_id}' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);     //页面标题栏目
			$str_costum = '';
			foreach($report as $rs1){
				$custom_total  = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_report_custom','where'=>"{$report_where}={$rs1['id']} and class_id={$rs['id']} and term_id={$termid}"));   //已填写数量
				$custom_ok  = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_report_custom','where'=>"{$report_where}={$rs1['id']} and class_id={$rs['id']} and flag_status<>\"待审核\" and term_id={$termid}"));   //已审核数量
				$custom_not = $custom_total - $custom_ok;
				//$custom_not  = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_report_custom','where'=>"{$report_where}={$rs1['id']} and class_id={$rs['id']} and flag_status=\"待审核\" and term_id={$termid}"));   //未审核数量
				$str_costum .= '<td class="tabtd">'.$custom_total.'/<span style="color:blue">'.$custom_ok.'</span>/<span style="color:red">'.$custom_not.'</span></td>';
			}
			
			$str .= '<tr><td class="tabtd">'.$i.'</td><td class="tabtd">'.$rs['grade_name'].$rs['class_name'].'</td><td class="tabtd">'.$rs['nums'].'</td>'.$str_costum.'</tr>';
			$i++;
		}

		//查询所有班级
		$params = array('field'=>'count(A.id)','table'=>'oa_zhsz_class AS A','where'=>$where);
		$rc = $pd->fetchOne($params);
		$page=getPageHtml_bt($rc,20,$p,"&t=zhsz_all_statistics&term_page={$termid}&report_page={$report_id}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("term_id",$termid); 
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("countclass",count($class)); 
		$T->Set("str",$str); 
		$T->Set("str_report",$str_report); 
		$T->Set("str_option",$str_option); 
		$T->Set("report_id",$report_id); 
		break;
		
	//管理员汇总页综评表点击进入的页面
	case "zhsz_comment":
		//当前进入的班级
		$class_dept=isset($_GET["dept"])?$_GET["dept"]:1;
		//学期下拉框
		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by year DESC,term_type DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rst){
			$str_term .= '<option value="'.$rst['id'].'">'.$rst['year'].'('.$rst['term_name'].')</option>';
		}
		$user_num = 0;
		$className = '';
		$gradeName = '';
		$tips      = '&nbsp;';
		$strWhere  = "flag_type=1 and flag_status=1";
		//修学或退学
		$noClass = false;
		//毕业
		$noGrade = false;
		//是否为班主任
		$isMaster = false;
		$isMaster1 = 0;
		$class_id = 0;
		//学生
	
		if($_SESSION['flag_type']==1||$_SESSION['flag_type']==3){
			//查询学生信息
			$user  = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
			//查询当前学生年级班级情况
			if(empty($user['dept'])){
				$noClass = true;
			}else{
				//查询班级
				$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
				//查询是否是毕业班
				if($class['grade_id']==0){
					$noGrade = true;
				}else{
					$cid = $class['id'];
					$class_id = $class['id'];
					$gid = $class['grade_id'];
				}
			}
			
		}
		//老师
		if($_SESSION['flag_type']==2){
			//查询当前代班
			$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
			
			if(!empty($class)){
				$cid = $class['id'];
				$class_id = $class['id'];
				$gid = $class['grade_id'];
				$isMaster = true;
				$isMaster1 = 1;
			}
			$isMaster = false;
		}
		$term_id   = empty($_POST['term_id'])?'':$_POST['term_id'];
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		//选择学期
		$wheret='';
		if($term_id){
			$wheret=" and ozrc.term_id=".$term_id."";
		}
		$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id=".$class_dept.""));
		$gradeName = $T->db->query("select b.grade_name from oa_zhsz_class as a left join oa_zhsz_grade as b on b.id=a.grade_id  where a.id=".$class_dept)->fetchColumn(0);
		$tips = "当前班级：{$gradeName}{$className}";

		//当是班主任角色时
		if($_SESSION['role_id']==6){
			if($class_dept){
				$sql = "select V.truename,V.id as stu_id,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id ".$wheret.") as num1,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status<>'待审核' ".$wheret.") as num2  from v_users as V where V.dept=".$class_dept." and V.role_id=1 GROUP BY V.truename";
				
				$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			    $str = '';
			    if(empty($students)){
				  $str = '<div style="width:200px; height:30px; overflow:hidden; margin:0 auto; text-align:center;">暂无学生信息</div>';
			    }
		
			    foreach($students as $rs){
					$num3 = $rs['num1'] - $rs['num2'];
				  $str.="<li class='user_list2' num1='{$rs['num1']}' style='position:relative;'>{$rs['truename']}<a class='ha_tipsicon' style='display:none;'>{$rs['num1']}/<span style='color:blue'>{$rs['num2']}</span>/<span style='color:green'>{$num3}</span></a></li>";
			    }
			
				$user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept={$class_dept}"));
			}else{
				$user_num = 0;
			}
		}elseif($_SESSION['role_id']==4||$_SESSION['role_id']==5){
				if($truename){
				  $T->Set("truename",$truename);
				  $sql = "select V.truename,V.id,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id ".$wheret.") as num1,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status<>'待审核' ".$wheret.") as num2 from v_users as V where V.dept=".$class_dept." and  V.truename like '%".$truename."%' GROUP BY V.truename";

				  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and  dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
				  
				   $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			       $str = '';
			       if(empty($students)){
				      $str = '<div style="width:200px; height:30px; overflow:hidden; margin:0 auto; text-align:center;">暂无学生信息</div>';
			       }
		
			       foreach($students as $rs){
					   $num3 = $rs['num1'] - $rs['num2'];
				      $str.="<li class='user_list2' num1='{$rs['num1']}' style='position:relative;'>{$rs['truename']}<a class='ha_tipsicon' style='display:none;'>{$rs['num1']}/<span style='color:blue'>{$rs['num2']}</span>/<span style='color:green'>{$num3}</span></a></li>";
			       }
			    }else{
					$where  = $strWhere;
					//$sql = "select V.truename,V.id,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id ".$wheret.") as num1,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status<>'待审核' ".$wheret.") as num2,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status='待审核' ".$wheret.") as num3 from v_users as V left join oa_message as M on M.geter=V.id left join oa_zhsz_report_custom as ozrc on ozrc.id=M.from_id where V.dept=".$class_dept." and V.role_id=1 GROUP BY V.truename";
					$sql = "select V.truename,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id ".$wheret.") as num1,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status<>'待审核' ".$wheret.") as num2 from v_users as V  where V.dept=".$class_dept." and V.role_id=1 GROUP BY V.truename";
		
					$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					$str = '';
					if(empty($students)){
					$str = '<div style="width:200px; height:30px; overflow:hidden; margin:0 auto; text-align:center;">暂无学生信息</div>';
					}
				
					foreach($students as $rs){
						$num3 = $rs['num1'] - $rs['num2'];
					   $str.="<li class='user_list2' num1='{$rs['num1']}' style='position:relative;'>{$rs['truename']}<a class='ha_tipsicon' style='display:none;'><span style='color:black'>{$rs['num1']}</span>/<span style='color:blue'>{$rs['num2']}</span>/<span style='color:green'>{$num3}</span></a></li>";
					   
					   $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept={$class_dept}"));
					}
				}
		}elseif($_SESSION['role_id']!=1&&$_SESSION['role_id']!=3&&$_SESSION['role_id']!=4&&$_SESSION['role_id']!=5&&$_SESSION['role_id']!=6){
				if($truename){
				  $T->Set("truename",$truename);
				  $sql = "select V.truename,V.id,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.teacher_id='".$uid."' ".$wheret.") as num1,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status<>'待审核' and ozrc.teacher_id='".$uid."' ".$wheret.") as num2 from v_users as V  where V.dept=".$class_dept." and  V.truename like '%".$truename."%' GROUP BY V.truename";
				 
				  $user_num = $T->db->query("select count(1) from v_users where role_id=1 and  dept=".$class_dept." and truename like '%".$truename."%'")->fetchColumn(0);
				  
				   $students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			
			       $str = '';
			       if(empty($students)){
				      $str = '<div style="width:200px; height:30px; overflow:hidden; margin:0 auto; text-align:center;">暂无学生信息</div>';
			       }
		
			       foreach($students as $rs){
					   $num3 = $rs['num1'] - $rs['num2'];
				      $str.="<li class='user_list2' num1='{$rs['num1']}' style='position:relative;'>{$rs['truename']}<a class='ha_tipsicon' style='display:none;'>{$rs['num1']}/<span style='color:blue'>{$rs['num2']}</span>/<span style='color:green'>{$num3}</span></a></li>";
			       }
			    }else{
					$tips = "当前班级：{$gradeName}{$className}";
					$where  = $strWhere;
					
					$sql = "select V.truename,V.id,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.teacher_id='".$uid."' ".$wheret.") as num1,(select count(*) from oa_zhsz_report_custom as ozrc where ozrc.uid=V.id and ozrc.flag_status<>'待审核' and ozrc.teacher_id='".$uid."' ".$wheret.") as num2 from v_users as V where V.dept=".$class_dept." and V.role_id=1 GROUP BY V.truename";
					
					//学生信息查询
					$students  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
					
					$str = '';
					if(empty($students)){
					$str = '<div style="width:200px; height:30px; overflow:hidden; margin:0 auto; text-align:center;">暂无学生信息</div>';
					}
				
					foreach($students as $rs){
						$num3 = $rs['num1'] - $rs['num2'];
						$str.="<li class='user_list2' num1='{$rs['num1']}' style='position:relative;'>{$rs['truename']}<a class='ha_tipsicon' style='display:none;'>{$rs['num1']}/<span style='color:blue'>{$rs['num2']}</span>/<span style='color:green'>{$num3}</span></a></li>";
					   $user_num = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"dept={$class_dept}"));
					}
				}
		}
		
		if($_SESSION['role_id']==4){
			$is_admin = 1;
		}else{
			$is_admin = 0;
		}
		$T->Set("is_admin",$is_admin);
		$T->Set("tips",$tips);
		$T->Set("isMaster1",$isMaster1);
		$T->Set("class_id",$class_id);
		$T->Set("str",$str);
		$T->Set("str_term",$str_term);
		$T->Set("user_num",$user_num);
		$T->Set("class_dept",$class_dept);
	    break;
	
	case "skills_manage": #技能素质管理
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
		$tyear_page=isset($_GET["tyear_page"])?$_GET["tyear_page"]:0;
		
		$gid      = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid      = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tyear    = empty($_POST['tyear'])?'':$_POST['tyear'];
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		if($tyear_page)
			$tyear = $tyear_page;

		if(empty($tyear)){
			$tyear = $curTerm['id'];
		}

		$Term = $pd->fetchRow(array('field'=>array('year','term_name'),'table'=>'oa_zhsz_term','where'=>'id='.$tyear.''));
		
		$T->SetBlock("list","select id,course_name from oa_zhsz_course where is_score=0 order by id DESC");
		
		//是否为班主任
		/*$isMaster = false;
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
			$isMaster1 = 1;
		}*/
		
		$skills  = $pd->query("select * from oa_zhsz_course where is_score=0 order by id DESC")->fetchAll(PDO::FETCH_ASSOC);  //菜单
		$str_menu = '';
		foreach($skills as $rsm){
			$str_menu .= '<td class="tabtd">'.$rsm['course_name'].'</td>';
		}
		//if($_SESSION['role_id']==4)
			$str_menu .=' <td width="8%" class="tabtd">操作选项</td>';
		
		//查询记录
		$strWhere = " B.term_id={$tyear}";
		
		if($_SESSION['role_id']!=4)
			$strWhere .= " and checker_id='{$_SESSION['uid']}'";
		
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and A.dept IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and A.dept={$classId} ";
			}
		}

		if(!empty($truename)){
			$strWhere .= "  and A.truename LIKE'{$truename}%'";
		}
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$where  = $strWhere;

		$sql 	= 'select DISTINCT(A.userid),A.dept,A.truename';
		$sql 	.= ' from v_users AS A ';
		$sql    .= ' inner join oa_zhsz_course_level as B ON A.userid=B.user_id ';
		$sql 	.= " where {$where}";
		$sql 	.= ' order by CONVERT(A.truename USING GBK) ';
		$sql_total = $sql;
		$sql 	.= " limit {$offset},20";

		$student  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$str_total = '';
		foreach($student as $rs){
			$class_name = $pd->query("select A.class_name,B.grade_name,A.grade_id from oa_zhsz_class as A inner join oa_zhsz_grade as B ON A.grade_id=B.id WHERE A.id={$rs['dept']}")->fetchAll(PDO::FETCH_ASSOC);
			$name = '';
			if($class_name){
				$name = $class_name[0]['grade_name'].$class_name[0]['class_name'];
			}
			
			$scores = '';
			foreach($skills as $rss){
				$skill_score  = $pd->query("select * from oa_zhsz_course_level where user_id='{$rs['userid']}' and term_id={$tyear} and course_id={$rss['id']}")->fetchAll(PDO::FETCH_ASSOC);
				
				if(!empty($skill_score)){
					$scores .= '<td class="tabtd">'.$skill_score[0]['level'].'</td>';
				}else{
					$scores .= '<td class="tabtd">-</td>';
				}
			}
			
			//if($_SESSION['role_id']==4)
				$scores .= '<td class="tabtd" style="text-align:left; padding-left:10px;"><img src="images/037.gif" /><a href="./?t=skills_modify&uid='.$rs['userid'].'&tid='.$tyear.'">修改</a> <img src="images/010.gif" /> <a href="javascript:delSkill(\''.$rs['userid'].'\','.$tyear.');">删除</a></td>';
			
			$str_total .= '<tr><td class="tabtd">'.$Term['year'].'('.$Term['term_name'].')</td><td class="tabtd">'.$rs['truename'].'</td><td class="tabtd">'.$name.'</td>'.$scores.'</tr>';
		}

		//查询所有记录总数
		$sql_total = $pd->query($sql_total)->fetchAll(PDO::FETCH_ASSOC);
		
		$rc = count($sql_total);
		$page=getPageHtml_bt($rc,20,$p,"&t=skills_manage&gid_page={$gid}&cid_page={$cid}&tyear_page={$tyear}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 

		$T->Set("truename",$truename);
		$T->Set("gradeName",$gradeName);
		$T->Set("tyear",$tyear);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("str_total",$str_total);
		$T->Set("str_menu",$str_menu);
		break; 
		
	case "skills_add":#技能素质添加
		//查询老师实际所教课程
		//$teachArray  = $pd->query("select * from oa_zhsz_teach where user_id='{$_SESSION['uid']}' and course_id not in (1,2,7,8,9) group by course_id")->fetchAll(PDO::FETCH_ASSOC);
		$teachArray  = $pd->query("select A.*,B.is_score from oa_zhsz_teach  as A inner join oa_zhsz_course AS B ON A.course_id=B.id where A.user_id='{$_SESSION['uid']}' and B.is_score=0 group by A.course_id")->fetchAll(PDO::FETCH_ASSOC);

		if(empty($teachArray)){
			$tips = array(
					   'tips' => '抱歉，当前用户没有设置所教班级及技能素质课程信息。',
					   'url'  => './?t=skills_manage'
					  );
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		/*foreach($teachArray as $k=>$v){
			$teachArray[$k]["is_checked"]  = $pd->fetchOne(array('field'=>'is_checked','table'=>'oa_zhsz_course','where'=>"id={$teachArray[$k]['course_id']}"));
			$teachArray[$k]["is_score"]  = $pd->fetchOne(array('field'=>'is_score','table'=>'oa_zhsz_course','where'=>"id={$teachArray[$k]['course_id']}"));
		}*/
		$str = "";
		foreach($teachArray as $rs){
		  $str .= "<option value='{$rs['course_id']}' stype='{$rs['is_score']}'>{$rs['course_name']}</option>";
		}
		$T->Set("str",$str);
		break;
		
	case "skills_modify":#技能素质编辑
		$uid   = isset($_GET['uid'])?Filter::filter_html($_GET['uid']):"";
		$tid   = isset($_GET['tid'])?Filter::filter_number($_GET['tid']):"";
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		
		$class_name = $pd->query("select A.class_name,B.grade_name,A.grade_id from oa_zhsz_class as A inner join oa_zhsz_grade as B ON A.grade_id=B.id WHERE A.id={$user['dept']}")->fetchAll(PDO::FETCH_ASSOC);
		$name = '';
		if($class_name){
			$name = $class_name[0]['grade_name'].$class_name[0]['class_name'];
		}
		
		$skills  = $pd->query("select * from oa_zhsz_course where is_score=0 order by id DESC")->fetchAll(PDO::FETCH_ASSOC);  //菜单
		$str_total = '';
		foreach($skills as $rs){
			$score = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course_level','where'=>"user_id='{$uid}' and term_id={$tid} and course_id={$rs['id']}"));
			if(!empty($score)){
				if($_SESSION['role_id']==4 || $score['checker_id']==$_SESSION['uid']){
					$str_score = '<input type="text" name="level[]" id="level" maxlength=20 value="'.$score['level'].'" /><input type="hidden" name="ids[]" id="ids" value="'.$score['id'].'" />';
				}else{
					$str_score = $score['level'];
				}
			}else{
				$str_score = '';
			}
			$str_total .= '<tr> <td width="20%" class="tabtd2_L">'.$rs['course_name'].'：</td><td class="tabtd2_R">'.$str_score.'</td></tr>';
		}
		
		$T->Set("truename",$user['truename']);
		$T->Set("name",$name);
		$T->Set("uid",$uid);
		$T->Set("str_total",$str_total);
		break;
		
	case "operation_skill": #学生技能素质
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));

		$skills  = $pd->query("select * from oa_zhsz_course where is_score=0 order by id DESC")->fetchAll(PDO::FETCH_ASSOC);  //菜单
		$str_menu = '';
		foreach($skills as $rsm){
			$str_menu .= '<td class="tabtd">'.$rsm['course_name'].'</td>';
		}
		
		$terms  = $pd->query("select * from oa_zhsz_term  order by year desc,term_type desc")->fetchAll(PDO::FETCH_ASSOC);
		$str_total = '';
		foreach($terms as $rs){
			$scores = '';
			$num = 0;
			foreach($skills as $rss){
				$skill_score  = $pd->query("select * from oa_zhsz_course_level where user_id='{$uid}' and term_id ={$rs['id']} and course_id={$rss['id']}")->fetchAll(PDO::FETCH_ASSOC);
				
				if(!empty($skill_score)){
					$scores .= '<td class="tabtd">'.$skill_score[0]['level'].'</td>';
				}else{
					$scores .= '<td class="tabtd">-</td>';
					$num++;
				}
			}
			
			$scores .= '<td class="tabtd" style="text-align:center;padding-left:10px;"><img src="images/see.png" style="text-align:left;width:18px;height:12px;">&nbsp;<a href="./?t=operation_skill_detail&tid='.$rs['id'].'">查看</a></td>';
			
			if($num != count($skills))    //把一项成绩都没有的学期过滤掉
				$str_total .= '<tr><td class="tabtd">'.$rs['year'].'('.$rs['term_name'].')</td><td class="tabtd">'.$user['truename'].'</td>'.$scores.'</tr>';
		}

		$T->Set("str_total",$str_total);
		$T->Set("str_menu",$str_menu);
		break; 
		
	case "operation_skill_detail":#学生技能素质查看
		$tid   = isset($_GET['tid'])?Filter::filter_number($_GET['tid']):"";
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
		
		$class_name = $pd->query("select A.class_name,B.grade_name,A.grade_id from oa_zhsz_class as A inner join oa_zhsz_grade as B ON A.grade_id=B.id WHERE A.id={$user['dept']}")->fetchAll(PDO::FETCH_ASSOC);
		$name = '';
		if($class_name){
			$name = $class_name[0]['grade_name'].$class_name[0]['class_name'];
		}
		
		$skills  = $pd->query("select * from oa_zhsz_course where is_score=0 order by id DESC")->fetchAll(PDO::FETCH_ASSOC);  //菜单
		$str_total = '';
		foreach($skills as $rs){
			$score = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course_level','where'=>"user_id='{$uid}' and term_id={$tid} and course_id={$rs['id']}"));
			if(!empty($score)){
				$str_score = $score['level'];
			}else{
				$str_score = '';
			}
			$str_total .= '<tr> <td width="20%" class="tabtd2_L">'.$rs['course_name'].'：</td><td class="tabtd2_R">'.$str_score.'</td></tr>';
		}
		
		$T->Set("truename",$user['truename']);
		$T->Set("name",$name);
		$T->Set("uid",$uid);
		$T->Set("str_total",$str_total);
		break;
		
	case "stu_jiangcheng": #学生页面奖惩管理
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		
		$tid_page=isset($_GET["tid_page"])?$_GET["tid_page"]:0;
		$flagType_page=isset($_GET["flagType_page"])?$_GET["flagType_page"]:0;
		
		//查询综评表信息
		$tid  = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id'],0);
		$flagType  = empty($_POST['flag_type'])?'':Filter::filter_html($_POST['flag_type']);

		if($tid_page)
			$tid = $tid_page;
		if($flagType_page)
			$flagType = $flagType_page;

		$strWhere = " a.user_id='{$uid}' ";
		if($tid!=0){
			$strWhere .= " and a.term_id={$tid} ";
		}
		if($flagType!=''){
			$strWhere .= " and a.flag_type='{$flagType}' ";
		}
		$from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";
		/* if($from_id){
			$where1 = " and a.id={$from_id}";
		} */

		//查询日常表现
		$where  = $strWhere;
		if($from_id){
			$where .= " and a.id={$from_id}";
		}
		$sql 	= "select a.*,(select concat(year,term_name) from oa_zhsz_term where id=a.term_id) term_name";
		$sql 	.= " from oa_zhsz_reward_punishment as a where  {$where} ";
		$sql	.= " order by a.id desc  limit {$offset},20 ";
		//动态信息查询
		//$performance  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$T->SetBlock("list",$sql);
		//查询所有动态
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_reward_punishment as a','where'=>$where);
		$rc  = $pd->fetchOne($params);

		$page=getPageHtml_bt($rc,20,$p,"&t=stu_jiangcheng&flagType_page={$flagType}&tid_page={$tid}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("term_id",$tid);
		$T->Set("flag_type",$flagType);
		break; 
}


#释放资源
$T->clearNoN();
$T->clearNaN();
$T->display();
$T->close();	
$pd->close();
unset($T);