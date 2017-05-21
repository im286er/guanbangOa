<?php
header("Content-type: text/html; charset=utf-8;");
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
//上传excel文件
include('upload.php');

$db = new pdo_mysql();

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
	include('../plugins/PHPExcel.php');
	$PHPExcel = new PHPExcel();
	
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
	
	$flag     = empty($_POST['flag'])?18:Filter::filter_number($_POST['flag']);
	$gid      = empty($_POST['gid'])?0:Filter::filter_number($_POST['gid']);
	$cid      = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
	$flagType = empty($_POST['flag_type'])?0:Filter::filter_html($_POST['flag_type']);
	$tid      = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id']);
	/* echo $gid;
	echo "<br/>";
	echo $cid; */
	
	$curTerm   = $db->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>"id={$tid} and school={$_SESSION['school']}"));
	
	$className = '';
	if($gid==0){
		$gradeName = '所有学生';
	}else{
		$gradeName = $db->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid} and school={$_SESSION['school']}"));
		//查询班级
		if(!empty($cid)){
			$className = $db->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
	}
	$dataname = $gradeName.$className.'('.$curTerm['term_name'].')学籍记录';
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	$a     = 0;
	$error = 0;
	$termId  = $curTerm['id'];
	$now = date('Y-m-d H:i:s');
	//exit;
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
		$school  = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
		if(empty($school)){
			$school='无';	
		}
		$content  = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
		if(empty($content)){
			$content='无';	
		}
		$usercard = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
		$usercard = trim($usercard);
		$status = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
		$status = trim($status);
		
		$time = PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("E{$j}")->getValue());
		$time = empty($time)?'0000-00-00':$time;
		$time = date('Y-m-d',$time);
		/* if(empty($time)){
			continue;	
		} */
		
		$user     = $db->fetchRow(array('field'=>array('dept','id','truename'),'table'=>'v_users','where'=>"cardno='{$usercard}'"));
		if(empty($user['dept'])){
			$error++;
			continue;
		}
		$userId   = $user['id'];

		$classD = $db->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
		$grade    = $db->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$classD['grade_id']}"));
		
		$termss   = $db->fetchRow(array('field'=>array('year','term_name'),'table'=>'oa_zhsz_term','where'=>"id={$termId}"));
		
		$status_name = $db->fetchOne(array('field'=>'status_name','table'=>'oa_zhsz_status_manage','where'=>"id={$status}"));
		$master_id = $db->fetchOne(array('field'=>'class_master','table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
		$master_name = $db->fetchOne(array('field'=>'truename','table'=>'v_users','where'=>"id='{$master_id}'"));
		//添加数据
		
		$data = array(
			'user_id'     => $userId,
			'user_name'    => $user['truename'],
			'master_id'    => $master_id,
			'master_name'    => $master_name,
			'term_id'     => $termId,
			'term_name'   => $termss['year'].'('.$termss['term_name'].')',
			'status'      => $status,
			'school'      => $school,
			'status_name'   => $status_name,
			'gid'    => $classD['grade_id'],
			'cid'    => $user['dept'],
			'dept_name'  => $grade['grade_name'].$classD['class_name'],
			'content'     => $content,
			'time'   => $time,
			'create_time' => $now
		);

		$result = $db->insert(array('data'=>$data,'table'=>'oa_student_status'));
		if(empty($result)){
			$error++;
		}
		$a++;
	}
	//插入上传记录
	$data = array(
		'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'file_type'   => 18,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$db->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	
	//数据插入错误
	$tips = array(
	   'tips' => "共{$a}条数据，出错{$error}条。",
	   'url'  => './?t=status_import'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
	
}
?>
