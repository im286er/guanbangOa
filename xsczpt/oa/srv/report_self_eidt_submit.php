<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('upload.php');

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
	//添加经历感悟
	$content = empty($_POST['content_edit'])?"":$_POST['content_edit'];
	$report_id = empty($_POST['report_id'])?"":$_POST['report_id'];
	$note = empty($_POST['note_edit'])?"":$_POST['note_edit'];
	
	if(!empty($content)){
		//添加经历感悟
		$data = array(
			'note'     => $note,
			'content'     => $content
		);
		$params     = array('data'=>$data,'table'=>'oa_zhsz_report_self','where'=>"id={$report_id}");
		$result     = $pd->update($params);
		if(empty($result)){
			//数据插入错误
			$tips = array(
			   'tips' => '修改失败，操作终止。',
			   'url'  => './?t=report_self'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		$tips = array(
		   'tips' => '修改成功。',
		   'url'  => './?t=report_self'
		);
	}else{
		$tips = array(
		   'tips' => '修改失败。',
		   'url'  => './?t=report_self'
		);
	}
	
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
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