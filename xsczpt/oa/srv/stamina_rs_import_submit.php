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
	
	$flag = empty($_POST['flag'])?2:Filter::filter_number($_POST['flag']);
	$gid  = empty($_POST['gid'])?1:Filter::filter_number($_POST['gid']);
	$op   = empty($_POST['repeat_op'])?2:Filter::filter_number($_POST['repeat_op']);
	
	$gradeName = $db->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
	$termType  = $curTerm['term_type']==1?'第一学期':'第二学期';
	$dataname = $gradeName.'('.$termType.')体能记录';
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	$a = 0;
	$e = 0;
    $error = 0;	
	$itemA = array();
	//查询项目
	for($i='C';$i<=$highestColumn;$i++){
		$val = $objPHPExcel->getActiveSheet()->getCell("{$i}1")->getValue();
		$val = explode('-',$val);
		if(empty($val[1])){
			break;	
		}
		$itemA[$val[0]] = $val[1];
	}
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
		//查询记录是否存在
		$e = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_stamina_record','where'=>"user_id='{$userId}' and term_id={$termId}"));
		if($e){
			//跳过存在的记录
			if($op==2){
				continue;
			}
			//删除旧数据
			if($op==1){
				$db->delete(array('table'=>'oa_zhsz_stamina_record','where'=>"user_id='{$userId}' and term_id={$termId}"));
			}
		}
		//更新数据
		$data = array(
			'user_id'      => $userId,
			'term_id'      => $termId,
			'teacher_id'   => $_SESSION['admin_id'],
			'teacher_name' => $_SESSION['truename'],
			'create_by'    => $_SESSION['username'],
			'create_time'  => date('Y-m-d H:i:s',time())
		);
		$i = 'C';
		foreach($itemA as $k => $v){
			$data['item_id']   = $k;
			$data['item_name'] = $v;
			$val = $objPHPExcel->getActiveSheet()->getCell("{$i}{$j}")->getValue();
			$data['result'] = $val;
			$db->insert(array('data'=>$data,'table'=>'oa_zhsz_stamina_record'));
			$i++;
		}
		//$a++;        
		$tyScore = $objPHPExcel->getActiveSheet()->getCell("{$i}{$j}")->getValue();
		$tyScore = intval($tyScore);
		//导入体育成绩(查询记录是否存在)
		if($tyScore>=0){
			//查询记录是否存在
			$e = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$termId} and exam_type='期末'"));
			if(!empty($e)){
				//更新数据
				$result = $db->update(array('table'=>'oa_zhsz_score','data'=>array('ty'=>$tyScore),'where'=>"id={$e['id']}"));
			}else{
				$user      = $db->fetchRow(array('field'=>array('dept','id','truename'),'table'=>'v_users','where'=>"id='{$userId}'"));
				$classD = $db->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
				$className = '';
				if($classD['grade_id']==1){
					$className = '高一'.$classD['class_name'];
				}
				if($classD['grade_id']==2){
					$className = '高二'.$classD['class_name'];
				}
				if($classD['grade_id']==3){
					$className = '高三'.$classD['class_name'];
				}
				$data = array();
				//添加新数据
				$data['user_id']     = $userId;
				$data['term_id']     = $termId;
				$data['ty']			 = $tyScore;
				$data['exam_type']   = '期末';
				$data['grade_id']    = $classD['grade_id'];
				$data['class_id']    = $user['dept'];
				$data['class_name']  = $className;
				$data['create_by']   = $_SESSION['username'];
				$data['create_time'] = $now;
				$result = $db->insert(array('data'=>$data,'table'=>'oa_zhsz_score'));
				if(empty($result)){
			      $error++;
		        }
				$a++;
			}
		}
	}
	
	//插入上传记录
	$data = array(
		'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'file_type'   => 2,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s',time())
		
	);
	$db->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	
	//数据插入错误
	$tips = array(
		   'tips' => "共{$a}条数据，出错{$error}条。",
		   'url'  => '/oa/?t=stamina_rs_manage'
		  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => '/oa/?t=stamina_rs_manage'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>