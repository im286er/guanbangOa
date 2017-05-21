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
	$teacherId = Filter::filter_number($_POST['teacher']);
	$gradeId   = Filter::filter_number($_POST['grade']);
	$courseId  = Filter::filter_number($_POST['course']);
	$class     = $_POST['class'];
	
	$now = date('Y-m-d H:i:s');
	$c = count($class);
	$i = 0;
	
	foreach($class as $classId){
		//查询是否已经存在
		$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_teach','where'=>"user_id={$teacherId} and class_id={$classId} and course_id={$courseId}"));
		if($e==0){
			$truename   = $pd->fetchOne(array('field'=>'truename','table'=>'oa_zhsz_users','where'=>"id={$teacherId}"));
			$courseName = $pd->fetchOne(array('field'=>'course_name','table'=>'oa_zhsz_course','where'=>"id={$courseId}"));
			$className  = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$classId}"));
			$gradeName  = '';
			if($gradeId==1){
				$gradeName  = '高一';
			}
			if($gradeId==2){
				$gradeName  = '高二';
			}
			if($gradeId==3){
				$gradeName  = '高三';
			}
			$data = array(
				'user_id'     => $teacherId,
				'truename'    => $truename,
				'course_id'   => $courseId,
				'course_name' => $courseName,
				'class_id'	  => $classId,
				'class_name'  => $gradeName.$className,
				'create_by'   => $_SESSION['uid'],
				'school'   => $_SESSION["school"],
				'create_time' => $now
			);
			$params = array('data'=>$data,'table'=>'oa_zhsz_teach');
			$result = $pd->insert($params);
			if(!empty($result)){
				$i++;
			}
		}
	}
	
	//数据插入错误
	$tips = array(
		   'tips' => "共需添加{$c}条记录，成功添加{$i}条记录。",
		   'url'  => './?t=teach_manage'
		  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
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