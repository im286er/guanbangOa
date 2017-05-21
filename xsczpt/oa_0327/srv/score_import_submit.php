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
		$fileDoc = uploadFile($_FILES['table_file'],$uid);
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
	$curTerm   = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	/* print_r($curTerm);
	exit; */
	
	$flag = empty($_POST['flag'])?4:Filter::filter_number($_POST['flag']);
	$gid  = empty($_POST['gid'])?0:Filter::filter_number($_POST['gid']);
	$cid  = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
	$et   = empty($_POST['exam_type'])?0:Filter::filter_html($_POST['exam_type']);
	$op   = empty($_POST['repeat_op'])?2:Filter::filter_number($_POST['repeat_op']);
	
	$className = '';
	if($gid==0){
		$gradeName = '所有学生';
	}else{
		$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		//查询班级
		if(!empty($cid)){
			$className = $pd->fetchOne(array('field'=>'class_name','table'=>'oa_zhsz_class','where'=>"id={$cid}"));	
		}
	}
	$termType  = $curTerm['term_type']==1?'第一学期':'第二学期';
	$dataname = $gradeName.$className.'('.$termType.')成绩记录';
	//读取excel文件内容
	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	$objPHPExcel   = $objReader->load($fileName);
	$sheet         = $objPHPExcel->getSheet(0);
	$highestRow    = $sheet->getHighestRow(); // 取得总行数
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	$a     = 0;
	$error = 0;
	$termId  = $curTerm['id'];
	$now = date('Y-m-d H:i:s',time());
	
	//循环读取excel文件,读取一条,插入一条
	for($j=2;$j<=$highestRow;$j++){
        $usercard = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
		$usercard = trim($usercard);
		
		$user     = $pd->fetchRow(array('field'=>array('dept','id'),'table'=>'v_users','where'=>"cardno='{$usercard}'"));
		$userId   = $user['id'];
		$yw  	  = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
		$sx 	  = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
		$wy    	  = $objPHPExcel->getActiveSheet()->getCell("E{$j}")->getValue();
		$wl    	  = $objPHPExcel->getActiveSheet()->getCell("F{$j}")->getValue();
		$hx   	  = $objPHPExcel->getActiveSheet()->getCell("G{$j}")->getValue();
		$sw    	  = $objPHPExcel->getActiveSheet()->getCell("H{$j}")->getValue();
		$zz  	  = $objPHPExcel->getActiveSheet()->getCell("I{$j}")->getValue();
		$ls    	  = $objPHPExcel->getActiveSheet()->getCell("J{$j}")->getValue();
		$dl   	  = $objPHPExcel->getActiveSheet()->getCell("K{$j}")->getValue();
		$xxjs     = $objPHPExcel->getActiveSheet()->getCell("L{$j}")->getValue();
		//$tyjs     = $objPHPExcel->getActiveSheet()->getCell("M{$j}")->getValue();
		//$ty  	  = $objPHPExcel->getActiveSheet()->getCell("N{$j}")->getValue();
		//$yy   	  = $objPHPExcel->getActiveSheet()->getCell("O{$j}")->getValue();
		//$ms   	  = $objPHPExcel->getActiveSheet()->getCell("P{$j}")->getValue();
		//$xl   	  = $objPHPExcel->getActiveSheet()->getCell("Q{$j}")->getValue();
		$szf   	  = $objPHPExcel->getActiveSheet()->getCell("M{$j}")->getValue();
		$scOrder  = $objPHPExcel->getActiveSheet()->getCell("N{$j}")->getValue();
		$sgOrder  = $objPHPExcel->getActiveSheet()->getCell("O{$j}")->getValue();
		
		if(empty($szf)){
			$szf = $yw + $sx + $wy;
		}
		$jzf = $szf + $wl + $hx + $sw + $zz + $ls + $dl;
		
		//查询记录是否存在
		$e = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$userId}' and term_id={$termId} and exam_type='{$et}'"));
		
		if(!empty($e)){
			//跳过存在的记录
			if($op==2){
				continue;
			}
			//更新数据
			if($op==1){
				//$pd->delete(array('table'=>'zhsz_score','where'=>"user_id={$userId} and term_id={$termId} and exam_type='{$et}'"));
				//更新数据
				$data = array(
								'yw'          => intval($yw),
								'sx'          => intval($sx),
								'wy'          => intval($wy),
								'wl'          => intval($wl),
								'hx'          => intval($hx),
								'sw'          => intval($sw),
								'zz'          => intval($zz),
								'ls'          => intval($ls),
								'dl'          => intval($dl),
								'xxjs'        => $xxjs,
								//'tyjs'        => $tyjs,
								//'ty'          => $ty,
								//'yy'          => $yy,
								//'ms'          => $ms,
								//'xl'          => $xl,
								'szf'         => intval($szf),
								'jzf'		  => intval($jzf),
								'sc_order'    => empty($scOrder)?-1:intval($scOrder),
								'sg_order'    => empty($sgOrder)?-1:intval($sgOrder)
							 );
							 
				$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_score','where'=>"id={$e['id']}"));
				
				if(empty($result)){
					$error++;
				}
			}
		}else{
			
			$user   = $pd->fetchRow(array('field'=>array('dept','id','truename'),'table'=>'v_users','where'=>" id='{$userId}' "));
			if(empty($user['dept'])){
				$error++;
				continue;
			}
			$userId   = $user['id'];
			$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>" id={$user['dept']} "));
			
			
			$grade    = $pd->fetchRow(array('field'=>array('id','grade_name'),'table'=>'oa_zhsz_grade','where'=>"id='{$classD['grade_id']}'"));
			//添加数据
			$data = array(
							'user_id'     => $userId,
							'term_id'     => $termId,
							'exam_type'   => $et,
							'grade_id'    => $classD['grade_id'],
							'class_id'    => $user['dept'],
							'class_name'  =>  $grade['grade_name'].$classD['class_name'],
							'yw'          => intval($yw),
							'sx'          => intval($sx),
							'wy'          => intval($wy),
							'wl'          => intval($wl),
							'hx'          => intval($hx),
							'sw'          => intval($sw),
							'zz'          => intval($zz),
							'ls'          => intval($ls),
							'dl'          => intval($dl),
							'xxjs'        => $xxjs,
							//'tyjs'        => $tyjs,
							//'ty'          => $ty,
							//'yy'          => $yy,
							//'ms'          => $ms,
							//'xl'          => $xl,
							'szf'         => intval($szf),
							'jzf'		  => intval($jzf),
							'sc_order'    => empty($scOrder)?-1:intval($scOrder),
							'sg_order'    => empty($sgOrder)?-1:intval($sgOrder),
							'create_by'   => $_SESSION['username'],
							'create_time' => $now
						 );
			$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_score'));
			if(empty($result)){
				$error++;
			}
		}
		$a++;
		
	}
	
	//插入上传记录
	$data = array(
				'file' 		  => '/oa/uploadfiles/attachment/'.$uid.'/'.$file,
				'name' 		  => $dataname,
				'flag_type'   => $flag,
				'file_type'   => 4,
				'create_by'   => $_SESSION['username'],
				'create_time' => date('Y-m-d H:i:s',time())
				
			  );
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	
	//数据插入错误
	$tips = array(
		   'tips' => "共{$a}条数据，出错{$error}条。",
		   'url'  => '/oa/?t=score_manage'
		  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
	exit;
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => '/oa/?t=score_import'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>