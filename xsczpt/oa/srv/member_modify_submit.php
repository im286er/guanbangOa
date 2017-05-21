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
	$id				 = $_POST['id'];
	$now = date('Y-m-d H:i:s');
	
	if($birthDate==""){
		$birthDate = '0000-00-00';	
	}
	
	$base_data = array(
		'email'       	   => $email,
		'mobile'           => $telphone,
		'truename'         => $truename,
		'birthday'         => $birthDate
	);

	$ext_data = array(
		'gender'           => $gender,
		'person_no'    	   => $personNo,
		'dept'             => $cid,
		'nation'           => $nation,
		'political_status' => $politicalStatus,
		'graduate_school'  => $graduateSchool,
		'qq'       		   => $qq,
		'address'          => $address,
		'flag_status'      => $flagStatus,
		'from_type'		   => $fromType,
		'user_status'	   => $userStatus,
		'medical_history'  => $medicalHistory,
		'update_time'      => $now
	);
			
	$userpass = $_POST['userpass'];
	if(!empty($userpass)){
		$base_data['pmd5'] = md5($userpass);
	}
	
	$parentPass = $_POST['parent_pass'];
	if(!empty($parentPass)){
		$ext_data['parent_pass'] = md5($parentPass);
	}
	
	$result = $pd->update(array('data'=>$base_data,'table'=>'act_member','where'=>"id='{$id}'"));
	$result11 = $pd->update(array('data'=>$ext_data,'table'=>'oa_user_extend','where'=>"userid='{$id}'"));

	if($result!=1&&$result!=0){
		$tips = array(
		   'tips' => "学生信息更新失败。",
		   'url'  => './?t=user_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$isExist = intval($_POST['is_middle']);
		$data    = array();
		
		$data['exam_no'] = $_POST['exam_no'];
		$data['yw'] 	 = intval($_POST['yw']);
		$data['sx'] 	 = intval($_POST['sx']);
		$data['wy'] 	 = intval($_POST['wy']);
		$data['lh']		 = intval($_POST['lh']);
		$data['zs']		 = intval($_POST['zs']);
		$data['ty']	     = intval($_POST['ty']);
		$data['zf']		 = $data['yw'] + $data['sx'] + $data['wy'] + $data['lh'] + $data['zs'] + $data['ty'];
		$data['intro']   = $_POST['intro'];
		
		$username = $pd->fetchOne(array('field'=>'username','table'=>'act_member','where'=>"id='{$id}'"));
		
		if($isExist==0){//添加中考信息
			$data['username']    = $username;
			$data['create_by']   = $_SESSION['admin_name'];
			$data['create_time'] = date('Y-m-d H:i:s');
		
			$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_about_primary'));
		}else{
			$pd->update(array('data'=>$data,'table'=>'oa_zhsz_about_primary','where'=>"username='{$username}'"));	
		}
		
		//更新联系人
		//查询已存在的联系人ID
		$linkIDs = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_relative','where'=>"user_id='{$id}'"));
		foreach($linkIDs as $rs){
			$data = array();
			$data['relation'] = $_POST["relation_{$rs}"];
			$data['truename'] = $_POST["rel_name_{$rs}"];
			$data['company']  = $_POST["company_{$rs}"];
			$data['telphone'] = $_POST["phone_{$rs}"];
			$pd->update(array('data'=>$data,'table'=>'oa_zhsz_relative','where'=>"id={$rs}"));
		}
		//添加新增的
		$relation = empty($_POST['relation'])?array():$_POST['relation'];
		$truename = empty($_POST['rel_name'])?array():$_POST['rel_name'];
		$company  = empty($_POST['company'])?array():$_POST['company'];
		$phone    = empty($_POST['phone'])?array():$_POST['phone'];
		
		$i = 0;
		
		foreach($truename as $rs){
			if(!empty($rs)){
				$data = array(
					'user_id'     => $id,
					'truename'    => $rs,
					'relation'    => $relation[$i],
					'telphone'    => $phone[$i],
					'company'     => $company[$i++],
					'create_by'   => $username,
					'create_time' => date('Y-m-d H:i:s')
				);
				$params  = array('data'=>$data,'table'=>'oa_zhsz_relative');
				$pd->insert($params);
			}else{
				$i++;	
			}
		}
		
		$tips = array(
		   'tips' => "学生信息更新成功。",
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