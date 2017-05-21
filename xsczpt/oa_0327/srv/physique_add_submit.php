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
	$user   = $pd->fetchRow(array('field'=>array('truename','dept','cardno'),'table'=>'v_users','where'=>"id='{$userId}'"));
		
	if(empty($user['dept'])){
		$tips = array(
		   'tips' => '该学生没有班级信息，请重新输入',
		   'url'  => './?t=physique_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	$now = date('Y-m-d H:i:s');
	
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_physique','where'=>"user_id='{$userId}' and term_id={$curTerm['id']}"));
	if($e!=0){
		$tips = array(
					   'tips' => "该学生体质记录已存在，请不要重复添加。",
					   'url'  => './?t=physique_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	$height = Filter::filter_number($_POST['height']);
	$weight = Filter::filter_number($_POST['weight']);
	$lEye   = Filter::filter_html($_POST['left_eye']);
	$rEye   = Filter::filter_html($_POST['right_eye']);
	$liver  = Filter::filter_html($_POST['liver']);
	$guts   = Filter::filter_html($_POST['guts']);
	$xfTing = Filter::filter_html($_POST['xf_ting']);
	$wkcg   = Filter::filter_html($_POST['wkcg']);
	$blood  = Filter::filter_html($_POST['blood']);
	$vc     = Filter::filter_html($_POST['vc']);
	$spleen = Filter::filter_html($_POST['spleen']);
	$yanke  = Filter::filter_html($_POST['yanke']);
	$mouth  = Filter::filter_html($_POST['mouth']);
	
	$tName = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$_SESSION['uid']}'"));
	
	$data = array(
				'user_id'      => $userId,
				'term_id'      => $curTerm['id'],
				'term_year'    => $curTerm['year'],
				'weight'       => $weight,
				'height'       => $height,
				'left_eye'     => $lEye,
				'right_eye'    => $rEye,
				'liver'        => $liver,
				'guts'         => $guts,
				'xf_ting'      => $xfTing,
				'wkcg'         => $wkcg,
				'blood'        => $blood,
				'vc'       	   => $vc,
				'spleen'       => $spleen,
				'yanke'        => $yanke,
				'mouth'        => $mouth,
				'teacher_id'   => $_SESSION['uid'],
				'teacher_name' => $tName,
				'create_by'    => $_SESSION['username'],
				'create_time'  => $now
			);
	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_physique'));
	if(empty($result)){
		$tips = array(
			   'tips' => "学生体质记录添加失败。",
			   'url'  => './?t=physique_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "学生体质记录添加成功。",
			   'url'  => './?t=physique_manage'
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