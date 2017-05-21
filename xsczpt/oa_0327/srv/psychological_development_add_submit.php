<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');
require('upload.php');
date_default_timezone_set("PRC");
$pd = new pdo_mysql();
$filter = new Filter();

//p($_FILES);exit;
//判断用户是否登录
if (!session_id()) session_start(); 
chkLogin("uid","/?t=login");  
$uid=$_SESSION['uid'];
if(empty($_SESSION['uid'])){
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
	$addtype=isset($_POST['addtype'])?$_POST['addtype']:'';
	$do=isset($_POST['do'])?$_POST['do']:'';
	if(empty($addtype)||empty($do)){
		$tips = array(
	        'tips' => '参数错误，请重新提交！',
			'url'  => './?t=psychological_development_add&addtype='.$addtype
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	//查询当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
	$grade_id = empty($_POST['grade_id'])?$curTerm['id']:$_POST['grade_id'];
	$itype = (count(@$_POST['itype'])<0)?array():@$_POST['itype'];
	$feature = (count(@$_POST['feature'])<0)?array():@$_POST['feature'];
	$path_name = empty($_POST['path_name'])?"":$_POST['path_name'];
	$title = empty($_POST['title'])?"":htmlspecialchars($_POST['title'],ENT_QUOTES);
	$content = empty($_POST['content'])?"":htmlspecialchars($_POST['content'],ENT_QUOTES);
    $attFile = (empty($_FILES['att_file']) || !isset($_FILES['att_file']))?array():$_FILES['att_file'];
	$id = empty($_POST['id'])?'':$_POST['id'];
	$table="";$data="";$result="";
	switch($addtype){
		case "evaluation":
		    $file_path=empty($_POST['file'])?'':$_POST['file'];
		    if($do=='m')$pd->delete(array('table'=>'oa_psychological_test','where'=>'grade_id='.$grade_id));
			//判断是否上传文件
			if(!empty($attFile['name'])){
				$fileInfo = array(
					'name'     => $attFile['name'],
					'type'     => $attFile['type'],
					'tmp_name' => $attFile['tmp_name'],
					'error'    => $attFile['error'],
					'size'     => $attFile['size']
				);
				//p($attFile);exit;
				$fileDoc = uploadpdf($fileInfo,$_SESSION['uid']);//p($fileDoc);exit;
				$att     = explode('|',$fileDoc);
				if($att[0]!=4){
					$tips = array(
					   'tips' => '文件上传失败，操作终止。',
					   'url'  => '../systeminfo.php'
					);
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
					exit;
				}
				$file_path=$att[1];
				
			}
			foreach($itype as $k=>$v){
				$data = array(
					'uid'	  => $uid,
					'type_name'	  => htmlspecialchars($itype[$k],ENT_QUOTES),
					'feature'	  => htmlspecialchars($feature[$k],ENT_QUOTES),
					'file'	  => $file_path,
					'path_name' => $path_name,
					'grade_id'	  => $grade_id,
					'timestamp'     => time()
				);
				
			    $result = $pd->insert(array('data'=>$data,'table'=>'oa_psychological_test'));
				
			}
		    //p($result);exit;
		    break;
	
		case "sensibility":
		    $data = array(
				'uid'	  => $uid,
				'title'	  => $title,
				'content'	  => $content,
				'grade_id'	  => $grade_id,
				'timestamp'     => time()
			);
			if($do=='a'){
			    $result = $pd->insert(array('data'=>$data,'table'=>'oa_psychological_story'));	
			}else{
				$where = "id={$id}";
			    $result = $pd->update(array('data'=>$data,'table'=>'oa_psychological_story','where'=>$where));	
			}
		    break;
	}
		//p($result);exit;
	if(empty($result)){
		//数据插入错误
		if($do=='a'){
			$tips = array(
			   'tips' => '添加失败，操作终止。',
			   'url'  => './?t=psychological_development_add&addtype='.$addtype
			);
		}else{
			$tips = array(
			   'tips' => '修改失败，操作终止。',
			   'url'  => './?t=psychological_development_add&addtype='.$addtype.'&do='.$do.'&id='.$id.'&grade_id='.$grade_id
			);
		}
		
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	} 
	$tips = array(
	   'tips' => '添加成功。',
	   'url'  => './?t=psychological_development&addtype='.$addtype
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
?>