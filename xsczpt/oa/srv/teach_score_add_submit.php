<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

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
	//查询当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
	$userId  = Filter::filter_html($_POST['user_id']);
	$et      = Filter::filter_html($_POST['exam_type']);
	$courseId = empty($_POST['course_id'])?0:Filter::filter_number($_POST['course_id']);
	$level = empty($_POST['level'])?"":Filter::filter_html($_POST['level']);
	$now = date('Y-m-d H:i:s');
	//$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$curTerm['id']} and exam_type='{$et}'"));
	
	//$parent_st=$pd->query("select is_captain from oa_zhsz_report_custom where id='".$_POST["id"]."' and uid='".$_POST["geter"]."' GROUP BY create_by ")->fetchColumn(0);
	
	$coursejp = $pd->fetchOne(array('field'=>'course_code','table'=>'oa_zhsz_course','where'=>"id=".$courseId.""));
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$curTerm['id']} and exam_type='{$et}' and ".$coursejp.">-1 "));
	
	if($e!=0){
		$tips = array(
					   'tips' => "该学生成绩记录已存在，请不要重复添加。",
					   'url'  => './?t=teach_score_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$user   = $pd->fetchRow(array('field'=>array('dept'),'table'=>'oa_user_extend','where'=>"userid='{$userId}'"));
	if(empty($user['dept'])){
		$tips = array(
		   'tips' => '该学生没有班级信息，请重新输入',
		   'url'  => './?t=teach_score_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
	$gradeName = '';
	if($classD['grade_id']==1){
		$gradeName = '高一';	
	}
	if($classD['grade_id']==2){
		$gradeName = '高二';	
	}
	if($classD['grade_id']==3){
		$gradeName = '高三';	
	}
	$data = array(
		'user_id'     => $userId,
		'term_id'     => $curTerm['id'],
		'exam_type'   => $et,
		'grade_id'    => $classD['grade_id'],
		'class_id'    => $user['dept'],
		'class_name'  => $gradeName.$classD['class_name'],
		''.$coursejp.''     => $level,
		'create_by'   => $_SESSION['username'],
		'create_time' => $now
	);

	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_score'));
	if(empty($result)){
		$tips = array(
			   'tips' => "学生成绩记录添加失败。",
			   'url'  => './?t=teach_score_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "学生成绩记录添加成功。",
			   'url'  => './?t=teach_score_manage'
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