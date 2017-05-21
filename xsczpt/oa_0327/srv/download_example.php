<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('../plugins/PHPExcel.php');
	
session_start(); 
//$uid = $_SESSION['uid'];

$pd = new pdo_mysql();
$filter = new Filter();
$PHPExcel = new PHPExcel();
	
$flag = empty($_GET['flag'])?1:Filter::filter_number($_GET['flag']);

if($flag<=0){
	$flag = 1;	
}
//查询当前学期
$curTerm   = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
$curTermId = $curTerm['id'];

//评语模板
if($flag==1){
	//查询当前代班
	$class = $class = $pd->fetchRow(array('field'=>array('id','grade_id','class_name'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
	if(!empty($class)){
		$classId = $class['id'];
		$grade   = $pd->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
		//查询当前班级有综评表的学生
		$users  = $pd->query("select id,username,truename from v_users where dept={$classId} AND flag_type=1 order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
		//查询已经写综评表的人
		$sql = "SELECT A.id,B.id AS main_id FROM v_users AS A INNER JOIN oa_zhsz_teacher_comment AS B ON a.id=b.user_id WHERE b.term_id={$curTermId} AND a.dept={$classId} AND a.flag_type=1";
		$main  = $pd->query($sql);
		$userA = array();
		foreach($main as $rs){
			$userA[$rs['id']] = $rs['main_id'];
		}
		//下载表格初始化
		$title = $grade['grade_name'].$class['class_name']."（{$curTerm['term_name']}）评语模板";
		$PHPExcel->setActiveSheetIndex(0);  
		$PHPExcel->getActiveSheet()->setTitle($title);  
		$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
		$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
		//设置标题栏
		$PHPExcel->getActiveSheet()->setCellValue('A1','身份证号');
		$PHPExcel->getActiveSheet()->setCellValue('B1','学生姓名');
		$PHPExcel->getActiveSheet()->setCellValue('C1','综评编号');
		$PHPExcel->getActiveSheet()->setCellValue('D1','学期评语');
		$i = 2;
		foreach($users as $rs){
			$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
			$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
			if(isset($userA[$rs['id']])){
				$PHPExcel->getActiveSheet()->setCellValue("C{$i}",$userA[$rs['id']]);
			}else{
				$PHPExcel->getActiveSheet()->setCellValue("C{$i}",0);
			}
			$PHPExcel->getActiveSheet()->setCellValue("D{$i}",'');
			$i++;
		}
		$filename  = date('YmdHis').'_'.$_SESSION['uid'];
		$dataname  = $title;
		$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
		//查询目录是否存在
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
			mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
		}
		
		$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
		$r = $objWriter->save($fileName);
		//插入下载文件到下载表中
		$data = array(
			'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
			'name' 		  => $dataname,
			'flag_type'   => 5,
			'create_by'   => $_SESSION['username'],
			'create_time' => date('Y-m-d H:i:s')
		);
		$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
		//直接下载
		downloadFile($fileName,$title.'.xls');
	}else{
		$tips = array(
		   'tips' => '抱歉，您不是班主任没有操作的权限。',
		   'url'  => './?t=zhsz_other'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
}
	
//体能记录模板
if($flag==2){
	$gid = empty($_GET['gid'])?1:Filter::filter_number($_GET['gid']);
	$cid = empty($_GET['cid'])?0:Filter::filter_number($_GET['cid']);
	$rp  = empty($_GET['rp'])?1:Filter::filter_number($_GET['rp']);
	
	$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
	$className = '';
	//当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	//查询体能项目
	$where = "(grade_id=0 OR grade_id={$gid}) and (term_type=0 OR term_type={$curTerm['term_type']}) ";
	$stamina  = $pd->query("select id,name from oa_zhsz_stamina where {$where} order by id")->fetchAll(PDO::FETCH_ASSOC);
	//查询班级
	if(empty($cid)){
		$cid = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
		$cid = join(',',$cid);
	}else{
		$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
	}
	//查询学生
	$student  = $pd->query("select id,truename,username from v_users where flag_type=1 and dept IN ({$cid}) order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);

	//下载表格初始化
	$title = $gradeName.$className."（{$curTerm['term_name']}）体能模板";
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','身份证号');
	$PHPExcel->getActiveSheet()->setCellValue('B1','学生姓名');
	$start = 67;
	foreach($stamina as $rsb){
		$index = chr($start);
		$start++;
		$PHPExcel->getActiveSheet()->setCellValue("{$index}1",$rsb['id'].'-'.$rsb['name']);	
	}
	//体育成绩
	$index = chr($start);
	$PHPExcel->getActiveSheet()->setCellValue("{$index}1",'体育成绩');	
	
	$i = 2;
	foreach($student as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 5,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//体质记录模板
if($flag==3){
	$gid = empty($_GET['gid'])?0:Filter::filter_number($_GET['gid']);
	$cid = empty($_GET['cid'])?0:Filter::filter_number($_GET['cid']);
	$rp  = empty($_GET['rp'])?1:Filter::filter_number($_GET['rp']);
	$className = '';
	$sFlag = false;
	$where = '';
	if($gid==0){
		$gradeName = '所有学生';
		$sFlag     = true;
	}else{
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		//查询班级
		if(empty($cid)){
			$cid = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
			$cid = join(',',$cid);
		}else{
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
		$where .= " and dept IN ({$cid})";
	}
	
	//当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	//查询学生
	$student  = $pd->query("select id,truename,username from v_users where flag_type=1 {$where} order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
	//下载表格初始化
	$title = $gradeName.$className."（{$curTerm['term_name']}）体质模板";
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','身份证号');
	$PHPExcel->getActiveSheet()->setCellValue('B1','学生姓名');
	$PHPExcel->getActiveSheet()->setCellValue('C1','身高');
	$PHPExcel->getActiveSheet()->setCellValue('D1','体重');
	$PHPExcel->getActiveSheet()->setCellValue('E1','左眼');
	$PHPExcel->getActiveSheet()->setCellValue('F1','右眼');
	$PHPExcel->getActiveSheet()->setCellValue('G1','肝');
	$PHPExcel->getActiveSheet()->setCellValue('H1','胆');
	$PHPExcel->getActiveSheet()->setCellValue('I1','心肺听诊');
	$PHPExcel->getActiveSheet()->setCellValue('J1','外科常规');
	$PHPExcel->getActiveSheet()->setCellValue('K1','血压');
	$PHPExcel->getActiveSheet()->setCellValue('L1','肺活量');
	$PHPExcel->getActiveSheet()->setCellValue('M1','脾');
	$PHPExcel->getActiveSheet()->setCellValue('N1','眼科');
	$PHPExcel->getActiveSheet()->setCellValue('O1','口腔');
	
	$i = 2;
	foreach($student as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 5,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//成绩记录模板
if($flag==4){
	$gid = empty($_GET['gid'])?0:Filter::filter_number($_GET['gid']);
	$cid = empty($_GET['cid'])?0:Filter::filter_number($_GET['cid']);
	$rp  = empty($_GET['rp'])?1:Filter::filter_number($_GET['rp']);

	//是否为班主任
	$isMaster = false;
	$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
	if(!empty($class)){
		$cid = $class['id'];
		$gid = $class['grade_id'];
		$isMaster = true;
	}

	$className = '';
	$sFlag = false;
	$where = '';
	if($gid==0){
		$gradeName = '所有学生';
		$sFlag     = true;
	}else{
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		//查询班级
		if(empty($cid)){
			$cid = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
			$cid = join(',',$cid);
		}else{
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
		$where .= " and dept IN ({$cid})";
	}
	
	//当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	//查询学生
	$student  = $pd->query("select id,truename,username from v_users where flag_type=1 {$where} order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
	//下载表格初始化
	$title = $gradeName.$className."（{$curTerm['term_name']}）成绩模板";
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','学生姓名');
	$PHPExcel->getActiveSheet()->setCellValue('B1','身份证号');
	$PHPExcel->getActiveSheet()->setCellValue('C1','语文');
	$PHPExcel->getActiveSheet()->setCellValue('D1','数学');
	$PHPExcel->getActiveSheet()->setCellValue('E1','外语');
	$PHPExcel->getActiveSheet()->setCellValue('F1','物理');
	$PHPExcel->getActiveSheet()->setCellValue('G1','化学');
	$PHPExcel->getActiveSheet()->setCellValue('H1','生物');
	$PHPExcel->getActiveSheet()->setCellValue('I1','政治');
	$PHPExcel->getActiveSheet()->setCellValue('J1','历史');
	$PHPExcel->getActiveSheet()->setCellValue('K1','地理');
	$PHPExcel->getActiveSheet()->setCellValue('L1','信息技术');
	//$PHPExcel->getActiveSheet()->setCellValue('M1','通用技术');
	//$PHPExcel->getActiveSheet()->setCellValue('N1','体育');
	//$PHPExcel->getActiveSheet()->setCellValue('O1','音乐');
	//$PHPExcel->getActiveSheet()->setCellValue('P1','美术');
	//$PHPExcel->getActiveSheet()->setCellValue('Q1','心理');
	$PHPExcel->getActiveSheet()->setCellValue('M1','总分');
	$PHPExcel->getActiveSheet()->setCellValue('N1','班级名次');
	$PHPExcel->getActiveSheet()->setCellValue('O1','年级名次');
	
	$i = 2;
	foreach($student as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",$rs['truename']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",' '.$rs['username']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 5,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//学生信息模板
if($flag==5){
	//下载表格初始化
	$title = '学生信息模板';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','登录名(18位身份证号)');
	$PHPExcel->getActiveSheet()->setCellValue('B1','姓名');
	$PHPExcel->getActiveSheet()->setCellValue('C1','性别(男、女)');
	$PHPExcel->getActiveSheet()->setCellValue('D1','出生日期(格式为:1983-01-26)');
	$PHPExcel->getActiveSheet()->setCellValue('E1','学籍号');
	$PHPExcel->getActiveSheet()->setCellValue('F1','所在班级ID(确保该班级已经存在)');
	$PHPExcel->getActiveSheet()->setCellValue('G1','民族');
	$PHPExcel->getActiveSheet()->setCellValue('H1','政治面貌');
	$PHPExcel->getActiveSheet()->setCellValue('I1','毕业学校');
	$PHPExcel->getActiveSheet()->setCellValue('J1','联系电话');
	$PHPExcel->getActiveSheet()->setCellValue('K1','Email');
	$PHPExcel->getActiveSheet()->setCellValue('L1','QQ');
	$PHPExcel->getActiveSheet()->setCellValue('M1','地址');
	
	$importType = empty($_GET['import_type'])?1:intval($_GET['import_type']);
	//新生入学导入处理
	if($importType==2){
		$PHPExcel->getActiveSheet()->setCellValue('N1','准考证号)');
		$PHPExcel->getActiveSheet()->setCellValue('O1','语文');
		$PHPExcel->getActiveSheet()->setCellValue('P1','数学');
		$PHPExcel->getActiveSheet()->setCellValue('Q1','外语');
		$PHPExcel->getActiveSheet()->setCellValue('R1','理化');
		$PHPExcel->getActiveSheet()->setCellValue('S1','政史');
		$PHPExcel->getActiveSheet()->setCellValue('T1','体育');
		$PHPExcel->getActiveSheet()->setCellValue('U1','奖惩情况');
	}
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 5,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}

//老师信息模板
if($flag==6){
	//下载表格初始化
	$title = '老师信息模板';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','登录名');
	$PHPExcel->getActiveSheet()->setCellValue('B1','姓名');
	$PHPExcel->getActiveSheet()->setCellValue('C1','性别(男、女)');
	$PHPExcel->getActiveSheet()->setCellValue('D1','出生日期(格式为:1983-01-26)');
	$PHPExcel->getActiveSheet()->setCellValue('E1','工号');
	$PHPExcel->getActiveSheet()->setCellValue('F1','所在部门ID(确保该部门已经存在)');
	$PHPExcel->getActiveSheet()->setCellValue('G1','民族');
	$PHPExcel->getActiveSheet()->setCellValue('H1','政治面貌');
	$PHPExcel->getActiveSheet()->setCellValue('I1','毕业学校');
	$PHPExcel->getActiveSheet()->setCellValue('J1','联系电话');
	$PHPExcel->getActiveSheet()->setCellValue('K1','Email');
	$PHPExcel->getActiveSheet()->setCellValue('L1','QQ');
	$PHPExcel->getActiveSheet()->setCellValue('M1','地址');
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 6,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//班级信息模板
if($flag==7){
	//下载表格初始化
	$title = '班级信息模板';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','班级');
	$PHPExcel->getActiveSheet()->setCellValue('B1','班主任（老师登录名）');
	$PHPExcel->getActiveSheet()->setCellValue('C1','年级ID(1,2,3)');
	$PHPExcel->getActiveSheet()->setCellValue('D1','入学年份(格式为:2013)');
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 7,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	
	
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//任课信息模板
if($flag==8){
	//下载表格初始化
	$title = '任课信息模板';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','任课老师（姓名）');
	$PHPExcel->getActiveSheet()->setCellValue('B1','课程名（如语文、数学、外语）');
	$PHPExcel->getActiveSheet()->setCellValue('C1','任课班级ID(例如：1,2,3)');
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 7,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}

//问题学生信息
if($flag==9){
	//下载表格初始化
	$title = '问题学生信息名单';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','登录名(18位身份证号)');
	$PHPExcel->getActiveSheet()->setCellValue('B1','姓名');
	$PHPExcel->getActiveSheet()->setCellValue('C1','性别');
	$PHPExcel->getActiveSheet()->setCellValue('D1','所在班级ID');
	$PHPExcel->getActiveSheet()->setCellValue('E1','班级名称');
	$PHPExcel->getActiveSheet()->setCellValue('F1','出错原因');
	
	//查询导入出错记录
	$member  = $pd->fetchAll(array('field'=>array('username','truename','reason','gender','dept'),'table'=>'v_users_temp','where'=>"flag_status<>1 and create_by='{$_SESSION['username']}'",'order'=>'CONVERT(truename USING GBK)'));
	
	$i = 2;
	foreach($member as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
		$PHPExcel->getActiveSheet()->setCellValue("C{$i}",$rs['gender']);
		$PHPExcel->getActiveSheet()->setCellValue("D{$i}",$rs['dept']);
		//查询班级名称
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
		$grade = '';
		if($class['grade_id']==1){
			$grade = '高一';	
		}
		if($class['grade_id']==2){
			$grade = '高二';	
		}
		if($class['grade_id']==3){
			$grade = '高三';	
		}
		$PHPExcel->getActiveSheet()->setCellValue("E{$i}",$grade.$class['class_name']);
		$PHPExcel->getActiveSheet()->setCellValue("F{$i}",$rs['reason']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 9,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}

//问题学生信息
if($flag==10){
	//下载表格初始化
	$title = '问题学生信息名单';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','登录名(18位身份证号)');
	$PHPExcel->getActiveSheet()->setCellValue('B1','姓名');
	$PHPExcel->getActiveSheet()->setCellValue('C1','性别');
	$PHPExcel->getActiveSheet()->setCellValue('D1','所在班级ID');
	$PHPExcel->getActiveSheet()->setCellValue('E1','班级名称');
	$PHPExcel->getActiveSheet()->setCellValue('F1','出错原因');
	
	//查询导入出错记录
	$member  = $pd->fetchAll(array('field'=>array('username','truename','reason','gender','dept'),'table'=>'v_users_temp','where'=>"(flag_status=2 OR flag_status=3 OR flag_status=5) and create_by='{$_SESSION['username']}'",'order'=>'CONVERT(truename USING GBK)'));
	
	$i = 2;
	foreach($member as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
		$PHPExcel->getActiveSheet()->setCellValue("C{$i}",$rs['gender']);
		$PHPExcel->getActiveSheet()->setCellValue("D{$i}",$rs['dept']);
		//查询班级名称
		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
		$grade = '';
		if($class['grade_id']==1){
			$grade = '高一';	
		}
		if($class['grade_id']==2){
			$grade = '高二';	
		}
		if($class['grade_id']==3){
			$grade = '高三';	
		}
		$PHPExcel->getActiveSheet()->setCellValue("E{$i}",$grade.$class['class_name']);
		$PHPExcel->getActiveSheet()->setCellValue("F{$i}",$rs['reason']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 10,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
		
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//成绩记录模板
if($flag==11){
	//当前页面权限
	/*$pValue = 26;
	$public = $db->fetchOne(array('field'=>'flag_public','table'=>'oa_zhsz_privileges','where'=>"type={$pValue}"));
	$priv   = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
	if(!isset($priv[0])&&$public==0){
		$tips = array(
				   'tips' => '抱歉，您没有操作的权限。',
				   'url'  => 'systeminfo.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}*/
	
	$courseId = empty($_GET['course_id'])?0:Filter::filter_number($_GET['course_id']);
	if($courseId==0){
		$tips = array(
		   'tips' => '所教课程没有设置。',
		   'url'  => 'systeminfo.php'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	//查询课程信息
	$courseInfo = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course','where'=>"id={$courseId}"));
	//查询当前老师所教课程与班级

	$teachArray  = $pd->query("select * from oa_zhsz_teach where user_id='{$_SESSION['uid']}' and course_id={$courseId}")->fetchAll(PDO::FETCH_ASSOC);
	$teachClass  = array();
	$teachCourse = array();
	$classId     = '';
	$className   = '';
	foreach($teachArray as $rs){
		if(isset($teachClass[$rs['class_id']])){
			continue;
		}else{
			$teachClass[$rs['class_id']] = $rs['class_name'];
			$classId .= $rs['class_id'].',';
			$className .= $rs['class_name'].' ';
		}
		$teachCourse[$rs['course_id']] = $rs['course_name'];
	}
	
	/*if(!isset($priv[$courseId])){
		$tips = array(
				   'tips' => '抱歉，您没有上传成绩的权限。',
				   'url'  => './?t=main'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}*/
	
	if(empty($teachArray)){
		$tips = array(
		   'tips' => '抱歉，当前用户没有设置所教班级及课程信息。',
		   'url'  => './?t=main'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$classId = trim($classId,',');
	$where = " and dept IN ({$classId}) ";
	
	//当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	//查询学生
	$student  = $pd->query("select id,username,truename,dept from v_users where flag_type=1 {$where} order by dept,CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);

	//下载表格初始化
	$title = "{$teachCourse[$courseId]}成绩模板";
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','身份证号');
	$PHPExcel->getActiveSheet()->setCellValue('B1','学生姓名');
	$PHPExcel->getActiveSheet()->setCellValue('C1','所在班级');
	$PHPExcel->getActiveSheet()->setCellValue('D1',$teachCourse[$courseId]);
	//如果为外语3，物理4，化学5，生物6，技术10则可以上传等级
	if($courseInfo['is_checked']==1){//有等级评定
		$PHPExcel->getActiveSheet()->setCellValue('E1',$courseInfo['level_name']);
	}
	
	$i = 2;
	foreach($student as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
		$PHPExcel->getActiveSheet()->setCellValue("C{$i}",$teachClass[$rs['dept']]);
		$i++;
	}
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 7,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
if($flag==12||$flag==13||$flag==14){
	$table = 'oa_zhsz_research';
	$title = '研究性学习';
	if($flag==13){
		$table = 'oa_zhsz_service';
		$title = '社区服务';
	}
	if($flag==14){
		$table = 'oa_zhsz_event';
		$title = '社会实践';
	}
	//查询当前代班
	$class = $class = $pd->fetchRow(array('field'=>array('id','grade_id','class_name'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
	if(!empty($class)){
		$classId = $class['id'];
		$grade   = $pd->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));
		//查询已经写研究性学习的人
		$sql = "SELECT a.user_id,b.create_by as username,(SELECT truename FROM v_users where id=a.user_id) as truename,b.id as r_id,b.subject FROM oa_zhsz_main AS A INNER JOIN {$table} AS B ON a.id=b.main_id WHERE a.term_id={$curTermId} AND a.class_id={$classId}";
		$main  = $pd->query($sql);
		//下载表格初始化
		$stitle = $grade['grade_name'].$class['class_name']."（{$curTerm['term_name']}）{$title}模板";
		$PHPExcel->setActiveSheetIndex(0);  
		$PHPExcel->getActiveSheet()->setTitle($stitle);  
		$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
		$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
		//设置标题栏
		$PHPExcel->getActiveSheet()->setCellValue('A1','身份证号');
		$PHPExcel->getActiveSheet()->setCellValue('B1','学生姓名');
		$PHPExcel->getActiveSheet()->setCellValue('C1',"{$title}编号");
		$PHPExcel->getActiveSheet()->setCellValue('D1','主题');
		$PHPExcel->getActiveSheet()->setCellValue('E1','等级');
		$PHPExcel->getActiveSheet()->setCellValue('F1','学分');
		
		$i = 2;
		foreach($main as $rs){
			$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
			$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$rs['truename']);
			$PHPExcel->getActiveSheet()->setCellValue("C{$i}",$rs['r_id']);
			$PHPExcel->getActiveSheet()->setCellValue("D{$i}",$rs['subject']);
			$i++;
		}
		$filename  = date('YmdHis').'_'.$_SESSION['uid'];
		$dataname  = $title;
		$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
		//查询目录是否存在
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
			mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
		}
		$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
		$r = $objWriter->save($fileName);
		//插入下载文件到下载表中
		$data = array(
			'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
			'name' 		  => $dataname,
			'flag_type'   => 7,
			'create_by'   => $_SESSION['username'],
			'create_time' => date('Y-m-d H:i:s')
		);
		$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
		//直接下载
		downloadFile($fileName,$title.'.xls');
	}else{
		$tips = array(
			   'tips' => '抱歉，您不是班主任没有操作的权限。',
			   'url'  => './?t=zhsz_other'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
}
	
//班主任信息模板
if($flag==15){
	//下载表格初始化
	$title = '班主任信息模板';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','班级ID');
	$PHPExcel->getActiveSheet()->setCellValue('B1','班级');
	$PHPExcel->getActiveSheet()->setCellValue('C1','班主任（姓名）');
	
	//查询所有当前可用班级
	$class = $pd->query("select id,grade_id,class_name from oa_zhsz_class where grade_id>0")->fetchAll(PDO::FETCH_ASSOC);
	$i = 2;
	foreach($class as $rs){
		$gradeName = '';
		if($rs['grade_id']==1){
			$gradeName = '高一';	
		}
		if($rs['grade_id']==2){
			$gradeName = '高二';		
		}
		if($rs['grade_id']==3){
			$gradeName = '高三';			
		}
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",$rs['id']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",$gradeName.$rs['class_name']);
		$i++;
	}
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => 7,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//学分导入
if($flag==16){
	//查询考试类型
	$examType=$pd->query("select * from oa_zhsz_code where parent_no='EXAM_TYPE' and flag_status=1")->fetchAll(PDO::FETCH_ASSOC);
	$exam = '';
	foreach($examType as $rs){
		$exam .= "{$rs['code_name']}、";	
	}
	$exam = trim($exam,'、');
	
	//下载表格初始化
	$title = '补考学分信息记录模板';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','登录名（身份证号）');
	$PHPExcel->getActiveSheet()->setCellValue('B1','学期ID');
	$PHPExcel->getActiveSheet()->setCellValue('C1',"考试类型（{$exam}）");
	$PHPExcel->getActiveSheet()->setCellValue('D1','语文');
	$PHPExcel->getActiveSheet()->setCellValue('E1','数学');
	$PHPExcel->getActiveSheet()->setCellValue('F1','外语');
	$PHPExcel->getActiveSheet()->setCellValue('G1','物理');
	$PHPExcel->getActiveSheet()->setCellValue('H1','化学');
	$PHPExcel->getActiveSheet()->setCellValue('I1','生物');
	$PHPExcel->getActiveSheet()->setCellValue('J1','政治');
	$PHPExcel->getActiveSheet()->setCellValue('K1','历史');
	$PHPExcel->getActiveSheet()->setCellValue('L1','地理');
	$PHPExcel->getActiveSheet()->setCellValue('M1','信息技术');
	$PHPExcel->getActiveSheet()->setCellValue('N1','体育');
	$PHPExcel->getActiveSheet()->setCellValue('O1','音乐');
	$PHPExcel->getActiveSheet()->setCellValue('P1','美术');

	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}

	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
		
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}
	
//奖惩记录模板 
if($flag==17){
	$gid = empty($_GET['gid'])?0:Filter::filter_number($_GET['gid']);
	$cid = empty($_GET['cid'])?0:Filter::filter_number($_GET['cid']);
	$rp  = empty($_GET['rp'])?1:Filter::filter_number($_GET['rp']);

	//是否为班主任
	$isMaster = false;
	$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
	if(!empty($class)){
		$cid = $class['id'];
		$gid = $class['grade_id'];
		$isMaster = true;
	}

	$className = '';
	$sFlag = false;
	$where = '';
	if($gid==0){
		$gradeName = '所有学生';
		$sFlag     = true;
	}else{
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		//查询班级
		if(empty($cid)){
			$cid = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
			$cid = join(',',$cid);
		}else{
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
		$where .= " and dept IN ({$cid})";
	}
	//查询学生
	$student  = $pd->query("select id,truename,username from v_users where flag_type=1 {$where} order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
	//下载表格初始化
	$title = $gradeName.$className."奖惩记录模板";
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','学生姓名');
	$PHPExcel->getActiveSheet()->setCellValue('B1','身份证号');
	$PHPExcel->getActiveSheet()->setCellValue('C1','奖惩内容');
	
	$i = 2;
	foreach($student as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",$rs['truename']);
		$PHPExcel->getActiveSheet()->setCellValue("B{$i}",' '.$rs['username']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);

	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}

//学籍记录模板 
if($flag==18){
	$gid = empty($_GET['gid'])?0:Filter::filter_number($_GET['gid']);
	$cid = empty($_GET['cid'])?0:Filter::filter_number($_GET['cid']);
	$rp  = empty($_GET['rp'])?1:Filter::filter_number($_GET['rp']);

	//是否为班主任
	$isMaster = false;
	$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
	if(!empty($class)){
		$cid = $class['id'];
		$gid = $class['grade_id'];
		$isMaster = true;
	}

	$className = '';
	$sFlag = false;
	$where = '';
	if($gid==0){
		$gradeName = '所有学生';
		$sFlag     = true;
	}else{
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		//查询班级
		if(empty($cid)){
			$cid = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
			$cid = join(',',$cid);
		}else{
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
		$where .= " and dept IN ({$cid})";
	}
	//查询学生
	$student  = $pd->query("select id,truename,username from v_users where flag_type=1 {$where} order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
	//下载表格初始化
	$title = $gradeName.$className."学籍记录模板";
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($title);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	//设置标题栏
	$PHPExcel->getActiveSheet()->setCellValue('A1','登录名（身份证号）');
	$PHPExcel->getActiveSheet()->setCellValue('B1','学籍状态ID');
	$PHPExcel->getActiveSheet()->setCellValue('C1','转入转出学校（当学籍状态是转入转出时填写）');
	$PHPExcel->getActiveSheet()->setCellValue('D1','转入转出说明');
	$PHPExcel->getActiveSheet()->setCellValue('E1','转入转出时间（格式：1970-01-01））');
	
	$i = 2;
	foreach($student as $rs){
		$PHPExcel->getActiveSheet()->setCellValue("A{$i}",' '.$rs['username']);
		$i++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	$dataname  = $title;
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.xls',
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);

	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
}	
	
$pd->close();
unset($pd);
unset($rs);