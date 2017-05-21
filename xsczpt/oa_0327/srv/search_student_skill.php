<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

session_start(); 

$pd = new pdo_mysql();
$filter = new Filter();

//是否为班主任
$where = '';
/*$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
if(!empty($class)){
	$cid = $class['id'];
	$gid = $class['grade_id'];
	$where = " and B.dept={$cid} ";
}*/

$truename = Filter::filter_html($_GET['q']);

$student  = $pd->query("SELECT A.id,A.truename,A.username FROM act_member as A inner join oa_user_extend as B ON A.id=B.userid WHERE B.flag_type=1 and B.flag_status=1 {$where} and A.truename LIKE '{$truename}%'  LIMIT 0,20")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($student);
	
$pd->close();
unset($pd);
unset($rs);