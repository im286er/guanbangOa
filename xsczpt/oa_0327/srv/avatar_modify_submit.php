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
	include '../../ppf/fun.php';
	include '../../ppf/pdo_mysql.php';
	include '../library/Filter.php';
	include('../common.php');
	include('upload.php');
	$pd = new pdo_mysql();
	//判断是否上传图片
	if(!empty($_FILES['image']['name'])){
		
		//原有图片信息
		$params = array('field'=>'avatar','table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
		$img    = $pd->fetchOne($params);
											
		$photoPic = upload($_FILES['image'],130,160,'avatar');

		$pic      = explode('|',$photoPic);

		if($pic[0]!=4){
			//图片上传失败
			$tips = array(
				   'tips' => "上传出错：{$pic[2]}。",
				   'url'  => 'avatar_modify.php'
				  );
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}
		$data['avatar'] = $pic[1];
		$params = array('data'=>$data,'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
		$result = $pd->update($params);
		
	
		if(empty($result)){
			//数据更新错误
			$tips = array(
				   'tips' => "照片更新失败，请稍后再试。",
				   'url'  => 'avatar_modify.php'
				  );
			$tips = urlencode(serialize($tips));
			header('Location:./tips.php?gets='.$tips);
			exit;
		}else{
			//删除原有图片
			if(!empty($img)){
				
					 //print_r($_SERVER['DOCUMENT_ROOT'].'<br>');
				$phyPath = $_SERVER['DOCUMENT_ROOT'].$img;
				if(file_exists($phyPath)){
					unlink($phyPath);
				}
			}
			$tips = array(
				   'tips' => '照片更新成功。',
				   'url'  => './?t=stu_modify&id=\''.$_SESSION['uid'].'\''
				  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
	}
	header('Location:./user_modify.php');
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