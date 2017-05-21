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
	$courseId = empty($_POST['course_id'])?0:Filter::filter_number($_POST['course_id']);
	$level = empty($_POST['level'])?"":Filter::filter_html($_POST['level']);
	$userId = empty($_POST['user_id'])?"":Filter::filter_html($_POST['user_id']);
	
	if($courseId==0){
		$tips = array(
			   'tips' => '所教课程没有设置。',
			   'url'  => 'systeminfo.php'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$user   = $pd->fetchRow(array('field'=>array('truename','dept','cardno'),'table'=>'v_users','where'=>"id='{$userId}'"));
	if(empty($user['dept'])){
		$tips = array(
		   'tips' => '该学生没有班级信息，请重新输入',
		   'url'  => './?t=skills_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
		
	//查询当前学期
	$curTerm   = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	//查询课程信息
	$courseInfo = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course','where'=>"id={$courseId}"));
		
	//等级审核处理
	$levelD    = array('course_id'=>$courseId,'checker'=>$_SESSION['truename']);
	$levelD['course_name']  = $courseInfo['course_name'];
	$levelD['level']  =  $level;
   
	//查询是否已经存在
	$e = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course_level','where'=>"user_id='{$userId}' and term_id={$curTerm['id']} and course_id='{$courseId}'"));

	if(!empty($e)){
		$tips = array(
			'tips' => "当前学期已存在此科目记录",
			'url'  => './?t=teach_score_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		$levelD['user_id']     = $userId;
		$levelD['term_id']     = $curTerm['id'];
		$levelD['create_by']   = $_SESSION['username'];
		//$levelD['checker_id']   = $_SESSION['uid'];
		$levelD['create_time'] = date('Y-m-d H:i:s');
		
		$result = $pd->insert(array('data'=>$levelD,'table'=>'oa_zhsz_course_level'));
		if(empty($result)){
			$tips = array(
			   'tips' => "技能素质成绩添加失败",
			   'url'  => './?t=teach_score_manage'
			  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
	}
	
	//数据插入 成功
	$tips = array(
	   'tips' => "技能素质成绩添加成功",
	   'url'  => './?t=teach_score_manage'
	  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => '../?t=teach_score_manage'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>