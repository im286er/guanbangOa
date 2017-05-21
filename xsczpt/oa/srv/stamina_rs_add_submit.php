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
	$item    = $_POST['item'];
	$userId  = Filter::filter_html($_POST['user_id']);
	
	$user   = $pd->fetchRow(array('field'=>array('truename','dept','cardno'),'table'=>'v_users','where'=>"id='{$userId}'"));
		
	if(empty($user['dept'])){
		$tips = array(
		   'tips' => '该学生没有班级信息，请重新输入',
		   'url'  => './?t=stamina_rs_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
		
	$tyScore = intval($_POST['ty_score']);
	
	$now = date('Y-m-d H:i:s');
	
	$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_stamina_record','where'=>"user_id='{$userId}' and term_id={$curTerm['id']}"));
	if($e!=0){
		$tips = array(
		   'tips' => "该学生体能记录已存在，请不要重复添加。",
		   'url'  => './?t=stamina_rs_manage'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	foreach($item as $rs){
		$itemId   = $rs;
		$itemVal  = Filter::filter_html($_POST["item_{$itemId}"]);
		$tName    = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$_SESSION['uid']}'"));
		$itemName = $pd->fetchOne(array('field'=>'name','table'=>'oa_zhsz_stamina','where'=>"id={$itemId}"));
		$data = array(
					'user_id'      => $userId,
					'term_id'      => $curTerm['id'],
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
		
		//查询记录是否存在
		$e = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$curTerm['id']} and exam_type='期末'"));
		
		if(!empty($e)){
			//更新数据
			$result = $pd->update(array('table'=>'oa_zhsz_score','data'=>array('ty'=>$tyScore),'where'=>"id={$e['id']}"));
		}else{
			$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			$className = '';
			if($classD['grade_id']==1){
				$className = '高一'.$classD['class_name'];
			}
			if($classD['grade_id']==2){
				$className = '高二'.$classD['class_name'];
			}
			if($classD['grade_id']==3){
				$className = '高三'.$classD['class_name'];
			}
			$data = array();
			//添加新数据
			$data['user_id']     = $userId;
			$data['term_id']     = $curTerm['id'];
			$data['ty']			 = $tyScore;
			$data['exam_type']   = '期末';
			$data['grade_id']    = $classD['grade_id'];
			$data['class_id']    = $user['dept'];
			$data['class_name']  = $className;
			$data['create_by']   = $_SESSION['username'];
			$data['create_time'] = $now;
			$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_score'));
		}
	}
	$tips = array(
		   'tips' => "学生体能记录添加成功。",
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