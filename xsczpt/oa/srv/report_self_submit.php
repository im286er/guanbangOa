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
	//添加经历感悟
	$subject_id = empty($_POST['report_type_add'])?"":$_POST['report_type_add'];
	$content = empty($_POST['content'])?"":$_POST['content'];
	$note = empty($_POST['note'])?"":$_POST['note'];
	
	if(!empty($subject_id)&&!empty($content)){
		$now = date('Y-m-d H:i:s');
		
		$subject = $pd->fetchOne(array('field'=>'code_name','table'=>'oa_zhsz_report','where'=>"id={$subject_id}"));
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));

		$report = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_report_self','where'=>"subject_id='{$subject_id}' and term_id={$curTerm['id']} and uid='{$_SESSION['uid']}'"));
		if($report){
			//数据插入错误
			$tips = array(
			   'tips' => '添加失败，该学期已存在'.$subject.'。',
			   'url'  => './?t=report_self'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		//添加经历感悟
		$data = array(
			'subject'	  => $subject,
			'subject_id'  => $subject_id,
			'content'     => $content,
			'note'     => $note,
			'term_id'     => $curTerm['id'],
			'term_year'   => $curTerm['year'],
			'uid'   => $_SESSION['uid'],
			'create_by'   => $_SESSION['username'],
			'create_time' => $now
		);

		$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_report_self'));
		if(empty($result)){
			//数据插入错误
			$tips = array(
			   'tips' => '添加失败，操作终止。',
			   'url'  => './?t=report_self'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		$tips = array(
		   'tips' => '添加成功。',
		   'url'  => './?t=report_self'
		);
		$custom_id_p = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report_self'));
	
		//写sql插入语句，记录到班主任的消息表
	    $rs=$pd->query("insert into oa_message(uid,geter,content,title,stime,is_read,flag,menu2,subject,from_id) values('".$_POST["uid"]."','".$_POST["geter"]."','".$_POST["content"]."','".$_POST["title"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','27','".$_POST["subject"]."','".$custom_id_p."')");
	}else{
		$tips = array(
		   'tips' => '添加失败。',
		   'url'  => './?t=report_self'
		);
	}
	
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