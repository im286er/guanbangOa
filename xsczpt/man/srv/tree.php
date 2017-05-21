<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
$wh = "";
if(!empty($type)){
  $wh = " and push_type=".$type;
}

$pd=new pdo_mysql();
$tbl = $_GET['tpl'];

$result = array();
$rs=$pd->query("select id,name,childnums,pid,odx from `".$tbl."` where pid=".$id.$wh." order by odx desc");
while($row=$rs->fetch(PDO::FETCH_ASSOC)){
	$node = array();
	$node['id'] = $row['id'];
	$node['text'] = $row['name'];	
	$node['state'] =$row['childnums'] ? 'closed' : 'open'; ;#has_child($pd,$row['id']) ? 'closed' : 'open';
	array_push($result,$node);
}
echo json_encode($result);

#²»Ê¹ÓÃ
function has_child($pd,$id){
	#$rs = mysql_query("select count(*) from nodes where parentId=$id");
	$pd->query("select count(1) from blog_t where pid=".$id)->fetchColumn(0);
	#$row = mysql_fetch_array($rs);
	return $pd->query("select count(1) from blog_t where pid=".$id)->fetchColumn(0) > 0 ? true : false;
}