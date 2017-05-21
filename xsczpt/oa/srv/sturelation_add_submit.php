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
		$telphone  = Filter::filter_html($_POST['telphone']);
			
		$data = array(
			'user_id'     => $_SESSION['uid'],
			'truename'    => $truename,
			'relation'    => $relation,
			'telphone'    => $telphone,
			'company'     => $company,
			'create_by'   => $_SESSION['username'],
			'create_time' => date('Y-m-d H:i:s')
		);
	
		$params     = array('data'=>$data,'table'=>'oa_zhsz_relative');
		$result     = $pd->insert($params);
		if(empty($result)){
			//数据插入错误
			$tips = array(
				   'tips' => '联系人添加失败，请稍后再试。',
				   'url'  => 'user_modify.php'
				  );
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
		}else{
			$tips = array(
				   'tips' => '联系人添加成功。',
				   'url'  => './?t=stu_contact&id\''.$_SESSION['uid'].'\''
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
		header('Location:./tips.php?gets='.$tips);
	}
?>