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
	$className  = Filter::filter_html($_POST['class_name']);
	$masterId   = Filter::filter_html($_POST['master_id']);
	$gradeId    = Filter::filter_number($_POST['grade']);
	$year       = Filter::filter_html($_POST['class_year']);
	$id         = Filter::filter_number($_POST['id']);

	$yeare      = $year + 3;
	$classStart = substr($year,2).'级';
	$classEnd   = substr($yeare,2).'届';
	
	$data = array(
		'class_name'   => $className,
		'class_master' => $masterId,
		'grade_id'     => $gradeId,
		'class_start'  => $classStart,
		'school' => $_SESSION["school"],
		'class_end'    => $classEnd
	);
	
	$where = "id={$id}";
	$params     = array('data'=>$data,'table'=>'oa_zhsz_class','where'=>$where);
	$result     = $pd->update($params);
	if(empty($result)){
		//数据修改错误
		$tips = array(
		   'tips' => '班级修改失败，请稍后再试。',
		   'url'  => './?t=class_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '班级修改成功。',
		   'url'  => './?t=class_manage'
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