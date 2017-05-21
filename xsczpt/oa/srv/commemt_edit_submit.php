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
	$content     = Filter::filter_html($_POST['content_edit']);
	$uid     = Filter::filter_html($_POST['uid_edit']);
	$c_id     = Filter::filter_number($_POST['c_id']);

	$dept = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$uid}'"));
	
	$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
	
	if($_SESSION['role_id']!=4){
		if(empty($class)){
			$tips = array(
				   'tips' => '您不是班主任，没有修改评语的权限',
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
	
	$data = array(
		'content'   => $content
	);
	
	$where = "id={$c_id}";
	$params = array('data'=>$data,'table'=>'oa_zhsz_teacher_comment','where'=>$where);
	$result     = $pd->update($params);
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '评语修改失败，请稍后再试。',
			   'url'  => './?t=master_comment_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '评语修改成功。',
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