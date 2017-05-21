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
if(!empty($_SESSION["role_id"])){
	if(($_SESSION['role_id']==4 || $_SESSION['role_id']==5) && ($qname=="index")){
			$qname="admin_index";
	} 
}
$T=new pdo_template('./html/'.$qname.'.htm',true,"utf8");
$uid=$x_uid;
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
				Header("Location:./?t=index");
			}

		}else{
			$T->SetBlock("list","select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}'");
			
		}
		
		#查询审核状态
		$rc= $T->db->query("select count(1) from oa_message where geter='{$_SESSION['uid']}'")->fetchColumn(0);
		$page=getPageHtml_bt($rc,12,$p,"&t=index");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		//$T->Set("uid",$_SESSION['uid']);
		break; 
		
	
	case "admin_index":
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5){
			$isadmin=1;
		}else{
			$isadmin=0;
		}
		if($_SESSION['role_id']==6){
			$ismaster=1;
		}else{
			$ismaster=0;
		}
	    $T->Set("uid",$_SESSION['uid']);
	    $T->Set("isadmin",$isadmin);
		$T->Set("ismaster",$ismaster);
        break;	
		
	case "teacher_index":
	    $T->Set("uid",$_SESSION['uid']);
        break;
		
		
	case "message_0504": #学生消息  
		$T->SetBlock("wx_top_pic","select * from oa_wx_pic where school=".$_SESSION['school']." order by id desc limit 3");
		
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$read_select=isset($_POST["read_select"])?$_POST["read_select"]:"";
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$state=isset($_POST["state"])?$_POST["state"]:"";
		$offset = ($p-1)*20;
		if($offset<0) $offset = 0;
		#查询消息状态
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		$ids =isset($_POST['id'])?join(',',$_POST['id']):"";
		$r = $T->db->query("update oa_message set is_read={$read_select} where id in ({$ids})");
		
		$total = $T->query("select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		$str_total = "";
		
		foreach($total as $rs){
			if($rs['menu2']==26){
				$str_href = "report&from_id={$rs['from_id']}";
				$flag_true = $pd->fetchOne(array('field'=>'flag','table'=>'oa_message','where'=>"from_id='{$rs['from_id']}'"));
			}elseif($rs['menu2']==34){
				$str_href = "stu_remarks&from_id={$rs['from_id']}&jiyu_page=我的寄语";
				$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_remarks','where'=>"id='{$rs['from_id']}'"));
			}elseif($rs['menu2']==68){
				$str_href = "stu_jiangcheng&from_id={$rs['from_id']}";
				$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_reward_punishment','where'=>"id='{$rs['from_id']}'"));
			}
			
			$str_total .= '<tr class="tabtitle" style="line-height:30px; min-height:30px;"><td class="tb_td tb_txt_center"><span class="check_cont" v="'.$rs['menuname1'].'">'.$rs['menuname1'].'-</span><span class="check_cont" v="'.$rs['menuname2'].'">'.$rs['menuname2'].'</span></td><td class="tb_td tb_txt_center" style="text-align:left !important;"><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['subject'].'\'>类型：<span limit="20">'.$rs['subject'].'</span><br/></span></a><a href="./?t='.$str_href.'"></a><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['leaveword'].'\' style="float:left;">内容：<span style="float:right;line-height:30px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['leaveword'].'</span></span><br/></a><span class="check_cont" v=\''.$rs['content'].'\' style="float:left;">原因：<span style="float:right;line-height:30px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['content'].'</span></span><br/></td><td class="tb_td tb_txt_center">'.$rs['stime'].'</td><td class="tb_td tb_txt_center">'.$flag_true.'</td><td class="checkread tb_td tb_txt_center" is_read="'.$rs['is_read'].'">'.$rs['is_read'].'</td><td class="tb_td tb_txt_center"><a id="yread" href="javascript:void(0);" onclick="yread(\''.$rs['id'].'\');">已读</a>&nbsp;&nbsp<a id="wread" href="javascript:void(0);" onclick="wread(\''.$rs['id'].'\');">未读</a></td></tr>';
		}

		$rc= $T->db->query("select count(1) from oa_message as M where geter='{$_SESSION['uid']}'")->fetchColumn(0);
		$page=getPageHtml_bt($rc,12,$p,"&t=message");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("uid",$_SESSION['uid']);
		$T->Set("str_total",$str_total);
		$T->Set("page",$page); 
		$T->Set("state",$state);
		break;
		
	case "message": #学生消息 
	     $T->SetBlock("wx_top_pic","select * from oa_wx_pic where school=".$_SESSION['school']." order by id desc limit 3");
        $ids =isset($_POST['id'])?join(',',$_POST['id']):"";
		$read_select=isset($_POST["read_select"])?$_POST["read_select"]:"";
		$T->Set("role_id",$_SESSION['role_id']);			
		$T->Set("ids",$ids);
		$T->Set("uid",$_SESSION['uid']);	
		break;	
		
	case "message_json": #学生消息 
        $ids =isset($_POST['id'])?join(',',$_POST['id']):"";
		$read_select=isset($_POST["read_select"])?$_POST["read_select"]:"";
		$xx=isset($_POST["is_read"])?$_POST["is_read"]:"0";
		
		$p=isset($_POST["p"])?$_POST["p"]:"1";
		$offset = ($p-1)*5;
		if($offset<0) $offset = 0;		
		
		//$T->SetBlock("list1","select M.* from oa_message M left join oa_zhsz_report_self O on O.uid=M.geter where M.uid='{$_SESSION['uid']}' and M.is_read=".$xx);
		//$r = $T->db->query("update oa_message set is_read={$read_select} where id in ({$ids})");
		$total = $T->query("select M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2 from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2  where  M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($total as $rs){
			if($rs['menu2']==26){
			$str_href = "report&from_id={$rs['from_id']}";
			$flag_true = $pd->fetchOne(array('field'=>'flag','table'=>'oa_message','where'=>"from_id='{$rs['from_id']}'"));
			}elseif($rs['menu2']==34){
				$str_href = "stu_remarks&from_id={$rs['from_id']}&jiyu_page=我的寄语";
				$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_remarks','where'=>"id='{$rs['from_id']}'"));
			}elseif($rs['menu2']==68){
				$str_href = "stu_jiangcheng&from_id={$rs['from_id']}";
				$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_reward_punishment','where'=>"id='{$rs['from_id']}'"));
			}
			$atts = $pd->query("select * from oa_zhsz_attachment where custom_id='{$rs['from_id']}'")->fetchAll(PDO::FETCH_ASSOC);  //获取图片和附件
			$att_pic = '无';
			$i=1;
			if($atts!=null){
				$att_pic = '';
				foreach($atts as $att){
					if($att['attachment']!=null&&$i<=4){						
						$total[$k]['imgs'.$i]=$att['attachment'];
						$i++;
					}
				}
			}
		}
        echo json_encode($total);	
		break;
		
	case "admin_message": #教师消息 
	    $T->SetBlock("wx_top_pic","select * from oa_wx_pic where school=".$_SESSION['school']." order by id desc limit 3");
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
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5){
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
				$str_total .= '<tr class="tabtitle" style="line-height:30px; min-height:30px;"><td class="tb_td tb_txt_center">'.$rs['xname'].'<br/>'.$rs['grade_name'].$rs['class_name'].'</td><td class="tb_td tb_txt_center" ><span class="check_cont" v="'.$rs['menuname1'].'">'.$rs['menuname1'].'</span><span class="check_cont" v="'.$rs['menuname2'].'">'.$rs['menuname2'].'</span></td><td class="tb_td tb_txt_center" style="text-align:left !important;"><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['subject'].'\'>类型：'.$rs['subject'].'<br/></span></a><span class="check_cont" v=\''.$rs['leaveword'].'\' limit="20">原因：'.$rs['leaveword'].'<br/></span><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['content'].'\' style="line-height:1rem;"><i style="font-style:normal;float:left;display:inline-block;">内容：</i><span style="float:left;line-height:30px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['content'].'</span></span></a></td><td class="tb_td tb_txt_center">'.$rs['stime'].'</td><td class="tb_td tb_txt_center">'.$flag_true.'</td><td class="checkread tb_td tb_txt_center"  is_read="'.$rs['is_read'].'">'.$rs['is_read'].'</td><td class="tb_td tb_txt_center"><a id="yread" href="javascript:void(0);" onclick="yread(\''.$rs['id'].'\');">已读</a>&nbsp;&nbsp<a id="wread" href="javascript:void(0);" onclick="wread(\''.$rs['id'].'\');">未读</a></td></tr>';
			}
		}else{
			if($stu_id==""){
				$total = $T->query("select cus.flag_status,M.*,O.fea_menuname as menuname1,O2.fea_menuname as menuname2,A.truename from oa_message M left join oa_features as O on O.id=M.menu1 left join oa_features as O2 on O2.id=M.menu2 left join act_member as A on A.id=M.uid left join oa_zhsz_report_custom as cus on cus.id=M.from_id where M.geter='{$_SESSION['uid']}' order by M.is_read,M.stime desc limit {$offset},20")->fetchAll(PDO::FETCH_ASSOC);
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
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_report_custom','where'=>"id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==34){
					$str_href = "remarks&from_id={$rs['from_id']}&type=2";
					$flag_true = $pd->fetchOne(array('field'=>'flag','table'=>'oa_message','where'=>"from_id='{$rs['from_id']}'"));
				}elseif($rs['menu2']==55){
					$str_href = "jiangcheng&from_id={$rs['from_id']}";
					$flag_true = $pd->fetchOne(array('field'=>'flag_status','table'=>'oa_zhsz_reward_punishment','where'=>"id='{$rs['from_id']}'"));
				}
				$str_total .= '<tr class="tabtitle" style="line-height:30px; min-height:30px;"><td class="tb_td tb_txt_center">'.$rs['truename'].'</td><td class="tb_td tb_txt_center" ><span class="check_cont" v="'.$rs['menuname1'].'">'.$rs['menuname1'].'</span><span class="check_cont" v="'.$rs['menuname2'].'">'.$rs['menuname2'].'</span></td><td class="tb_td tb_txt_center" style="text-align:left !important;"><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['subject'].'\'>类型：'.$rs['subject'].'<br/></span></a><span class="check_cont" v=\''.$rs['leaveword'].'\' limit="20">原因：'.$rs['leaveword'].'<br/></span><a href="./?t='.$str_href.'"><span class="check_cont" v=\''.$rs['content'].'\' style="float:left;line-height:1rem;"><i style="display:inline-block;float:left;font-style:normal;">内容：</i><span style="float:left;line-height:20px;max-height:25px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp: 1;overflow:hidden;">'.$rs['content'].'</span></span></a></td><td class="tb_td tb_txt_center">'.$rs['stime'].'</td><td class="tb_td tb_txt_center">'.$flag_true.'</td><td class="checkread tb_td tb_txt_center"  is_read="'.$rs['is_read'].'">'.$rs['is_read'].'</td><td class="tb_td tb_txt_center"><a id="yread" href="javascript:void(0);" onclick="yread(\''.$rs['id'].'\');">已读</a>&nbsp;&nbsp<a id="wread" href="javascript:void(0);" onclick="wread(\''.$rs['id'].'\');">未读</a></td></tr>';
			}
		}
			$page=getPageHtml_bt($rc,20,$p,"&t=admin_message");
		    $page = mb_convert_encoding($page, 'utf-8', 'gbk');
		    $T->Set("page",$page); 
			
		//}
		$T->Set("uid",$_SESSION['uid']);
		$T->Set("str_total",$str_total);	
		/* $T->SetBlock("wx_top_pic","select * from oa_wx_pic where school=".$_SESSION['school']." order by id desc limit 3");
		
		
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		
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
		$T->Set("uid",$_SESSION['uid']); */
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
					$_SESSION['username']   = $g_user['username'];
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
			
			//$str_main .= '<tr class="ms_info"><td class="content" width="15%">'.$subject.'</td><td class="content" width="15%">'.$rs['title'].'</td><td class="topic_name" width="6%">'.$grade.'</td><td class="content"  width="33%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="10%">'.$rs['times'].'</td><td class="content" width="15%">'.$rs['place'].'</td><td width="6%" class="tb_td" align="left">'.$if_edit.'</td></tr>';
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
				
			//$str_main .= '<tr class="ms_info"><td class="content" width="17%">'.$rs['subject'].'</td><td class="topic_name" width="7%">'.$grade.'</td><td class="content" width="13%">'.$cur_term['year'].'('.$cur_term['term_name'].')</td><td class="content" width="38%"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="content" width="19%"><div class="ha_tacontent">'.$rs['note'].'</div></td><td  width="7%" class="tb_td" align="left">'.$if_edit.'</td></tr>';
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
		
	case "teacher_user": 
		$T->Set("truename",$_SESSION['truename']);
		break;
		
	case "admin_user": 
		$T->Set("truename",$_SESSION['truename']);
		break;
		
	case "pass_modify": 
		if($_SESSION['role_id']==1){
			$index_back = "index";
			$user_back = "user";
		}elseif($_SESSION['role_id']==4 || $_SESSION['role_id']==5 || $_SESSION['role_id']==6){
			$index_back = "admin_index";
			$user_back = "admin_user";
		}else{
			$index_back = "teacher_index";
			$user_back = "teacher_user";
		}
		$T->Set("index_back",$index_back);
		$T->Set("user_back",$user_back);
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
		
		if($_SESSION['role_id']==1){
			$index_back = "index";
			$user_back = "user";
		}elseif($_SESSION['role_id']==4 || $_SESSION['role_id']==5 || $_SESSION['role_id']==6){
			$index_back = "admin_index";
			$user_back = "admin_user";
		}else{
			$index_back = "teacher_index";
			$user_back = "teacher_user";
		}
		$T->Set("index_back",$index_back);
		$T->Set("user_back",$user_back);
		break;
		
	case "jiangcheng":
        $from_id   = isset($_GET['from_id'])?Filter::filter_html($_GET['from_id']):"";	
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
		$T->Set("flag_type",$flagType);
		$T->Set("isMaster1",$isMaster1);
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
	
	case "score_manage": #成绩管理
	    $p=isset($_GET["p"])?$_GET["p"]:"1"; 
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
			if($isadmin){
				$str1.='<td class="tb_td" width="6%">'.$score[$k]['sc_order'].'</td><td class="tb_td" width="6%">'.$score[$k]['sg_order'].'</td>';
			}
			
			
			$str .= '<tr><td class="tb_td" width="100">'.$rs['year'].'('.$rs['term_name'].')</td><td class="tb_td" width="4%">'.$rs['exam_type'].'</td><td class="tb_td" width="5%">'.$rs['truename'].'</td><td class="tb_td" width="6%">'.$rs['class_name'].'</td><td class="tb_td" width="4%">'.$score[$k]['yw'].'</td><td class="tb_td" width="4%">'.$score[$k]['sx'].'</td><td class="tb_td" width="4%">'.$score[$k]['wy'].'</td><td class="tb_td" width="4%">'.$score[$k]['wl'].'</td><td class="tb_td" width="4%">'.$score[$k]['hx'].'</td><td class="tb_td" width="4%">'.$score[$k]['sw'].'</td><td class="tb_td" width="4%">'.$score[$k]['zz'].'</td><td class="tb_td" width="4%">'.$score[$k]['ls'].'</td><td class="tb_td" width="4%">'.$score[$k]['dl'].'</td><td class="tb_td" width="4%">'.$score[$k]['xxjs'].'</td><td class="tb_td" width="4%">'.$score[$k]['tyjs'].'</td><td class="tb_td" width="4%">'.$score[$k]['ty'].'</td><td class="tb_td" width="4%">'.$score[$k]['yy'].'</td><td class="tb_td" width="4%">'.$score[$k]['ms'].'</td><td class="tb_td" width="4%">'.$score[$k]['xl'].'</td><td class="tb_td" width="4%">'.$rs['szf'].'</td>'.$str1.'</tr>';
		}

		$str2 = "";
		if($isadmin){
			$str2='<td width="6%" class="tb_td tb_txt_bgc"><a href="./?t=score_manage&order=sc_order{strQuery}" style="color:#f00;" title="点击进行排序">班级名次</a></td><td width="6%" class="tb_td tb_txt_bgc"><a href="./?t=score_manage&order=sg_order{strQuery}" style="color:#f00;" title="点击进行排序">年级名次</a></td>';
		}
		
		$str_tr='<tr class="tabtitle" height="26"><td class="tb_td tb_txt_bgc" width="100">学期</td><td class="tb_td tb_txt_bgc" width="4%">类型</td><td class="tb_td tb_txt_bgc" width="5%">姓名</td><td class="tb_td tb_txt_bgc" width="6%">班级</td><td class="tb_td tb_txt_bgc" width="4%">语文</td><td class="tb_td tb_txt_bgc" width="4%">数学</td><td class="tb_td tb_txt_bgc" width="4%">外语</td><td class="tb_td tb_txt_bgc" width="4%">物理</td><td class="tb_td tb_txt_bgc" width="4%">化学</td><td class="tb_td tb_txt_bgc" width="4%">生物</td><td class="tb_td tb_txt_bgc" width="4%">政治</td><td class="tb_td tb_txt_bgc" width="4%">历史</td><td class="tb_td tb_txt_bgc" width="4%">地理</td><td class="tb_td tb_txt_bgc" width="4%">信息技术</td><td class="tb_td tb_txt_bgc" width="4%">通用技术</td><td class="tb_td tb_txt_bgc" width="4%">体育</td><td class="tb_td tb_txt_bgc" width="4%">音乐</td><td class="tb_td tb_txt_bgc" width="4%">美术</td><td class="tb_td tb_txt_bgc" width="4%">心理</td><td class="tb_td tb_txt_bgc" width="4%">总分</td>'.$str2.'</tr>';
		
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
		
	/*case "score_manage": #成绩管理
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
	
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5){
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
		break; */
		
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
			if(!$gradeName){
				$get_grade = $pd->query("select grade_name from oa_zhsz_grade where id=(select grade_id from oa_zhsz_class where id={$rs['dept']})")->fetchAll(PDO::FETCH_ASSOC);
				$gradeName = $get_grade[0]['grade_name'];
			}
			$str_total .= '<tr><td class="tb_td tb_txt_center" width="7%">'. $rs['truename'].'</td><td class="tb_td tb_txt_center" width="8%">'.$gradeName.$rs['class_name'].'</td>';
			if($sm){
				foreach($stamina as $rss){
					$is_ok = 0;
					foreach($sm as $rsb){
						if($rss['id']==$rsb['item_id']){
							$str_total .= "<td class='tb_td' width='7%'>{$rsb['result']}</td>";	
							$is_ok = 1;
						}
					}
					if(!$is_ok){
						$str_total .= "<td class='tb_td' width='7%'>-</td>";	
					}
				}
			}
			if(empty($sm)){
				for($i=0;$i<$len;$i++){
					$str_total .= "<td class='tb_td tb_txt_center' width='7%'>-</td>";	
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
		
	/* case "course_all_statistics": #技能素质统计
		if($_SESSION['role_id']!=4 && $_SESSION['role_id']!=5){
			$tips = array(
			   'tips' => '抱歉，您没有操作的权限。',
			   'url'  => './?t=admin_index'
			  );
			$tips = urlencode(serialize($tips));
			header('Location:tips.php?gets='.$tips);
			exit;
		}
		#学期查询
		$term_id   = isset($_POST['term_id'])?Filter::filter_html($_POST['term_id']):"";
		$tyear_page=isset($_GET["tyear_page"])?$_GET["tyear_page"]:0;
		$tyear    = empty($_POST['tyear'])?'':$_POST['tyear'];
		if($tyear_page)
			$tyear = $tyear_page;
		
		$T->SetBlock("list","select id,course_name from oa_zhsz_course where is_checked=1 order by id DESC");
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
		$params = array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'school='.$_SESSION['school'],'order'=>'flag_default DESC');
		$terms  = $pd->fetchAll($params);
		$termid = isset($_GET['term_id'])?$_GET['term_id']:$curTerm["id"];
		$str_term = '';
		$terms=$pd->query("select id,year,term_name from oa_zhsz_term where school={$_SESSION['school']} order by year DESC,term_type DESC")->fetchAll(PDO::FETCH_ASSOC);
		foreach($terms as $rs){
			$str_term .= '<option value="'.$rs['id'].'">'.$rs['year'].'('.$rs['term_name'].')</option>';
		}
		
		if(empty($tyear)){
			$tyear = $curTerm['id'];
		}
		if($term_id==""){
			$term_id=isset($_GET['term_id'])?Filter::filter_html($_GET['term_id']):"";
		}
		if($term_id)
			$where  .= " and A.term_id='{$term_id}'";
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

		$page=getPageHtml_bt($rc,20,$p,"&t=course_all_statistics&tyear_page={$tyear}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		$T->Set("page",$page); 
		$T->Set("str_total",$str_total);
		$T->Set("tyear",$tyear);
		break; */ 
	case "course_all_statistics": #技能素质统计
		if($_SESSION['role_id']!=4 && $_SESSION['role_id']!=5){
			$tips = array(
			   'tips' => '抱歉，您没有操作的权限。',
			   'url'  => './?t=admin_index'
			  );
			$tips = urlencode(serialize($tips));
			header('Location:tips.php?gets='.$tips);
			exit;
		}
		#学期查询
		$tyear_page=isset($_GET["tyear_page"])?$_GET["tyear_page"]:0;
		$tyear    = empty($_POST['tyear'])?'':$_POST['tyear'];
		if($tyear_page)
			$tyear = $tyear_page;
		
		$T->SetBlock("list","select id,course_name from oa_zhsz_course where is_checked=1 order by id DESC");
		
		$p=isset($_GET["p"])?$_GET["p"]:"1";
		$offset = ($p-1)*20;
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
		$course  = $pd->fetchAll(array('field'=>array('id','course_name'),'table'=>'oa_zhsz_course','where'=>'is_checked=1 and school='.$_SESSION['school'],'order'=>'id DESC'));

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
		  $str_total .= ' <tr><td width="5%" class="tb_td tb_txt_center">'.$num.'</td><td width="9%" class="tb_td tb_txt_center">'.$rs['grade_name'].$rs['class_name'].'</td><td width="4%" class="tb_td tb_txt_center">'.$rs['nums'].'</td>';
		   for($j=0;$j<count($rs['cnums']);$j++){
			    $str_total .= ' <td class="tb_td tb_txt_center" width="8%">'.$rs['cnums'][$j].'</td>';
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
		
	case "work_exp_search": #工作经历 查询
		if($_SESSION['role_id']!=4 && $_SESSION['role_id']!=5){
			$tips = array(
			   'tips' => '抱歉，您没有操作的权限。',
			   'url'  => './?t=admin_index'
			  );
			$tips = urlencode(serialize($tips));
			header('Location:tips.php?gets='.$tips);
			exit;
		}
		
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
		if($_SESSION['role_id']!=4 && $_SESSION['role_id']!=5){
			$tips = array(
			   'tips' => '抱歉，您没有操作的权限。',
			   'url'  => './?t=admin_index'
			  );
			$tips = urlencode(serialize($tips));
			header('Location:tips.php?gets='.$tips);
			exit;
		}
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
			
			$str_total .= '<tr id="reply_'.$rs['id'].'" class="tabtr" onmousemove="$(this).css(\'background-color\',\'#a3c0f9\');" onmouseout="$(this).css(\'background-color\',\'#ffffff\');"><td class="tb_td tb_txt_center" width="5%">'.$i.'</td><td class="tb_td tb_txt_center" width="9%">'.$rs['bname'].'</td><td class="tb_td" >'.$rs['content'].'</td><td class="tb_td tb_txt_center" width="9%">'.$tips.'</td><td class="tb_td tb_txt_center" width="8%">'.$user.'</td><td class="tb_td" width="8%" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;';
			
				$str_total .= ' <a href="javascript:showReply('.$rs['id'].','.$rs['flag_checked'].');" class="glyphicon glyphicon-ok" style=" margin-left:-7px;margin-right:15px;"></a> <a href="javascript:delReply('.$rs['id'].');" class="glyphicon glyphicon-remove"></a> ';
			
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
			$str_show = '<td width="5%" class="tb_td" style="text-align:center;">操作选项</td><td width="5%" class="tb_td" style="text-align:center;">状态</td>';
			$str_check = '<div><select name="op" id="op" ><option value="0">请选择操作</option><option value="1">审核通过</option><option value="2">取消审核</option></select><input type="submit" id="but_op" name="but_op" value="确定" /></div>';
			
			$str_top = '<td width="5%" class="tb_td"><input type="checkbox" name="all_select" id="all_select" value="1" onclick="selectAll(\'id[]\');" /></td>';
			
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
		$submitMethod = $_SERVER["REQUEST_METHOD"];
		$value = '';
        if(!empty($_POST['but_op'])){
			$value=$_POST['but_op'];
		}else if(!empty($_POST['h_but_op'])){
			$value=$_POST['h_but_op'];
		}
		$status_page = empty($_GET['status_page'])?'':$_GET['status_page'];
		$jiyu_page = empty($_GET['jiyu_page'])?'':$_GET['jiyu_page'];
		
		$jiyu = empty($_GET['jiyu'])?'':$_GET['jiyu'];
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
		}else if($_SESSION['role_id']!=6){
			$where = " FIND_IN_SET('{$_SESSION['uid']}',A.geter_id) or A.user_id='{$_SESSION['uid']}'"  ;
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
			
			if($rs['flag_checked'] == 0){
				$tips = '不公开';
			}else if($rs['flag_checked']==1){
				$tips = '全体学生公开';
			}else if($rs['flag_checked']==2){
				$tips = '只对本班公开';
			}else if($rs['flag_checked']==3){
				$tips = '只对这个学生公开';
			}
			
			$str_total = '';
			//if(isset($priv[3])){
			if($_SESSION['role_id']==4 || $_SESSION['role_id']==6){
				if(!empty($jiyu)){
					$str_total .= '<td class="tb_td" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);" width="200">'.$tips.'</td><td class="tb_td" style="text-align:center;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);" width="100">'.$is_check.'</td><td width="100" class="tb_td" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;&nbsp;&nbsp;<a  style="padding-right: .4rem;text-align: center;display: inline-block;"  id="shwtg" href="javascript:void(0);" class="glyphicon glyphicon-remove" title="删除寄语" onclick="delEvent('.$rs['id'].');" style="width:15px;overflow:hidden;margin:0 6px 0 10px ;">&nbsp;</a></td>';
				}else{
					$str_total .= '<td class="tb_td" width="200" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$tips.'</td><td width="100" class="tb_td" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$is_check.'</td><td width="100" class="tb_td" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;&nbsp;&nbsp;<img src="images/037.gif" /> <a style="padding-right: 1rem;text-align: center;display: inline-block;" class="ha_checkup" href="javascript:showReply('.$rs['id'].');">审核</a></td>';
				}
			}else{
				if(!empty($jiyu)){
					$str_total .= '<td class="tb_td" width="200" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$tips.'</td><td width="100" class="tb_td" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$is_check.'</td><td width="100" class="tb_td" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;&nbsp;&nbsp;<a  style="display: inline-block;text-align: center;"  id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="删除寄语" onclick="delEvent('.$rs['id'].');" style="width:15px;overflow:hidden;margin:0 6px 0 10px ;">&nbsp;</a></td>';
				}
			}

			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tb_td" style="text-align:center;" width="50">'.$i.'</td><td class="tb_td" style="display:none;" width="50"><input type="checkbox" name="uid[]" uid="uid_'.$rs['user_id'].'" value="'.$rs['user_id'].'" /></td><td class="tb_td" style="text-align:center;" width="50">'.$rs['bname'].'</td><td width="50" class="tb_td" style="text-align:center;">'.$class_name.'</td><td class="tb_td" style="text-align:center;" width="100">'.$rs['create_time'].'</td><td class="tb_td" style="text-align:center;" width="200">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		/* <a style="padding-right: 1rem;text-align: center;display: inline-block;" id="shtg" href="javascript:void(0);"class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['user_id'].'\');"></a>&nbsp;<a style="display: inline-block;text-align: center;" id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-minus-sign" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\'未通过\');">&nbsp;</a> */
		$str_show = '';
		if($_SESSION['role_id']==4|| $_SESSION['role_id']==5|| $_SESSION['role_id']==6){
			$str_show = '<td width="200" class="tb_td tb_txt_bgc" style="text-align:center;">权限状态</td><td width="100" class="tb_td tb_txt_bgc" style="text-align:center;">审核状态</td><td width="100" class="tb_td tb_txt_bgc" style="text-align:center;">操作选项</td>';
		}else{
			if(!empty($jiyu)){
				$str_show = '<td width="200" class="tb_td tb_txt_bgc" style="text-align:center;">权限状态</td><td width="100" class="tb_td tb_txt_bgc" style="text-align:center;">审核状态</td><td width="100" class="tb_td tb_txt_bgc" style="text-align:center;">操作选项</td>';
			}
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
		
	case "stu_remarks": #友朋寄语
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
		
		$jiyu = empty($_GET['jiyu'])?'':$_GET['jiyu'];
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
			$where = "A.id={$from_id}" ;
		}else if($_SESSION['role_id']!=6){
			$where = " FIND_IN_SET('{$_SESSION['uid']}',A.geter_id) or A.user_id='{$_SESSION['uid']}'"  ;
		}
		/* else{
			$where = " FIND_IN_SET('{$_SESSION['uid']}',A.geter_id) or A.user_id='{$_SESSION['uid']}'"  ;
		} */
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
					$str_total .= '<td class="tb_td" style="text-align:center;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">'.$is_check.'</td><td class="tb_td" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;&nbsp;&nbsp;<a  style="padding-right: .4rem;text-align: center;display: inline-block;"  id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="删除寄语" onclick="delEvent('.$rs['id'].');" style="width:15px;overflow:hidden;margin:0 6px 0 10px ;">&nbsp;</a></td>';
				}else{
					$str_total .= '<td class="tb_td" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$is_check.'</td><td class="tb_td" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;&nbsp;&nbsp;<a style="padding-right: .4rem;text-align: center;display: inline-block;" id="shtg" href="javascript:void(0);"class="glyphicon glyphicon-ok" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['user_id'].'\');"></a>&nbsp;<a style="display: inline-block;text-align: center;" id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['user_id'].'\',\'未通过\');">&nbsp;</a></td>';
				}
			}else{
				if(!empty($jiyu)){
					$str_total .= '<td class="tb_td" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$is_check.'</td><td class="tb_td" align="left" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);">&nbsp;&nbsp;&nbsp;&nbsp;<a  style="display: inline-block;text-align: center;"  id="shwtg" href="javascript:void(0);"class="glyphicon glyphicon-remove" title="删除寄语" onclick="delEvent('.$rs['id'].');" style="width:15px;overflow:hidden;margin:0 6px 0 10px ;">&nbsp;</a></td>';
				}
			}

			$str_event .= '<tr id="exp_'.$rs['id'].'"><td class="tb_td" style="text-align:center;">'.$i.'</td><td class="tb_td" style="display:none;"><input type="checkbox" name="uid[]" uid="uid_'.$rs['user_id'].'" value="'.$rs['user_id'].'" /></td><td class="tb_td" style="text-align:center;">'.$rs['bname'].'</td><td class="tb_td" style="text-align:center;">'.$class_name.'</td><td class="tb_td" style="text-align:center;">'.$rs['create_time'].'</td><td class="tb_td" style="text-align:center;">'.$rs['content'].'</td>'.$str_total.'</tr>';
			$i++;
		}
		
		$str_show = '';
		if($_SESSION['role_id']==4|| $_SESSION['role_id']==6){
			$str_show = '<td width="8%" class="tb_td tb_txt_bgc" style="text-align:center;">状态</td><td width="10%" class="tb_td tb_txt_bgc" style="text-align:center;">操作选项</td>';
		}else{
			if(!empty($jiyu)){
				$str_show = '<td width="6%" class="tb_td tb_txt_bgc" style="text-align:center;">状态</td><td width="8%" class="tb_td tb_txt_bgc" style="text-align:center;">操作选项</td>';
			}
		}
		$rc = count($study);

		$status1 = $status;
		$jiyu1 = $jiyu;
		$status1 = mb_convert_encoding($status1, 'gbk', 'utf-8');
		$jiyu1 = mb_convert_encoding($jiyu, 'gbk', 'utf-8');
		if($p<1)$p=1;
		$page=getPageHtml_bt($rc,20,$p,"&t=remarks&status_page={$status1}&jiyu_page={$jiyu1}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		
		if($_SESSION['role_id']==1){
			$index_back = "index";
			$user_back = "user";
		}elseif($_SESSION['role_id']==4 || $_SESSION['role_id']==5 || $_SESSION['role_id']==6){
			$index_back = "admin_index";
			$user_back = "admin_user";
		}else{
			$index_back = "teacher_index";
			$user_back = "teacher_user";
		}
		$T->Set("index_back",$index_back);
		$T->Set("user_back",$user_back);

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
	    $dept   = isset($_GET['dept'])?Filter::filter_html($_GET['dept']):"";
		$code_id = isset($_GET['code_id'])?Filter::filter_html($_GET['code_id']):"";
		$code_son_id = isset($_GET['code_son_id'])?Filter::filter_html($_GET['code_son_id']):"";
		$where  = "1=1";
		$flag   = isset($_GET['flag'])?Filter::filter_html($_GET['flag']):"";
		$type=isset($_GET["type"])?$_GET["type"]:"";    //判断班主任页面跳转
		$from_id = "";   //消息页面精确跳转定位变量
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		if(!empty($class)){
			$master_val=$class['class_master'];
		}else{
			$master_val='';
		}
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
		$flag_id   = isset($_POST['flag_id'])?Filter::filter_html($_POST['flag_id']):"";
		$report_type = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		
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
		$report = $T->query("SELECT id,code_name,content,is_submit from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			if($rs['is_submit']){
				$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
			}
		}
		//echo $str_report;
		#审核状态
		$str_status = '';
		$status = $T->query("SELECT id,flag_status from  oa_zhsz_report_custom where flag_status='已审核' or flag_status='待审核' or flag_status='未通过' group by flag_status")->fetchAll(PDO::FETCH_ASSOC);
		foreach($status as $rs){
				$str_status .= "<option value='{$rs['id']}'>{$rs['flag_status']}</option>";
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
        if($report_type==""){
			$report_type=isset($_GET['report_type'])?Filter::filter_html($_GET['report_type']):"";
		}
		if($term_id==""){
			$term_id=isset($_GET['term_id'])?Filter::filter_html($_GET['term_id']):"";
		}
		if($rid==""){
			$rid=isset($_GET['rid'])?Filter::filter_html($_GET['rid']):"";
		}
		if($truename==""){
			$truename=isset($_GET['truename'])?Filter::filter_html($_GET['truename']):"";
		}
		
		if($term_id)
			$where  .= " and A.term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and A.subject_id='.$report_type.'';
		
		if($truename)
			$where  .= " and B.truename LIKE'{$truename}%' ";
		
		if($rid)
			$where  .= ' and A.subject_son_id='.$rid.'';
		$xs1='selected';
		$xs2='';
		$xs3='';
		if($flag_id==""){
			$flag_id=isset($_GET['flag_id'])?Filter::filter_html($_GET['flag_id']):"";
		}
		/* if($flag>0){
			if($flag==4){
				$where  .= ' and A.flag_status="待审核"';
			}elseif($flag==5){
				$where  .= ' and A.flag_status="已审核"';
			}elseif($flag==6){
				$where  .= ' and (A.flag_status="已审核" or A.flag_status="待审核")';
			}
		}else{
			if($flag_id==1 || $flag_id==''){
				$xs1='selected';
				$xs2='';
				$xs3='';
			}
			if($flag_id==2){
				$xs2='selected';
				$xs1='';
				$xs3='';
			}
			if($flag_id==3){
				$xs3='selected';
				$xs1='';
				$xs2='';
			}
			$where  .= ' and A.flag_status="待审核"';
			if($flag_id==1 || $flag_id==''){
				$where  .= ' and A.flag_status="待审核"';
			}elseif($flag_id==2){
				$where  .= ' and A.flag_status="已审核"';	
			}elseif($flag_id==3){
				$where  .= ' and A.flag_status="未通过"';
			}elseif($flag_id==8){
				$where  .= ' ';
			}
		} */
		
		$where  .= ' and A.flag_status="待审核"';
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
		$offset = ($p-1)*10;
		if($offset<0) $offset = 0;
		
		if($_SESSION['role_id']!=4&&$_SESSION['role_id']!=5&&$_SESSION['role_id']!=6){
			$where = " A.teacher_id='{$_SESSION['uid']}'";
		}
		
		if($from_id){
			$where = " A.id={$from_id}";
		}

		$str_main = '';
		$main = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_custom as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} and A.subject_son<>'学期计划' order by CONVERT(B.truename USING GBK)  limit {$offset},10")->fetchAll(PDO::FETCH_ASSOC);
		$i = 1;
		foreach($main as $rs){
			$grade = $pd->query("SELECT class_name,(SELECT grade_name from oa_zhsz_grade WHERE id=grade_id) as grade_name from oa_zhsz_class WHERE id={$rs['dept']}")->fetchAll(PDO::FETCH_ASSOC);
			if(empty($grade)){
				$grade[0]['grade_name'] = '';
				$grade[0]['class_name'] = '';
			}
			/* $str_main .= '<tr height="30px" ><td class="tb_td" style="text-align:center;">'.$i++.'</td><td class="tb_td">'.$rs['truename'].'</td><td class="tb_td">'.$grade[0]['grade_name'].$grade[0]['class_name'].'</td><td class="tb_td">'.$rs['subject'].'</td><td class="tb_td">'.$rs['subject_son'].'</td><td class="tb_td"><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="tb_td">'.$rs['flag_status'].'</td><td class="tb_td" text-align="center" style="text-align:center;display: table-cell;vertical-align: middle;text-align: center;height: 100%;transform: translateY(0%);"><a 
			id="shtg" href="javascript:void(0);"
			class="glyphicon glyphicon-ok" style="padding-right: 1rem;text-align: center;display: inline-block;" title="审核通过"  onclick="abc(\''.$rs['id'].'\',\''.$rs['uid'].'\',\''.$rs['subject'].'\');"></a><a style="text-align: center;display: inline-block;"  
			id="shwtg" href="javascript:void(0);"
			class="glyphicon glyphicon-minus-sign" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['uid'].'\',\'未通过\',\''.$rs['subject'].'\');" ></a></td></tr>'; */
			$atts = $pd->query("select * from oa_zhsz_attachment where custom_id='{$rs['id']}'")->fetchAll(PDO::FETCH_ASSOC);  //获取图片和附件
			$att_pic = '无';
			$i=1;
			if($atts!=null){
				$att_pic = '';
				foreach($atts as $att){
					if($att['attachment']!=null){
						$att_pic .= '<br/><img class="layerimg" style="width:80%;" id='.$i++.' src="'.$att['attachment'].'"/>';
						$i++;
					}
				}
			}
			
			if($rs['subject_son_id']){
				$subject_id = $rs['subject_son_id'];
			}else{
				$subject_id = $rs['subject_id'];
			}
			$str_re="";
			if($rs['re_submit']==1){
				$str_re = '<a class="ha_checkup" aid="'.$rs['id'].'" id="apply" href="javascript:void(0);" style="display:inline-block;color:#DD6BFA;" >修改申请</a><br/>';
			}else{
				$str_re = "";
			}
			
			$report_item = $pd->query("select * from oa_zhsz_report_item where report_id='{$subject_id}'")->fetchAll(PDO::FETCH_ASSOC);//自定义内容项
			$str_custom = '';
			$item = '';
			$item_array = array();
			$row = count($report_item)+1;     //获取表格操作栏的行数
			if(strlen($rs['items'])>2){
				$item = $rs['items'];
				$item = substr($item,2,-2);
				$item_array = explode("\",\"",$item);
			}
			foreach($report_item as $rsr){
				$array = array();
				$rsr_value = '';
				foreach($item_array as $ita){
					$array = explode(":",$ita);
					if($array[0] == $rsr['id']){
						$rsr_value = base64_decode($array[1]);
					
						//$rsr_value = mb_convert_encoding($rsr_value, 'utf-8');
					}
				}
				//$str_custom .= '<tr><td width="15%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%" colspan="3" class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td></tr>';
				$str_custom .= '<p>'.$rsr['item_name'].':&nbsp;&nbsp;<span>'.$rsr_value.'</span></p>';
			}
			if($rs['subject_son']){
				$subject = $rs['subject'].'（'.$rs['subject_son'].'）';
			}else{
				$subject = $rs['subject'];
			}
			$str_main .='<li style="border:2px solid #999;margin-top:20px;padding:10px;" id='.'a'.$rs['id'].'>				
				<div class="gro_left">
					<p>姓名：<span>'.$rs['truename'].'</span></p>
					<p>班级：<span>'.$grade[0]['grade_name'].$grade[0]['class_name'].'</span></p>
					<p>类型：<span>'.$subject.'</span></p>
					<p>内容：<span>'.$rs['content'].'</span></p>
					<p>图片：<span>'.$att_pic.'</span></p>
					'.$str_custom.'
				</div>
				<div class="gro_right">
					<span  style="color:red;display:block;text-align:center;width:100%;position:absolute;top:10px;left:0;">'.$rs['flag_status'].'</span>
					<a id="shtg" style="display:block;font-size:1.5rem;width:100%;padding-top:2.5rem;" href="javascript:void(0);" class="glyphicon glyphicon-ok" title="审核通过" onclick="abc(\''.$rs['id'].'\',\''.$rs['uid'].'\',\''.$rs['subject'].'\');"></a>&nbsp;<a id="shwtg" style="display:block;font-size:1.5rem;text-align:center;width:100%;" href="javascript:void(0);" class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['uid'].'\',\'未通过\',\''.$rs['subject'].'\');"></a>
				</div>
			
			</li>';
		}
		
		$total = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_custom as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,10,$p,"&t=report_custom_check&report_type={$report_type}&term_id={$term_id}&flag_id={$flag_id}&flag={$flag}&dept={$dept}&code_id={$code_id}&code_son_id={$code_son_id}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		if($_SESSION['role_id']==4){
			$is_admin = 1;
		}else{
			$is_admin = 0;
		}
			
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5 || $_SESSION['role_id']==6){
			$index_back = "admin_index";
			$user_back = "admin_user";
		}else{
			$index_back = "teacher_index";
			$user_back = "teacher_user";
		}
		
		$T->Set("xs1",$xs1);
		$T->Set("xs2",$xs2);
		$T->Set("xs3",$xs3);
		$T->Set("dept",$dept);
		$T->Set("flag_id",$flag_id);
		$T->Set("code_id",$code_id);
		$T->Set("code_son_id",$code_son_id);
		$T->Set("str_report",$str_report);
		$T->Set("index_back",$index_back);
		$T->Set("user_back",$user_back);
		$T->Set("str_report",$str_report);
		$T->Set("str_term",$str_term);
		$T->Set("str_status",$str_status);
		$T->Set("str_grade",$str_grade);
		$T->Set("str_main",$str_main);
		$T->Set("truename",$truename);
		$T->Set("term_id",$term_id);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("report_type",$report_type);
		$T->Set("rid",$rid);
		$T->Set("is_admin",$is_admin);
		$T->Set("master_val",$master_val);
		$T->Set("from_id",$from_id);
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
			$str_menu .= '<td class="tb_td tb_txt_bgc" width="5%">'.$rsm['course_name'].'</td>';
		}
		//if($_SESSION['role_id']==4)
			//$str_menu .=' <td width="8%" class="tb_td">操作选项</td>';
		
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
					$scores .= '<td class="tb_td" style="text-align:center;" width="5%">'.$skill_score[0]['level'].'</td>';
				}else{
					$scores .= '<td class="tb_td" style="text-align:center;" width="5%">-</td>';
				}
			}
			
			//if($_SESSION['role_id']==4)
				//$scores .= '<td class="tb_td" style="text-align:left; padding-left:10px;"><img src="images/037.gif" /><a href="./?t=skills_modify&uid='.$rs['userid'].'&tid='.$tyear.'">修改</a> <img src="images/010.gif" /> <a href="javascript:delSkill(\''.$rs['userid'].'\','.$tyear.');">删除</a></td>';
			
			$str_total .= '<tr><td class="tb_td" width="5%"  style="text-align:center;">'.$i++.'</td><td class="tb_td" style="text-align:center;" >'.$Term['year'].'('.$Term['term_name'].')</td><td class="tb_td" style="text-align:center;" width="7%">'.$rs['truename'].'</td><td class="tb_td" style="text-align:center;" width="7%">'.$name.'</td>'.$scores.'</tr>';
		}

		//查询所有记录总数
		$sql_total = $pd->query($sql_total)->fetchAll(PDO::FETCH_ASSOC);
		
		$rc = count($sql_total);
		$page=getPageHtml_bt($rc,20,$p,"&t=skills_manage&gid_page={$gid}&cid_page={$cid}&tyear_page={$tyear}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 

		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5 || $_SESSION['role_id']==6){
			$index_back = "admin_index";
			$user_back = "admin_user";
		}else{
			$index_back = "teacher_index";
			$user_back = "teacher_user";
		}
		$T->Set("index_back",$index_back);
		$T->Set("user_back",$user_back);
			
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
		
	case "operation_skill": #学生技能素质
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));

		$skills  = $pd->query("select * from oa_zhsz_course where is_score=0 order by id DESC")->fetchAll(PDO::FETCH_ASSOC);  //菜单
		$str_menu = '';
		foreach($skills as $rsm){
			$str_menu .= '<td class="tb_td tb_txt_bgc">'.$rsm['course_name'].'</td>';
		}
		
		$terms  = $pd->query("select * from oa_zhsz_term  order by year desc,term_type desc")->fetchAll(PDO::FETCH_ASSOC);
		$str_total = '';
		foreach($terms as $rs){
			$scores = '';
			$num = 0;
			foreach($skills as $rss){
				$skill_score  = $pd->query("select * from oa_zhsz_course_level where user_id='{$uid}' and term_id ={$rs['id']} and course_id={$rss['id']}")->fetchAll(PDO::FETCH_ASSOC);
				
				if(!empty($skill_score)){
					$scores .= '<td class="tb_td" style="text-align: center;">'.$skill_score[0]['level'].'</td>';
				}else{
					$scores .= '<td class="tb_td" style="text-align: center;">-</td>';
					$num++;
				}
			}
			
			//$scores .= '<td class="tb_td" style="text-align:center;padding-left:10px;"><img src="images/see.png" style="text-align:left;width:18px;height:12px;">&nbsp;<a href="./?t=operation_skill_detail&tid='.$rs['id'].'">查看</a></td>';
			
			if($num != count($skills))    //把一项成绩都没有的学期过滤掉
				$str_total .= '<tr><td class="tb_td" style="text-align: center;">'.$rs['year'].'('.$rs['term_name'].')</td><td class="tb_td" style="text-align: center;">'.$user['truename'].'</td>'.$scores.'</tr>';
		}

		$T->Set("str_total",$str_total);
		$T->Set("str_menu",$str_menu);
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
				
				$str_total .= '<tr><td class="tb_td">'.$getTerm['year'].'('.$getTerm['term_name'].')</td><td class="tb_td">'.$rs['truename'].'</td>';
				if($sm){
					foreach($stamina as $rss){
						$is_ok = 0;
						foreach($sm as $rsb){
							if($rss['id']==$rsb['item_id']){
								$str_total .= "<td class='tb_td'>{$rsb['result']}</td>";	
								$is_ok = 1;
							}
						}
						if(!$is_ok){
							$str_total .= "<td class='tb_td'>-</td>";	
						}
					}
				}
				if(empty($sm)){
					for($i=0;$i<$len;$i++){
						$str_total .= "<td class='tb_td'>-</td>";	
					}
					$str_total .= '<td class="tb_td" style="text-align:left; padding-left:10px; color:#333;">&nbsp;暂无数据</td>';
				}
				//$str_total .= '<td class="tb_td" style="text-align:center;padding-left:10px;"><img src="images/see.png" style="text-align:left;width:18px;height:12px;"/>&nbsp;<a href="./?t=stamina_stu_modify&uid='.$rs['userid'].'&tid='.$rst['term_id'].'">查看</a></td>';
				
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
			$str.='<li  '.$selected.'><a href="./?t=zhsz_score&term_id='.$rs['id'].'">'.$rs['term'].'</a></li>';
			//$str.='<label '.$selected.'><a href="./?t=zhsz_score&term_id='.$rs['id'].'">'.$rs['term'].'</a></label>';
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
			$str_cj = $rs['yw']==-1?'-':$rs['yw'].'/';
			$str_xf = $rs['yw_xf']==-1?'-':$rs['yw_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str1 = $str_cj.$str_xf;
			$str_cj = $rs['sx']==-1?'-':$rs['sx'].'/';
			$str_xf = $rs['sx_xf']==-1?'-':$rs['sx_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str2 = $str_cj.$str_xf;
			$str_cj = $rs['wy']==-1?'-':$rs['wy'].'/';
			$str_xf = $rs['wy_xf']==-1?'-':$rs['wy_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str3 = $str_cj.$str_xf;
			$str_cj = $rs['wl']==-1?'-':$rs['wl'].'/';
			$str_xf = $rs['wl_xf']==-1?'-':$rs['wl_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str4 = $str_cj.$str_xf;
			$str_cj = $rs['hx']==-1?'-':$rs['hx'].'/';
			$str_xf = $rs['hx_xf']==-1?'-':$rs['hx_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str5 = $str_cj.$str_xf;
			$str_cj = $rs['sw']==-1?'-':$rs['sw'].'/';
			$str_xf = $rs['sw_xf']==-1?'-':$rs['sw_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str6 = $str_cj.$str_xf;
			$str_cj = $rs['zz']==-1?'-':$rs['zz'].'/';
			$str_xf = $rs['zz_xf']==-1?'-':$rs['zz_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str7 = $str_cj.$str_xf;
			$str_cj = $rs['ls']==-1?'-':$rs['ls'].'/';
			$str_xf = $rs['ls_xf']==-1?'-':$rs['ls_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str8 = $str_cj.$str_xf;
			$str_cj = $rs['dl']==-1?'-':$rs['dl'].'/';
			$str_xf = $rs['dl_xf']==-1?'-':$rs['dl_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str9 = $str_cj.$str_xf;
			$str_cj = $rs['szf']==-1?'-':$rs['szf'].'/';
			$str_xf = $rs['sxf']==-1?'-':$rs['sxf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str10 = $str_cj.$str_xf;
			$str_cj = $rs['xxjs']==-1?'-':$rs['xxjs'].'/';
			$str_xf = $rs['xxjs_xf']==-1?'-':$rs['xxjs_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str11 = $str_cj.$str_xf;
			$str_cj = $rs['tyjs']==-1?'-':$rs['tyjs'].'/';
			$str_xf = $rs['tyjs_xf']==-1?'-':$rs['tyjs_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str12 = $str_cj.$str_xf;
			$str_cj = $rs['ty']==-1?'-':$rs['ty'].'/';
			$str_xf = $rs['ty']==-1?'-':$rs['ty'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str13 = $str_cj.$str_xf;
			$str_cj = $rs['xx']==-1?'-':$rs['xx'].'/';
			$str_xf = $rs['xx_xf']==-1?'-':$rs['xx_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str14 = $str_cj.$str_xf;
			$str_cj = $rs['yy']==-1?'-':$rs['yy'].'/';
			$str_xf = $rs['yy_xf']==-1?'-':$rs['yy_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str15 = $str_cj.$str_xf;
			$str_cj = $rs['ms']==-1?'-':$rs['ms'].'/';
			$str_xf = $rs['ms_xf']==-1?'-':$rs['ms_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str16 = $str_cj.$str_xf;
			$str_cj = $rs['xl']==-1?'-':$rs['xl'].'/';
			$str_xf = $rs['xl_xf']==-1?'-':$rs['xl_xf'];
			$str_xf = "<span style=\"color:#ff0000;\">".$str_xf."</span>";
			$str17 = $str_cj.$str_xf;

			$str_total .= '<table width="100%" border="0" class="tb_tp" cellpadding="0" cellspacing="0" style="margin-bottom:15px;"><tr><td colspan="10" class="tb_td">'.$rs['exam_type'].'考试成绩</td></tr><tr class="tb_td"><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">语文</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">数学</td><td class="tb_td" style="width:80px;font-weight:bold; text-align:center;">外语</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">物理</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">化学</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">生物</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">政治</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">历史</td><td class="tb_td" style="width:56px;font-weight:bold; text-align:center;">地理</td><td class="tb_td" style="text-align:center;font-weight:bold;">总分</td></tr><tr><td class="tb_td" style="text-align:center;">'.$str1.'</td><td class="tb_td" style="text-align:center;">'.$str2.'</td><td class="tb_td" style="text-align:center;">'.$str3.'</td><td class="tb_td" style="text-align:center;">'.$str4.'</td><td class="tb_td" style="text-align:center;">'.$str5.'</td><td class="tb_td" style="text-align:center;">'.$str6.'</td><td class="tb_td" style="text-align:center;">'.$str7.'</td><td class="tb_td" style="text-align:center;">'.$str8.'</td><td class="tb_td" style="text-align:center;">'.$str9.'</td><td class="tb_td" style="text-align:center;">'.$str10.'</td></tr><tr><td class="tb_td" style="text-align:center; font-weight:bold;" colspan="2" rowspan="2">其它学科</td><td class="tb_td" style="text-align:center;font-weight:bold;">信息技术</td><td class="tb_td" style="text-align:center;">'.$str11.'</td><td class="tb_td" style="text-align:center;font-weight:bold;">通用技术</td><td class="tb_td" style="text-align:center;">'.$str12.'</td><td class="tb_td" style="text-align:center;font-weight:bold;">体育与健康</td><td class="tb_td" style="text-align:center;">'.$str13.'</td><td class="tb_td" style="text-align:center;font-weight:bold;">选修II</td><td class="tb_td" style="text-align:center;">'.$str14.'</td></tr><tr><td class="tb_td" style="text-align:center;font-weight:bold;">音乐</td><td class="tb_td" style="text-align:center;">'.$str15.'</td><td class="tb_td" style="text-align:center;font-weight:bold;">美术</td><td class="tb_td" style="text-align:center;">'.$str16.'</td><td class="tb_td" style="text-align:center;font-weight:bold;">心理</td><td class="tb_td" style="text-align:center;">'.$str17.'</td><td class="tb_td" style="text-align:center;font-weight:bold;">&nbsp;</td><td class="tb_td" style="text-align:center;">&nbsp;</td></tr></table>';
		}

		$T->Set("str_total",$str_total);
		$T->Set("str",$str);
		$T->Set("tips",$tips);
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
			$term_name = '<select name="term_id" id="term_id" class="weui-select"></select> ';
			$flag_type = '<select name="flag_type" id="flag_type" class="weui-select"></select> ';
		}else{
			$term_name = $info['term_name'];
			$flag_type = $info['flag_type'];
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
		$T->Set("flag_type",$flag_type);
		$T->Set("id",$info['id']);
		$T->Set("term_name",$term_name);
		$T->Set("content",$info['content']);
		$T->Set("flag_status",$info['flag_status']);
		$T->Set("butV",$butV);
		$T->Set("role",$role);
		$T->Set("master_val",$master_val);
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
			//if($curTerm['id'] == $rs['term_id'] && ($rs['flag_status']=="待审核"||$rs['flag_status']=="未通过")){
				//$if_edit = '&nbsp;&nbsp;  <a aid="'.$rs['id'].'" id="caozuo" href="./?t=report_custom_edit&id='.$rs['id'].'" class="glyphicon glyphicon-pencil">修改</a>';
			//}
			
			$grade = '';
			foreach($term as $rs1){    //判断年级
				if($rs['term_id'] == $rs1['id'])
					$grade = $rs1['term'];
			}
			
			//$str_main .= '<tr class="ms_info"><td class="tb_td">'.$rs['subject'].'</td><td class="tb_td">'.$rs['subject_son'].'</td><td class="tb_td">'.$grade.'</td><td class="tb_td" ><div class="ha_tacontent">'.$rs['content'].'</div></td><td class="tb_td" align="left" style="text-align:center;vertical-align: middle;height: 100%;transform: translateY(0%);">'.$if_edit.'</td></tr>';
			$str_main .= '<tr class="ms_info"><td class="tb_td" width="100">'.$rs['subject'].'</td><td class="tb_td" width="100">'.$rs['subject_son'].'</td><td class="tb_td" width="100">'.$grade.'</td><td class="tb_td" ><div class="ha_tacontent" width="500"><pre style="white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap;white-space: -o-pre-wrap;word-wrap: break-word;width:100%;background-color:white;border:none;text-align:left;font-size:14px;">'.$rs['content'].'</pre></div></td></tr>';
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
			$str_content .= '<div class="ha_content ha_stext _ha_content s1_tips"><label style="width:15%;">说&nbsp;明：</label><div class="ha_cont" style="width:85%;float:left;"><pre>'.$rs['content'].'</pre></div></div>';
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
		
	case "accept_message": #选择寄语接收人
		$cur_dept = $T->db->query("select dept from v_users where id='{$_SESSION['uid']}' and role_id=1 ")->fetchColumn(0);
		$gid_page=isset($_GET["gid_page"])?$_GET["gid_page"]:0;
		$cid_page=isset($_GET["cid_page"])?$_GET["cid_page"]:0;
        
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		
		if($gid_page)
			$gid = $gid_page;
		if($cid_page)
			$cid = $cid_page;
		if($cid==0 && empty($truename)){
			$cid = $cur_dept;
		}
		if($cid==0 && $cur_dept==0){
			$cid = $T->db->query('select dept from v_users order by dept limit 1')->fetchColumn(0);
		}
		//$where = 'school='.$_SESSION['school'];
		$where = ' dept='.$cid.' ';
		if(!empty($truename)){
			$where .= "  and truename LIKE'{$truename}%' ";
		}
		if($cid==0 && $cur_dept==0 && empty($truename)){
			$T->SetBlock("list","select id,truename from v_users where role_id=1 limit 60");
		}else{
			$T->SetBlock("list","select id,truename,dept from v_users where {$where}");
		}
		$T->Set("truename",$truename);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		break;
		
	case "report_custom_select":  //成长经历记录查询
	    $dept   = isset($_GET['dept'])?Filter::filter_html($_GET['dept']):"";
		$code_id = isset($_GET['code_id'])?Filter::filter_html($_GET['code_id']):"";
		$code_son_id = isset($_GET['code_son_id'])?Filter::filter_html($_GET['code_son_id']):"";
		$where  = "1=1";
		$flag   = isset($_GET['flag'])?Filter::filter_html($_GET['flag']):"";
		$type=isset($_GET["type"])?$_GET["type"]:"";    //判断班主任页面跳转
		$from_id = "";   //消息页面精确跳转定位变量
		/* $class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		if(!empty($class)){
			$master_val=$class['class_master'];
		}else{
			$master_val='';
		} */
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
		$flag_id   = isset($_POST['flag_id'])?Filter::filter_html($_POST['flag_id']):"";
		$report_type = isset($_POST['report_type'])?Filter::filter_html($_POST['report_type']):"";
		
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
		$report = $T->query("SELECT id,code_name,content,is_submit from  oa_zhsz_report where parent_no='' and school={$_SESSION['school']} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		foreach($report as $rs){
			$str_report .= "<option value='{$rs['id']}'>{$rs['code_name']}</option>";
		}
		//echo $str_report;
		#审核状态
		$str_status = '';
		$status = $T->query("SELECT id,flag_status from  oa_zhsz_report_custom where flag_status='已审核' or flag_status='待审核' or flag_status='未通过' group by flag_status")->fetchAll(PDO::FETCH_ASSOC);
		foreach($status as $rs){
				$str_status .= "<option value='{$rs['id']}'>{$rs['flag_status']}</option>";
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
        if($report_type==""){
			$report_type=isset($_GET['report_type'])?Filter::filter_html($_GET['report_type']):"";
		}
		if($term_id==""){
			$term_id=isset($_GET['term_id'])?Filter::filter_html($_GET['term_id']):"";
		}
		if($rid==""){
			$rid=isset($_GET['rid'])?Filter::filter_html($_GET['rid']):"";
		}
		if($truename==""){
			$truename=isset($_GET['truename'])?Filter::filter_html($_GET['truename']):"";
		}
		
		if($term_id)
			$where  .= " and A.term_id='{$term_id}'";
		if($report_type)
			$where  .= ' and A.subject_id='.$report_type.'';
		
		if($truename)
			$where  .= " and B.truename LIKE'{$truename}%' ";
		
		if($rid)
			$where  .= ' and A.subject_son_id='.$rid.'';
		$xs1='';
		$xs2='';
		$xs3='';
		
		if($flag_id==""){
			$flag_id=isset($_GET['flag_id'])?Filter::filter_html($_GET['flag_id']):"";
		}
		if($flag_id==1){
			$xs1='selected';
			$xs2='';
			$xs3='';
		}
		if($flag_id==2 || $flag_id==''){
			$xs2='selected';
			$xs1='';
			$xs3='';
		}
		if($flag_id==3){
			$xs3='selected';
			$xs1='';
			$xs2='';
		}
		if($flag_id==1){
			$where  .= ' and A.flag_status="待审核"';
		}else if($flag_id==2 || $flag_id==''){
			$where  .= ' and A.flag_status="已审核"';	
		}else if($flag_id==3){
			$where  .= ' and A.flag_status="未通过"';
		}else{
			$where  .= ' ';
		}
		
		if($dept)
			$where  .= ' and C.dept='.$dept.'';
		if($code_id)
			$where  .= ' and A.subject_id='.$code_id.'';
		if($code_son_id)
			$where  .= ' and A.subject_son_id='.$code_son_id.'';
		if($flag==4)
			$where  .= ' and A.flag_status="待审核"';
		if($flag==5)
			$where  .= ' and A.flag_status="已审核"';
		
		$code_id_page=isset($_GET["code_id_page"])?$_GET["code_id_page"]:0;
		$son_id_page=isset($_GET["son_id_page"])?$_GET["son_id_page"]:0;
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
		$offset = ($p-1)*10;
		if($offset<0) $offset = 0;
		
		if($_SESSION['role_id']!=4&&$_SESSION['role_id']!=5&&$_SESSION['role_id']!=6){
			$where = " A.teacher_id='{$_SESSION['uid']}'";
		}
		
		if($from_id){
			$where = " A.id={$from_id}";
		}

		$str_main = '';
		$main = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_custom as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by CONVERT(B.truename USING GBK)  limit {$offset},10")->fetchAll(PDO::FETCH_ASSOC);
		foreach($main as $rs){
			$grade = $pd->query("SELECT class_name,(SELECT grade_name from oa_zhsz_grade WHERE id=grade_id) as grade_name from oa_zhsz_class WHERE id={$rs['dept']}")->fetchAll(PDO::FETCH_ASSOC);
			if(empty($grade)){
				$grade[0]['grade_name'] = '';
				$grade[0]['class_name'] = '';
			}
			$str_main .= '<li style="border:2px solid #999;margin-top:20px;padding:10px;">				
				<div class="gro_left">
					<p>姓名：<span>'.$rs['truename'].'</span></p>
					<p>班级：<span>'.$grade[0]['grade_name'].$grade[0]['class_name'].'</span></p>
					<p>类型：<span>'.$rs['subject'].'</span>-<span>'.$rs['subject_son'].'</span></p>
					<p>内容：<span>'.$rs['content'].'</span></p>
				</div>
				<div class="gro_right">
					<span style="color:red;display:block;text-align:center;width:100%;position:absolute;top:10px;left:0;">'.$rs['flag_status'].'</span>
					<a aid="'.$rs['id'].'" href="./?t=report_custom_check_detail&id='.$rs['id'].'" class="glyphicon glyphicon-zoom-in" style="display:block;font-size:1.5rem;width:100%;padding-top:2.5rem;" target="_blank" class="caozuo" title="查看详情"></a>&nbsp;
				</div>
			
			</li>';
			/* $atts = $pd->query("select * from oa_zhsz_attachment where custom_id='{$rs['id']}'")->fetchAll(PDO::FETCH_ASSOC);  //获取图片和附件
			$att_pic = '无';
			$i=1;
			if($atts!=null){
				$att_pic = '';
				foreach($atts as $att){
					if($att['attachment']!=null){
						$att_pic .= '<br/><img class="layerimg" style="width:80%;" id='.$i++.' src="'.$att['attachment'].'"/>';
						$i++;
					}
				}
			}
			
			if($rs['subject_son_id']){
				$subject_id = $rs['subject_son_id'];
			}else{
				$subject_id = $rs['subject_id'];
			} */
			
			/* $report_item = $pd->query("select * from oa_zhsz_report_item where report_id='{$subject_id}'")->fetchAll(PDO::FETCH_ASSOC);//自定义内容项
			$str_custom = '';
			$item = '';
			$item_array = array();
			$row = count($report_item)+1;     //获取表格操作栏的行数
			if(strlen($rs['items'])>2){
				$item = $rs['items'];
				$item = substr($item,2,-2);
				$item_array = explode("\",\"",$item);
			}
			foreach($report_item as $rsr){
				$array = array();
				$rsr_value = '';
				foreach($item_array as $ita){
					$array = explode(":",$ita);
					if($array[0] == $rsr['id']){
						$rsr_value = base64_decode($array[1]);
					
						//$rsr_value = mb_convert_encoding($rsr_value, 'utf-8');
					}
				}
				//$str_custom .= '<tr><td width="15%" class="tabtd2_L" style="text-align:center;font-weight:bold;">'.$rsr['item_name'].'</td><td width="30%" colspan="3" class="tabtd2_L" style="text-align:center;">'.$rsr_value.'</td></tr>';
				$str_custom .= '<p>'.$rsr['item_name'].':&nbsp;&nbsp;<span>'.$rsr_value.'</span></p>';
			}
			if($rs['subject_son']){
				$subject = $rs['subject'].'（'.$rs['subject_son'].'）';
			}else{
				$subject = $rs['subject'];
			}
			$str_main .='<li style="border:2px solid #999;margin-top:20px;padding:10px;">				
				<div class="gro_left">
					<p>姓名：<span>'.$rs['truename'].'</span></p>
					<p>班级：<span>'.$grade[0]['grade_name'].$grade[0]['class_name'].'</span></p>
					<p>类型：<span>'.$subject.'</span></p>
					<p>内容：<span>'.$rs['content'].'</span></p>
					<p>图片：<span>'.$att_pic.'</span></p>
					'.$str_custom.'
				</div>
				<div class="gro_right">
					<span style="color:red;display:block;text-align:center;width:100%;position:absolute;top:10px;left:0;">'.$rs['flag_status'].'</span>
					<a id="shtg" style="display:block;font-size:1.5rem;width:100%;padding-top:2.5rem;" href="javascript:void(0);" class="glyphicon glyphicon-ok" title="审核通过" onclick="abc(\''.$rs['id'].'\',\''.$rs['uid'].'\',\''.$rs['subject'].'\');"></a>&nbsp;<a id="shwtg" style="display:block;font-size:1.5rem;text-align:center;width:100%;" href="javascript:void(0);" class="glyphicon glyphicon-remove" title="审核未通过" onclick="shwtg(\''.$rs['id'].'\',\''.$rs['uid'].'\',\'未通过\',\''.$rs['subject'].'\');"></a>
				</div>
			
			</li>';*/
		} 
		
		$total = $pd->query("select A.* ,B.truename,C.dept from oa_zhsz_report_custom as A inner join act_member as B on(A.uid=B.id) inner join oa_user_extend as C on(B.id=C.userid) where {$where} order by create_time desc")->fetchAll(PDO::FETCH_ASSOC);
		$rc = count($total);

		$page=getPageHtml_bt($rc,10,$p,"&t=report_custom_check&report_type={$report_type}&term_id={$term_id}&flag_id={$flag_id}&flag={$flag}&dept={$dept}&code_id={$code_id}&code_son_id={$code_son_id}");
		$page = mb_convert_encoding($page, 'utf-8', 'gbk');
		$T->Set("page",$page); 
		$T->Set("total",$rc); 
		
		if($_SESSION['role_id']==4){
			$is_admin = 1;
		}else{
			$is_admin = 0;
		}
			
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==5 || $_SESSION['role_id']==6){
			$index_back = "admin_index";
			$user_back = "admin_user";
		}else{
			$index_back = "teacher_index";
			$user_back = "teacher_user";
		}
		
		$T->Set("xs1",$xs1);
		$T->Set("xs2",$xs2);
		$T->Set("xs3",$xs3);
		$T->Set("dept",$dept);
		$T->Set("flag_id",$flag_id);
		$T->Set("code_id",$code_id);
		$T->Set("code_son_id",$code_son_id);
		$T->Set("index_back",$index_back);
		$T->Set("user_back",$user_back);
		$T->Set("str_report",$str_report);
		$T->Set("str_term",$str_term);
		$T->Set("str_status",$str_status);
		$T->Set("str_grade",$str_grade);
		$T->Set("str_main",$str_main);
		$T->Set("truename",$truename);
		$T->Set("term_id",$term_id);
		$T->Set("gid",$gid);
		$T->Set("cid",$cid);
		$T->Set("report_type",$report_type);
		$T->Set("rid",$rid);
		$T->Set("is_admin",$is_admin);
		$T->Set("from_id",$from_id);
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
			$str_pic .= "<div style='width:100px;float:left;margin-right:20px;text-align:center;' class=\"ha_eimglist\"><input type='hidden' id='PicUrl{$i}' name='PicUrl[]' value=''><input type='hidden' id='PicName{$i}' name='PicName[]' value=''><img id='preview{$i}' style='width:100px;height:100px;margin-top:20px;' src='{$rs['attachment']}'/></div>";
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
}

#释放资源
$T->clearNoN();
$T->clearNaN();
$T->display();
$T->close();	
unset($T);
?>