<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

session_start(); 

$pd = new pdo_mysql();
$filter = new Filter();

$truename = Filter::filter_html($_GET['q']);

$teacher  = $pd->query("SELECT A.id,A.truename,A.username FROM act_member as A inner join oa_user_extend as B ON A.id=B.userid WHERE B.flag_type=2 and B.flag_status=1 and A.truename LIKE '{$truename}%'   LIMIT 0,20")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($teacher);
	
$pd->close();
unset($pd);
unset($rs);