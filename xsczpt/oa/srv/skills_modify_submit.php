<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
date_default_timezone_set("PRC");
$pd = new pdo_mysql();
$filter = new Filter();

//判断用户是否登录
session_start();
header("Content-type: text/html; charset=utf-8");
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
	$level = empty($_POST['level'])?array():$_POST['level'];
	$ids = empty($_POST['ids'])?array():$_POST['ids'];
	
	$i=0;
	$is_false = 0;
	foreach($ids as $id){
		if(!empty($level[$i])){
			
			$levelD['level']  =  $level[$i];
			$where = "id={$id}";
			$params     = array('data'=>$levelD,'table'=>'oa_zhsz_course_level','where'=>$where);
			$result     = $pd->update($params);
			if(empty($result)){
				$is_false++;
			}
			$i++;
		}
	}

	if($is_false==count($ids)){   //数据修改错误
		$tips = array(
		   'tips' => "技能素质成绩修改失败",
		   'url'  => './?t=skills_manage'
		  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	//数据修改 成功
	$tips = array(
	   'tips' => "技能素质成绩修改成功",
	   'url'  => './?t=skills_manage'
	  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
	
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => '../?t=skills_manage'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>