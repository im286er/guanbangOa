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
	$subject = empty($_POST['subject'])?array():$_POST['subject'];
	$name = empty($_POST['name'])?array():$_POST['name'];
	$content = empty($_POST['content'])?array():$_POST['content'];
	$start_date = empty($_POST['start_date'])?"":$_POST['start_date'];
	$end_date = empty($_POST['end_date'])?"":$_POST['end_date'];
	
	$now = date('Y-m-d H:i:s');

	$size = count($name);

	for($i=0;$i<$size;$i++){
		if(!empty($name[$i])&&!empty($start_date[$i])&&!empty($end_date[$i])){
			//添加经历感悟
			$data = array(
				'user_id'   => $_SESSION['uid'],
				'subject'	  => $subject[$i],
				'type'	  => 2,
				'name'     => $name[$i],
				'content'     => $content[$i],
				'start_date'   => $start_date[$i],
				'end_date'     => $end_date[$i],
				'create_by'   => $_SESSION['username'],
				'create_time' => $now
			);

			$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_biye'));
			
			if(empty($result)){
				//数据插入错误
				$tips = array(
				   'tips' => '第'.($i+1).'个工作经历失败，操作终止。',
				   'url'  => './?t=work_exp'
				);
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
			}
		}
	}

	$tips = array(
	   'tips' => '工作经历添加成功。',
	   'url'  => './?t=work_exp'
	);
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