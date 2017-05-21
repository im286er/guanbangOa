<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require '../common.php';

session_start(); 
$uid = $_SESSION['uid'];
//check_session();

$pd = new pdo_mysql();
$filter = new Filter();

$action = empty($_POST['tpl'])?$_GET['tpl']:$_POST['tpl'];

switch($action){ 
	case "del_other_report":
		$id   = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_other_report','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '举报删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '举报删除失败。';
		}
		echo json_encode($returnVal);
		break;
	
	case "check_reply":
		$id  = $filter->filter_number($_POST['id']);
		$val = $filter->filter_number($_POST['val']);
		//审核信息
		$data = array('flag_checked'=>$val);
		$params = array('data'=>$data,'table'=>'oa_zhsz_comment','where'=>"id={$id}");
		if($pd->update($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '操作成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '操作失败。';
		}
	
		echo json_encode($returnVal);
		break;
	// 专业信息库
	case "cate_add_fid":#获取父级分类
		$result = $pd->query("select id,name from major_cate")->fetchAll(PDO::FETCH_ASSOC);
		echo json_encode($result);
		break;
	case "get_level":
		$pid = $_POST['pid'];
		$rs=$pd->query("select level from major_cate where id = {$pid} limit 1");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
	case "get_all_son":
		$pid = $_POST['pid'];
		$rs=$pd->query("select * from major_cate where fid = {$pid}");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;
}
$pd->close();
unset($pd);
unset($rs);