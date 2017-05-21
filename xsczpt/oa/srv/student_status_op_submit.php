<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

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
	$master_name  = Filter::filter_html($_POST['master_name']);
	$status_name   = Filter::filter_html($_POST['status_name']);
	$school   = Filter::filter_html($_POST['school']);
	$Gname       = Filter::filter_html($_POST['Gname']);
	$Cname       = Filter::filter_html($_POST['Cname']);
	$content       = Filter::filter_html($_POST['content']);
	$id         = Filter::filter_number($_POST['id']);
    
	/* $yeare      = $year + 3;
	$classStart = substr($year,2).'级';
	$classEnd   = substr($yeare,2).'届'; */
	
	#修改后的班级cid
	$Gid = $pd->fetchOne(array('field'=>'id','table'=>'oa_zhsz_grade','where'=>"grade_name='".$Gname."'"));
	$Cid = $pd->fetchOne(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"class_name='".$Cname."' and grade_id=".$Gid.""));
	
	$data = array(
		'master_name'   => $master_name,
		'status_name' => $status_name,
		'gid'   => $Gid,
		'cid'   => $Cid,
		'dept_name' => $Gname.$Cname,
 		'content'   => $content,
		'school'     => $school
	);
	//exit;
	$where = "id={$id}";
	$params     = array('data'=>$data,'table'=>'oa_student_status','where'=>$where);
	$result     = $pd->update($params);
	if(empty($result)){
		//数据修改错误
		$tips = array(
		   'tips' => '学籍修改失败，请稍后再试。',
		   'url'  => './?t=student_status'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '学籍修改成功。',
		   'url'  => './?t=student_status'
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