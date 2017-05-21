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
	$id = Filter::filter_number($_POST['id']);
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
	
	$tName = $pd->fetchOne(array('field'=>'truename','table'=>'oa_zhsz_users','where'=>"id='{$_SESSION['uid']}'"));
	
	$data = array(
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
				'teacher_name' => $tName
			);
	$where = "id={$id}";

	$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_physique','where'=>$where));
	if(empty($result)){
		$tips = array(
			   'tips' => "学生体能记录更新失败。",
			   'url'  => './?t=physique_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "学生体能记录更新成功。",
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