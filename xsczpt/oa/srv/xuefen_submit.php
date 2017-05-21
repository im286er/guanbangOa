<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require("../../ppf/pdo_template.php");
require('../common.php');

session_start(); 

$pd = new pdo_mysql();
$filter = new Filter();
$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
	//查询当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
	
	$et  = empty($_POST['exam_type'])?'期末':Filter::filter_html($_POST['exam_type']);
	
	$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
	$tid = empty($_POST['term_id'])?intval($curTerm['id']):intval($_POST['term_id']);
	
	//查询当前学期期末成绩
	$etScore = $pd->query("select * from oa_zhsz_score where term_id='{$tid}' and exam_type='{$et}' and grade_id={$gid}")->fetchAll();
	//查询课程
	$course = $pd->query("select * from oa_zhsz_course where is_print=1 and school={$_SESSION['school']} order by order_value")->fetchAll();

	$scoreData = array();
	
	foreach($course as $rs){
		$xf = $_POST["{$rs['course_code']}_xf"];
		$sc = $_POST[$rs['course_code']];
		$scoreData[$rs['course_code']] = array('score'=>$sc,'xf'=>$xf);
	}

	$ac = count($etScore);
	$t  = 0;
	$e  = 0;

	foreach($etScore as $rs){
		$data = array();
		foreach($scoreData as $key=>$val){
			$val['score'] = trim($val['score']);
			//判断是数字还是字符
			if(isset($rs[$key])){
				if(preg_match('/([a-zA-Z\+])+/',$rs[$key])){
					$rs[$key] = strtoupper($rs[$key]);
					$rs[$key] = substr($rs[$key],0,1);
					if($rs[$key]<=$val['score']){
						$data["{$key}_xf"] = $val['xf'];
					}
				}else{
					$val['score'] = intval($val['score']);
					if($rs[$key]>=$val['score']){
						$data["{$key}_xf"] = $val['xf'];
					}
				}
			}
		}
		
		$r = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_score','where'=>"id={$rs['id']}"));
		if(empty($r)){
			$e++;	
		}
		$t++;
	}
	$tips = array(
	   'tips' => "共更新{$t}条记录，出错{$e}条。",
	   'url'  => './?t=xuefen'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
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