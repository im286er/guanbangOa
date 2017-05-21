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
	   'url'  => './?t=index'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}
$submitMethod = $_SERVER["REQUEST_METHOD"];

//判断提交方式
if($submitMethod=='POST'){	
	$codeName 		 = $_POST['code_name'];
	$content 		 = empty($_POST['content'])?"":$_POST['content'];
	//$flagStatus      = Filter::filter_number($_POST['flag_status']);
	$is_partner      = Filter::filter_number($_POST['is_partner']);
	$is_open      = Filter::filter_number($_POST['is_open']);
	$is_att      = Filter::filter_number($_POST['is_att']);
	$is_submit      = Filter::filter_number($_POST['is_submit']);
	$is_pic      = Filter::filter_number($_POST['is_pic']);
	$is_teacher      = Filter::filter_number($_POST['is_teacher']);
	$orderValue      = Filter::filter_number($_POST['order_value']);
	$id				 = Filter::filter_number($_POST['id']);
	$now = date('Y-m-d H:i:s');
	$item = empty($_POST['item_all'])?'':$_POST['item_all'];
	$item_all = explode(";",$item);
	
	
	
	$data = array(
		'code_name'   => $codeName,
		'content' => $content,
		//'flag_status' => $flagStatus,
		'school'      => $_SESSION['school'],
		'is_partner' => $is_partner,
		'is_att' => $is_att,
		'is_open' => $is_open,
		'is_submit' => $is_submit,
		'is_pic' => $is_pic,
		'is_teacher' => $is_teacher,
		'order_value' => $orderValue
	);
			
	$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_report','where'=>"id={$id}"));
	
	$result1 = '';
	foreach($item_all as $rs){
		if(!empty($rs)){
			$item_one = explode(",",$rs);
			if($item_one[0]=="new"){
				$is_name = $pd->fetchOne(array('field'=>'item_name','table'=>'oa_zhsz_report_item','where'=>"report_id={$id} and item_name='{$item_one[1]}'"));
				if($is_name){
					$tips = array(
					   'tips' => "该内容项已存在，请重新填写",
					   'url'  => './?t=report_manage'
					);
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
					exit;
				}
				$data_item = array(
					'item_name'        => $item_one[1],
					'report_id'        => $id,
					'create_by'        => $_SESSION['username'],
					'create_time'      => $now
				);
				$result1 = $pd->insert(array('data'=>$data_item,'table'=>'oa_zhsz_report_item'));
			}else{
				$data_item1 = array(
					'item_name'        => $item_one[1]
				);
				$result2 = $pd->update(array('data'=>$data_item1,'table'=>'oa_zhsz_report_item','where'=>"id={$item_one[0]}"));
			}
		}
	}
	
	if(empty($result)&&empty($result1)&&empty($result2)){
		$tips = array(
		   'tips' => "报表信息更新失败。",
		   'url'  => './?t=report_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$tips = array(
			   'tips' => "报表信息更新成功。",
			   'url'  => './?t=report_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => './?t=index'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>