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
	   'url'  => './?t=login'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}
$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){

	//基本信息
	$webName   		= Filter::filter_html($_POST['web_name']);
	$domain    		= Filter::filter_html($_POST['web_domain']);
	$description 	= Filter::filter_html($_POST['description']);
	$phone     		= Filter::filter_html($_POST['phone']);
	$fax       		= Filter::filter_html($_POST['fax']);
	$email     		= Filter::filter_html($_POST['email']);
	$address   		= Filter::filter_html($_POST['address']);
	$copyright 		= Filter::filter_html($_POST['copyright']);
	//功能设置
	$status    		= Filter::filter_number($_POST['status']);
	$flagLog   		= Filter::filter_number($_POST['flag_log']);
	$flagAlias 		= Filter::filter_number($_POST['flag_alias']);
	$flagAlvatar    = Filter::filter_number($_POST['flag_avatar']);
	$flagInfo    = Filter::filter_number($_POST['flag_info']);
	$start   		= Filter::filter_html($_POST['self_start']);
	$end     		= Filter::filter_html($_POST['self_end']);
	$cstart   		= Filter::filter_html($_POST['course_start']);
	$cend     		= Filter::filter_html($_POST['course_end']);
	$cselect  		= Filter::filter_html($_POST['course_select']);
	//邮件设置
	$emailHost   	= Filter::filter_html($_POST['email_host']);
	$emailPort      = Filter::filter_html($_POST['email_port']);
	$emailUser 	    = Filter::filter_html($_POST['email_user']);
	$emailPass      = Filter::filter_html($_POST['email_pass']);
	$emailReplyName = Filter::filter_html($_POST['email_reply_name']);
	$emailSendName  = Filter::filter_html($_POST['email_send_name']);
	
	$data = array(
		'name'               => $webName,
		'domain'             => $domain,
		'description'        => $description,
		'phone'              => $phone,
		'email'              => $email,
		'fax'                => $fax,
		'address'	         => $address,
		'copyright'	   		 => $copyright,
		'flag_status'   	 => $status,
		'flag_log'			 => $flagLog,
		'flag_alias'	     => $flagAlias,
		'flag_avatar'	     => $flagAlvatar,
		'flag_info'	         => $flagInfo,
		'self_start'	     => $start,
		'self_end'	         => $end,
		'course_start'	     => $cstart,
		'course_end'	     => $cend,
		'course_select'      => $cselect,
		'email_host'         => $emailHost,
		'email_port'         => $emailPort,
		'email_user'         => $emailUser,
		'email_pass'         => $emailPass,
		'email_reply_name'   => $emailReplyName,
		'school'   => $_SESSION["school"],
		'email_send_name'    => $emailSendName
	);
	
	$params     = array('data'=>$data,'table'=>'oa_zhsz_system','where'=>"id=1");
	$result     = $pd->update($params);
	
	if(empty($result)){
		//数据插入错误
		$tips = array(
		   'tips' => '平台信息更新失败，请稍后再试。',
		   'url'  => './?t=web_modify'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '平台信息更新成功。',
		   'url'  => './?t=web_modify'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	//提交方式出错
	$tips = array(
	   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
	   'url'  => './?t=web_modify'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>