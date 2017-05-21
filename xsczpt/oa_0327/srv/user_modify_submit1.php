<?php
	//判断用户是否登录
	session_start();
	if(empty($_SESSION['username'])){
		$tips = array(
				   'tips' => '请登录后再进行操作。',
				   'url'  => './?t=login'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
	
	$submitMethod = $_SERVER["REQUEST_METHOD"];
	//判断提交方式
	if($submitMethod=='POST'){
		include '../../ppf/pdo_mysql.php';
		include '../library/Filter.php';
		include('../common.php');
		$pd = new pdo_mysql();
		
		$flag = Filter::filter_number($_POST['flag']);
		
		//选课提交
		if($flag==3){
			$selectId = Filter::filter_number($_POST['course_select']);
			if($selectId!=0){
				$selectName = $pd->fetchOne(array('field'=>'select_name','table'=>'oa_zhsz_course_select','where'=>"id={$selectId} and school={$_SESSION['school']}"));
				$data   = array('select_course_id'=>$selectId,'select_course_name'=>$selectName);
				$params = array('data'=>$data,'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
				$result = $pd->update($params);
				if(empty($result)){
					//数据插入错误
					$tips = array(
						   'tips' => '选课保存失败，请稍后再试。',
						   'url'  => './?t=stu_selectcourse'
						  );
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
					exit;
				}else{
					$tips = array(
						   'tips' => '选课保存成功。',
						   'url'  => './?t=stu_selectcourse'
						  );
					$tips = urlencode(serialize($tips));
					header('Location:../tips.php?gets='.$tips);
					exit;
				}	
			}
		}
		
		//基本信息
		if($flag==1){
			$truename  = Filter::filter_html($_POST['truename']);
			$aliasName = Filter::filter_html($_POST['alias_name']);
			//查询别名是否已经存在
			$e = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_user_extend','where'=>"id<>{$_SESSION['uid']} and (alias_name='{$aliasName}'  or username='{$aliasName}')"));
			if($e>0){
				$tips = array(
				   'tips' => '抱歉，该别名已存在。',
				   'url'  => 'user_modify.php'
				);
				$tips = urlencode(serialize($tips));
				header('Location:./tips.php?gets='.$tips);
				exit;	
			}
			
			$data = array(
				'alias_name' => $aliasName,
				'truename'   => $truename
			);
		}
		
		if($flag==2){
			$gender    = Filter::filter_html($_POST['gender']);
			$birthDate = Filter::filter_html($_POST['birth_date']);
			$telphone  = Filter::filter_html($_POST['telphone']);
			$email     = Filter::filter_html($_POST['email']);
			$qq        = Filter::filter_html($_POST['qq']);
			$address   = Filter::filter_html($_POST['address']);
			$usedName  = Filter::filter_html($_POST['used_name']);
			$duty      = Filter::filter_html($_POST['duty']);
			$nation    = Filter::filter_html($_POST['nation']);
			$pStatus   = Filter::filter_html($_POST['political_status']);
			$school    = Filter::filter_html($_POST['graduate_school']);
			//$personNo  = Filter::filter_html($_POST['person_no']);
			$medical   = Filter::filter_html($_POST['medical_history']);			
			$data = array(
				'gender'           => $gender,
				'qq' 		 	   => $qq,
				'used_name'  	   => $usedName,
				'duty'		       => $duty,
				'nation' 		   => $nation,
				'political_status' => $pStatus,
				'graduate_school'  => $school,
				'medical_history'  => $medical
			);
			
			$data1 = array(
				'birthday'       => $birthDate,
				'mobile'   	   => $telphone,
				'email'      	   => $email,
				'addr'    	   => $address
			);
		}
		$params     = array('data'=>$data,'table'=>'oa_user_extend','where'=>"userid='{$_SESSION['uid']}'");
		$result     = $pd->update($params);
		$params1     = array('data'=>$data1,'table'=>'act_member','where'=>"id='{$_SESSION['uid']}'");
		$result1     = $pd->update($params1);
		
		if(empty($result)&&empty($result1)){
			//数据插入错误
			$tips = array(
				   'tips' => '个人信息更新失败，请稍后再试。',
				   'url'  => './?t=stu_modify'
				  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
		}else{
			//保存初中相关信息
			if(!empty($_POST['middle_no'])){
				
				$data = array();
				$data['username'] = $_SESSION['username'];
				//判断是否有中考信息
				$data['exam_no'] 	 = $_POST['middle_no'];
				$data['yw'] 	 	 = intval($_POST['middle_yw']);
				$data['sx'] 	 	 = intval($_POST['middle_sx']);
				$data['wy'] 	 	 = intval($_POST['middle_wy']);
				$data['lh']		 	 = intval($_POST['middle_lh']);
				$data['zs']		 	 = intval($_POST['middle_zs']);
				$data['ty']	     	 = intval($_POST['middle_ty']);
				$data['zf']			 = $data['yw'] + $data['sx'] + $data['wy'] + $data['lh'] + $data['zs'] + $data['ty'];
				$data['intro']   	 = $_POST['middle_intro'];
				$data['create_by']   = $_SESSION['username'];
				$data['create_time'] = date('Y-m-d H:i:s');
				
				$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_about_primary'));
			}
			$tips = array(
				   'tips' => '个人信息更新更新成功。',
				   'url'  => './?t=stu_modify'
				  );
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
		}
	}else{
		//提交方式出错
		$tips = array(
				   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
				   'url'  => 'index.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:./tips.php?gets='.$tips);
	}
?>