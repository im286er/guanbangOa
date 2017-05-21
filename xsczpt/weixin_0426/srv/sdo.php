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
	//检查别名
	case "check_exist":
		$aliasName =$_POST['alias_name'];
		$where     = " where B.alias_name='{$aliasName}' or A.username='{$aliasName}'";
		//$params    = array('field'=>array('id','username'),'table'=>'oa_zhsz_users','where'=>"{$where}");
		//$r = $pd->fetchRow($params);
		$r=$pd->query("select A.id,A.username,B.alias_name from act_member as A inner join oa_user_extend as B ON A.id=B.userid  {$where}")->fetchAll(PDO::FETCH_ASSOC);
		//检查是否已经存在并且这个用户不是自已
		if(!empty($r)&&$r[0]['id']!=$_SESSION['uid']){
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '抱歉，该别名已存在。';
		}else{
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '恭喜，该别名可以使用。';
		}
		echo json_encode($returnVal);
		break;
		
	//修改用户
	case "save_user":
		$aliasName =$_POST['alias_name'];
		$truename =$_POST['truename'];
		$id =$_POST['id'];
		$data = array(
			'truename' => $truename
		);
		$params     = array('data'=>$data,'table'=>'act_member','where'=>"id='{$id}'");
		$r = $pd->update($params);
		
		$data1 = array(
			'alias_name'     => $aliasName
		);
		
		$params1     = array('data'=>$data1,'table'=>'oa_user_extend','where'=>"userid='{$id}'");
		$r1 = $pd->update($params1);
		
		//检查是否已经存在并且这个用户不是自已
		//if(!empty($r)&&$r['id']!=$_SESSION['uid']){
		if(!empty($r)||!empty($r1)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '操作成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '操作失败。';
		}
		echo json_encode($returnVal);
		break;
	//修改密码
	case "change_pass":
		$passpwd =$_POST['passpwd'];
		$passpwd2 =$_POST['passpwd2'];
		if(!empty($passpwd)&&($passpwd==$passpwd2)){
			$passpwd = md5($passpwd);
			if($_SESSION['role_id']==3){      //家长密码
				$result = $pd->exec("update oa_user_extend set parent_pass='".$passpwd."' where userid='{uid}'");
			}else{
				$result = $pd->exec("update act_member set pmd5='{$passpwd}' where id='{$uid}'");
			}
			
			if(!$result){
				$returnVal['flag']  = 'false';
				$returnVal['value'] = '密码修改失败，请稍后再试。';
			}else{
				$returnVal['flag']  = 'true';
				$returnVal['value'] = '密码修改成功。';
				
				$flag_log = $pd->query("select flag_log from oa_zhsz_system where school={$_SESSION['school']}")->fetchColumn(0);    

				if($flag_log==1){   //写入日志文件
					$now = date('Y-m-d H:i:s');
					$ip  = $_SERVER['REMOTE_ADDR'];
					//系统日志
					$data = array(
						'content'     => "会员[{$_SESSION['username']}]于{$now}修改密码。",
						'flag_type'	  => '修改密码',
						'use_ip'	  => $ip,
						'create_by'   => $_SESSION['username'],
						'create_time' => $now
					);
					$pd->insert(array('table'=>'oa_zhsz_logs','data'=>$data));
				}
			}
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '两次输入的密码不一致，请重新输入。';
		}
		echo json_encode($returnVal);
		break;
		
	//选择班级
	case "select_class":
		$id = $filter->filter_number($_POST['grade_id']);
		$where  = " grade_id={$id} ";
		
		$class=$pd->query("select id,class_name from oa_zhsz_class where".$where)->fetchAll(PDO::FETCH_ASSOC);

		if(!empty($class)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = $class;
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '暂无班级记录。';
		}
		echo json_encode($returnVal);
		break;
		
	//选择班级
	case "select_biye_class":
		$class_end = Filter::filter_html($_POST['class_end']);
		$where  = " class_end=\"{$class_end}\" and grade_id = 0";

		$class=$pd->query("select id,class_name from oa_zhsz_class where".$where)->fetchAll(PDO::FETCH_ASSOC);

		if(!empty($class)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = $class;
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '暂无班级记录。';
		}
		echo json_encode($returnVal);
		break;
		
	//查询品德发展大类、兴趣爱好等
	case 'select_item' :
		$table = empty($_POST['table'])?'zhsz_setting_morality':$_POST['table'];
		if($table=='oa_zhsz_setting_morality'){
			$code   = $pd->query('select code_no,code_name from oa_zhsz_code where flag_status=1 and left(code_no,6)="PINDE_" and parent_no=""')->fetchAll(PDO::FETCH_ASSOC);
		}
		if($table=='oa_zhsz_setting_interest'){
			$code   = $pd->query('select code_no,code_name from oa_zhsz_code where flag_status=1 and parent_no="XINGQU"')->fetchAll(PDO::FETCH_ASSOC);
		}
		if($table=='oa_zhsz_setting_event'){
			$code   = $pd->query('select code_no,code_name from oa_zhsz_code where flag_status=1 and parent_no="EVENT"')->fetchAll(PDO::FETCH_ASSOC);
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
	
	//删除成绩记录
	case 'del_score' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_score','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '成绩记录删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '成绩记录删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//查询学生信息
	case 'student_info' :
		$uid  = $filter->filter_html($_POST['uid']);
		$user = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_user_extend','where'=>"userid='{$uid}'"));
		//没有所属班级
		if($user['dept']<=0){
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '该学生没有班级信息，请重新输入。';
		}else{
			//查询班级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$user['dept']}"));
			//查询是否是毕业班
			if($class['grade_id']==0){
				$returnVal['flag']  = 'false';
				$returnVal['value'] = '该学生已经毕业，请重新输入。';
			}else{
				//当前学期
				$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1 and school='.$_SESSION['school']));
				$ttid = empty($curTerm['term_type'])?0:intval($curTerm['term_type']);
				$gid  = $class['grade_id'];
				$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
				//查询体能项目
				$where = "(grade_id=0 OR grade_id={$gid}) and (term_type=0 OR term_type={$ttid}) ";
				$stamina  = $pd->query("SELECT id,name FROM oa_zhsz_stamina WHERE  {$where}  order by id")->fetchAll(PDO::FETCH_ASSOC);
				$returnVal['flag']  = 'true';
				$returnVal['value'] = $stamina;
				$returnVal['gid']   = $gid;
				$returnVal['ttid']  = $ttid;
				$returnVal['tips']  = $gradeName.$class['class_name'];
			}
		}
		echo json_encode($returnVal);
		break;
		
	//计算排名
	case 'exam_order' :
		$gid       = $filter->filter_number($_POST['grade_id']);
		$orderType = $filter->filter_number($_POST['order_type']);
		$examType  = $_POST['exam_type'];
		//当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		$where = "exam_type='{$examType}' and term_id={$curTerm['id']} and grade_id={$gid}";
		$flag = true;
		if($orderType==1){//年级排名
			//按三门总分查询数据
			$allScore = $pd->query("select id,szf,jzf from oa_zhsz_score where {$where} order by szf DESC")->fetchAll(PDO::FETCH_ASSOC);
			$t = 0;
			$i = 0;
			foreach($allScore as $rs){
				if($t!=$rs['szf']){
					$t = $rs['szf'];
					$i++;
				}
				$pd->update(array('data'=>array('sg_order'=>$i),'table'=>'oa_zhsz_score','where'=>"id={$rs['id']}"));
			}
		}
		if($orderType==2){//班级排名
			//查询班级
			$classes = $pd->query("select id,class_name from oa_zhsz_class where grade_id={$gid}")->fetchAll(PDO::FETCH_ASSOC);
			foreach($classes as $rsb){
				//班级三门总分
				$where = "exam_type='{$examType}' and term_id={$curTerm['id']} and class_id={$rsb['id']}";
				$allScore = $pd->query("select id,szf,jzf from oa_zhsz_score where {$where} order by szf DESC")->fetchAll(PDO::FETCH_ASSOC);
				$t = 0;
				$i = 0;
				foreach($allScore as $rsc){
					if($t!=$rsc['szf']){
						$t = $rsc['szf'];
						$i++;
					}
					$pd->update(array('data'=>array('sc_order'=>$i),'table'=>'oa_zhsz_score','where'=>"id={$rsc['id']}"));
				}
			}
		}
		$returnVal['flag']  = 'true';
		$returnVal['value'] = '计算成功。';
		
		echo json_encode($returnVal);
		break;
		
	//删除体能项
	case 'del_stamina' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_stamina','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '体能项删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '体能项删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除体能记录
	case 'del_stamina_rs' :
		$uid = $filter->filter_number($_POST['uid']);
		$tid = $filter->filter_number($_POST['tid']);
		//删除信息
		$params = array('table'=>'oa_zhsz_stamina_record','where'=>"user_id={$uid} and term_id={$tid}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '体能记录删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '体能记录删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除体质记录
	case 'del_physique' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_physique','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '体质记录删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '体质记录删除失败。';
		}
	
		echo json_encode($returnVal);
		break;
		
	//删除学期
	case 'del_term' :
		$id  = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_term','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '学期删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '学期删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除年级
	case 'del_grade' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_grade','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '年级删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '年级删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除班级
	case 'del_class' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_class','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '班级删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '班级删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除部门
	case 'del_dept' :
		$id     = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_dept','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '部门删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '部门删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除课程
	case 'del_course' :
		$id = $filter->filter_number($_POST['id']);
		//删除课程
		$params = array('table'=>'oa_zhsz_course','where'=>"id={$id}");
		if($pd->delete($params)){
			//删除课程模块
			$params = array('table'=>'oa_zhsz_course_module','where'=>"course_id={$id}");
			$pd->delete($params);
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '课程删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '课程删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除模块
	case 'del_module' :
		$id     = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_course_module','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '模块删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '模块删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除任课信息
	case 'del_teach' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_teach','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '任课记录删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '任课记录删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除全部任课信息
	case 'del_teach_all' :
		//删除信息
		$params = array('table'=>'oa_zhsz_teach','where'=>"school=".$_SESSION["school"]);
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '任课记录删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '任课记录删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//查询班级
	case 'select_class' :
		$id     = $filter->filter_number($_POST['grade_id']);
		$where  = " grade_id={$id} ";
		$class  = $pd->query("select id,class_name from oa_zhsz_class where {$where}")->fetchAll(PDO::FETCH_ASSOC);
		if(!empty($class)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = $class;
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '暂无班级记录。';
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
		$teacher = $pd->query("select id,truename,username from oa_zhsz_users where {$where} order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
		
		if(!empty($teacher)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = $teacher;
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '暂无教师记录。';
		}
		echo json_encode($returnVal);
		break;
		
	//删除选课
	case 'del_select' :
		$id     = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_course_select','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '选课删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '选课删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//清空班级信息
	case 'clear_class' :
		//清空原有班级信息
		$r = $pd->exec("update oa_zhsz_users set org_class=department,department=0 where flag_type=1 and department>0");
		if($r==true){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '班级信息清空成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '班级信息清空失败。';
		}
		echo json_encode($returnVal);
		break;
		
	//删除日常表现
	case 'del_performance' :
		$id   = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_performance','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//修改日常表现状态
	case 'ch_performance' :
		$id   = $filter->filter_number($_POST['id']);
		$pertype= $filter->filter_number($_POST['pertype']);
		//修改信息信息
		$data = array('pertype'=>$pertype);
		$params = array('data'=>$data,'table'=>'oa_zhsz_performance','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		if($pd->update($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '修改成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '修改失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//担任职务设置
	case 'setting_duty' :
		$duty   = $filter->filter_html($_POST['duty']);
		$uid    = $filter->filter_number($_POST['uid']);
		$where  = " user_id={$uid} ";
		$data   = array('duty'=>$duty,'user_id'=>$uid,'create_time'=>date('Y-m-d H:i:s'));
		$e = $pd->fetchRow(array('field'=>array('id','flag_checked'),'table'=>'oa_zhsz_setting_duty','where'=>$where));
		if(empty($e)){
			$user   = $pd->fetchRow(array('field'=>array('truename','department'),'table'=>'oa_zhsz_users','where'=>"id='{$uid}'"));
			$classD = $pd->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['department']}"));
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
			$result  = $pd->insert(array('data'=>$data,'table'=>'oa_zhsz_setting_duty'));
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
				$result  = $pd->update(array('data'=>$data,'table'=>'oa_zhsz_setting_duty','where'=>$where));
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
	
	//删除经历感悟
	case 'del_exp' :
		$id = $filter->filter_number($_POST['id']);
		//查询信息
		$exp = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_experience','where'=>"id={$id}"));
		//删除信息
		$params = array('table'=>'oa_zhsz_experience','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		if($pd->delete($params)){
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
		
		echo json_encode($returnVal);
		break;
		
	//删除有意义的成果
	case 'del_result' :
		$id = $filter->filter_number($_POST['id']);
		//查询信息
		$exp = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_result','where'=>"id={$id}"));
		//删除信息
		$params = array('table'=>'oa_zhsz_result','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		
		if($pd->delete($params)){
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
		
		echo json_encode($returnVal);
		break;
		
	//删除研究性学习
	case 'del_research' :
		$id = $filter->filter_number($_POST['id']);
		//查询信息
		$exp = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_research','where'=>"id={$id}"));
		//删除信息
		$params = array('table'=>'oa_zhsz_research','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		if($pd->delete($params)){
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
		
		echo json_encode($returnVal);
		break;
		
	//删除社区服务
	case 'del_service' :
		$id = $filter->filter_number($_POST['id']);
		//查询信息
		$exp = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_service','where'=>"id={$id}"));
		//删除信息
		$params = array('table'=>'oa_zhsz_service','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		if($pd->delete($params)){
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
		
		echo json_encode($returnVal);
		break;
		
	//删除社会实践
	case 'del_event' :
		$id = $filter->filter_number($_POST['id']);
		//查询信息
		$exp = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_event','where'=>"id={$id}"));
		//删除信息
		$params = array('table'=>'oa_zhsz_event','where'=>"id={$id} and create_by='{$_SESSION['username']}'");
		if($pd->delete($params)){
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
		
		echo json_encode($returnVal);
		break;
		
	//删除学习经历
	case 'del_biye' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_biye','where'=>"id={$id}");
		
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '学习经历删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '学习经历删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除用户
	case 'del_user' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_users','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '记录删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '记录删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//重置密码
	case 'set_pass' :
		$id   = $filter->filter_number($_POST['id']);
		$p    = isset($_POST['p'])?intval($_POST['p']):0;
		$username = $pd->fetchOne(array('field'=>'username','table'=>'oa_zhsz_users','where'=>"id={$id}"));
		if($p){
			$userpass = '000000';
		}else{
			$userpass = substr($username,-6);
		}
		$data = array('userpass'=>md5($userpass));

		$params = array('table'=>'oa_zhsz_users','data'=>$data,'where'=>"id={$id}");
		
		$r = $pd->update($params);
		if($r || $r ==0){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '密码更新成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '密码更新失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//上传学生身份证号码
	case 'upload_card' :
		$attFile = empty($_FILES['user_list'])?'':$_FILES['user_list'];
		include('upload.php');
		//判断是否上传文件
		if(!empty($attFile['name'])){
			$fileDoc = uploadFile($attFile,$_SESSION['uid']);
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
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = "上传错误";
		}
		echo json_encode($returnVal);
		break;
		
	//查询登录名是否存在
	case 'check_user' :
		$username = Filter::filter_html($_POST['username']);
		$where    = "username='{$username}'";
		$params   = array('field'=>'count(*)','table'=>'oa_zhsz_users','where'=>"{$where}");
		$nums     = $pd->fetchOne($params);
		
		if($nums>0){
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '抱歉，该登录名已存在。';
		}else{
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '恭喜，该登录名可以使用。';
		}
		echo json_encode($returnVal);
		break;
		
	//删除联系人
	case 'del_relationa' :
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

	//删除附件
	case "del_file":
		$path_id = $filter->filter_number($_POST['path_id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_attachment','where'=>"id={$path_id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '文件删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '文件记录删除失败。';
		}
		echo json_encode($returnVal);
		break;
		
	//删除图片
	case "del_pic":
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_attachment','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '图片删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '图片记录删除失败。';
		}
		echo json_encode($returnVal);
		break;
		
	//删除考试项
	case 'del_exam' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_exam_type','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '考试项删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '考试项删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//查询自定义报表
	case 'select_report' :
		$id     = $filter->filter_number($_POST['id']);
		$where  = " parent_no={$id} and school={$_SESSION['school']}";
		$report  = $pd->query("SELECT id,code_name,content from  oa_zhsz_report where {$where} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		$returnVal['value'] = $report;
		echo json_encode($returnVal);
		break;
		
	case 'del_jiyu' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_remarks','where'=>"id={$id}");
		
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '寄语删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '寄语删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	//删除奖惩
	case 'del_jiangcheng' :
		$id = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_reward_punishment','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '奖惩项删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '奖惩项删除失败。';
		}
		
		echo json_encode($returnVal);
		break;
	case 'select_report_custom' :
		$sub_id = $filter->filter_number($_POST['sub_id']);
		$custom  = $pd->query("SELECT * from  oa_zhsz_report where  id={$sub_id}")->fetchAll(PDO::FETCH_ASSOC);

		if(!empty($custom)){
			$returnVal['flag']  = 'true';
			$returnVal['is_partner']  = $custom[0]['is_partner'];
			$returnVal['is_att']  = $custom[0]['is_att'];
			$returnVal['is_pic']  = $custom[0]['is_pic'];
			$returnVal['is_teacher']  = $custom[0]['is_teacher'];
			
			$custom_son  = $pd->query("SELECT * from  oa_zhsz_report_item where  report_id={$sub_id}")->fetchAll(PDO::FETCH_ASSOC);
			
			$returnVal['value'] = $custom_son;
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '分类查询错误';
		}
		
		echo json_encode($returnVal);
		break;
	case 'select_report' :
		$id     = $filter->filter_number($_POST['id']);
		$where  = " parent_no={$id} and school={$_SESSION['school']}";
		$report  = $pd->query("SELECT id,code_name,content,is_submit from  oa_zhsz_report where {$where} order by order_value")->fetchAll(PDO::FETCH_ASSOC);
		$returnVal['value'] = $report;
		echo json_encode($returnVal);
		break;
		
	#友朋寄语审核权限
	case 'other_remarks_edit' :
		$id = $filter->filter_number($_POST['id']);
		$content  = $pd->query("SELECT * from  oa_zhsz_remarks where id={$id}")->fetchAll(PDO::FETCH_ASSOC);
		
		if(!empty($content)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = $content;
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '举报获取错误';
		}
		
		echo json_encode($returnVal);
		break;
}		
$pd->close();
unset($pd);
unset($rs);