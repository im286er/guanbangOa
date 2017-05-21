<?php
//后台Session清空
	header("Content-type: text/html; charset=utf-8;"); 
	require '../../ppf/fun.php';
	require '../../ppf/pdo_mysql.php';
	require '../library/Filter.php';
	require('../common.php');
	
	session_start();
	session_destroy();
	header('Location:../');
?>