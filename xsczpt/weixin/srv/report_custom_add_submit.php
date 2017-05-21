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
	$subject_id  = Filter::filter_number($_POST['report_type']);
	$subject_son_id  = isset($_POST['rid'])?Filter::filter_number($_POST['rid']):"";
	$subject = $pd->fetchOne(array('field'=>'code_name','table'=>'oa_zhsz_report','where'=>"id={$subject_id} and school={$_SESSION['school']}"));
	
	$subject_son = "";
	if($subject_son_id){
		$subject_son = $pd->fetchOne(array('field'=>'code_name','table'=>'oa_zhsz_report','where'=>"id={$subject_son_id} and school={$_SESSION['school']}"));
		$sub_id = $subject_son_id;
	}else{
		$sub_id = $subject_id;
	}
	
	$is_submit = $pd->fetchOne(array('field'=>'is_submit','table'=>'oa_zhsz_report','where'=>"id={$sub_id} and school={$_SESSION['school']}"));   //判断是否需要提交审核
	
	$content = empty($_POST['content'])?"":$_POST['content'];
	$partner = empty($_POST['partner'])?"":$_POST['partner'];
	$partner_id = empty($_POST['partner_id'])?"":$_POST['partner_id'];
	$task = empty($_POST['task'])?"":$_POST['task'];
	$note = empty($_POST['note'])?"":$_POST['note'];
	$teacher_id = empty($_POST['teacher_id'])?"":$_POST['teacher_id'];
	$path_name = empty($_POST['path_name'])?"":$_POST['path_name'];
	$attFile = (empty($_FILES['att_file']) || !isset($_FILES['att_file']))?array():$_FILES['att_file'];
	$PicUrl = (empty($_POST['PicUrl']) || !isset($_POST['PicUrl']))?array():$_POST['PicUrl'];
	$is_captain  = Filter::filter_number($_POST['is_captain']);
	$now = date('Y-m-d H:i:s');
	
	$item = empty($_POST['item_name'])?array():$_POST['item_name'];
	$item_real_name = empty($_POST['item_real_name'])?array():$_POST['item_real_name'];
	
	$i = 0;
	foreach($item_real_name as  $key => $value){      //拼接自定义项内容，并转化成json格式存储
		//$item[$i] = mb_convert_encoding($item[$i], 'gbk', 'utf-8');
		$item_real_name[$key] = $value.":".base64_encode($item[$i]);
		$i++;
	}
	$cunstom_item = json_encode($item_real_name);
	$cunstom_item = addslashes($cunstom_item);

	//查询当前学期
	$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','start','end'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
	$class_id = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));

	if(!empty($subject_id)&&!empty($content)){
		//添加
		$data = array(
			'subject'	  => $subject,
			'subject_id'	  => $subject_id,
			'subject_son_id'	  => $subject_son_id,
			'subject_son'	  => $subject_son,
			'content'     => $content,
			'partner_id'	  => $partner_id,
			'partner'     => $partner,
			'task'     => $task,
			'items'     => $cunstom_item,
			'teacher_id'     => $teacher_id,
			'note'     => $note,
			'term_id'     => $curTerm['id'],
			'term_year'   => $curTerm['year'],
			'class_id'   => $class_id,
			'is_captain'   => $is_captain,
			'uid'   => $_SESSION['uid'],
			'create_by'   => $_SESSION['username'],
			'create_time' => $now
		);
		$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_report_custom'));
		if(empty($result)){
			//数据插入错误
			$tips = array(
			   'tips' => '添加失败，操作终止。',
			   'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		$custom_id = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report_custom'));   //最后一条插入的数据ID，也就是最新插入的自定义报表数据的ID
		
		//判断是否上传图片
		if(!empty($PicUrl)){
			$i = 1;
			foreach($PicUrl as $rs){
				$data_att = array(
					'attachment'	  => $rs,
					'custom_id'	  => $custom_id,
					'type'	  => 0,
					'create_by'   => $_SESSION['username'],
					'create_time' => $now
				);
				$result_p = $pd->insert(array('data'=>$data_att,'table'=>'oa_zhsz_attachment'));
				if(empty($result_p)){
					//数据插入错误
					$tips = array(
					   'tips' => '第'.$i.'张图片上传失败。附件未上传',
					   'url'  => './?t=report'
					);
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
					exit;
				}
				$i++;
			}
		}
		//判断是否上传文件
		if(!empty($attFile['name'])){
			$fileInfo = array(
				'name'     => $attFile['name'],
				'type'     => $attFile['type'],
				'tmp_name' => $attFile['tmp_name'],
				'error'    => $attFile['error'],
				'size'     => $attFile['size']
			);
			$fileDoc = uploadFile($fileInfo,$_SESSION['uid']);
			$att     = explode('|',$fileDoc);
			if($att[0]!=4){
				//图片上传失败
				$tips = array(
				   'tips' => '文件上传失败，操作终止。',
				   'url'  => '../systeminfo.php'
				);
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
			}
			
			$data_att = array(
				'attachment'	  => $att[1],
				'custom_id'	  => $custom_id,
				'path_name'	  => $path_name,
				'type'	  => 1,
				'create_by'   => $_SESSION['username'],
				'create_time' => $now
			);
			$result_a = $pd->insert(array('data'=>$data_att,'table'=>'oa_zhsz_attachment'));
			if(empty($result_a)){
				//数据插入错误
				$tips = array(
				   'tips' => '附件上传失败。',
				   'url'  => './?t=report'
				);
				$tips = urlencode(serialize($tips));
				header('Location:../tips.php?gets='.$tips);
				exit;
			}
		}
		
		$custom_id_p = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report_custom'));  //最后一条插入的数据ID，也就是最新插入的自定义报表数据的ID
		
		if($teacher_id){
			$geter = $teacher_id;
		}else{
			$geter = $_POST["geter"];
		}
		//写sql插入语句，记录到班主任的消息表
		if($is_submit)
			$rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,subject,from_id) values('".$uid."','".$geter."','".$_POST["content"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','26','".$subject."','".$custom_id_p."')");
	
		
		if($partner_id){   //为合作人添加同样的记录
			$partner_arr = explode(",", $partner_id); 
			$partner_id_new = $partner_id;
			$partner_name_new = $partner;
			
			if($is_captain==1)   //若登录用户是组长，则剩下都是组员
				$is_captain = 0;
			
			foreach($partner_arr as $rs){
				$partner_name = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$rs}'"));
				$partner_id_temp = str_replace($rs,$_SESSION['uid'],$partner_id_new);
				
				$partner_name_temp = str_replace($partner_name,$_SESSION['truename'],$partner_name_new);
				$class_id = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$rs}'"));
				$data = array(
					'subject'	  => $subject,
					'subject_id'	  => $subject_id,
					'subject_son_id'	  => $subject_son_id,
					'subject_son'	  => $subject_son,
					'content'     => $content,
					'partner_id'	  => $partner_id_temp,
					'partner'     => $partner_name_temp,
					'task'     => $task,
					'teacher_id'     => $teacher_id,
					'items'     => $cunstom_item,
					'note'     => $note,
					'term_id'     => $curTerm['id'],
					'class_id'     => $class_id,
					'term_year'   => $curTerm['year'],
					'is_captain'   => $is_captain,
					'uid'   => $rs,
					'create_by'   => $_SESSION['username'],
					'create_time' => $now
				);
				
				$result = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_report_custom'));
				if(empty($result)){
					//数据插入错误
					$tips = array(
					   'tips' => '合作人记录添加失败，操作终止。',
					   'url'  => './?t=report'
					);
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
					exit;
				}
				
				$custom_id_p = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report_custom'));   //最后一条插入的数据ID，也就是最新插入的自定义报表数据的ID
				
				//判断是否上传图片
				if(!empty($PicUrl)){
					$i = 1;
					foreach($PicUrl as $rs1){
						$data_att = array(
							'attachment'	  => $rs1,
							'custom_id'	  => $custom_id_p,
							'type'	  => 0,
							'create_by'   => $_SESSION['username'],
							'create_time' => $now
						);
						$result_p = $pd->insert(array('data'=>$data_att,'table'=>'oa_zhsz_attachment'));
						if(empty($result_p)){
							//数据插入错误
							$tips = array(
							   'tips' => '合作人第'.$i.'张图片上传失败。附件未上传',
							   'url'  => './?t=report'
							);
							$tips = urlencode(serialize($tips));
							header('Location:../tips.php?gets='.$tips);
							exit;
						}
						$i++;
					}
				}
				//判断是否上传文件
				if(!empty($attFile['name'])){
					$data_att = array(
						'attachment'	  => $att[1],
						'custom_id'	  => $custom_id_p,
						'path_name'	  => $path_name,
						'type'	  => 1,
						'create_by'   => $_SESSION['username'],
						'create_time' => $now
					);
					$result_a = $pd->insert(array('data'=>$data_att,'table'=>'oa_zhsz_attachment'));
					if(empty($result_a)){
						//数据插入错误
						$tips = array(
						   'tips' => '合作人附件上传失败。',
						   'url'  => './?t=report'
						);
						$tips = urlencode(serialize($tips));
						header('Location:../tips.php?gets='.$tips);
						exit;
					}
				}
				$custom_id_p = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report_custom'));
				//写sql插入语句，记录到班主任的消息表
				if($is_submit)
					$rs2=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,subject,from_id) values('".$rs."','".$geter."','".$_POST["content"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','26','".$_POST["subject"]."','".$custom_id_p."')");
			}
		}
	}else{
		$tips = array(
		   'tips' => '添加失败。',
		   'url'  => './?t=report'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	$tips = array(
	   'tips' => '添加成功。',
	   'url'  => './?t=report'
	);
	
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
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