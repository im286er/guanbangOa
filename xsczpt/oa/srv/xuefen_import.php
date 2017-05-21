<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require("../../ppf/pdo_template.php");
require('../common.php');
require('upload.php');
require('../plugins/PHPExcel.php');


session_start(); 
//$uid = $_SESSION['uid'];

$pd = new pdo_mysql();
$db = new pdo_template();
$filter = new Filter();

$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
	
	$flag = empty($_POST['flag'])?15:Filter::filter_number($_POST['flag']);
	$op   = empty($_POST['op'])?'':Filter::filter_number($_POST['op']);

	//导入班主任信息操作
	if($op=='import'){
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
				
		$dataname = '补考学分信息';
		//读取excel文件内容
		$objReader     = PHPExcel_IOFactory::createReader('Excel5');
		$objPHPExcel   = $objReader->load($fileName);
		$sheet         = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow(); // 取得总行数
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
		
		$a     = 0;
		$error = 0;
		
		//循环读取excel文件,读取一条,插入一条
		for($j=2;$j<=$highestRow;$j++){
			$a++;
			$data = array();
			
			$username = $objPHPExcel->getActiveSheet()->getCell("A{$j}")->getValue();
			$username = strtoupper($username);
			$username = trim($username);
			$termId   = $objPHPExcel->getActiveSheet()->getCell("B{$j}")->getValue();
			$examType = $objPHPExcel->getActiveSheet()->getCell("C{$j}")->getValue();
			$yw  	  = $objPHPExcel->getActiveSheet()->getCell("D{$j}")->getValue();
			$sx 	  = $objPHPExcel->getActiveSheet()->getCell("E{$j}")->getValue();
			$wy    	  = $objPHPExcel->getActiveSheet()->getCell("F{$j}")->getValue();
			$wl    	  = $objPHPExcel->getActiveSheet()->getCell("G{$j}")->getValue();
			$hx   	  = $objPHPExcel->getActiveSheet()->getCell("H{$j}")->getValue();
			$sw    	  = $objPHPExcel->getActiveSheet()->getCell("I{$j}")->getValue();
			$zz  	  = $objPHPExcel->getActiveSheet()->getCell("J{$j}")->getValue();
			$ls    	  = $objPHPExcel->getActiveSheet()->getCell("K{$j}")->getValue();
			$dl   	  = $objPHPExcel->getActiveSheet()->getCell("L{$j}")->getValue();
			$xxjs     = $objPHPExcel->getActiveSheet()->getCell("M{$j}")->getValue();
			$ty  	  = $objPHPExcel->getActiveSheet()->getCell("N{$j}")->getValue();
			$yy   	  = $objPHPExcel->getActiveSheet()->getCell("O{$j}")->getValue();
			$ms   	  = $objPHPExcel->getActiveSheet()->getCell("P{$j}")->getValue();
			
			if(empty($username)||empty($termId)||empty($examType)){
				$error++;
				continue;
			}
			
			if(!is_null($yw)){
				$data['yw_xf'] = $yw;
			}
			if(!is_null($sx)){
				$data['sx_xf'] = $sx;
			}
			if(!is_null($wy)){
				$data['wy_xf'] = $wy;
			}
			if(!is_null($wl)){
				$data['wl_xf'] = $wl;
			}
			if(!is_null($hx)){
				$data['hx_xf'] = $hx;
			}
			if(!is_null($sw)){
				$data['sw_xf'] = $sw;
			}
			if(!is_null($ls)){
				$data['ls_xf'] = $ls;
			}
			if(!is_null($dl)){
				$data['dl_xf'] = $dl;
			}
			if(!is_null($zz)){
				$data['zz_xf'] = $zz;
			}
			if(!is_null($xxjs)){
				$data['xxjs_xf'] = $xxjs;
			}
			if(!is_null($ty)){
				$data['ty_xf'] = $ty;
			}
			if(!is_null($yy)){
				$data['yy_xf'] = $yy;
			}
			if(!is_null($ms)){
				$data['ms_xf'] = $ms;
			}
			
			$userId = $pd->fetchOne(array('field'=>'id','table'=>'act_member','where'=>"username='{$username}'"));
			$where = "user_id='{$userId}' and term_id={$termId} and exam_type='{$examType}'";

			//查询之前信息是否存在
			$params = array('field'=>'id','table'=>'oa_zhsz_score','where'=>$where);
			$has = $pd->fetchOne($params);
			
			//信息不存在
			if(empty($has)){
				$error++;
				continue;
			}
			
			if(!empty($data)){
				$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_score','where'=>$where));
				if(empty($result)){
					$error++;
				}
			}
		}
		
		//插入上传记录
		$data = array(
			'file' 		  => '/oa/uploadfiles/attachment/'.$_SESSION['uid'].'/'.$file,
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
			   'url'  => './?t=xuefen_import'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => 'index.php'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}

$pd->close();
unset($pd);
unset($rs);