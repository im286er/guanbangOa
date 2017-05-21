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
//判断提交方式
if($submitMethod=='POST'){
	$content = Filter::filter_html($_POST['comment']);
    $pertype = Filter::filter_number($_POST['pertype']);
	$termId  = Filter::filter_number($_POST['term_id']);
	$uid     = Filter::filter_html($_POST['uid']);
	
	$now = date('Y-m-d H:i:s');
	$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'v_users','where'=>"id='{$uid}'"));
	$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
	$gradeName = '';
	if($classD['grade_id']){
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$classD['grade_id']}"));
	}
	
	$data = array(
	   'user_id'     => $user['id'],
	   'truename'    => $user['truename'],
	   'user_card'   => $user['cardno'],
	   'term_id'     => $termId,
	   'grade_id'    => $classD['grade_id'],
	   'class_id'    => $user['dept'],
	   'class_name'  => $gradeName.$classD['class_name'],
	   'performance' => $content,
		'pertype'     => $pertype,
	   'create_by'   => $_SESSION['username'],
	   'create_time' => $now
	);
	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_performance'));
	if(empty($result)){
		//数据插入错误
		$tips = array(
		   'tips' => '添加失败，请稍后再试。',
		   'url'  => './?t=zhsz_performance&uid='.$uid
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		$tips = array(
		   'tips' => '添加成功。',
		   'url'  => './?t=zhsz_performance&uid='.$uid
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