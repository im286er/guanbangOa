<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
date_default_timezone_set("PRC");
//上传excel文件
include('upload.php');
include('../plugins/PHPExcel.php');

$pd = new pdo_mysql();

//判断用户是否登录
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];
if(empty($_SESSION['uid'])){
	$tips = array(
	   'tips' => '请登录后再进行操作。',
	   'url'  => 'index.php'
	);
	$tips = urlencode(serialize($tips));
	header('Location:./tips.php?gets='.$tips);
	exit;
} 
$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
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
			
	$flag = empty($_POST['flag'])?7:Filter::filter_number($_POST['flag']);
	
	$dataname = '班级信息记录';
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	
	$a     = 0;
	$error = 0;
	
	$now = date('Y-m-d H:i:s',time());
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
		$data = array();
		$data['create_time']  = $now;
		$data['create_by']    = $_SESSION['username'];
		$data['class_name']   = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
		$data['class_master'] = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
		$data['grade_id']     = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
		$data['class_start']  = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
			
		$a++;	
		
		//必须填字段有一空则退出
		if(empty($data['class_name'])||empty($data['class_start'])){
			$error++;
			continue;
		}
		//查询年級是否存在
		$params = array('field'=>'count(*)','table'=>'oa_zhsz_grade','where'=>"id={$data['grade_id']}");
		$nums   = $pd->fetchOne($params);
		if($nums==0){
			$data['grade_id'] = 0;	
		}
		//查询班主任ID
		$params = array('field'=>array('id'),'table'=>'v_users','where'=>"username='{$data['class_master']}' and flag_type=2");
		//$data['class_master'] = $pd->fetchOne($params);
		/* echo "444";
		exit; */
		/* echo $pd->fetchOne($params);
		exit; */
		//$sid = $T->db->query("select school from class where id=".$cid)->fetchColumn(0);
		$data['school']   = $pd->query("select school from v_users where username='{$data['class_master']}' and flag_type=2")->fetchColumn(0);;
		$data['class_master'] = $pd->query("select id from v_users where username='{$data['class_master']}' and flag_type=2")->fetchColumn(0);
		$year       = abs(intval($data['class_start']));
		if($year<=0){
			$year = date('Y');	
		}
		$yeare      = $year + 3;
		$classStart = substr($year,2).'级';
		$classEnd   = substr($yeare,2).'届';
		
		$data['class_start'] = $classStart;
		$data['class_end']   = $classEnd;
		
		$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_class'));
		if(empty($result)){
			$error++;
		}
	}
	
	//插入上传记录
	$data = array(
		'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'file_type'   => 7,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s',time())
		
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//数据插入错误
	$tips = array(
	   'tips' => "共{$a}条数据，出错{$error}条。",
	   'url'  => '/oa/?t=class_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}else{
	//提交方式出错
	$tips = array(
	   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
	   'url'  => '/oa/?t=class_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>