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
	$truename 		 = $_POST['truename'];
	$gender   		 = $_POST['gender'];
	$birthDate   	 = $_POST['birth_date'];
	$personNo  		 = $_POST['person_no'];
	$did 			 = empty($_POST['did'])?0:Filter::filter_number($_POST['did']);
	$nation			 = $_POST['nation'];
	$politicalStatus = $_POST['political_status'];
	$graduateSchool  = $_POST['graduate_school'];
	$telphone    	 = $_POST['telphone'];
	$email 			 = $_POST['email'];
	$qq  			 = $_POST['qq'];
	$address  		 = $_POST['address'];
	$userStatus      = $_POST['user_status'];
	$medicalHistory  = $_POST['medical_history'];
	$flagStatus      = Filter::filter_number($_POST['flag_status']);
	$rid 			 = empty($_POST['rid'])?2:Filter::filter_number($_POST['rid']);
	$id				 = $_POST['id'];
	
	if($birthDate==""){
		$birthDate = '0000-00-00';	
	}
			
	$userpass = $_POST['userpass'];
	
	$base_data = array(
		'email'       	   => $email,
		'mobile'           => $telphone,
		'truename'         => $truename,
		'birthday'         => $birthDate
	);
	if(!empty($userpass)){
		$base_data['pmd5'] = md5($userpass);
	}
	
	$result = $pd->update(array('data'=>$base_data,'table'=>'act_member','where'=>"id='{$id}'"));

	$ext_data = array(
		'gender'           => $gender,
		'person_no'    	   => $personNo,
		'dept'             => $did,
		'nation'           => $nation,
		'political_status' => $politicalStatus,
		'graduate_school'  => $graduateSchool,
		'qq'       		   => $qq,
		'address'          => $address,
		'role_id'		   => $rid,
		'flag_status'      => $flagStatus,
		'medical_history'  => $medicalHistory
	);
	$result11 = $pd->update(array('data'=>$ext_data,'table'=>'oa_user_extend','where'=>"userid='{$id}'"));
	
	if($result!=1&&$result!=0){
		$tips = array(
		   'tips' => "老师信息更新失败。",
		   'url'  => './?t=user_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => "老师信息更新成功。",
		   'url'  => './?t=user_manage'
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