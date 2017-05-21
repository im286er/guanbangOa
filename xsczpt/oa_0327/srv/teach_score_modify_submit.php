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
	
	$data = array(
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
		'jzf'		  => $jzf
	);
	$where = "id={$id}";
	
	$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_score','where'=>$where));
	if(empty($result)){
		$tips = array(
			   'tips' => "学生成绩记录更新失败。",
			   'url'  => './?t=teach_score_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "学生成绩记录更新成功。",
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