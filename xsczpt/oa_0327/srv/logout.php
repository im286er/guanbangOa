<?php
//后台Session清空
	header("Content-type: text/html; charset=utf-8;"); 
	require '../../ppf/fun.php';
	require '../../ppf/pdo_mysql.php';
	require '../library/Filter.php';
	require('../common.php');
	
	session_start();
	
	$pd = new pdo_mysql();

//平台基本信息
$params = array('field'=>array('flag_alias','flag_log'),'table'=>'oa_zhsz_system');
$web    = $pd->fetchRow($params);

//系统日志
if($web['flag_log']==1){
	$now = date('Y-m-d H:i:s');
	$ip  = $_SERVER['REMOTE_ADDR'];
}

$str=(isset($_SESSION['bid']))?'?logout_from_tl=1':'';//用于判断是否来自同一登陆

session_destroy();
header('Location:../'.$str);
?>