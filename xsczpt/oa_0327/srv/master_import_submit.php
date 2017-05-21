<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
//上传excel文件
include('upload.php');

$pd = new pdo_mysql();

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
	$flag = empty($_POST['flag'])?15:Filter::filter_number($_POST['flag']);
	$op   = empty($_POST['op'])?'':$_POST['op'];
	//导入班主任信息操作
	if($op=='import'){
		include('../plugins/PHPExcel.php');
		//上传excel文件
		$file = '';
		//判断是否上传文件
		if(!empty($_FILES['table_file']['name'])){			
			$fileDoc = uploadFile($_FILES['table_file'],$_SESSION['admin_id']);
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
				
		$dataname = '班主任信息记录';
		//读取excel文件内容
		$objReader     = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($fileName);
		$sheet         = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); // 取得总行数
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
		
		$a     = 0;
		$error = 0;
		
		$now = date('Y-m-d H:i:s');
		$data = array();
		//循环读取excel文件,读取一条,插入一条
		for($j=2;$j<=$highestRow;$j++){
			$classId = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
			$master  = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
			
			//查询班级和班主任信息是否存在
			$params = array('field'=>'id','table'=>'oa_zhsz_users','where'=>"truename='{$master}' and flag_type=2");
			$data['class_master'] = $pd->fetchOne($params);
			
			$a++;	
			
			//老师信息不存在
			if(empty($data['class_master'])){
				$error++;
				continue;
			}
			
			$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_class','where'=>"id={$classId}"));
			if(empty($result)){
				$error++;
			}
		}
		
		//插入上传记录
		$data = array(
					'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['admin_id'].'/'.$file,
					'name' 		  => $dataname,
					'flag_type'   => $flag,
					'file_type'   => $flag,
					'create_by'   => $_SESSION['username'],
					'create_time' => date('Y-m-d H:i:s')
					
				  );
		$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
		//数据插入错误
		$tips = array(
			   'tips' => "共{$a}条数据，出错{$error}条。",
			   'url'  => '../?t=class_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
}
?>