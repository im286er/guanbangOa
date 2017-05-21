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
	$now = date('Y-m-d H:i:s');
		
	$username = Filter::safe_string($_POST['username']);
	$username = strtoupper($username);
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'act_member','where'=>"username='{$username}'"));
	if($e!=0){
		$tips = array(
		   'tips' => "登录名已存在，请不要重复添加。",
		   'url'  => './?t=member_add'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$userpass = $_POST['userpass'];
	if(empty($userpass)){
		$userpass = substr($username,-6);
	}
	$parentPass = $_POST['parent_pass'];
	if(empty($parentPass)){
		$parentPass = '000000';
	}
	$truename 		 = $_POST['truename'];
	$gender   		 = $_POST['gender'];
	$birthDate   	 = $_POST['birth_date'];
	$personNo  		 = $_POST['person_no'];
	$cid 			 = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
	$nation			 = $_POST['nation'];
	$politicalStatus = $_POST['political_status'];
	$graduateSchool  = $_POST['graduate_school'];
	$telphone    	 = $_POST['telphone'];
	$email 			 = $_POST['email'];
	$qq  			 = $_POST['qq'];
	$address  		 = $_POST['address'];
	$fromType  		 = $_POST['from_type'];
	$userStatus      = $_POST['user_status'];
	$medicalHistory  = $_POST['medical_history'];
	
	$flagStatus      = Filter::filter_number($_POST['flag_status']);
	
	if($birthDate==""){
		$birthDate = '0000-00-00';	
	}
	$new_id='a'.time();
	
	$base_data = array(
		'id'         => $new_id,
		'username'         => $username,
		'email'       	   => $email,
		'mobile'           => $telphone,
		'cardno'		   => $username,
		'pmd5'             => md5($userpass),
		'idtype'           => 11,
		'truename'         => $truename,
		'ename'            => '',
		'birthday'         => $birthDate,
		'lgnums'           => 0,
		'school'           => $_SESSION['school'],
		'notice'           => 0,
		'state'            => 2,
		'recommend'        => 0,
		'subject'          => 11,
		'grade'            => 0,
		'period'           => 2,
		'timestamp'        =>time()
	);
	$result = $pd->insert(array('data'=>$base_data,'table'=>'act_member'));
	$ext_data = array(
		'userid'           => $new_id,
		'alias_name'       => $username,
		'parent_pass'	   => md5($parentPass),
		'gender'           => $gender,
		'person_no'    	   => $personNo,
		'dept'             => $cid,
		'nation'           => $nation,
		'political_status' => $politicalStatus,
		'graduate_school'  => $graduateSchool,
		'qq'       		   => $qq,
		'address'          => $address,
		'role_id'		   => 1,
		'flag_type'		   => 1,
		'flag_status'      => $flagStatus,
		'from_type'		   => $fromType,
		'user_status'	   => $userStatus,
		'medical_history'  => $medicalHistory,
		'create_by'        => $_SESSION['username'],
		'create_time'      => $now
	);
	$result11 = $pd->insert(array('data'=>$ext_data,'table'=>'oa_user_extend'));
	
	if(empty($result)){
		$tips = array(
			   'tips' => "学生信息添加失败。",
			   'url'  => './?t=user_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$data = array();
		$data['username'] = $username;
		//判断是否有中考信息
		$data['exam_no'] 	 = $_POST['exam_no'];
		$data['yw'] 	 	 = intval($_POST['yw']);
		$data['sx'] 	 	 = intval($_POST['sx']);
		$data['wy'] 	 	 = intval($_POST['wy']);
		$data['lh']		 	 = intval($_POST['lh']);
		$data['zs']		 	 = intval($_POST['zs']);
		$data['ty']	     	 = intval($_POST['ty']);
		$data['zf']			 = $data['yw'] + $data['sx'] + $data['wy'] + $data['lh'] + $data['zs'] + $data['ty'];
		$data['intro']   	 = $_POST['intro'];
		$data['create_by']   = $_SESSION['admin_name'];
		$data['create_time'] = $now;
		
		$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_about_primary'));
		
		//添加联系人
		$relation = $_POST['relation'];
		$truename = $_POST['rel_name'];
		$company  = $_POST['company'];
		$phone    = $_POST['phone'];
		
		$i = 0;
		
		foreach($truename as $rs){
			if(!empty($rs)){
				$data = array(
					'user_id'     => $new_id,
					'truename'    => $rs,
					'relation'    => $relation[$i],
					'telphone'    => $phone[$i],
					'company'     => $company[$i++],
					'create_by'   => $username,
					'create_time' => $now
				);
				$params  = array('data'=>$data,'table'=>'oa_zhsz_relative');
				$pd->insert($params);
			}else{
				$i++;	
			}
		}
		
		$tips = array(
		   'tips' => "学生信息添加成功。",
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