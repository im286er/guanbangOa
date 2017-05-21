<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

$pd = new pdo_mysql();
$filter = new Filter();
//判断用户是否登录
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];
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
	$content = Filter::filter_html($_POST['comment']);
	$termId  = Filter::filter_number($_POST['term_id']);
	$uid     = Filter::filter_html($_POST['uid']);
	$now = date('Y-m-d H:i:s');
	$truename = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$_SESSION['uid']}'"));
	//添加新评论
	$data = array(
		'user_id'     => $uid,
		'term_id'     => $termId,
		'content'     => $content,
		'truename'    => $truename,
		'user_type'   => $_SESSION['flag_type'],
		'create_by'   => $_SESSION['username'],
		'create_time' => $now
	);

	if($_SESSION['flag_type']==3){
		$data['user_type'] = 3;
	}
	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_comment'));
	if($result){
		//数据插入错误
		$tips = array(
		   'tips' => '评论添加成功。',
		   'url'  => './?t=zhsz_other_comment&uid='.$uid
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		$tips = array(
		   'tips' => '评论添加失败，请稍后再试。',
		   'url'  => './?t=zhsz_other_comment&uid='.$uid
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
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