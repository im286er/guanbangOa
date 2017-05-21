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
	$staminaName = Filter::filter_html($_POST['stamina_name']);
	$termType    = Filter::filter_number($_POST['term_type']);
	$gradeId     = Filter::filter_number($_POST['grade_id']);
	$gradeName   = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gradeId}"));
	$orderValue =  Filter::filter_number($_POST['order_value']);
	
	$data = array(
		'name'        => $staminaName,
		'term_type'   => $termType,
		'grade_id'    => $gradeId,
		'grade_name'  => $gradeName,
		'order_value' => $orderValue,
		'school' => $_SESSION["school"],
		'create_time' => date('Y-m-d H:i:s'),
		'create_by'   => $_SESSION['username']
	);
	$params     = array('data'=>$data,'table'=>'oa_zhsz_stamina');
	$result     = $pd->insert($params);
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '体能项目添加失败，请稍后再试。',
			   'url'  => './?t=stamina_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => '体能项目添加成功。',
			   'url'  => './?t=stamina_manage'
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