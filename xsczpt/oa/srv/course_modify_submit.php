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
	$op = Filter::filter_html($_POST['op']);
	$id = Filter::filter_number($_POST['id']);
	
	if($op=='course'){
		$courseName = Filter::filter_html($_POST['course_name']);
		$isChecked  = Filter::filter_number($_POST['is_checked']);
		$isScore  = Filter::filter_number($_POST['is_score']);
		$score      = Filter::filter_number($_POST['score']);
		$orderValue = Filter::filter_number($_POST['order_value']);
		$isPrint    = Filter::filter_number($_POST['is_print']);
		$courseCode = Filter::filter_html($_POST['course_code']);
		$scoreLevel = Filter::filter_html($_POST['score_level']);
		
		$data = array(
			'course_name' => $courseName,
			'course_code' => $courseCode,
			'is_checked'  => $isChecked,
			'is_score'  => $isScore,
			'score'		  => $score,
			'score_level' => $scoreLevel,
			'order_value' => $orderValue,
			'school'   => $_SESSION["school"],
			'is_print'    => $isPrint,
		);
		$params = array('data'=>$data,'table'=>'oa_zhsz_course');
		$where  = "id={$id}";
		$params = array('data'=>$data,'table'=>'oa_zhsz_course','where'=>$where);
		$result = $pd->update($params);
		if(empty($result)){
			//数据修改错误
			$tips = array(
			   'tips' => '课程修改失败，请稍后再试。',
			   'url'  => './?t=course_manage'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
		}else{
			$tips = array(
			   'tips' => '课程修改成功。',
			   'url'  => "./?t=course_modify&id={$id}"
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
		}
	}
	if($op=='module'){
		//添加课程模块
		$nums = Filter::filter_number($_POST['nums']);
		for($i=1;$i<=$nums;$i++){
			$mName    = Filter::filter_html($_POST["m_name_{$i}"]);
			$gradeId  = Filter::filter_number($_POST["grade_{$i}"]);
			$termId   = Filter::filter_number($_POST["term_{$i}"]);
			$selectId = Filter::filter_number($_POST["select_{$i}"]);
			
			if(!empty($mName)){
				$gradeName  = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gradeId}"));
				$selectName = '全部';
				if($selectId!=0){
					$selectName = $pd->fetchOne(array('field'=>'select_name','table'=>'oa_zhsz_course_select','where'=>"id={$selectId}"));
				}
				$data = array(
				   'course_id' 		    => $id,
				   'module_name' 	    => $mName,
				   'grade_id' 		    => $gradeId,
				   'grade_name'	        => $gradeName,
				   'term_type'   	    => $termId,
				   'select_course_id'   => $selectId,
				   'select_course_name' => $selectName,
				);
				$params = array('data'=>$data,'table'=>'oa_zhsz_course_module');
				$pd->insert($params);
			}
		}
		
		$tips = array(
		   'tips' => '模块添加成功。',
		   'url'  => "./?t=course_modify&id={$id}&op=module"
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