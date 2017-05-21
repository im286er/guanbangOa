<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('upload.php');
include('../plugins/PHPExcel.php');
date_default_timezone_set("PRC");
$PHPExcel = new PHPExcel();
		
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
	//上传excel文件
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
	
	//查询当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].$file;
	
	$class_info = $pd->fetchRow(array('field'=>array('id','grade_id','class_name'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));

	$dataname = '';
	if(!empty($class_info)){
		$grade    = $pd->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$class_info['grade_id']}"));
		$dataname = $grade['grade_name'].$class_info['class_name']."（{$curTerm['term_name']}）评语记录";
	}
	
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	$a = 0;
	$e = 0;
	$now  = date('Y-m-d H:i:s');
	$date = date('Y-m-d');
	
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
		$usercard = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
		$usercard = trim($usercard);
		$user     = $pd->fetchRow(array('field'=>array('dept','id'),'table'=>'v_users','where'=>"cardno='{$usercard}'"));
		if(empty($user['dept'])){
			$e++;
			continue;
		}
		$userId   = $user['id'];
		$mainId  = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
		$comment = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();

			
		if(!empty($mainId)){
			//更新数据
			$data = array('content'=>$comment);
			$where = "id={$mainId} and user_id='{$userId}'";
			$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_teacher_comment','where'=>$where));

			if(empty($result)&&$result!=0){
				$e++;
			}
		}else{
			//判断是否真的没有综评表信息
			$mainId = $pd->fetchOne(array('field'=>'id','table'=>'oa_zhsz_teacher_comment','where'=>"user_id='{$userId}' and term_id={$curTerm['id']}"));
			
			if(empty($mainId)){
				$class = $pd->fetchRow(array('field'=>array('id','class_name','grade_id','class_master'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
				$grade    = $pd->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
				$classMaster = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
				
				//自动添加综评信息
				$data = array(
					'user_id'      => $userId,
					'term_id'      => $curTerm['id'],
					'grade_id'     => $class['grade_id'],
					'class_name'   => $grade['grade_name'].$class['class_name'],
					'class_id'     => $user['dept'],
					'master_name'  => $classMaster,
					'content'      => $comment,
					'master_id'      => $_SESSION['uid'],
					'flag_status'      => "已审核",
					'create_time'  => $now
				);
				$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_teacher_comment'));
				
				if(empty($result)){
					$e++;
				}
			}else{
				//更新数据
				$data = array('content'=>$comment);
				$where = "id={$mainId} and user_id='{$userId}'";
				$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_teacher_comment','where'=>$where));

				if(empty($result)&&$result!=0){
					$e++;
				}
			}
		}
		$a++;
	}
	
	//插入上传记录
	$data = array(
		'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
		'name' 		  => $dataname,
		'flag_type'   => 1,
		'file_type'   => 2,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	
	//数据插入错误
	$tips = array(
	   'tips' => "共{$a}条数据，出错{$e}条。",
	   'url'  => './?t=zhsz_comment_batch'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
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