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
	$user_name = Filter::filter_html($_POST['truename']);
	$user_id = Filter::filter_html($_POST['user_id']);
	$gid = Filter::filter_number($_POST['gid']);
	$cid = Filter::filter_number($_POST['cid']);
	$term_id = Filter::filter_number($_POST['term_id']);
	$status = Filter::filter_number($_POST['status']);
	$term_name = Filter::filter_html($_POST['term_name']);
	$status_name = Filter::filter_html($_POST['status_name']);
	$school = Filter::filter_html($_POST['school']);
	$time = Filter::filter_html($_POST['time']);
	$content = Filter::filter_html($_POST['content']);
	$cid_name = Filter::filter_html($_POST['cid_name']);
	$gid_name = Filter::filter_html($_POST['gid_name']);
	
	$master_id = $pd->fetchOne(array('field'=>'class_master','table'=>'oa_zhsz_class','where'=>"id={$cid}"));
	$master_name = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$master_id}'"));
	
	
	$data = array(
		'user_name'   => $user_name,
		'user_id' => $user_id,
		'gid'     => $gid,
		'cid'  => $cid,
		'term_id'    => $term_id,
		'status'    => $status,
		'term_name'    => $term_name,
		'status_name'    => $status_name,
		'content'    => $content,
		'time'    => $time,
		'dept_name'    => $gid_name.$cid_name,
		'master_id' => $master_id,
		'master_name' => $master_name,
		'school' => $school,
		'create_time'  => date('Y-m-d H:i:s')
	);
	$params = array('data'=>$data,'table'=>'oa_student_status');
	$result = $pd->insert($params);
	$rs=$pd->query("update oa_user_extend set xj_status=".$status." where  userid='".$user_id."'");
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '学籍添加失败，请稍后再试。',
			   'url'  => './?t=student_status'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '学籍添加成功。',
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