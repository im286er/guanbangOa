<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('upload.php');
include('../plugins/PHPExcel.php');
$PHPExcel = new PHPExcel();

$pd = new pdo_mysql();
$filter = new Filter();
//判断用户是否登录
session_start();
if(empty($_SESSION['uid'])){
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
	$file = '';
	//判断是否上传文件
	if(!empty($_FILES['table_file']['name'])){			
		$fileDoc = uploadFile($_FILES['table_file'],$_SESSION['uid']);
		$att     = explode('|',$fileDoc);
		if($att[0]!=4){
			//图片上传失败
			$tips = array(
			   'tips' => '文件上传失败，请稍后再试。',
			   'url'  => 'systeminfo.php'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		$file = $att[1];
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].$file;
			
	$flag = empty($_POST['flag'])?6:Filter::filter_number($_POST['flag']);
	
	$dataname = '老师信息记录';
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	//清空之前记录
	$pd->delete(array('table'=>'oa_zhsz_users_temp','where'=>"create_by='{$_SESSION['username']}'"));
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
		$data = array();
		$data['create_by'] 		  = $_SESSION['username'];
		$data['username'] 		  = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
		$data['truename']  		  = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
		$data['gender']    		  = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
		$data['birth_date'] 	  = PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue());
		$data['birth_date'] 	  = empty($data['birth_date'])?'0000-00-00':$data['birth_date'];
		$data['birth_date'] 	  = date('Y-m-d',$data['birth_date']);
		$data['person_no']  	  = $objPHPExcel->getActiveSheet()->getCell("E{$j}")->getValue();
		$data['department']   	  = $objPHPExcel->getActiveSheet()->getCell("F{$j}")->getValue();
		$data['department'] 	  = empty($data['department'])?0:intval($data['department']);
		$data['nation']    		  = $objPHPExcel->getActiveSheet()->getCell("G{$j}")->getValue();
		$data['political_status'] = $objPHPExcel->getActiveSheet()->getCell("H{$j}")->getValue();
		$data['graduate_school']  = $objPHPExcel->getActiveSheet()->getCell("I{$j}")->getValue();
		$data['telphone']    	  = $objPHPExcel->getActiveSheet()->getCell("J{$j}")->getValue();
		$data['email']   	      = $objPHPExcel->getActiveSheet()->getCell("K{$j}")->getValue();
		$data['qq']               = $objPHPExcel->getActiveSheet()->getCell("L{$j}")->getValue();
		$data['address']  	      = $objPHPExcel->getActiveSheet()->getCell("M{$j}")->getValue();
		
		//验证规则
		$regexEmail  = '/^[A-Za-z0-9-_\.]+@[a-zA-Z0-9\.]+\.[a-zA-Z0-9]{2,}$/i';
		$regexMobile = '/^(13|15|18|14)\d{9}$/i';
		
		//必须填字段有一空则退出
		if(empty($data['username'])||empty($data['truename'])||empty($data['department'])){
			$data['reason'] = '必填字段(登录名、姓名、所在部门ID)有一项或多项为空。';
			$data['flag_status'] = 2;
			$r = $pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data));
			if($r==0){
				$pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data,'debug'=>1));	
			}
			continue;
		}
		//Email规则验证
		if(!empty($data['email'])&&!preg_match($regexEmail,$data['email'])){
			$data['reason'] = "Email地址({$data['email']})不正确。";
			$data['flag_status'] = 4;
			$r = $pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data));
			if($r==0){
				$pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data,'debug'=>1));	
			}
			continue;
		}
		//查询部门是否存在
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_dept','where'=>"id={$data['department']}");
		$nums   = $pd->fetchOne($params);
		if($nums==0){
			$data['flag_status'] = 5;
			$data['reason'] = "所在部门ID不存在。";
			$r = $pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data));
			if($r==0){
				$pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data,'debug'=>1));	
			}
			continue;	
		}
		//查询登录名是否存在
		$params = array('field'=>'count(*)','table'=>'act_member','where'=>"username='{$data['username']}'");
		$nums   = $pd->fetchOne($params);
		if($nums>0){
			$data['flag_status'] = 6;
			$data['reason'] = "登录名({$data['username']})已经存在。";
			$r = $pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data));
			if($r==0){
				$pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data,'debug'=>1));	
			}
			continue;
		}else{
			//查询临时表中是否已经存在该用户
			$params = array('field'=>'count(*)','table'=>'oa_zhsz_users_temp','where'=>"username='{$data['username']}' and flag_status=1");
			$nums   = $pd->fetchOne($params);
			if($nums>0){
				continue;
			}else{
				$data['reason']      = "可以正常导入。";
				$data['flag_status'] = 1;
				$r = $pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data));
				if($r==0){
					$pd->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data,'debug'=>1));	
				}
			}
		}
	}
	
	//插入上传记录
	$data = array(
		'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'file_type'   => 6,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	header('Location:../?t=teacher_import_list');
	exit;
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