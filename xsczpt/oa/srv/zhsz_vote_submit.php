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
	$voteCat = Filter::filter_html($_POST['vote_cat']);
	$termId  = Filter::filter_number($_POST['term_id']);
	$userId  = empty($_POST['user_id'])?$_SESSION['uid']:Filter::filter_html($_POST['user_id']);
	$vote    = $_POST['vote'];
	
	$now = date('Y-m-d H:i:s');
	$e   = 0;
	$c   = count($vote);
	foreach($vote as $rs){
		$voteId = $rs;
		$itemId = Filter::filter_number($_POST["item_{$voteId}"]);
		$data = array(
			'user_id'     => $userId,
			'term_id'     => $termId,
			'vote_cat'    => $voteCat,
			'vote_id'     => $voteId,
			'item_id'     => $itemId,
			'reply_user'  => $_SESSION['uid'],
			'create_time' => $now
		);
		
		$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_vote'));

		if(empty($result)){
			$e++;
		}
	}
	$url = './?t=zhsz_vote';
	if($voteCat=='父母评价'){
		$url = './?t=zhsz_vote_parent';
	}
	if($voteCat=='学生互评'){
		$url = './?t=zhsz_other';
	}
	if($voteCat=='班主任评价'){
		$url = './?t=zhsz_master_vote&uid='.$userId;
	}
	$tips = array(
	   'tips' => "{$voteCat}提交成功，共{$c}条数据，出错{$e}条。",
	   'url'  => $url
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