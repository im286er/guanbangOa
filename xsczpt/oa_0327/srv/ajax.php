<?php
	//判断用户是否登录
	/*session_start();
	if(empty($_SESSION['admin_name'])){
		$tips = array(
				   'tips' => '请登录后再进行操作。',
				   'url'  => 'index.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:./tips.php?gets='.$tips);
		exit;
	}*/
	
	$submitMethod = $_SERVER["REQUEST_METHOD"];
	//判断提交方式
	if($submitMethod=='POST'){
	        include '../../ppf/pdo_mysql.php';
            include '../library/Filter.php';
            include('../common.php');
			$pd = new pdo_mysql();
			
		$returnVal = array('flag'=>'false','value'=>'Ajax默认返回值');
		$filter = new Filter();
		//判断何种操作
		$action = empty($_POST['flag'])?$_GET['flag']:$_POST['flag'];
		switch($action){
			//查询别是否存在
			case 'check_exist' :
				$aliasName = Filter::safe_string($_POST['alias_name']);
				$where     = "alias_name='{$aliasName}' or username='{$aliasName}'";
				$params    = array('field'=>array('id','username'),'table'=>'zhsz_users','where'=>"{$where}");
				$user      = $db->fetchRow($params);
				//检查是否已经存在并且这个用户不是自已
				if(!empty($user)&&$user['id']!=$_SESSION['admin_id']){
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '抱歉，该别名已存在。';
				}else{
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '恭喜，该别名可以使用。';
				}
				echo json_encode($returnVal);
				break;
			//删除联系人
			case 'del_relation' :
				$id     = $filter->filter_number($_POST['id']);
				//删除信息
				$params = array('table'=>'oa_zhsz_relative','where'=>"id={$id}");
				if($pd->delete($params)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '联系人删除成功。';
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '联系人删除失败。';
				}
				echo json_encode($returnVal);
				break;
			//删除学期
			case 'del_term' :
				//判断是否有删除权限
				$pValue = 2;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_term','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '学期删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '学期删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除学期
			case 'del_grade' :
				//判断是否有删除权限
				$pValue = 3;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_grade','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '年级删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '年级删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除班级
		case 'del_class' :
				//判断是否有删除权限
				$pValue = 4;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_class','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '班级删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '班级删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除经历感悟
			case 'del_exp' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//查询信息
					$exp = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_experience','where'=>"id={$id}"));
					//删除信息
					$params = array('table'=>'zhsz_experience','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->delete($params)){
						//删除附件
						if(!empty($exp['attachment'])){
							$phyPath = $_SERVER['DOCUMENT_ROOT'].$exp['attachment'];
							if(file_exists($phyPath)){
								unlink($phyPath);
							}
						}
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '个人经历删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '个人经历删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除有意义的成果
			case 'del_result' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//查询信息
					$exp = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_result','where'=>"id={$id}"));
					//删除信息
					$params = array('table'=>'zhsz_result','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->delete($params)){
						//删除附件
						if(!empty($exp['attachment'])){
							$phyPath = $_SERVER['DOCUMENT_ROOT'].$exp['attachment'];
							if(file_exists($phyPath)){
								unlink($phyPath);
							}
						}
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '个人成果删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '个人成果删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除社区服务
			case 'del_service' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//查询信息
					$exp = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_service','where'=>"id={$id}"));
					//删除信息
					$params = array('table'=>'zhsz_service','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->delete($params)){
						//删除附件
						if(!empty($exp['attachment'])){
							$phyPath = $_SERVER['DOCUMENT_ROOT'].$exp['attachment'];
							if(file_exists($phyPath)){
								unlink($phyPath);
							}
						}
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '社区服务删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '社区服务删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除社会实践
			case 'del_event' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//查询信息
					$exp = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_event','where'=>"id={$id}"));
					//删除信息
					$params = array('table'=>'zhsz_event','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->delete($params)){
						//删除附件
						if(!empty($exp['attachment'])){
							$phyPath = $_SERVER['DOCUMENT_ROOT'].$exp['attachment'];
							if(file_exists($phyPath)){
								unlink($phyPath);
							}
						}
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '社区实践删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '社区实践删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
				//删除研究性学习
			case 'del_research' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//查询信息
					$exp = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_research','where'=>"id={$id}"));
					//删除信息
					$params = array('table'=>'zhsz_research','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->delete($params)){
						//删除附件
						if(!empty($exp['attachment'])){
							$phyPath = $_SERVER['DOCUMENT_ROOT'].$exp['attachment'];
							if(file_exists($phyPath)){
								unlink($phyPath);
							}
						}
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '研究性学习删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '研究性学习删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除选课
			case 'del_select' :
				//判断是否有删除权限
				$pValue = 21;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_course_select','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '选课删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '选课删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除模块
			case 'del_module' :
				//判断是否有删除权限
				$pValue = 5;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_course_module','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '模块删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '模块删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除课程
			case 'del_course' :
				//判断是否有删除权限
				$pValue = 5;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除课程
					$params = array('table'=>'zhsz_course','where'=>"id={$id}");
					if($db->delete($params)){
						//删除课程模块
						$params = array('table'=>'zhsz_course_module','where'=>"course_id={$id}");
						$db->delete($params);
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '课程删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '课程删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除部门
			case 'del_dept' :
				//判断是否有删除权限
				$pValue = 22;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_dept','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '部门删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '部门删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//查询教师
			case 'select_teacher' :
				$id    = $filter->filter_number($_POST['dept_id']);
				$where = ' flag_type=2 ';
				if($id!=0){
					$where .= " and department={$id} ";
				}
				$params = array('field'=>array('id','username','truename'),'table'=>'zhsz_users','where'=>$where,'order'=>'CONVERT(truename USING GBK)');
				$teacher = $db->fetchAll($params);
				if(!empty($teacher)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = $teacher;
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '暂无教师记录。';
				}
				echo json_encode($returnVal);
				break;
			//查询班级
			case 'select_class' :
				$id     = $filter->filter_number($_POST['grade_id']);
				$where  = " grade_id={$id} ";
				$params = array('field'=>array('id','class_name'),'table'=>'zhsz_class','where'=>$where);
				$class  = $db->fetchAll($params);
				if(!empty($class)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = $class;
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '暂无班级记录。';
				}
				echo json_encode($returnVal);
				break;
			//删除任课信息
			case 'del_teach' :
				//判断是否有删除权限
				$pValue = 6;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_teach','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '任课记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '任课记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除全部任课信息
			case 'del_teach_all' :
				//判断是否有删除权限
				$pValue = 6;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					//删除信息
					$params = array('table'=>'zhsz_teach','where'=>"1=1");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '任课记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '任课记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除评价项
			case 'del_vote' :
				//判断是否有删除权限
				$pValue = 12;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_votelib','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '评价项删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '评价项删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除体能项
			case 'del_stamina' :
				//判断是否有删除权限
				$pValue = 13;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_stamina','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '体能项删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '体能项删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除评论
			case 'del_comment' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$cid   = $filter->filter_number($_POST['cid']);
					$uname = $filter->filter_html($_POST['uname']);
					//删除信息
					$params = array('table'=>'zhsz_comment','where'=>"id={$cid} and flag_checked=0 and create_by='{$uname}'");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '评论删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '评论删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除评论
			case 'del_reply' :
				//判断是否有删除权限
				$pValue = 11;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id   = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_comment','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '评论删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '评论删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//审核评论
			case 'check_reply' :
				//判断是否有删除权限
				$pValue = 11;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[1])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id  = $filter->filter_number($_POST['id']);
					$val = $filter->filter_number($_POST['val']);
					//审核信息
					$data = array('flag_checked'=>$val);
					$params = array('data'=>$data,'table'=>'zhsz_comment','where'=>"id={$id}");
					if($db->update($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '操作成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '操作失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//查询学生信息
			case 'student_info' :
				$uid  = $filter->filter_number($_POST['uid']);
				$user = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_users','where'=>"id={$uid}"));
				//没有所属班级
				if($user['department']<=0){
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '该学生没有班级信息，请重新输入。';
				}else{
					//查询班级
					$class = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_class','where'=>"id={$user['department']}"));
					//查询是否是毕业班
					if($class['grade_id']==0){
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '该学生已经毕业，请重新输入。';
					}else{
						//当前学期
						$curTerm = $db->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'zhsz_term','where'=>'flag_default=1'));
						$ttid = empty($curTerm['term_type'])?0:intval($curTerm['term_type']);
						$gid  = $class['grade_id'];
						$gradeName = $db->fetchOne(array('field'=>'grade_name','table'=>'zhsz_grade','where'=>"id={$gid}"));
						//查询体能项目
						$where = "(grade_id=0 OR grade_id={$gid}) and (term_type=0 OR term_type={$ttid}) ";
						$stamina = $db->fetchAll(array('field'=>array('id','name'),'table'=>'zhsz_stamina','where'=>$where,'order'=>'id'));
						$returnVal['flag']  = 'true';
						$returnVal['value'] = $stamina;
						$returnVal['gid']   = $gid;
						$returnVal['ttid']  = $ttid;
						$returnVal['tips']  = $gradeName.$class['class_name'];
					}
				}
				echo json_encode($returnVal);
				break;
			//删除体能记录
			case 'del_stamina_rs' :
				//判断是否有删除权限
				$pValue = 10;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$uid = $filter->filter_number($_POST['uid']);
					$tid = $filter->filter_number($_POST['tid']);
					//删除信息
					$params = array('table'=>'zhsz_stamina_record','where'=>"user_id={$uid} and term_id={$tid}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '体能记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '体能记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除体质记录
			case 'del_physique' :
				//判断是否有删除权限
				$pValue = 9;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_physique','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '体质记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '体质记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除成绩记录
			case 'del_score' :
				//判断是否有删除权限
				$pValue = 7;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_score','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '成绩记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '成绩记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//查询登录名是否存在
			case 'check_user' :
				$username = Filter::safe_string($_POST['username']);
				$where    = "username='{$username}'";
				$params   = array('field'=>'count(*)','table'=>'zhsz_users','where'=>"{$where}");
				$nums     = $db->fetchOne($params);
				
				if($nums>0){
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '抱歉，该登录名已存在。';
				}else{
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '恭喜，该登录名可以使用。';
				}
				echo json_encode($returnVal);
				break;
			//删除用户
			case 'del_user' :
				//判断是否有删除权限
				$pValue = 14;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_users','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//查询微代码是否存在
			case 'check_code' :
				$codeNo   = Filter::safe_string($_POST['code_no']);
				$where    = "code_no='{$codeNo}'";
				$params   = array('field'=>'count(*)','table'=>'zhsz_code','where'=>"{$where}");
				$nums     = $db->fetchOne($params);
				
				if($nums>0){
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '抱歉，该编码已存在。';
				}else{
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '恭喜，该编码可以使用。';
				}
				echo json_encode($returnVal);
				break;
			//删除编码
			case 'del_code' :
				//判断是否有删除权限
				$pValue = 23;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id = $filter->filter_number($_POST['id']);
					//查询详情
					$code = $db->fetchRow(array('field'=>array('*'),'table'=>'zhsz_code','where'=>"id={$id}"));
					//删除信息
					$params = array('table'=>'zhsz_code','where'=>"id={$id}");
					if($db->delete($params)){
						//查询是否为顶级代码
						if(empty($code['parent_no'])){
							$params = array('table'=>'zhsz_code','where'=>"parent_no='{$code['code_no']}'");
							$db->delete($params);
						}
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '记录删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '记录删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除角色
			case 'del_role' :
				$id     = $filter->filter_number($_POST['id']);
				//删除信息
				$params = array('table'=>'zhsz_roles','where'=>"id={$id}");
				if($db->delete($params)){
					//删除角色相关权限
					$params = array('table'=>'zhsz_role_privileges','where'=>"role_id={$id}");
					$db->delete($params);
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '角色删除成功。';
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '角色删除失败。';
				}
				echo json_encode($returnVal);
				break;
			//重置密码
			case 'set_pass' :
				$id   = $filter->filter_number($_POST['id']);
				$p    = isset($_POST['p'])?intval($_POST['p']):0;
				$username = $db->fetchOne(array('field'=>'username','table'=>'zhsz_users','where'=>"id={$id}"));
				if($p){
					$userpass = '000000';
				}else{
					$userpass = substr($username,-6);
				}
				$data = array('userpass'=>md5($userpass));
				$params = array('table'=>'zhsz_users','data'=>$data,'where'=>"id={$id}");
				if($db->update($params)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '密码更新成功。';
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '密码更新失败。';
				}
				echo json_encode($returnVal);
				break;
			//删除联系人
			case 'del_relationa' :
				$id     = $filter->filter_number($_POST['id']);
				//删除信息
				$params = array('table'=>'zhsz_relative','where'=>"id={$id}");
				if($db->delete($params)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '联系人删除成功。';
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '联系人删除失败。';
				}
				echo json_encode($returnVal);
				break;
			//清空班级信息
			case 'clear_class' :
				//清空原有班级信息
				$r = $db->execSQL("update zhsz_users set org_class=department,department=0 where flag_type=1 and department>0");
				if($r==true){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '班级信息清空成功。';
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '班级信息清空失败。';
				}
				echo json_encode($returnVal);
				break;
			//上传学生身份证号码
			case 'upload_card' :
				$attFile = empty($_FILES['user_list'])?'':$_FILES['user_list'];
				include('upload.php');
				//判断是否上传文件
				if(!empty($attFile['name'])){
					$fileDoc = uploadFile($attFile,$_SESSION['admin_id']);
					$att     = explode('|',$fileDoc);
					if($att[0]!=4){
						$returnVal['flag']  = 'false';
						$returnVal['value'] = $att[0];
					}else{
						//处理上传的文件
						$phyFile = $_SERVER['DOCUMENT_ROOT'].$att[1];
						//处理文件
						$fp = fopen($phyFile,'r');
						if($fp===false){
							$returnVal['flag']  = 'false';
							$returnVal['value'] = -1;
						}else{
							$userCard = '';
							$i = 0;
							while(!feof($fp))
							{
								$rs = trim(fgets($fp));
								if(!empty($rs)){
									$userCard .= $rs.'\',\'';
									$i++;
								}
							}
							$userCard = trim($userCard,',\'');
							$userCard = "'{$userCard}'";
							fclose($fp);
							$returnVal['flag']  = 'true';
							$returnVal['nums']  = $i;
							$returnVal['value'] = $userCard;
						}
					}
				}
				echo json_encode($returnVal);
				break;
      //修改日常表现状态
			case 'ch_performance' :
				//判断是否有修改权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id   = $filter->filter_number($_POST['id']);
          $pertype= $filter->filter_number($_POST['pertype']);
					//修改信息信息
          //$pertype = ($pertype==0)?1:0;
					$data = array('pertype'=>$pertype);
					$params = array('data'=>$data,'table'=>'zhsz_performance','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->update($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '修改成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '修改失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除日常表现
			case 'del_performance' :
				//判断是否有删除权限
				$pValue = 1;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[0])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id   = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_performance','where'=>"id={$id} and create_by='{$_SESSION['admin_name']}'");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//计算排名
			case 'exam_order' :
				//判断是否有删除权限
				$pValue = 7;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[5])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$gid       = $filter->filter_number($_POST['grade_id']);
					$orderType = $filter->filter_number($_POST['order_type']);
					$examType  = $_POST['exam_type'];
					//当前学期
					$curTerm = $db->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'zhsz_term','where'=>'flag_default=1'));
					$where = "exam_type='{$examType}' and term_id={$curTerm['id']} and grade_id={$gid}";
					$flag = true;
					if($orderType==1){//年级排名
						//按三门总分查询数据
						$allScore = $db->fetchAll(array('field'=>array('id','szf','jzf'),'table'=>'zhsz_score','where'=>$where,'order'=>'szf DESC'));
						$t = 0;
						$i = 0;
						foreach($allScore as $rs){
							if($t!=$rs['szf']){
								$t = $rs['szf'];
								$i++;
							}
							$db->update(array('data'=>array('sg_order'=>$i),'table'=>'zhsz_score','where'=>"id={$rs['id']}"));
						}
					}
					if($orderType==2){//班级排名
						//查询班级
						$classes = $db->fetchAll(array('field'=>array('id','class_name'),'table'=>'zhsz_class','where'=>"grade_id={$gid}"));
						foreach($classes as $rsb){
							//班级三门总分
							$where = "exam_type='{$examType}' and term_id={$curTerm['id']} and class_id={$rsb['id']}";
							$allScore = $db->fetchAll(array('field'=>array('id','szf','jzf'),'table'=>'zhsz_score','where'=>$where,'order'=>'szf DESC'));
							$t = 0;
							$i = 0;
							foreach($allScore as $rsc){
								if($t!=$rsc['szf']){
									$t = $rsc['szf'];
									$i++;
								}
								$db->update(array('data'=>array('sc_order'=>$i),'table'=>'zhsz_score','where'=>"id={$rsc['id']}"));
							}
						}
					}
					$returnVal['flag']  = 'true';
					$returnVal['value'] = '计算成功。';
				}
				echo json_encode($returnVal);
				break;
			//删除评价分类
			case 'del_coucat' :
				//判断是否有删除权限
				$pValue = 27;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_coucat','where'=>"id={$id}");
					if($db->delete($params)){
						//删除所有子结点
						$params = array('table'=>'zhsz_coucat','where'=>"parent_id={$id}");
						$db->delete($params);
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '操作成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '操作失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除评价项目
			case 'del_couitem' :
				//判断是否有删除权限
				$pValue = 28;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id     = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_coucat','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '操作成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '操作失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//根据所教课程选择班级
			case 'course_class' :
				$id     = $filter->filter_number($_POST['course_id']);
				$where  = " course_id={$id} and user_id={$_SESSION['admin_id']}";
				$params = array('field'=>array('class_id','class_name'),'table'=>'zhsz_teach','where'=>$where,'order'=>'class_name asc');
				$class  = $db->fetchAll($params);
				if(!empty($class)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = $class;
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '暂无班级记录。';
				}
				echo json_encode($returnVal);
				break;
			//担任职务设置
			case 'setting_duty' :
				$duty   = $filter->safe_string($_POST['duty']);
				$uid    = $filter->filter_number($_POST['uid']);
				$where  = " user_id={$uid} ";
				$data   = array('duty'=>$duty,'user_id'=>$uid,'create_time'=>date('Y-m-d H:i:s'));
				$e = $db->fetchRow(array('field'=>array('id','flag_checked'),'table'=>'zhsz_setting_duty','where'=>$where));
				if(empty($e)){
					$user   = $db->fetchRow(array('field'=>array('truename','department'),'table'=>'zhsz_users','where'=>"id='{$uid}'"));
					$classD = $db->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'zhsz_class','where'=>"id={$user['department']}"));
					$gradeName = '';
					if($classD['grade_id']==1){
						$gradeName = '高一';	
					}
					if($classD['grade_id']==2){
						$gradeName = '高二';	
					}
					if($classD['grade_id']==3){
						$gradeName = '高三';	
					}
					$data['class_id']   = $user['department'];
					$data['class_name'] = $gradeName.$classD['class_name'];
					$data['truename']   = $user['truename'];
					$result  = $db->insert(array('data'=>$data,'table'=>'zhsz_setting_duty'));
					if(!empty($result)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '保存成功';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '保存失败。';
					}
				}else{
					if($e['flag_checked']==0){
						unset($data['user_id']);
						unset($data['create_time']);
						$result  = $db->update(array('data'=>$data,'table'=>'zhsz_setting_duty','where'=>$where));
						if(!empty($result)){
							$returnVal['flag']  = 'true';
							$returnVal['value'] = '保存成功';
						}else{
							$returnVal['flag']  = 'false';
							$returnVal['value'] = '保存失败。';
						}
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '已经审核过，不可更改。';
					}
				}
				echo json_encode($returnVal);
				break;
			//删除日常表现
			case 'del_jiangcheng' :
				//判断是否有删除权限
				$pValue = 34;
				//实际拥有的权限
				$priv = selectHasPrivileges($db,$_SESSION['role_id'],$pValue);
				if(!isset($priv[3])){
					$returnVal['flag']  = 'forbid';
					$returnVal['value'] = '抱歉，您没有操作的权限。';
				}else{
					$id   = $filter->filter_number($_POST['id']);
					//删除信息
					$params = array('table'=>'zhsz_reward_punishment','where'=>"id={$id}");
					if($db->delete($params)){
						$returnVal['flag']  = 'true';
						$returnVal['value'] = '删除成功。';
					}else{
						$returnVal['flag']  = 'false';
						$returnVal['value'] = '删除失败。';
					}
				}
				echo json_encode($returnVal);
				break;
			//查询品德发展大类、兴趣爱好等
			case 'select_item' :
				$table = empty($_POST['table'])?'zhsz_setting_morality':$_POST['table'];
				if($table=='zhsz_setting_morality'){
					$code   = $db->fetchAll(array('field'=>array('code_no','code_name'),'table'=>'zhsz_code','where'=>'flag_status=1 and left(code_no,6)="PINDE_" and parent_no=""'));
				}
				if($table=='zhsz_setting_interest'){
					$code   = $db->fetchAll(array('field'=>array('code_no','code_name'),'table'=>'zhsz_code','where'=>'flag_status=1 and parent_no="XINGQU"'));
				}
				if($table=='zhsz_setting_event'){
					$code   = $db->fetchAll(array('field'=>array('code_no','code_name'),'table'=>'zhsz_code','where'=>'flag_status=1 and parent_no="EVENT"'));
				}
				if(!empty($code)){
					$returnVal['flag']  = 'true';
					$returnVal['value'] = $code;
				}else{
					$returnVal['flag']  = 'false';
					$returnVal['value'] = '暂无类型记录。';
				}
				echo json_encode($returnVal);
				break;
			default :
				echo json_encode($returnVal);
		}
	}else{
		//表单提交方式
		$tips = array(
				   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
				   'url'  => 'index.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:./tips.php?gets='.$tips);
	}
?>