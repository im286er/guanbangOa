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
	$geter_ids = empty($_POST['_recid'])?"":$_POST['_recid'];
	$now = date('Y-m-d H:i:s',time());
	
	if(!get_magic_quotes_gpc()&&$content){
		$content=addslashes($content);
		
		if($_SESSION['role_id']==4 || $_SESSION['role_id']==6)
			$flag_status = "已审核";
		else
			$flag_status = "待审核";
		
		//添加经历感悟
		$data = array(
			'user_id'   => $_SESSION['uid'],
			'geter_id'  => $geter_ids,
			'content'     => $content,
			'flag_status'     => $flag_status,
			'flag_checked'    => 0,
			'create_by'   => $_SESSION['username'],
			'create_time' => $now
		);

		$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_remarks'));
		
		if(empty($result)){
			//数据插入错误
			$tips = array(
			   'tips' => '添加寄语失败，操作终止。',
			   'url'  => './?t=remarks'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
	}
	
	$tips = array(
	   'tips' => '寄语添加成功。',
	   'url'  => './?t=remarks'
	);
	
	$biye_id = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_remarks'));
	//写sql插入语句，记录到班主任的消息表
	if($_SESSION['role_id']==4||$_SESSION['role_id']==5||$_SESSION['role_id']==6){
		$fzt = '已审核';
	}else{
		$fzt = '待审核';
	}
	$geter_id = explode(',',$geter_ids);
	for($i=0;$i<count($geter_id);$i++){
		$rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,from_id) values('".$_POST["uid"]."','".$geter_id[$i]."','".$_POST["content"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','34',{$biye_id})");
	}
	//$rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,from_id) values('".$_POST["uid"]."','".$_POST["geter"]."','".$_POST["content"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','34',{$biye_id})");
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