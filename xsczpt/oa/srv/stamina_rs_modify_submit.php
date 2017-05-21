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
	$item    = $_POST['item'];
	$userId  = Filter::filter_html($_POST['user_id']);
	$termId  = Filter::filter_number($_POST['term_id']);
	$tyScore = intval($_POST['ty_score']);
	$now = date('Y-m-d H:i:s');
	
	//删除之前的记录
	$pd->exec("delete from oa_zhsz_stamina_record where user_id='{$userId}' and term_id={$termId}");
	
	foreach($item as $rs){
		$itemId   = $rs;
		$itemVal  = Filter::filter_html($_POST["item_{$itemId}"]);
		$tName    = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$_SESSION['uid']}'"));
		$itemName = $pd->fetchOne(array('field'=>'name','table'=>'oa_zhsz_stamina','where'=>"id={$itemId}"));
		$data = array(
			'user_id'      => $userId,
			'term_id'      => $termId,
			'item_id'      => $itemId,
			'item_name'    => $itemName,
			'result'	   => $itemVal,
			'teacher_id'   => $_SESSION['uid'],
			'teacher_name' => $tName,
			'create_by'    => $_SESSION['username'],
			'create_time'  => $now
		);
		$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_stamina_record'));
	}
	//添加体育成绩
	if($tyScore>=0){
		//更新数据
		$result = $pd->update(array('table'=>'oa_zhsz_score','data'=>array('ty'=>$tyScore),'where'=>"user_id='{$userId}' and term_id={$termId} and exam_type='期末'"));
	}
	$tips = array(
		   'tips' => "学生体能记录更新成功。",
		   'url'  => './?t=stamina_rs_manage'
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