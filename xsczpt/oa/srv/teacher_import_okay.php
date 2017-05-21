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

$strWhere = "flag_status=1 and create_by='{$_SESSION['username']}'";
$now = date('Y-m-d H:i:s');
$sql = "INSERT INTO oa_zhsz_users(username,userpass,truename,email,address,qq,gender,birth_date,person_no,department,nation,political_status,graduate_school,telphone,role_id,flag_type,flag_status,create_by,create_time) VALUES ";

$rs  = $pd->fetchAll(array('field'=>array('*'),'table'=>'oa_zhsz_users_temp','where'=>$strWhere));

$ids = '';

if(!empty($rs)){
	$data = '';
	$now  = date('Y-m-d H:i:s');
	$m_i=0;
	foreach($rs as $r){
		$ids  .= "{$r['id']},";
		//$data .= " ('{$r['username']}','".md5('000000')."','{$r['truename']}','{$r['email']}','{$r['address']}','{$r['qq']}','{$r['gender']}','{$r['birth_date']}','{$r['person_no']}','{$r['department']}','{$r['nation']}','{$r['political_status']}','{$r['graduate_school']}','{$r['telphone']}',2,2,1,'{$_SESSION['admin_name']}','{$now}'),";	
		
		$new_id='a'.time();
		$base_data = array(
			'id'         => $new_id,
			'username'         => $r['username'],
			'email'       	   => $r['email'],
			'mobile'           => $r['telphone'],
			'cardno'		   => $r['username'],
			'pmd5'             => md5('000000'),
			'idtype'           => 11,
			'truename'         => $r['truename'],
			'ename'            => '',
			'birthday'         => $r['birth_date'],
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
		$ext_data = array(
			'userid'           => $new_id,
			'alias_name'       => $r['username'],
			'gender'           => $r['gender'],
			'person_no'    	   => $r['person_no'],
			'dept'             => $r['department'],
			'nation'           => $r['nation'],
			'political_status' => $r['political_status'],
			'graduate_school'  => $r['graduate_school'],
			'qq'       		   => $r['qq'],
			'address'          => $r['address'],
			'role_id'		   => 2,
			'flag_type'		   => 2,
			'flag_status'      => 1,
			'create_by'        => $_SESSION['username'],
			'create_time'      => $now
		);
		$result = $pd->insert(array('data'=>$base_data,'table'=>'act_member'));
		$result11 = $pd->insert(array('data'=>$ext_data,'table'=>'oa_user_extend'));
		sleep(2);
		$m_i=$m_i+1;
	}
	$ids  = trim($ids,',');
	/*data = trim($data,',');
	$sql .= $data;
	$r = $pd->exec($sql);*/
	if($m_i>0){
		//删除临时数据
		$pd->exec("delete from oa_zhsz_users_temp where id IN ({$ids})");
		$tips = array(
		   'tips' => "老师信息导入成功。",
		   'url'  => './?t=user_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		$tips = array(
		   'tips' => "老师信息导入失败。",
		   'url'  => './?t=user_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	$tips = array(
	   'tips' => "没有可以导入的数据。",
	   'url'  => './?t=user_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>