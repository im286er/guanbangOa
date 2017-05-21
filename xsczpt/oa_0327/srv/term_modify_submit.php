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
	$year        = Filter::filter_html($_POST['year']);
	$termType    = Filter::filter_number($_POST['term_type']);
	if($termType==1){
		$termName = '第一学期';
	}
	if($termType==2){
		$termName = '第二学期';
	}
	$startDate = Filter::filter_html($_POST['start']);
	$endDate  = Filter::filter_html($_POST['end']);
	$flagDefault = Filter::filter_number($_POST['flag_default']);
	$id = Filter::filter_number($_POST['id']);

	if($flagDefault==1){
		//更新term
		$pd->exec('UPDATE oa_zhsz_term SET flag_default=0');
	}
	
	$data = array(
		'year'         => $year,
		'term_name'    => $termName,
		'term_type'    => $termType,
		'start'        => $startDate,
		'end'          => $endDate,
		'flag_default' => $flagDefault,
		'school' =>  $_SESSION["school"],
		'create_time'  => date('Y-m-d H:i:s'),
		'create_by'    => $_SESSION['username']  
	);
	
	$where = "id={$id}";
	
	$params     = array('data'=>$data,'table'=>'oa_zhsz_term','where'=>$where);

	$result     = $pd->update($params);
	if(empty($result)){
		//数据修改错误
		$tips = array(
		   'tips' => '学期修改失败，请稍后再试。',
		   'url'  => './?t=term_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '学期修改成功。',
		   'url'  => './?t=term_manage'
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