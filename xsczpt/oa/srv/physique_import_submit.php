<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
date_default_timezone_set("PRC");
//上传excel文件
include('upload.php');

$db = new pdo_mysql();

//判断用户是否登录
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];
if(empty($uid)){
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
	
	//查询当前学期
	$curTerm   = $db->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	
	$flag = empty($_POST['flag'])?3:Filter::filter_number($_POST['flag']);
	$gid  = empty($_POST['gid'])?0:Filter::filter_number($_POST['gid']);
	$cid  = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
	$op   = empty($_POST['repeat_op'])?2:Filter::filter_number($_POST['repeat_op']);
	
	$className = '';
	if($gid==0){
		$gradeName = '所有学生';
	}else{
		$gradeName = $db->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		//查询班级
		if(!empty($cid)){
			$className = $db->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
	}
	$termType  = $curTerm['term_type']==1?'第一学期':'第二学期';
	$dataname = $gradeName.$className.'('.$termType.')体能记录';
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	$a     = 0;
	$error = 0;
	$termId  = $curTerm['id'];
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
		$usercard = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
		$usercard = trim($usercard);
		$user     = $db->fetchRow(array('field'=>array('dept','id','truename'),'table'=>'v_users','where'=>"cardno='{$usercard}'"));
		if(empty($user['dept'])){
			$error++;
			continue;
		}
		$userId   = $user['id'];
		$height  = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
		$weight  = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
		$lEye    = $objPHPExcel->getActiveSheet()->getCell("E{$j}")->getValue();
		$rEye    = $objPHPExcel->getActiveSheet()->getCell("F{$j}")->getValue();
		$liver   = $objPHPExcel->getActiveSheet()->getCell("G{$j}")->getValue();
		$guts    = $objPHPExcel->getActiveSheet()->getCell("H{$j}")->getValue();
		$xfTing  = $objPHPExcel->getActiveSheet()->getCell("I{$j}")->getValue();
		$wkcg    = $objPHPExcel->getActiveSheet()->getCell("J{$j}")->getValue();
		$blood   = $objPHPExcel->getActiveSheet()->getCell("K{$j}")->getValue();
		$vc      = $objPHPExcel->getActiveSheet()->getCell("L{$j}")->getValue();
		$spleen  = $objPHPExcel->getActiveSheet()->getCell("M{$j}")->getValue();
		$yanke   = $objPHPExcel->getActiveSheet()->getCell("N{$j}")->getValue();
		$mouth   = $objPHPExcel->getActiveSheet()->getCell("O{$j}")->getValue();
		//查询记录是否存在
		$e = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_physique','where'=>"user_id='{$userId}' and term_id={$termId}"));
		if($e){
			//跳过存在的记录
			if($op==2){
				continue;
			}
			//删除旧数据
			if($op==1){
				$db->delete(array('table'=>'oa_zhsz_physique','where'=>"user_id='{$userId}' and term_id={$termId}"));
			}
		}
		//更新数据
		$data = array(
			'user_id'      => $userId,
			'term_id'      => $termId,
			'term_year'    => $curTerm['year'],
			'height'       => $height,
			'weight'       => $weight,
			'left_eye'     => $lEye,
			'right_eye'    => $rEye,
			'guts'	       => $guts,
			'xf_ting'	   => $xfTing,
			'wkcg'	       => $wkcg,
			'blood'	       => $blood,
			'vc'	       => $vc,
			'spleen'	   => $spleen,
			'yanke'	       => $yanke,
			'mouth'	       => $mouth,
			'liver'	       => $liver,
			'teacher_id'   => $_SESSION['uid'],
			'teacher_name' => $_SESSION['truename'],
			'create_by'    => $_SESSION['username'],
			'create_time'  => date('Y-m-d H:i:s',time())
		 );
		$result = $db->insert(array('data'=>$data,'table'=>'oa_zhsz_physique'));
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
		'file_type'   => 3,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
		
	);
	$db->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	
	//数据插入错误
	$tips = array(
		   'tips' => "共{$a}条数据，出错{$error}条。",
		   'url'  => '/oa/?t=physique_manage'
		  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => '/oa/?t=physique_manage'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>