<?php
header("Content-type: text/html; charset=utf8;");
require('../ppf/fun.php');
require("../ppf/pdo_template.php");
require("../ppf/pdo_mysql.php"); 
require('../oa/common.php');
require '../oa/library/Filter.php';

$qname=isset($_GET["t"])?$_GET["t"]:"index"; 
$pd = new pdo_mysql();
ini_set('memory_limit','1000M');  //初始化数据库内存大小
session_start();
$x_uid=isset($_SESSION['uid'])?$_SESSION['uid']:"";
if(empty($x_uid)){	
	$qname="login";
}

$T=new pdo_template('./html/'.$qname.'.htm',true,"utf8");

switch($qname){
	case "index": #微信首页  
		$T->SetBlock("wx_top_pic","select * from oa_wx_pic where school=".$_SESSION['school']." order by id desc limit 3");
		
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		
		//$read = $T->db->query("select is_read from message where geter=".$x_uid);
		#查询消息状态
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		
		$status="";
		//echo "'{$_SESSION['uid']}'";
		//判断提交方式
		if($submitMethod=='POST'){
			$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
			//$idss =isset($_POST['id'])?join(',',$_POST['id']):"";
			$where="";
			//echo $_POST['status'];
			if(!empty($_POST['status'])){
				//echo $_POST['status'];
				$status=($_POST['status']=="1")?"已审核":"未通过";
				//echo $status;
				$where.="M.flag='".$status."' and ";
				//echo $where;
				//exit;
				$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where ".$where." M.geter='{$_SESSION['uid']}'");
				$r = $T->db->query("update oa_message set is_read=1 where id in ({$ids})");
			    //$rs = $T->db->query("update oa_message set is_read=0 where id in ({$idss})");
			}else{
				$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}'");
				$r = $T->db->query("update oa_message set is_read={$_POST['read_select']} where id in ({$ids})");
				//$rs = $T->db->query("update oa_message set is_read=0 where id in ({$idss})");
				Header("Location:./?t=index");
			}

		}else{
			$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}'");
			//ECHO "select M.* from oa_message M left join oa_zhsz_setting_duty O on O.user_id=M.geter where  M.geter='{$_SESSION['uid']}'";
			//exit;
			
		}
		
		#查询审核状态
		$rc= $T->db->query("select count(1) from oa_message where geter='{$_SESSION['uid']}'")->fetchColumn(0);
		$page=getPageHtml_bt($rc,12,$p,"&t=index");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("uid",$_SESSION['uid']);
		break; 
	case "login":
		$appid = 'wxa597f6339dd0dc3a';
		$secre='6db724daf2d62a250c5c03d8d6cc3110';
		$codes=isset($_GET['code'])?$_GET['code']:"";
        $opendid='';
		
		if(!empty($codes)){
			$token_url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secre.'&code='.$codes.'&grant_type=authorization_code';
			$token = json_decode(file_get_contents($token_url));
			$opendid= $token->openid;
			$c_user=$pd->query("select * from wx_user where opid='".$opendid."'");
			if($c_user){
				$g_user=$pd->query("select * from v_users where id='".$c_user['w_uid']."'");
				//cache
				if($g_user){
					$_SESSION['uid'] = $g_user['id'];
					$_SESSION['wname']   = $g_user['username'];
					$_SESSION['role_id']    = $g_user['role_id'];
					$_SESSION['user_type']  = $g_user['from_type'];
					$_SESSION['flag_type']  = $g_user['flag_type'];
					$_SESSION['truename']   = $g_user['truename'];
					$_SESSION['school']   = $g_user['school'];
					$_SESSION['dept']   = $g_user['dept'];
					$now = date('Y-m-d H:i:s');
					$ip  = $_SERVER['REMOTE_ADDR'];
					//更新管理员信息
					$pd->exec("UPDATE act_member SET lgnums = lgnums + 1,lastip = '{$_SERVER['REMOTE_ADDR']}',lasttime = '{$now}' where username='{$g_user['username']}'");
					//系统日志
					if($web['flag_log']==1){
						$data = array(
							'content'     => "用户[{$g_user['username']}]于{$now}成功登录平台。",
							'flag_type'   => '登录系统T',
							'use_ip'	  => $ip,
							'create_by'   => $g_user['username'],
							'create_time' => $now
						);
					}
					if($_SESSION['role_id']==1)
						header('Location:../?t=index');
					elseif($_SESSION['role_id']==4)  //管理员
						header('Location:../?t=admin_index');
					else
						header('Location:../?t=admin_index');
				}
			}
		}
		$T->Set("op_id",$opendid); 
		break;
	case "group_list":  //自定义评价
		$term_id   = isset($_POST['grade_id'])?Filter::filter_html($_POST['grade_id']):"";
		$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		$rid   = isset($_POST['rid'])?Filter::filter_html($_POST['rid']):"";

		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		/*if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}*/
	   
		$uid=$_SESSION['uid'];
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and report_type=0 and school={$_SESSION['school']}")->fetchAll(PDO::FETCH_ASSOC);
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
		
		$str_main = '';
		$main = $pd->query("SELECT * from oa_zhsz_report_custom where {$where} order by create_time desc  limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$cur_term = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'id='.$rs['term_id'].' and school='.$_SESSION['school']));
			
			$if_edit = '';  //是否可以编辑
			if($curTerm['id'] == $rs['term_id'] && $rs['flag_status']=="待审核"){
				$if_edit = '&nbsp;&nbsp; <img src="images/037.gif" /> <a aid="'.$rs['id'].'" id="caozuo" href="./?t=report_custom_edit&id='.$rs['id'].'" style="display:inline-block;">修改</a>';
			}
			
			$grade = '';
			foreach($term as $rs1){    //判断年级
				if($rs['term_id'] == $rs1['id'])
					$grade = $rs1['term'];
			}
			
			if(empty($rs['subject_son_id']))
				$subject = $rs['subject'];
			else 
				$subject = $rs['subject_son'];
			
			//$str_main .= '<tr class="ms_info"><td class="content" width="15%">'.$subject.'</td><td class="content" width="15%">'.$rs['title'].'</td><td class="topic_name" width="6%">'.$grade.'</td><td class="content"  width="33%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="10%">'.$rs['times'].'</td><td class="content" width="15%">'.$rs['place'].'</td><td width="6%" class="tabtd" align="left">'.$if_edit.'</td></tr>';
			$str_main .=' <div class="row"><div class="col col20 bd_l_ntp">'.$rs['title'].'</div><div class="col col20 bd_l_ntp">'.$rs['content'].'</div><div class="col col20 bd_l_ntp">'.$subject.'</div><div class="col col20 bd_l_ntp">'.$grade.'</div><div class="col col20 bd_r_ntp">'.$rs['times'].'</div></div>';
		}
		
		$total = $pd->query("SELECT * from oa_zhsz_report_custom where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=group_list");
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
	case "group_list1":  //自我评价
		$term_id   = isset($_POST['grade_id'])?Filter::filter_html($_POST['grade_id']):"";
		$report_type   = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		/*if(empty($curTerm)){
			$tips = array(
			   'tips' => '抱歉，请联系管理人员设置当前学期。',
			   'url'  => 'user_modify.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;	
		}*/
	   
		$uid=$_SESSION['uid'];
		$term   = showTerm($pd,$uid);	
	
		$str_grade = '';
		foreach($term as $rs){
			$str_grade.='<option value="'.$rs['id'].'">'.$rs['term'].'</option>';
		}
		
		$str_report = '';
		$str_report_type = '';
		$str_content = '';
		$report = $T->query("SELECT id,code_name,content from  oa_zhsz_report where parent_no='' and report_type=1 and school={$_SESSION['school']}")->fetchAll(PDO::FETCH_ASSOC);
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
				
			//$str_main .= '<tr class="ms_info"><td class="content" width="17%">'.$rs['subject'].'</td><td class="topic_name" width="7%">'.$grade.'</td><td class="content" width="13%">'.$cur_term['year'].'('.$cur_term['term_name'].')</td><td class="content" width="38%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="19%"><div class="ha_tacontent">'.$rs['note'].'</div></td><td  width="7%" class="tabtd" align="left">'.$if_edit.'</td></tr>';
			$str_main .='<div class="row"><div class="col col20 bd_l_ntp">'.$rs['subject'].'</div><div class="col col20 bd_l_ntp">'.$grade.'</div><div class="col col20 bd_l_ntp">'.$cur_term['year'].''.$cur_term['term_name'].'</div><div class="col col20 bd_l_ntp">'.$rs['content'].'</div><div class="col col20 bd_r_ntp">'.$rs['note'].'</div></div>';
		}
		
		$total = $pd->query("SELECT * from oa_zhsz_report_self where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,20,$p,"&t=group_list1");
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
		break;
	case "user": 
		$T->Set("truename",$_SESSION['truename']);
		break;
	case "user_mes": 
	    $r=$pd->query("select * from v_users where id='".$_SESSION['uid']."'")->fetch(PDO::FETCH_ASSOC);
		$T->Set("username",$r["username"]); 
		$T->Set("truename",$r["truename"]);
		$T->Set("gender",$r["gender"]);
		$T->Set("birthday",$r["birthday"]);
		$T->Set("address",$r["address"]);
		$T->Set("mobile",$r["mobile"]);
		$T->Set("email",$r["email"]);
		$T->Set("political_status",$r["political_status"]);
		break;
	case "jiangcheng": 
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		
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
		
		//查询日常表现
		$where  = $strWhere;
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
		$T->Set("flag_type",$flagType);
		$T->Set("isMaster1",$isMaster1);
		break;
	case "zhsz_other": #综合评价记录
		
		break;
	case "score_manage": #成绩管理
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
		$isMaster1 = 0;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
			$isMaster1 = 1;
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
			$strWhere .= "  and C.truename LIKE'{$truename}%'";
			$strQuery .= "&truename={$truename}";
		}
		if(!empty($tid)){
			$strWhere .= "  and B.term_id={$tid} ";
			$strQuery .= "&tid={$tid}";
		}
		$strQuery .= "&cid={$cid}";
		$strQuery .= "&gid={$gid}";
		$strQuery .= "&tid={$tid}";
		
		$where  = $strWhere;
		//查询所有成绩记录
		$sql 	= 'select A.userid ';
		$sql 	.= ' from oa_user_extend AS A ';
		$sql    .= ' inner join oa_zhsz_score as B ON A.userid=B.user_id inner join act_member AS C ON B.user_id=C.id';
		$sql 	.= " where {$where}";
		$scoreN = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($scoreN);
	
		if($_SESSION['role_id']==4){
			$isadmin=1;
		}else{
			$isadmin=0;
		}
		
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
		$T->Set("isMaster1",$isMaster1);
		$T->Set("orderBy",$orderBy);
		$T->Set("isadmin",$isadmin);
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
			
			$sm = $pd->query("select item_id,item_name,result from oa_zhsz_stamina_record where user_id='{$rs['userid']}' {$where} order by item_id")->fetchAll(PDO::FETCH_ASSOC);

			$str_total .= '<tr><td class="tb_td tb_txt_center">'. $rs['truename'].'</td><td class="tb_td tb_txt_center">'.$gradeName.$rs['class_name'].'</td>';
			foreach($sm as $rsb){
				if(isset($itemA[$rsb['item_id']])){
					$str_total .= "<td class='tb_td tb_txt_center'>{$rsb['result']}</td>";	
				}
			}
			if(empty($sm)){
				for($i=0;$i<$len;$i++){
					$str_total .= "<td class='tb_td tb_txt_center'>-</td>";	
				}
				//$str_total .= '<td class="tb_td" style="text-align:left; padding-left:10px; color:#333;">&nbsp;暂无数据</td>';
			}else{
				//if($isadmin)
					//$str_total .= '<td class="tb_td" style="text-align:left; padding-left:10px;"><img src="images/037.gif" /> <a href="./?t=stamina_rs_modify&uid='.$rs['userid'].'&tid='.$tid.'">修改</a> <img src="images/010.gif" /> <a href="javascript:delStamina(\''.$rs['userid'].'\','.$tid.');">删除</a></td>';
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
	case "physique_manage": #体质管理
		set_time_limit(0);
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

		if(!empty($tyear)){
			$strWhere .= "  and B.term_year='{$tyear}' ";
		}
		
		if(!empty($truename)){
			$strWhere .= "  and A.truename LIKE'{$truename}%'";
		}
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		
		$where  = $strWhere;
		$sql 	= 'select A.truename,B.*,(select class_name from oa_zhsz_class where id=A.dept) AS class_name ';
		$sql 	.= ' from v_users AS A ';
		$sql    .= ' inner join oa_zhsz_physique as B ON A.id=B.user_id';
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
		$T->Set("gradeName",$gradeName);
		$T->Set("tyear",$tyear);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->SetBlock("list","select id,year,term_name from oa_zhsz_term  group by year order by flag_default DESC,year DESC ");
		$T->Set("isMaster1",$isMaster1);
		$T->Set("isadmin",$isadmin);
		break; 
	case "course_all_statistics": #技能素质统计
		$T->SetBlock("list","select id,course_name from oa_zhsz_course where is_checked=1 order by id DESC");
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		$params = array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'school='.$_SESSION['school'],'order'=>'flag_default DESC');
		$terms  = $pd->fetchAll($params);
		$termid = isset($_GET['term_id'])?$_GET['term_id']:$curTerm["id"];

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
		$course  = $pd->fetchAll(array('field'=>array('id','course_name'),'table'=>'oa_zhsz_course','where'=>'is_checked=1 and school='.$_SESSION['school'],'order'=>'id DESC'));

		//统计报表
		foreach($class as $k=>$rs){
			$class[$k]['grade_name']  = $rs['grade_id']==3?'高三':($rs['grade_id']==1?'高一':'高二');
			for($i=0;$i<count($course);$i++){
				$course_num = $pd->query("select count(*) as num from oa_zhsz_course_level A left join oa_user_extend B on A.user_id=B.userid where course_id={$course[$i]['id']} and B.dept={$rs['id']} and A.term_id={$termid}")->fetchAll(PDO::FETCH_ASSOC);
				$class[$k]["cnums"][$i] = $course_num[0]['num'];
			}  
		}

		$classlen=count($class);
		$str_total = '';
		for($i=0;$i<$classlen;$i++){
			$num = $i+1;
		  $rs=$class[$i];              
		  $str_total .= ' <tr><td class="tb_td tb_txt_center">'.$num.'</td><td class="tb_td tb_txt_center">'.$rs['grade_name'].$rs['class_name'].'</td><td class="tb_td tb_txt_center">'.$rs['nums'].'</td>';
		   for($j=0;$j<count($rs['cnums']);$j++){
			    $str_total .= ' <td class="tb_td tb_txt_center">'.$rs['cnums'][$j].'</td>';
		   }
		   $str_total .= '</tr>';
		}
		
		//查询所有班级
		$params = array('field'=>'count(A.id)','table'=>'oa_zhsz_class AS A','where'=>$where);
		$rc = $pd->fetchOne($params);

		$page=getPageHtml_bt($rc,20,$p,"&t=course_all_statistics");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("page",$page); 
		$T->Set("str_total",$str_total); 
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
				$str_total .= '<td class="tb_td" style="text-align:center;"><img src="images/010.gif" /> <a href="javascript:delEvent('.$rs['id'].');">删除</a></td>';
		//	}
			
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tb_td" style="text-align:center;">'.$i.'</td><td class="tb_td" style="text-align:center;">'.$rs['bname'].'</td><td class="tb_td" style="text-align:center;">'.$class_end.$class_name.'</td><td class="tb_td" style="text-align:center;">'.$rs['start_date'].'&nbsp;至&nbsp;'.$rs['end_date'].'</td><td class="tb_td" style="text-align:center;">'.$rs['name'].'</td><td class="tb_td" style="text-align:center;">'.$rs['subject'].'</td><td class="tb_td" style="text-align:center;">'.$rs['content'].'</td></tr>';
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
				$str_total .= '<td class="tb_td" style="text-align:center;"><img src="images/010.gif" /> <a href="javascript:delEvent('.$rs['id'].');">删除</a></td>';
			//}
			
			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tb_td" style="text-align:center;">'.$i.'</td><td class="tb_td" style="text-align:center;">'.$rs['bname'].'</td><td class="tb_td" style="text-align:center;">'.$class_end.$class_name.'</td><td class="tb_td" style="text-align:center;">'.$rs['subject'].'</td><td class="tb_td" style="text-align:center;">'.$rs['start_date'].'&nbsp;至&nbsp;'.$rs['end_date'].'</td><td class="tb_td" style="text-align:center;">'.$rs['name'].'</td><td class="tb_td" style="text-align:center;">'.$rs['content'].'</td></tr>';
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
			
			$str_total .= '<tr id="reply_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tb_td tb_txt_center" width="5%">'.$i.'</td><td class="tb_td tb_txt_center"  width="10%">'.$rs['bname'].'</td><td class="tb_td"  width="55%">'.$rs['content'].'</td><td class="tb_td tb_txt_center" width="10%">'.$tips.'</td><td class="tb_td tb_txt_center" width="10%">'.$user.'</td>';
			
			//if(isset($priv[1])){
				//$str_total .= '<img src="images/037.gif" /> <a href="javascript:showReply('.$rs['id'].','.$rs['flag_checked'].');">审核</a>';
			//}
			//if(isset($priv[3])){
				//$str_total .= '<img src="images/010.gif" /> <a href="javascript:delReply('.$rs['id'].');">删除</a>';
			//}
			
			$str_total .= '</tr>';
			$i++;
		}
						
		$page=getPageHtml_bt($rc,20,$p,"&t=reply_manage");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("truename",$truename);
		$T->Set("str_total",$str_total);
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
		/*if($submitMethod=='POST'){
			$id     = $_POST['id'];
			$op     = $_POST['op'];
			$ids = join(',',$id);

			if($op==1){//审核
				$data = array('is_check'=>'1');
			}
			if($op==2){//取消审核
				$data = array('is_check'=>'0');
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
		}*/
		
		$p=isset($_GET["p"])?$_GET["p"]:"0";
		if($p==1) $p = 0;
		$strWhere = 'type=3';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);
		if($_SESSION['role_id']!=4){
			$strWhere .= ' and is_check=1';
		}
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
			if($_SESSION['role_id']==4){
				if($rs['is_check'] == 1){
					$is_check = "<font color=\'green\'>已审核</font>";
				}else if($rs['is_check'] == 2){
					$is_check = "未通过";
				}else{
					$is_check = "<font color=\"red\">待审核</font>";
				}
			
				$str_total .= '<td class="tb_td" align="left">&nbsp;&nbsp;&nbsp;&nbsp;<a 
			id="shtg" href="javascript:void(0);"
			class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\''.$rs['subject'].'\');"></a>&nbsp;<a   
			id="shwtg" href="javascript:void(0);"
			class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\'未通过\',\''.$rs['subject'].'\');" style="width:15px;overflow:hidden;margin:0 6px 0 5px ;">&nbsp;</a></td><td class="tb_td" style="text-align:center;">'.$is_check.'</td>';
				$str_first = '<td class="tb_td"><input type="checkbox" name="id[]" id="id_'.$rs['id'].'" value="'.$rs['id'].'" />';
			}else{
				$str_first = '<td class="tb_td tb_txt_center">'.$i.'</td>';
			}

			$str_event .= '<tr id="exp_'.$rs['id'].'">'.$str_first.'<td class="tb_td">'.$rs['bname'].'</td><td class="tb_td">'.$class_end.$class_name.'</td><td class="tb_td" style="text-align:center;">'.$rs['update_time'].'</td><td class="tb_td" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$str_show = '';
		$str_check = '';
		//if(isset($priv[3])){
		if($_SESSION['role_id']==4){
			$str_show = '<td width="5%" class="tabtd" style="text-align:center;">操作选项</td><td width="5%" class="tabtd" style="text-align:center;">状态</td>';
			$str_check = '<div><select name="op" id="op" ><option value="0">请选择操作</option><option value="1">审核通过</option><option value="2">取消审核</option></select><input type="submit" id="but_op" name="but_op" value="确定" /></div>';
			
			$str_top = '<td width="5%" class="tabtd"><input type="checkbox" name="all_select" id="all_select" value="1" onclick="selectAll(\'id[]\');" /></td>';
			
		}else{
			$str_top = '<td width="5%" class="tb_td tb_txt_bgc">序号</td>';
		}
		$rc = count($study);

		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=senior_message_search");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		$T->Set("uid",$_SESSION['uid']);
		//echo $uid;
		//exit;
		$T->Set("truename",$truename);
		$T->Set("str_event",$str_event);
		$T->Set("str_show",$str_show);
		$T->Set("str_check",$str_check);
		$T->Set("str_top",$str_top);
		break; 
		
	case "remarks": #友朋寄语
		$from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		if(!empty($class)){
			$master_val=$class['class_master'];
		}else{
			$master_val='';
		}
		$status_page = empty($_GET['status_page'])?'':$_GET['status_page'];
		$jiyu_page = empty($_GET['jiyu_page'])?'':$_GET['jiyu_page'];
		
		$jiyu = empty($_POST['jiyu'])?'':$_POST['jiyu'];
		$status = empty($_POST['status'])?'':$_POST['status'];
		
		if($status_page)
			$status = $status_page;
		if($jiyu_page)
			$jiyu = $jiyu_page;
		
		
		$p=isset($_GET["p"])?$_GET["p"]:"0";
		if($p==1) $p = 0;
		$strWhere = '1=1';
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$truename = Filter::filter_html($truename);
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
			if($_SESSION['role_id']==4|| $_SESSION['role_id']==6 || $_SESSION['role_id']==6){
					$str_total .= '<td class="tb_td" style="text-align:center;">'.$is_check.'</td>';
			}

			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tb_td" style="text-align:center;">'.$i.'</td><td class="tabtd" style="display:none;"><input type="checkbox" name="uid[]" uid="uid_'.$rs['user_id'].'" value="'.$rs['user_id'].'" /></td><td class="tb_td" style="text-align:center;">'.$rs['bname'].'</td><td class="tb_td" style="text-align:center;">'.$class_name.'</td><td class="tb_td" style="text-align:center;">'.$rs['create_time'].'</td><td class="tb_td" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$str_show = '';
		if($_SESSION['role_id']==4|| $_SESSION['role_id']==6 || $_SESSION['role_id']==6){
			$str_show = '<td width="7%" class="tb_td tb_txt_bgc" style="text-align:center;">状态</td>';
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
		$T->Set("uid",$_SESSION['uid']);
		$T->Set("truename",$truename);
		$T->Set("str_event",$str_event);
		$T->Set("str_show",$str_show);
		$T->Set("status",$status);
		$T->Set("master_val",$master_val);
		break; 
		
	case "report_custom_check":  //自定义评价审核
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
		$i = 1;
		foreach($main as $rs){
			$grade = $pd->query("SELECT class_name,(SELECT grade_name from oa_zhsz_grade WHERE id=grade_id) as grade_name from oa_zhsz_class WHERE id={$rs['dept']}")->fetchAll(PDO::FETCH_ASSOC);
			$str_main .= '<tr class="ms_info"><td class="tb_td" style="text-align:center;">'.$i++.'</td><td class="tb_td" width="7%">'.$rs['truename'].'</td><td class="tb_td" width="7%">'.$grade[0]['grade_name'].$grade[0]['class_name'].'</td><td class="tb_td" width="15%">'.$rs['subject'].'</td><td class="tb_td" width="15%">'.$rs['subject_son'].'</td><td class="tb_td" width="39%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="tb_td" width="6%">'.$rs['flag_status'].'</td></tr>';
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
			$str_menu .= '<td class="tb_td tb_txt_bgc">'.$rsm['course_name'].'</td>';
		}
		//if($_SESSION['role_id']==4)
			//$str_menu .=' <td width="8%" class="tabtd">操作选项</td>';
		
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
		$i = 1;
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
					$scores .= '<td class="tb_td" style="text-align:center;">'.$skill_score[0]['level'].'</td>';
				}else{
					$scores .= '<td class="tb_td" style="text-align:center;">-</td>';
				}
			}
			
			//if($_SESSION['role_id']==4)
				//$scores .= '<td class="tabtd" style="text-align:left; padding-left:10px;"><img src="images/037.gif" /><a href="./?t=skills_modify&uid='.$rs['userid'].'&tid='.$tyear.'">修改</a> <img src="images/010.gif" /> <a href="javascript:delSkill(\''.$rs['userid'].'\','.$tyear.');">删除</a></td>';
			
			$str_total .= '<tr><td class="tb_td" style="text-align:center;">'.$i++.'</td><td class="tb_td" style="text-align:center;">'.$Term['year'].'('.$Term['term_name'].')</td><td class="tb_td" style="text-align:center;">'.$rs['truename'].'</td><td class="tb_td" style="text-align:center;">'.$name.'</td>'.$scores.'</tr>';
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
		$T->Set("str_grade",$str_grade);
		$T->Set("str_term",$str_term);
		break; 
}

#释放资源
$T->clearNoN();
$T->clearNaN();
$T->display();
$T->close();	
unset($T);
?>