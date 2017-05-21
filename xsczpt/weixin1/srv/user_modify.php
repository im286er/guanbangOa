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
/*if(empty($_SESSION['uid'])){
	$tips = array(
			   'tips' => '请登录后再进行操作。',
			   'url'  => './?t=index'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}*/

$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
	$truename 		 = $_POST['truename'];
	$gender=$_POST['gender'];
	$birthday=$_POST['birthday'];
	$address=$_POST['address'];
	$mobile=$_POST['mobile'];
	$political_status=$_POST['political_status'];
	$email=$_POST['email'];
	$id=$_SESSION['uid'];
	
	$data = array(
		'truename'        => $truename,
		'birthday'   => $birthday,
		'mobile'   => $mobile,
		'email'    => $email
	);
	$params     = array('data'=>$data,'table'=>'act_member','where'=>"id='{$id}'");
	$result     = $pd->update($params);
	
	$data1 = array(
		'gender'        => $gender,
		'address'   => $address,
		'political_status'   => $political_status
	);
	$params1     = array('data'=>$data1,'table'=>'oa_user_extend','where'=>"userid='{$id}'");
	$result1     = $pd->update($params1);
	
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '修改失败，请稍后再试。'.$params,
			   'url'  => './?t=user_mes'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => '修改成功。',
			   'url'  => './?t=user_mes'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => './?t=user_mes'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>