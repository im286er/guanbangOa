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
				
$sql = "INSERT INTO oa_zhsz_score(user_id,class_name,yy,ms,create_by,create_time) VALUES ";
$rs  = $pd->fetchAll(array('field'=>array('*'),'table'=>'oa_zhsz_users_temp','where'=>$strWhere));
$ids = '';
if(!empty($rs)){
	$data = '';
	$now  = date('Y-m-d H:i:s');
	$m_i=0;
	//$department = $pd->fetchOne(array('field'=>'dept','table'=>'v_users','where'=>"username=".$r['username']." "));
	foreach($rs as $r){
		$user = $pd->fetchRow(array('field'=>array('dept','id'),'table'=>'v_users','where'=>"username='".$r['username']."' "));
		$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
		$grade    = $pd->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$classD['grade_id']}"));
		$userId    = $user['id'];
		$className = $grade['grade_name'].$classD['class_name'];
		$ids  .= "{$r['id']},";
		$data .= " ('".$userId."','".$className."','".$r['yy']."','".$r['ms']."','{$_SESSION['username']}','{$now}'),";
		//$user_id = $pd->fetchOne(array('field'=>'id','table'=>'v_users','where'=>"username=".$r['username']." "));
		$base_data = array(
			//'userid'           => $new_id,
			'user_id'          => $userId,
			'class_name'       => $className,
			'yy'               => $r['yy'],
			'ms'               => $r['ms'],
			'create_by'        => $_SESSION['username'],
			'create_time'      => $now
		);
		$result = $pd->insert(array('data'=>$base_data,'table'=>'oa_zhsz_score'));
		//$result11 = $pd->insert(array('data'=>$ext_data,'table'=>'oa_user_extend'));
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
		   'tips' => "信息导入成功。",
		   'url'  => './?t=skills_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		$tips = array(
		   'tips' => "信息导入失败。",
		   'url'  => './?t=skills_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	$tips = array(
	   'tips' => "没有可以导入的数据。",
	   'url'  => './?t=skills_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>