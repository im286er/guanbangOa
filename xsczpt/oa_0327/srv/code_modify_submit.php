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
	   'url'  => './?t=index'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}
$submitMethod = $_SERVER["REQUEST_METHOD"];

//判断提交方式
if($submitMethod=='POST'){	
	$codeName 		 = $_POST['code_name'];
	$flagStatus      = Filter::filter_number($_POST['flag_status']);
	$orderValue      = Filter::filter_number($_POST['order_value']);
	$id				 = Filter::filter_number($_POST['id']);
	
	$data = array(
		'code_name'   => $codeName,
		'flag_status' => $flagStatus,
		'order_value' => $orderValue
	);
			
	$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_code','where'=>"id={$id}"));
	
	if(empty($result)){
		$tips = array(
		   'tips' => "微代码信息更新失败。",
		   'url'  => './?t=code_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "微代码信息更新成功。",
			   'url'  => './?t=code_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => './?t=index'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>