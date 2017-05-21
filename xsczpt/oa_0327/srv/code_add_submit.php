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
	$now = date('Y-m-d H:i:s');
	
	$codeNo = Filter::safe_string($_POST['code_no']);
	
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_code','where'=>"code_no='{$codeNo}'"));
	if($e!=0){
		$tips = array(
		   'tips' => "编码已存在，请不要重复添加。",
		   'url'  => './?t=code_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$codeName 		 = $_POST['code_name'];
	$parentNo		 = empty($_POST['parent_no'])?'':$_POST['parent_no'];
	$flagStatus      = Filter::filter_number($_POST['flag_status']);
	$orderValue      = Filter::filter_number($_POST['order_value']);
	
	$data = array(
		'code_no'          => strtoupper($codeNo),
		'code_name'        => $codeName,
		'parent_no'        => $parentNo,
		'flag_status'      => $flagStatus,
		'order_value'      => $orderValue,
		'create_by'        => $_SESSION['admin_name'],
		'create_time'      => $now
	);
	//如果是二级编码
	if($parentNo!=''){
		$max = $pd->fetchOne(array('field'=>'max(code_no)','table'=>'oa_zhsz_code','where'=>"parent_no='{$parentNo}'"));
		$maxA = explode('_',$max);
		$maxV = array_pop($maxA);
		$maxV = intval($maxV) + 1;
		$maxV = sprintf('%03d',$maxV);
		$data['code_no'] = $parentNo.'_'.$maxV;
	}
	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_code'));
	
	if(empty($result)){
		$tips = array(
		   'tips' => "微代码信息添加失败。",
		   'url'  => './?t=code_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => "微代码信息添加成功。",
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