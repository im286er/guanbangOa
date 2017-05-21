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
session_start();
if(empty($_SESSION['username'])){
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
	//添加经历感悟

	$content = empty($_POST['content'])?"":$_POST['content'];
	
	$now = date('Y-m-d H:i:s',time());
	
	
	if(!get_magic_quotes_gpc()&&$content){
		$content=addslashes($content);

		//添加经历感悟
		$data = array(
			'user_id'   => $_SESSION['uid'],
			'type'	  => 3,
			'content'     => $content,
			'flag_status'     => "待审核",
			'create_by'   => $_SESSION['username'],
			'create_time' => $now
		);

		$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_biye'));
		
		if(empty($result)){
			//数据插入错误
			$tips = array(
			   'tips' => '第'.($i+1).'个学长寄语失败，操作终止。',
			   'url'  => './?t=senior_message'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
	}


	$tips = array(
	   'tips' => '学长寄语添加成功。',
	   'url'  => './?t=senior_message'
	);
	
	$biye_id = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_biye'));
	//写sql插入语句，记录到班主任的消息表
	$rs=$pd->query("insert into oa_message(uid,geter,content,title,stime,is_read,flag,menu2,from_id) values('".$_POST["uid"]."','".$_POST["geter"]."','".$_POST["content"]."','".$_POST["title"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','31',{$biye_id})");
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
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