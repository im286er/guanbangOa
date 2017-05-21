<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('upload.php');
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
//var_dump($_POST);die;
// var_dump($_FILES);
// if($_FILES['pic_file']['name']!=""){
// 	echo "string";
// }
// die;
//array(6) { ["cate_id"]=> string(1) "9" ["pid"]=> string(1) "1" ["cate_name"]=> string(15) "不知道让我" ["cate_level"]=> string(1) "2" ["path_name"]=> string(44) "../uploadfiles/major_cate/14951003007027.png" ["but_add"]=> string(7) "修 改" }
//判断提交方式
if($submitMethod=='POST'){
	$cate_id  = Filter::filter_number($_POST['cate_id']);
	$pid   = Filter::filter_number($_POST['pid']);
	$cate_name    = Filter::filter_html($_POST['cate_name']);
	$path_name       = $_POST['path_name'];
	$cate_level         = Filter::filter_number($_POST['cate_level']);

	// 文件处理
	if($_FILES['pic_file']['name']!=""){
		$file = $_FILES['pic_file'];
		$dir =  "../uploadfiles/major_cate";
		//$name = $cate_name?$cate_name:"undefind";
		$path_name = single_upload($file,$dir);
	}
	//var_dump($dir);die;
	// 如果传入新的图片更换图片地址

	$data = array(
		'level'   => $cate_level,
		'fid' => $pid,
		'name'     => $cate_name,
		'pic'  => $path_name,
	);
	
	$where = "id={$cate_id}";
	$params     = array('data'=>$data,'table'=>'major_cate','where'=>$where);
	$result     = $pd->update($params);
	if(empty($result)){
		//数据修改错误
		$tips = array(
		   'tips' => '班级修改失败，请稍后再试。',
		   'url'  => './?t=major_cate_show'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
		   'tips' => '班级修改成功。',
		   'url'  => './?t=major_cate_show'
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