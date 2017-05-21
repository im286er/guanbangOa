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
session_start();
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
	$id	= Filter::filter_number($_POST['id']);
	$subject_id  = Filter::filter_number($_POST['report_type']);
	$subject_son_id  = isset($_POST['rid'])?Filter::filter_number($_POST['rid']):"";
	$flag_old = empty($_POST['flag_old'])?"待审核":$_POST['flag_old'];
	$content = empty($_POST['content'])?"":$_POST['content'];
	$partner = empty($_POST['partner'])?"":$_POST['partner'];
	$partner_id = empty($_POST['partner_id'])?"":$_POST['partner_id'];
	$if_partner = empty($_POST['if_partner'])?0:$_POST['if_partner'];
	$task = empty($_POST['task'])?"":$_POST['task'];
	$note = empty($_POST['note'])?"":$_POST['note'];
	$path_name = empty($_POST['path_name'])?"":$_POST['path_name'];
	$attFile = (empty($_FILES['att_file']) || !isset($_FILES['att_file']))?array():$_FILES['att_file'];
	$PicUrl = (empty($_POST['PicUrl']) || !isset($_POST['PicUrl']))?array():$_POST['PicUrl'];
	$is_captain  = Filter::filter_number($_POST['is_captain']);
	$now = date('Y-m-d H:i:s');
	$path_name = str_replace("\\","\\\\",$path_name);  //存数据库一个斜杠会消失掉
	$teacher_id = empty($_POST['teacher_id'])?"":$_POST['teacher_id'];
	$item = empty($_POST['item_name'])?array():$_POST['item_name'];
	$item_real_name = empty($_POST['item_real_name'])?array():$_POST['item_real_name'];
	
	if($subject_son_id){
		$sub_id = $subject_son_id;
	}else{
		$sub_id = $subject_id;
	}
	
	$is_submit = $pd->fetchOne(array('field'=>'is_submit','table'=>'oa_zhsz_report','where'=>"id={$sub_id} and school={$_SESSION['school']}"));   //判断是否需要提交审核
	
	$i = 0;
	foreach($item_real_name as  $key => $value){      //拼接自定义项内容，并转化成json格式存储
		//$item[$i] = mb_convert_encoding($item[$i], 'gbk', 'utf-8');
		$item_real_name[$key] = $value.":".base64_encode($item[$i]);
		$i++;
	}
	$cunstom_item = json_encode($item_real_name);
	$cunstom_item = addslashes($cunstom_item);
	
	if(!empty($subject_id)&&!empty($content)){
		$class_id = $pd->fetchOne(array('field'=>'dept','table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'"));
		//修改
		$data = array(
			'content'     => $content,
			'partner_id'	  => $partner_id,
			'partner'     => $partner,
			'task'     => $task,
			'items'     => $cunstom_item,
			'class_id'     => $class_id,
			'note'     => $note,
			'is_captain'   => $is_captain,
		);
		$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_report_custom','where'=>"id={$id}"));

		$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$_SESSION['dept']}"));
		$master_val=$class['class_master'];
		if($teacher_id){
			$master_val = $teacher_id;
		}
		$flag_old =$pd->db->query("select flag_status from oa_zhsz_report_custom where id=".$id)->fetchColumn(0);

		if($flag_old=='未通过'){
			$data = array(
				'flag_status'     => "待审核"
			);
			$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_report_custom','where'=>"id={$id}"));
			if($is_submit)
				$rs=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,subject,from_id) values('".$_POST["uid"]."','".$master_val."','".$_POST["content"]."','".date('Y-m-d H:i:s',time())."',0,'待审核','26',(select subject from oa_zhsz_report_custom where id=".$id."),{$id})");
			//添加
			/* $data = array(
				'flag_status'     => "待审核"
			);
			$result = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_report_custom','where'=>"id={$id}")); */
		}

		if($result<0){
			//数据插入错误
			$tips = array(
			   'tips' => '更新失败，操作终止。',
			   'url'  => './?t=report'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;
		}
		
		//判断是否上传图片
		if(!empty($PicUrl)){
			$i = 1;
			foreach($PicUrl as $rs){
				if($rs){
					$data_att = array(
						'attachment'	  => $rs,
						'custom_id'	  => $id,
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
				'custom_id'	  => $id,
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
		
		if($if_partner == 0){   //合作人记录添加
			if($partner_id){ 
				//$custom_all = $pd->query("SELECT * from  oa_zhsz_report_custom where id={$id}")->fetchAll(PDO::FETCH_ASSOC);
				$custom_all = $pd->fetchRow(array('field'=>array("*"),'table'=>'oa_zhsz_report_custom','where'=>'id='.$id));
				
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
						'term_id'	  => $custom_all['term_id'],
						'term_year'	  => $custom_all['term_year'],
						'is_captain'	  => $custom_all['is_captain'],
						'partner_id'	  => $partner_id_temp,
						'partner'	  => $partner_name_temp,
						'teacher_id'     => $teacher_id,
						'items'     => $cunstom_item,
						'class_id'     => $class_id,
						'subject_son'	  => $custom_all['subject_son'],
						'subject_son_id'	  => $custom_all['subject_son_id'],
						'subject_id'	  => $custom_all['subject_id'],
						'subject'	  => $custom_all['subject'],
						'task'	  => $custom_all['task'],
						'content'	  => $custom_all['content'],
						'note'	  => $custom_all['note'],
						'flag_status'	  => $custom_all['flag_status'],
						'create_by'	  => $custom_all['create_by'],
						'uid'	  => $rs,
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
					$custom_id_p = $pd->fetchOne(array('field'=>'max(id)','table'=>'oa_zhsz_report_custom'));  //最后一条插入的数据ID，也就是最新插入的自定义报表数据的ID
					//写sql插入语句，记录到班主任的消息表
					if($is_submit)
						$rs2=$pd->query("insert into oa_message(uid,geter,content,stime,is_read,flag,menu2,subject,from_id) values('".$rs."','".$master_val."','".$custom_all['content']."','".date('Y-m-d H:i:s',time())."',0,'待审核','26','".$custom_all['subject']."','".$custom_id_p."')");
					
					//数据库已有图片添加
					$cumstom_pic = $pd->query("select * from oa_zhsz_attachment where custom_id={$id} and type=0")->fetchAll(PDO::FETCH_ASSOC);
					$i = 1;
					foreach($cumstom_pic as $rs){
						if($rs){
							$data_att = array(
								'attachment'	  => $rs['attachment'],
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
					$cumstom_pic = $pd->query("select * from oa_zhsz_attachment where custom_id={$id} and type=1")->fetchAll(PDO::FETCH_ASSOC);
					$i = 1;
					foreach($cumstom_pic as $rs){
						if($rs){
							$data_att = array(
								'attachment'	  => $rs['attachment'],
								'custom_id'	  => $custom_id_p,
								'type'	  => 1,
								'path_name'	  => $rs['path_name'],
								'create_by'   => $_SESSION['username'],
								'create_time' => $now
							);
							$result_f = $pd->insert(array('data'=>$data_att,'table'=>'oa_zhsz_attachment'));
							if(empty($result_f)){
								//数据插入错误
								$tips = array(
								   'tips' => '合作人附件上传失败。',
								   'url'  => './?t=report'
								);
								$tips = urlencode(serialize($tips));
								header('Location:../tips.php?gets='.$tips);
								exit;
							}
							$i++;
						}
					}
				}
			}
		}
	}else{
		$tips = array(
		   'tips' => '更新失败。',
		   'url'  => './?t=report'
		);
	}
	$tips = array(
	   'tips' => '更新成功。',
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