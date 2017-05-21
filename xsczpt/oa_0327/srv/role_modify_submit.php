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
	$id          = Filter::filter_number($_POST['id']);

	$data = array(
		'name'    => $roleName
	);
	
	$where = "id={$id}";
	$result     = $pd->exec("update oa_zhsz_roles set name='".$roleName."' where ".$where);
	$ss="update oa_zhsz_roles set name='".$roleName."' where ".$where;
	if(!isset($result)){
		//数据修改错误
		$tips = array(
		   'tips' => '角色修改失败，请稍后再试。'.$result,
		   'url'  => './?t=role_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		//添加角色权限
		$privileges  = $_POST['privileges'];
		//删除原有权限
		$sql = "delete from oa_zhsz_role_privileges where role_id={$id}";
		$pd->exec($sql);
		//添加权限角色对应
		$sql = 'INSERT INTO oa_zhsz_role_privileges(role_id,privilege_id) VALUES';
		foreach($privileges as $rs){
			$sql .= "({$id},{$rs}),";	
		}
		$sql = trim($sql,',');
		$pd->exec($sql);
		$tips = array(
			   'tips' => '角色修改成功。',
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