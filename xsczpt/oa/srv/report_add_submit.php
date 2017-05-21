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
	$now = date('Y-m-d H:i:s');
	
	$codeName = $_POST['code_name'];
	$content = empty($_POST['content'])?"":$_POST['content'];
	$parentNo = empty($_POST['parent_no'])?'':$_POST['parent_no'];
	$item = empty($_POST['item_name'])?array():$_POST['item_name'];
	
	
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_report','where'=>"code_name='{$codeName}' and parent_no='{$parentNo}'  and school={$_SESSION['school']}"));
	if($e!=0){
		$tips = array(
		   'tips' => "该报表字段已存在，请重新添加。",
		   'url'  => './?t=report_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	//$flagStatus      = Filter::filter_number($_POST['flag_status']);
	$is_partner      = Filter::filter_number($_POST['is_partner']);
	$is_teacher      = Filter::filter_number($_POST['is_teacher']);
	$is_att      = Filter::filter_number($_POST['is_att']);
	$is_pic      = Filter::filter_number($_POST['is_pic']);
	$orderValue      = Filter::filter_number($_POST['order_value']);
	$is_submit      = Filter::filter_number($_POST['is_submit']);
	$is_open      = Filter::filter_number($_POST['is_open']);
	
	$data = array(
		'code_name'        => $codeName,
		'parent_no'        => $parentNo,
		//'flag_status'      => $flagStatus,
		'content'      => $content,
		'is_partner'      => $is_partner,
		'is_att'      => $is_att,
		'is_submit'      => $is_submit,
		'is_open'      => $is_open,
		'is_pic'      => $is_pic,
		'is_teacher'      => $is_teacher,
		'school'      => $_SESSION['school'],
		'order_value'      => $orderValue,
		'create_by'        => $_SESSION['username'],
		'create_time'      => $now
	);

	$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_report'));
	
	if(empty($result)){
		$tips = array(
		   'tips' => "报表信息添加失败。",
		   'url'  => './?t=report_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		$last_id = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report'));   //最后一条插入的数据ID，也就是最新插入的自定义项的ID
		foreach($item as $rs){
			if(!empty($rs)){
				$data_time = array(
					'item_name'        => $rs,
					'report_id'        => $last_id,
					'create_by'        => $_SESSION['username'],
					'create_time'      => $now
				);
				$result1 = $pd->insert(array('data'=>$data_time,'table'=>'oa_zhsz_report_item'));
				if(empty($result1)){
					$tips = array(
					   'tips' => "内容项添加失败。",
					   'url'  => './?t=report_manage'
					);
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
				}
			}
		}
		$tips = array(
		   'tips' => "报表信息添加成功。",
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