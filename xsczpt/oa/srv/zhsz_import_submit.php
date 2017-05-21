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
session_start();
header("Content-type: text/html; charset=utf-8");
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

	$courseId = empty($_POST['course_id'])?0:Filter::filter_number($_POST['course_id']);
	if($courseId==0){
		$tips = array(
			   'tips' => '所教课程没有设置。',
			   'url'  => 'systeminfo.php'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
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
	
	$flag     = empty($_POST['flag'])?11:Filter::filter_number($_POST['flag']);
	$et       = empty($_POST['exam_type'])?0:Filter::filter_html($_POST['exam_type']);
	$op       = empty($_POST['repeat_op'])?2:Filter::filter_number($_POST['repeat_op']);
	
	//查询当前老师所教课程与班级                              
	$teachArray = $db->query("select * from oa_zhsz_teach where user_id='{$_SESSION['uid']}' and course_id={$courseId}")->fetchAll(PDO::FETCH_ASSOC);
	$teachClass  = array();
	$teachCourse = array();
	$classId     = '';
	$className   = '';
	//查询课程信息
	$courseInfo = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course','where'=>"id={$courseId}"));
	
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
	
	$dataname = $className.'('.$curTerm['term_name'].').'.$teachCourse[$courseId].'成绩记录';
	
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
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
		$level     = '';
		$field     = '';
		$levelD    = array('course_id'=>$courseId,'checker'=>$_SESSION['truename'],'checker_id'=>$_SESSION['uid']);
		$usercard  = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
		$true_name = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
		$usercard  = trim($usercard);
		$true_name  = trim($true_name);
		$user      = $db->fetchRow(array('field'=>array('dept','id'),'table'=>'v_users','where'=>"username='{$usercard}'"));
		
		if(empty($user['dept'])){
			$error++;
			$data1['reason'] = "该用户({$usercard})不存在。";
			$data1['truename'] =  $true_name;
			$data1['username']  =  $usercard;
			$r = $db->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data1));
			//echo $r."err"."<br/>";
			 if($r==0){
				$db->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data1,'debug'=>1));	
			} 
		}else{
			$userId    = $user['id'];
			$classD = $db->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			$grade    = $db->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id={$classD['grade_id']}"));
			$className = $grade['grade_name'].$classD['class_name'];
			
			if($courseInfo['is_score']==1){
				$score     = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
				$data      = array();
				$data[$courseInfo['course_code']] = empty($score)?-1:$score;
				$field = $courseInfo['course_code'];
			
				//查询记录是否存在
				$e = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$termId} and exam_type='{$et}'"));
				//查询记录
				$params = array('field'=>'count(*)','table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$termId} and exam_type='{$et}'");
				$nums   = $db->fetchOne($params);
				
				//查询临时表中是否已经存在该用户
				$user_name = $db->fetchOne(array('field'=>'username','table'=>'v_users','where'=>"id='{$userId}' "));
				$truename = $db->fetchOne(array('field'=>'truename','table'=>'v_users','where'=>"id='{$userId}' "));
				$params = array('field'=>'count(*)','table'=>'oa_zhsz_users_temp','where'=>"username='".$user_name."' and flag_status=1");
				$nums   = $db->fetchOne($params);
				if($nums>0){
					continue;
				}else{
					$data['truename']    = $truename;
					$data['username']    = $user_name;
					$data['reason']      = "可以正常导入。";
					$data['flag_status'] = 1;
					$data['create_by']   = $_SESSION['username'];
					$r = $db->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data));
					if($r==0){
						$db->insert(array('table'=>'oa_zhsz_users_temp','data'=>$data,'debug'=>1));	
					}
				}
				if(!empty($e)){
					if($e[$field]!=-1){
						//跳过存在的记录
						if($op==2){
							continue;
						}
						//更新数据
						if($op==1){
							$result = $db->update(array('table'=>'oa_zhsz_score','data'=>$data,'where'=>"id={$e['id']}"));
							if(empty($result)){
								//echo "222";
								$error++;
							}
							$a++;
						}
					}else{
						$result = $db->update(array('table'=>'oa_zhsz_score','data'=>$data,'where'=>"id={$e['id']}"));
							if(empty($result)){
								//echo "333";
								$error++;
							}
					}
				}else{
					//添加新数据
					$data['user_id']     = $userId;
					$data['term_id']     = $termId;
					$data['exam_type']   = $et;
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
			}else{
				//等级审核处理
				$levelD['course_name']  = $courseInfo['course_name'];
				$levelD['level']  =  $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
			   
				//查询是否已经存在
				$e = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_course_level','where'=>"user_id='{$userId}' and term_id={$termId} and course_id='{$courseId}'"));
			
				if(!empty($e)){
					//跳过存在的记录
					if($op==2){
						continue;
					}
					//更新数据
					if($op==1){
						$result = $db->update(array('table'=>'oa_zhsz_course_level','data'=>$levelD,'where'=>"id={$e['id']}"));
					  if(empty($result)){
							$error++;
						}
						$a++;
					}
				}else{
					$levelD['user_id']     = $userId;
					$levelD['term_id']     = $termId;
					$levelD['checker_id']   = $_SESSION['uid'];
					$levelD['create_by']   = $_SESSION['username'];
					$levelD['create_time'] = $now;
					
					$result = $db->insert(array('data'=>$levelD,'table'=>'oa_zhsz_course_level'));
					if(empty($result)){
							$error++;
					}
					$a++;
				}
			}
		}
	}
	//exit;
	//插入上传记录
	$data = array(
		'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
		'name' 		  => $dataname,
		'flag_type'   => $flag,
		'file_type'   => 11,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
		
	);
	$db->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	header('Location:../?t=zhsz_import_list');
	exit;
	//数据插入错误
	/* $tips = array(
		   'tips' => "共{$a}条数据，出错{$error}条。",
		   'url'  => './?t=skills_manage'
		  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit; */
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => '../?t=skills_manage'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>