<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
date_default_timezone_set("PRC");
//上传excel文件
include('upload.php');

$pd = new pdo_mysql();

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
				
		$flag = empty($_POST['flag'])?8:Filter::filter_number($_POST['flag']);
		
		$dataname = '任课信息记录';
		//读取excel文件内容
		$objReader     = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($fileName);
		$sheet         = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); // 取得总行数
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
		
		$a     = 0;
		$error = 0;
		
		$now = date('Y-m-d H:i:s',time());
		/* echo "123";
		exit; */
		//循环读取excel文件,读取一条,插入一条
		for($j=2;$j<=$highestRow;$j++){	
			$data = array();
			$data['create_time'] = $now;
			$data['create_by']   = $uid;
			$username            = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
			$data['course_name'] = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
			$classIds            = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
				
			//$a++;
            /* print_r($classIds);
            exit; */			
			
			//必须填字段有一空则退出
			if(empty($username)||empty($data['course_name'])||empty($classIds)){
				/* echo "123";
				exit; */
				$error++;
				continue;
			}
			
			//查询老师是否存在
			$params = array('field'=>array('id','truename'),'table'=>'v_users','where'=>"truename='{$username}' and flag_type=2");

			$user   = $pd->fetchRow($params);
			/* print_r($user);
            exit; */
			/* print_r($user);
            exit; */
			if(empty($user)){
				$error++;
				continue;	
			}
			
			$data['user_id']  = $user['id'];
			$data['truename'] = $user['truename'];
			
			//查询课程信息
			
			$params   = array('field'=>'id','table'=>'oa_zhsz_course','where'=>"course_name='{$data['course_name']}'");
			
			$courseId = $pd->fetchOne($params);
			
			if(empty($courseId)){
				$error++;
				continue;	
			}
			//echo "456";
			$data['course_id'] = $courseId;
			
			//查询班级信息
			$classA = explode(',',$classIds);
			
			//echo "789";
			foreach($classA as $rsb){
				//查询班级是否存在
				$params    = array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rsb}");
				$classInfo = $pd->fetchRow($params);
				$data['school']   = $classInfo['school'];
				if(empty($classInfo)){
					continue;
				}else{
					$gradeId = $classInfo['grade_id'];
					$gradeName = '';
					
					if($gradeId==1){
						$gradeName = '高一';	
					}
					if($gradeId==2){
						$gradeName = '高二';	
					}
					if($gradeId==3){
						$gradeName = '高三';	
					}
					$data['class_id']   = $rsb;
					$data['class_name'] = $gradeName.$classInfo['class_name'];
				}
				/* echo "123";
				exit; */
				//判断是否已经存在
				$e = $pd->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_teach','where'=>"user_id='{$data['user_id']}' and class_id={$data['class_id']} and course_id={$data['course_id']}"));
				if($e==0){
					$count=$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_teach'));
					if($count>0){
						$a++;
					}
				}
			}
		}
		
		//插入上传记录
		$data = array(
					'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['admin_id'].'/'.$file,
					'name' 		  => $dataname,
					'flag_type'   => $flag,
					'file_type'   => 8,
					'create_by'   => $uid,
					'create_time' => date('Y-m-d H:i:s',time())
					
				  );
		$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
		//数据插入错误
		$tips = array(
			   'tips' => "共{$a}条数据，出错{$error}条。",
			   'url'  => '/oa/?t=teach_manage'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		//提交方式出错
		$tips = array(
				   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
				   'url'  => '/oa/?t=teach_manage'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}
?>