<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

$db = new pdo_mysql();
$filter = new Filter();
//判断用户是否登录
session_start();
if(empty($_SESSION['admin_name'])){
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
    
	//是否为班主任
	$isMaster = false;
	$class = $db->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master={$_SESSION['admin_id']} and grade_id<>0"));
	
	$gid  = empty($_POST['gid'])?0:Filter::filter_number($_POST['gid']);
	$cid  = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
	$tid  = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id']);
	$et   = empty($_POST['exam_type'])?'':Filter::filter_html($_POST['exam_type']);
	
	if(!empty($class)){
		$cid = $class['id'];
		$gid = $class['grade_id'];
		$isMaster = true;
	}
	
	//导出字段
	$truename  = empty($_POST['truename'])?'':Filter::filter_html($_POST['truename']);
	$userCard  = empty($_POST['user_card'])?'':Filter::filter_html($_POST['user_card']);
	$classN    = empty($_POST['class_name'])?'':Filter::filter_html($_POST['class_name']);
	$yw        = empty($_POST['yw'])?'':Filter::filter_number($_POST['yw']);
	$sx        = empty($_POST['sx'])?'':Filter::filter_number($_POST['sx']);
	$wy        = empty($_POST['wy'])?'':Filter::filter_number($_POST['wy']);
	$wl        = empty($_POST['wl'])?'':Filter::filter_number($_POST['wl']);
	$hx        = empty($_POST['hx'])?'':Filter::filter_number($_POST['hx']);
	$sw        = empty($_POST['sw'])?'':Filter::filter_number($_POST['sw']);
	$ls        = empty($_POST['ls'])?'':Filter::filter_number($_POST['ls']);
	$dl        = empty($_POST['dl'])?'':Filter::filter_number($_POST['dl']);
	$zz        = empty($_POST['zz'])?'':Filter::filter_number($_POST['zz']);
	$xxjs      = empty($_POST['xxjs'])?'':Filter::filter_number($_POST['xxjs']);
	$tyjs      = empty($_POST['tyjs'])?'':Filter::filter_number($_POST['tyjs']);
	$ty        = empty($_POST['ty'])?'':Filter::filter_number($_POST['ty']);
	$yy        = empty($_POST['yy'])?'':Filter::filter_number($_POST['yy']);
	$ms        = empty($_POST['ms'])?'':Filter::filter_number($_POST['ms']);
	$xl        = empty($_POST['xl'])?'':Filter::filter_number($_POST['xl']);
	$xx        = empty($_POST['xx'])?'':Filter::filter_number($_POST['xx']);
	$szf       = empty($_POST['szf'])?'':Filter::filter_number($_POST['szf']);
	$scOrder   = empty($_POST['sc_order'])?'':Filter::filter_number($_POST['sc_order']);
	$sgOrder   = empty($_POST['sg_order'])?'':Filter::filter_number($_POST['sg_order']);
	
	//查询当前学期
	$curTerm = $db->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>"id={$tid}"));

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
	$termType = $curTerm['term_type']==1?'第一学期':'第二学期';
	$dataname = str_replace('/','-',$curTerm['year']).'('.$termType.')'.$gradeName.$className.$et.'成绩';
	$PHPExcel->setActiveSheetIndex(0);  
	$PHPExcel->getActiveSheet()->setTitle($dataname);  
	$PHPExcel->getDefaultStyle()->getFont()->setName('宋体');  
	$PHPExcel->getDefaultStyle()->getFont()->setSize(11);
	$exportFiled = array();
	$exportData  = array();
	//设置标题栏
	if(!empty($truename)){
		$exportFiled['truename'] = '姓名';
	}
	if(!empty($userCard)){
		$exportFiled['user_card'] = '身份证';
	}
	if(!empty($classN)){
		$exportFiled['class_name'] = '班级';
	}
	if($yw!==''){
		$exportFiled['yw'] = '语文';
	}
	if($sx!==''){
		$exportFiled['sx'] = '数学';
	}
	if($wy!==''){
		$exportFiled['wy'] = '外语';
	}
	if($wl!==''){
		$exportFiled['wl'] = '物理';
	}
	if($hx!==''){
		$exportFiled['hx'] = '化学';
	}
	if($sw!==''){
		$exportFiled['sw'] = '生物';
	}
	if($zz!==''){
		$exportFiled['zz'] = '政治';
	}
	if($ls!==''){
		$exportFiled['ls'] = '历史';
	}
	if($dl!==''){
		$exportFiled['dl'] = '地理';
	}
	if($xxjs!==''){
		$exportFiled['xxjs'] = '信息技术';
	}
	if($tyjs!==''){
		$exportFiled['tyjs'] = '通用技术';
	}
	if($ty!==''){
		$exportFiled['ty'] = '体育';
	}
	if($yy!==''){
		$exportFiled['yy'] = '音乐';
	}
	if($ms!==''){
		$exportFiled['ms'] = '美术';
	}
	if($xl!==''){
		$exportFiled['xl'] = '心理';
	}
	if($xx!==''){
		$exportFiled['xx'] = '选修';
	}
	if($szf!==''){
		$exportFiled['szf'] = '三门总分';
	}
	if($scOrder!==''){
		$exportFiled['sc_order'] = '三门班级名次';
	}
	if($sgOrder!==''){
		$exportFiled['sg_order'] = '三门年级名次';
	}
	
	$i = 65;
	//生成标题行
	foreach($exportFiled as $k=>$v){
		$c = chr($i);
		$PHPExcel->getActiveSheet()->setCellValue("{$c}1",$v);
		$exportData[$c] = $k;
		$i++;
	}

	$where = '';
	if(!empty($cid)){
		$where .= " and A.class_id={$cid} ";
	}else{
		if(!empty($gid)){
			$where .= " and A.grade_id={$gid} ";
		}
	}
	if(!empty($tid)){
		$where .= " and A.term_id={$tid} ";
	}
	if(!empty($et)){
		$where .= " and A.exam_type='{$et}' ";
	}
	
	//查询成绩记录
  $scoreAll = $db->query("select A.*,B.user_card,B.truename from oa_zhsz_score AS A,oa_zhsz_users AS B where A.user_id=B.id {$where}")->fetchAll(PDO::FETCH_ASSOC);
	
	$j = 2;
	
	$col = count($exportData);
	foreach($scoreAll as $rs){
		$i = 65;
		for($t=0;$t<$col;$t++){
			$c = chr($i);
			if($exportData[$c]=='user_card'){
				$PHPExcel->getActiveSheet()->setCellValue("{$c}{$j}",' '.$rs[$exportData[$c]]);
			}else{
				if($rs[$exportData[$c]]==-1){
					$rs[$exportData[$c]] = '';
				}
				$PHPExcel->getActiveSheet()->setCellValue("{$c}{$j}",$rs[$exportData[$c]]);
			}
			$i++;
		}
		$j++;
	}
	$filename  = date('YmdHis').'_'.$_SESSION['admin_id'];
	$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel5');
	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['admin_id'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['admin_id'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['admin_id'].'/'.$filename.'.xls';
	$r = $objWriter->save($fileName);
	//插入下载文件到下载表中
	$data = array(
					'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['admin_id'].'/'.$filename.'.xls',
					'name' 		  => $dataname,
					'flag_type'   => 11,
					'create_by'   => $_SESSION['admin_name'],
					'create_time' => date('Y-m-d H:i:s')
					
				  );
	$db->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$title.'.xls');
	exit;
}else{
	//提交方式出错
	$tips = array(
			   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
			   'url'  => 'index.php'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>