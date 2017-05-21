<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('upload.php');
date_default_timezone_set("PRC");
$pd = new pdo_mysql();
$filter = new Filter();
//判断用户是否登录
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid']; 
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

// var_dump($_POST);
// var_dump($_FILES);
//array(5) { ["pid"]=> string(1) "1" ["cate_name"]=> string(0) "" ["cate_level"]=> string(0) "" ["path_name"]=> string(17) "C:\fakepath\6.jpg" ["but_add"]=> string(7) "添 加" } array(1) { ["pic_file"]=> array(5) { ["name"]=> string(5) "6.jpg" ["type"]=> string(10) "image/jpeg" ["tmp_name"]=> string(24) "D:\xampp\tmp\php50B6.tmp" ["error"]=> int(0) ["size"]=> int(18532) } }
	if(!empty($_POST)) {
		//添加
		extract($_POST);
		//获取分类等级
		if (!empty($pid)) {
			$result = $pd->query("select level from major_cate where id = $pid limit 1")->fetchAll(PDO::FETCH_ASSOC);
			//var_dump($result['0']['level']);die;
			$level = $result['0']['level']+1;

		}

		//文件处理
		if($_FILES['pic_file']['name']!=""){
			$file = $_FILES['pic_file'];
			$dir = "../uploadfiles/major_cate";
			//$name = $cate_name?$cate_name:"undefind";
			$path = single_upload($file,$dir);
		}
		//var_dump($path);die;
		$data = array(
			'fid' => $pid,
			'name' => $cate_name,
			'level' => $level,
			'pic' => $path,
		);
		//var_dump($data);die;
		$result = $pd->insert(array('data'=>$data,'table'=>'major_cate'));
		if(empty($result)) {
			//数据插入错误
			$tips = array(
				'tips' => '添加失败，操作终止。',
				'url' => './?t=major_cate_add'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets=' . $tips);
			exit;

		}else{
			$tips = array(
				'tips' => '添加成功。',
				'url'  => './?t=major_cate_add'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets=' . $tips);
			exit;
		}
	}
?>