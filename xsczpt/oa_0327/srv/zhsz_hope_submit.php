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
	$hope = Filter::filter_html($_POST['hope']);
	$open = Filter::filter_number($_POST['flag_open']);
	$op   = Filter::filter_html($_POST['op']);
	$termId = Filter::filter_number($_POST['term_id']);
	$now = date('Y-m-d H:i:s');
	
	if($op=='add'){
		$gradeId   = Filter::filter_number($_POST['grade_id']);
		$className = Filter::filter_html($_POST['class_name']);
		$classId   = Filter::filter_html($_POST['class_id']);
		//添加新目标
		$data = array(
						'user_id'     => $_SESSION['uid'],
						'term_id'     => $termId,
						'grade_id'    => $gradeId,
						'class_name'  => $className,
						'class_id'    => $classId,
						'hope'        => $hope,
						'flag_open'   => $open,
						'create_time' => $now
					);
		//判断是否已经存在
		$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_hope','where'=>"term_id={$termId} and user_id='{$_SESSION['uid']}'"));
		if($e==0){
			
			$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_hope'));
			
			if(empty($result)){
				//数据插入错误
				$tips = array(
					   'tips' => '家长期望更新失败，请稍后再试。',
					   'url'  => './?t=parent_hope'
					  );
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
			}else{
				$tips = array(
					   'tips' => '家长期望更新更新成功。',
					   'url'  => './?t=parent_hope'
					  );
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
			}
		}else{
			$tips = array(
					   'tips' => '记录已经存在，请不要重复添加。',
					   'url'  => './?t=parent_hope'
					  );
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
		}
	}
	
	if($op=='update'){
		$mainId  = Filter::filter_number($_POST['main_id']);
		//修改新目标
		$data = array(
			'hope'      => $hope,
			'flag_open' => $open
		);
		$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_hope','where'=>"id={$mainId}"));
		if(empty($result)){
			//数据插入错误
			$tips = array(
			   'tips' => '家长期望更新失败，请稍后再试。',
			   'url'  => './?t=parent_hope'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}else{
			$tips = array(
			   'tips' => '家长期望更新更新成功。',
			   'url'  => './?t=parent_hope'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
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