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
	$roleName    = Filter::filter_html($_POST['role_name']);
	
	$data = array(
					'name'         => $roleName,
					'create_time'  => date('Y-m-d H:i:s'),
					'create_by'    => $_SESSION['username']
				);
	$params     = array('data'=>$data,'table'=>'oa_zhsz_roles');
	$result_o     = $pd->insert($params);
	$result=$pd->lastInsertId();
	if(empty($result)){
		//数据插入错误
		$tips = array(
			   'tips' => '角色添加失败，请稍后再试。',
			   'url'  => './?t=role_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		//添加角色权限
		$privileges  = $_POST['privileges'];
		//添加权限角色对应
		$sql = 'INSERT INTO oa_zhsz_role_privileges(role_id,privilege_id) VALUES';
		foreach($privileges as $rs){
			$sql .= "({$result},{$rs}),";	
		}
		$sql = trim($sql,',');
		$pd->exec($sql);
		$tips = array(
			   'tips' => '角色添加成功。',
			   'url'  => './?t=role_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => './?t=role_manage'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>