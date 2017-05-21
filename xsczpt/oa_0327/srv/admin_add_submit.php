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
	$now = date('Y-m-d H:i:s');
		
	$username = Filter::safe_string($_POST['username']);
	
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'act_member','where'=>"username='{$username}'"));
	if($e!=0){
		$tips = array(
		   'tips' => "登录名已存在，请不要重复添加。",
		   'url'  => './?t=admin_add'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$userpass = $_POST['userpass'];
	if(empty($userpass)){
		$userpass = '000000';
	}
	$truename 		 = $_POST['truename'];
	$rid 			 = empty($_POST['rid'])?0:Filter::filter_number($_POST['rid']);
	$flagStatus      = Filter::filter_number($_POST['flag_status']);
	
	$new_id='a'.time();
	
	$base_data = array(
		'id'         => $new_id,
		'username'         => $username,
		'cardno'		   => $username,
		'pmd5'             => md5($userpass),
		'idtype'           => 11,
		'truename'         => $truename,
		'lgnums'           => 0,
		'school'           => $_SESSION['school'],
		'notice'           => 0,
		'state'            => 2,
		'recommend'        => 0,
		'subject'          => 11,
		'grade'            => 0,
		'period'           => 2,
		'timestamp'        =>time()
	);
	$result = $pd->insert(array('data'=>$base_data,'table'=>'act_member'));
	$ext_data = array(
		'userid'           => $new_id,
		'alias_name'       => $username,
		'dept'             => $rid,
		'role_id'		   => $rid,
		'flag_type'		   => 4,
		'flag_status'      => $flagStatus,
		'create_by'        => $_SESSION['username'],
		'create_time'      => $now
	);
	$result11 = $pd->insert(array('data'=>$ext_data,'table'=>'oa_user_extend'));
	
	if(empty($result)){
		$tips = array(
		   'tips' => "管理人员信息添加失败。",
		   'url'  => './?t=user_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => "管理人员信息添加成功。",
		   'url'  => './?t=user_manage'
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