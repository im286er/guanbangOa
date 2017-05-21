<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
date_default_timezone_set("PRC");
$pd = new pdo_mysql();
$filter = new Filter();
//判断用户是否登录
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid']; 
if(empty($_SESSION['username'])){
	$tips = array(
	   'tips' => '请登录后再进行操作。',
	   'url'  => 'index.php'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}

$submitMethod = $_SERVER["REQUEST_METHOD"];
$next=0;
//判断提交方式
if($submitMethod=='POST'){
	$userId   = empty($_POST['user_id'])?'':Filter::filter_html($_POST['user_id']);
	$flagType = empty($_POST['flag_type'])?'':Filter::filter_html($_POST['flag_type']);
	$termId   =  empty($_POST['term_id'])?'':Filter::filter_number($_POST['term_id']);
	$content  =  empty($_POST['content'])?'':Filter::filter_html($_POST['content']);
	$id = empty($_POST['id'])?0:Filter::filter_number($_POST['id']);
	
	$op	= $_POST['op'];
	$table = 'oa_zhsz_reward_punishment';
	
	if($_SESSION['role_id']!=1){
		$status = "已审核";
	}else{
		$status = "待审核";
		$userId = $_SESSION['uid'];
	}
	if($op=='add'&&$_SESSION['role_id']==1){
		$user   = $pd->fetchRow(array('field'=>array('truename','dept','cardno'),'table'=>'v_users','where'=>"id='{$userId}'"));
		
		if(empty($user['dept'])){
			$tips = array(
			   'tips' => '该学生没有班级信息，请重新输入',
			   'url'  => './?t=stu_jiangcheng'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));

		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$classD['grade_id']}"));

		$data = array(
			'user_id'     => $userId,
			'truename'    => $user['truename'],
			'term_id'     => $termId,
			'user_card'    => $user['cardno'],
			'flag_type'   => $flagType,
			'grade_id'    => $classD['grade_id'],
			'class_id'    => $user['dept'],
			'class_name'  => $gradeName.$classD['class_name'],
			'content'	  => $content,
			'flag_status'	  => $status,
			'create_id'	  => $_SESSION['uid'],
			'create_time' => date('Y-m-d H:i:s'),
			'create_by'   => $_SESSION['username']
		);
		$params     = array('data'=>$data,'table'=>$table);
		$result     = $pd->insert($params);
	}elseif($op=='add'&&$_SESSION['role_id']!=1){
		$user   = $pd->fetchRow(array('field'=>array('truename','dept','cardno'),'table'=>'v_users','where'=>"id='{$userId}'"));
		if(empty($user['dept'])){
			$tips = array(
			   'tips' => '该学生没有班级信息，请重新输入',
			   'url'  => './?t=jiangcheng'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));

		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$classD['grade_id']}"));

		$data = array(
			'user_id'     => $userId,
			'truename'    => $user['truename'],
			'term_id'     => $termId,
			'user_card'    => $user['cardno'],
			'flag_type'   => $flagType,
			'grade_id'    => $classD['grade_id'],
			'class_id'    => $user['dept'],
			'class_name'  => $gradeName.$classD['class_name'],
			'content'	  => $content,
			'flag_status'	  => $status,
			'create_id'	  => $_SESSION['uid'],
			'create_time' => date('Y-m-d H:i:s'),
			'create_by'   => $_SESSION['username']
		);
		$params     = array('data'=>$data,'table'=>$table);
		$result     = $pd->insert($params);
		$bid = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_reward_punishment'));
		$rs=$pd->query("insert into oa_message(uid,geter,leaveword,stime,is_read,flag,menu2,subject,from_id) values('".$uid."','".$data['user_id']."','".$content."','".date('Y-m-d H:i:s',time())."',0,'已审核','68','".$flagType."',".$bid.") ");
	}
	if($op=='edit'){
		$next=1;
		$data = array('content'=>$content);
		
		$where = "id={$id}";
		$params     = array('data'=>$data,'table'=>$table,'where'=>$where);
		$result     = $pd->update($params);
		$flag_old =$pd->db->query("select flag_status from oa_zhsz_reward_punishment where id=".$id)->fetchColumn(0);

		if($flag_old=='未通过'){
			$rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,subject,from_id) values('".$uid."','".$_POST["geter"]."',(select content from oa_zhsz_reward_punishment where id=".$id."),'".date('Y-m-d H:i:s',time())."',0,'待审核','55',(select flag_type from oa_zhsz_reward_punishment where id=".$id."),{$id})");
			
			//添加
			$data = array(
				'flag_status'     => "待审核"
			);
			$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_reward_punishment','where'=>"id={$id}"));
		}
	}
	if(empty($result)){
		//数据插入错误
		if($_SESSION['role_id']==1){
			$tips = array(
			   'tips' => '操作失败，请稍后再试。',
			   'url'  => './?t=stu_jiangcheng'
			  );
		}else{
			$tips = array(
			   'tips' => '操作失败，请稍后再试。',
			   'url'  => './?t=jiangcheng'
			  );
		}
		
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		if($_SESSION['role_id']==1){
			$tips = array(
			   'tips' => '操作成功。',
			   'url'  => './?t=stu_jiangcheng'
			  );
		}else{
			$tips = array(
			   'tips' => '操作成功。',
			   'url'  => './?t=jiangcheng'
			  );
		}
		
		if($next!=1){
		   $biye_id = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_reward_punishment'));
		   $rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,subject,from_id) values('".$uid."','".$_POST["geter"]."',(select content from oa_zhsz_reward_punishment where id=".$biye_id."),'".date('Y-m-d H:i:s',time())."',0,'".$status."','55',(select flag_type from oa_zhsz_reward_punishment where id=".$biye_id."),{$biye_id})");	
		}
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	//提交方式出错
	$tips = array(
	   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
	   'url'  => 'index.php'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>