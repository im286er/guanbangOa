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
$sql = "INSERT INTO oa_zhsz_teach(user_id,course_name,id,truename,create_by,create_time) VALUES ";
$rs  = $pd->fetchAll(array('field'=>array('*'),'table'=>'oa_zhsz_users_temp','where'=>$strWhere));

$ids = '';

if(!empty($rs)){
	$data = '';
	$now  = date('Y-m-d H:i:s');
	$m_i=0;
	foreach($rs as $r){
		$ids  .= "{$r['id']},";
		//$data .= " ('{$r['username']}','".md5('000000')."','{$r['truename']}','{$r['email']}','{$r['address']}','{$r['qq']}','{$r['gender']}','{$r['birth_date']}','{$r['person_no']}','{$r['department']}','{$r['nation']}','{$r['political_status']}','{$r['graduate_school']}','{$r['telphone']}',2,2,1,'{$_SESSION['admin_name']}','{$now}'),";	
		$user = $pd->fetchRow(array('field'=>array('dept','id'),'table'=>'v_users','where'=>"truename='".$r['truename']."' "));
		//$new_id='a'.time();
		$cl_name = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id='".$r['course_id']."' "));
		$gr_name = $pd->fetchRow(array('field'=>array('grade_name'),'table'=>'oa_zhsz_grade','where'=>"id='".$cl_name['grade_id']."' "));
		#班级
		$gcn = $gr_name['grade_name'].''.$cl_name['class_name'];
		$base_data = array(
			'class_id'         => $r['course_id'],
			'user_id'          => $user['id'],
			'course_name'      => $r['course_name'],
			'truename'         => $r['truename'],
			'create_by'        => $_SESSION['username'],
			'create_time'      => $now,
			'school'           => $_SESSION['school'],
			'class_name'       => $gcn
		);
		$result = $pd->insert(array('data'=>$base_data,'table'=>'oa_zhsz_teach'));
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
		   'url'  => './?t=teach_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		$tips = array(
		   'tips' => "老师信息导入失败。",
		   'url'  => './?t=teach_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	$tips = array(
	   'tips' => "没有可以导入的数据。",
	   'url'  => './?t=teach_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>