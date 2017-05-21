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
session_start();
if(empty($_SESSION['uid'])){
	$tips = array(
	   'tips' => '请登录后再进行操作。',
	   'url'  => 'index.php'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}

$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
	$content     = Filter::filter_html($_POST['content']);
	$uid     = Filter::filter_html($_POST['user_id']);
	$curTerm = $pd->fetchRow(array('field'=>array('id'),'table'=>'oa_zhsz_term','where'=>'flag_default=1')); 
	$dept = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$uid}'"));
	
	if($dept==0){
		$tips = array(
		   'tips' => '该学生已毕业',
		   'url'  => './?t=master_comment_manage'
		  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
			
	$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
	
	if($_SESSION['role_id']!=4){
		if(empty($class)){
			$tips = array(
				   'tips' => '您不是班主任，没有添加评语的权限',
				   'url'  => './?t=master_comment_manage'
				  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}else{
			if($class['id']!=$dept){
				$tips = array(
				   'tips' => '请选择本班学生',
				   'url'  => './?t=master_comment_manage'
				  );
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
			}
		}
	}
	
	$conent_name = $pd->query("select * from oa_zhsz_teacher_comment WHERE user_id='{$uid}' and term_id={$curTerm['id']}")->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($conent_name)){
		$tips = array(
			   'tips' => '该学生当前学期已有评语',
			   'url'  => './?t=master_comment_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}

	
	$class_name = $pd->query("select A.id,A.class_name,A.class_master,A.grade_id,B.grade_name from oa_zhsz_class as A inner join oa_zhsz_grade as B ON A.grade_id=B.id WHERE A.id={$dept}")->fetchAll(PDO::FETCH_ASSOC);
	
	$master_name =$pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='".$class_name[0]['class_master']."'"));
	
	$data = array(
		'content'   => $content,
		'user_id'   => $uid,
		'master_id' => $class_name[0]['class_master'],
		'master_name' => $master_name,
		'grade_id'     => $class_name[0]['grade_id'],
		'class_id'     => $class_name[0]['id'],
		'class_name'     =>  $class_name[0]['grade_name'].$class_name[0]['class_name'],
		'term_id'  => $curTerm['id'],
		'flag_status' => "已审核",
		'create_time'  => date('Y-m-d H:i:s')
	);
	
	$params = array('data'=>$data,'table'=>'oa_zhsz_teacher_comment');
	$result = $pd->insert($params);
	
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '评语添加失败，请稍后再试。',
			   'url'  => './?t=master_comment_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '评语添加成功。',
		   'url'  => './?t=master_comment_manage'
		);
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