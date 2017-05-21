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
	
	$now = date('Y-m-d H:i:s');
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$curTerm['id']} and exam_type='{$et}'"));
	if($e!=0){
		$tips = array(
					   'tips' => "该学生成绩记录已存在，请不要重复添加。",
					   'url'  => './?t=score_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	$yw = Filter::filter_number($_POST['yw']);
	$sx = Filter::filter_number($_POST['sx']);
	$wy = Filter::filter_number($_POST['wy']);
	$wl = Filter::filter_number($_POST['wl']);
	$hx = Filter::filter_number($_POST['hx']);
	$sw = Filter::filter_number($_POST['sw']);
	$zz = Filter::filter_number($_POST['zz']);
	$ls = Filter::filter_number($_POST['ls']);
	$dl = Filter::filter_number($_POST['dl']);
	$xxjs = $_POST['xxjs'];
	$tyjs = $_POST['tyjs'];
	$ty = $_POST['ty'];
	$yy = $_POST['yy'];
	$ms = $_POST['ms'];
	$xl = $_POST['xl'];
	$xx = Filter::filter_number($_POST['xx']);
	
	$szf = $yw + $sx + $wy;
	$jzf = $szf + $wl + $hx + $sw + $zz + $ls + $dl;
	
	$user   = $pd->fetchRow(array('field'=>array('dept'),'table'=>'oa_user_extend','where'=>"userid='{$userId}'"));
	if(empty($user['dept'])){
		$tips = array(
		   'tips' => '该学生没有班级信息，请重新输入',
		   'url'  => './?t=score_manage'
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
		'yw'          => $yw,
		'sx'          => $sx,
		'wy'          => $wy,
		'wl'          => $wl,
		'hx'          => $hx,
		'sw'          => $sw,
		'zz'          => $zz,
		'ls'          => $ls,
		'dl'          => $dl,
		'xxjs'        => $xxjs,
		'tyjs'        => $tyjs,
		'ty'          => $ty,
		'yy'          => $yy,
		'ms'          => $ms,
		'xl'          => $xl,
		'xx'          => $xx,
		'szf'         => $szf,
		'jzf'		  => $jzf,
		'create_by'   => $_SESSION['username'],
		'create_time' => $now
	);

	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_score'));
	if(empty($result)){
		$tips = array(
			   'tips' => "学生成绩记录添加失败。",
			   'url'  => './?t=score_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "学生成绩记录添加成功。",
			   'url'  => './?t=score_manage'
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