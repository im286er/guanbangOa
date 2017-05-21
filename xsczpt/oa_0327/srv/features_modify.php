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

//当前页面权限
$pValue = 7;
//实际拥有的权限
$priv = selectHasPrivileges($pd,$_SESSION['role_id'],$pValue);
if(!isset($priv[1])){
	$tips = array(
			   'tips' => '抱歉，您没有操作的权限。',
			   'url'  => './?t=systeminfo'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}

$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
	$fea_name 		 = $_POST['fea_name'];
	$fea_des=$_POST['fea_des'];
	$fea_menuname=$_POST['fea_menuname'];
	$pid    = Filter::filter_number($_POST['pid']);
	$fea_url=$_POST['fea_url'];
	$fea_icon=$_POST['fea_icon'];
	$fea_sort=Filter::filter_number($_POST['fea_sort']);
	$fea_states=Filter::filter_number($_POST['fea_states']);
	$fea_type=Filter::filter_number($_POST['fea_type']);
	$fea_level=Filter::filter_number($_POST['fea_level']);
	$m_id=Filter::filter_number($_POST['m_id']);
	$id				 = Filter::filter_number($_POST['id']);
	$data = array(
		'fea_name'        => $fea_name,
		'fea_des'   => $fea_des,
		'fea_menuname'   => $fea_menuname,
		'pid'    => $pid,
		'fea_url'  => $fea_url,
		'fea_icon' => $fea_icon,
		'fea_sort' => $fea_sort,
		'fea_states' => $fea_states,
		'fea_type' => $fea_type,
		'fea_level'=>$fea_level,
		'm_id'=>$m_id
	);
	
	$params     = array('data'=>$data,'table'=>'oa_features','where'=>"id={$id}");
	$result     = $pd->update($params);
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '权限修改失败，请稍后再试。'.$params,
			   'url'  => './?t=features_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => '权限修改成功。',
			   'url'  => './?t=features_manage'
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