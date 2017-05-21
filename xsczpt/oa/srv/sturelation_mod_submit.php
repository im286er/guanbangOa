<?php
	//判断用户是否登录
	session_start();
	if(empty($_SESSION['username'])){
		$tips = array(
				   'tips' => '请登录后再进行操作。',
				   'url'  => './?t=login'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	$submitMethod = $_SERVER["REQUEST_METHOD"];
	//判断提交方式
	if($submitMethod=='POST'){
		include '../../ppf/pdo_mysql.php';
		include '../library/Filter.php';
		include('../common.php');
		$pd = new pdo_mysql();
		
		//基本信息
		$relation = Filter::filter_html($_POST['relation_name']);
		$truename = Filter::filter_html($_POST['truename']);
		$company  = Filter::filter_html($_POST['company']);
		$telphone = Filter::filter_html($_POST['telphone']);
		$id       = Filter::filter_number($_POST['stu_id']);
			
		$data = array(
			'truename'    => $truename,
			'relation'    => $relation,
			'telphone'    => $telphone,
			'company'     => $company
		);
	
		$params     = array('data'=>$data,'table'=>'oa_zhsz_relative','where'=>"id={$id}");
		$result     = $pd->update($params);
		
		if(empty($result)){
			//数据插入错误
			$tips = array(
				   'tips' => '联系人更新失败，请稍后再试。',
				   'url'  => './?t=stu_contact&id=\''.$_SESSION['uid'].'\''
				  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
		}else{
			$tips = array(
				   'tips' => '联系人更新成功。',
				   'url'  => './?t=stu_contact&id\''.$_SESSION['uid'].'\''
				  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
		}
	}else{
		//提交方式出错
		$tips = array(
		   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
		   'url'  => './?t=stu_contact&id=\''.$_SESSION['uid'].'\''
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
?>