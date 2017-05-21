<?php 
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
session_start(); 
//$uid= $_SESSION["uid"];

$pd=new pdo_mysql();
$filter = new Filter();

switch($_POST["tpl"]){
	case "get_terms":
		$rs=$pd->query("select id,year,term_name from oa_zhsz_term where school=".$_SESSION["school"]." order by year desc,term_type desc");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;   
		
	case "get_grades":
		$rs=$pd->query("select id,grade_name from oa_zhsz_grade");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;   
		
	case "get_biye":
		$rs=$pd->query("SELECT id,class_end from oa_zhsz_class WHERE grade_id =0 group by class_end order by class_end DESC");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
		
	case "get_dept":
		$rs=$pd->query("select id,dept_name from oa_zhsz_dept");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;   
	
	case "get_role":
		$rs=$pd->query("select id,name from oa_zhsz_roles");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;   
	
	case "jiangcheng":
		$rs=$pd->query("select * from oa_zhsz_code where parent_no='JIANGCHENG' and flag_status=1");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
	
	case "exam_type":
		//$rs=$pd->query("select * from oa_zhsz_code where parent_no='EXAM_TYPE' and flag_status=1");
		$rs=$pd->query("select * from oa_exam_type order by id");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break; on_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
	
	case "nation":
		$rs=$pd->query("select * from oa_zhsz_code where parent_no='NATION'");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
		
	case "from_type":
		$rs=$pd->query("select * from oa_zhsz_code where parent_no='STUDENT_FROM'");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
		
	case "user_status":
		$rs=$pd->query("select * from oa_zhsz_code where parent_no='STUDENT_STATUS'");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
		
	case "techer_status":
		$rs=$pd->query("select * from oa_zhsz_code where parent_no='TEACHER_STATUS'");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;  
		
	case "political_status":
		$rs=$pd->query("select * from oa_zhsz_code where parent_no='POLITICAL'");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break; 
		
	case "course_all_statistics":
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		$where = 'A.grade_id<>0';
		$sql 	= 'select A.*,(select count(*) from oa_zhsz_users where department=A.id and flag_type=1) as nums ';
		$sql 	.= ' from oa_zhsz_class AS A ';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by A.grade_id,A.id ';
		$sql 	.= " limit {$p},20";
		
		$class=$pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$course  = $pd->query("select id,course_name from oa_zhsz_course where is_checked=1 order by id DESC")->fetchAll(PDO::FETCH_ASSOC);
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		$termid = isset($_GET['term_id'])?$_GET['term_id']:$curTerm["id"];
		
		//统计报表
		foreach($class as $k=>$rs){
			$class[$k]['grade_name']  = $rs['grade_id']==3?'高三':($rs['grade_id']==1?'高一':'高二');
			for($i=0;$i<count($course);$i++){
				$course_num = $pd->query("select count(*) as num from oa_zhsz_course_level A left join oa_zhsz_users B on A.user_id=B.id where course_id={$course[$i]['id']} and B.department={$rs['id']} and A.term_id={$termid}")->fetchAll(PDO::FETCH_ASSOC);
				$class[$k]["cnums"][$i] = $course_num[0]['num'];
			}  
		}
		echo json_encode($class);
		break;  
		
	case "del_reply":
		$id   = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_comment','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '评论删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '评论删除失败。';
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
		
	//删除评价项
		$id     = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_votelib','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '评价项删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '评价项删除失败。';
		}
	
		echo json_encode($returnVal);
		break;
		
	case "vote_add_submit":
		$voteName = Filter::filter_html($_POST['vote_name']);
		$voteCat  = Filter::filter_html($_POST['vote_cat']);
		$voteItem = Filter::filter_html($_POST['vote_item']);
		
		$data = array(
			'name'        => $voteName,
			'vote_cat'    => $voteCat,
			'item'        => $voteItem,
			'create_time' => date('Y-m-d H:i:s'),
			'create_by'   => $_SESSION['username']
		);
		$params = array('data'=>$data,'table'=>'oa_zhsz_votelib');
		$result = $pd->insert($params);
		
		if(!empty($result)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '操作成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '操作失败。';
		}
		
		echo json_encode($returnVal);
		break;
		
	case "vote_modify_submit":
		$voteName = Filter::filter_html($_POST['vote_name']);
		$voteCat  = Filter::filter_html($_POST['vote_cat']);
		$voteItem = Filter::filter_html($_POST['vote_item']);
		$id       = Filter::filter_number($_POST['id']);
		
		$data = array(
			'name'     => $voteName,
			'vote_cat' => $voteCat,
			'item'     => $voteItem
		);
		$where = "id={$id}";
		$params     = array('data'=>$data,'table'=>'oa_zhsz_votelib','where'=>$where);
		$result     = $pd->update($params);
		if(!empty($result)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '操作成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '操作失败。';
		}
	
		echo json_encode($returnVal);
		break;
	
	case "zhsz_search": #综合查询
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$table = empty($_POST['table'])?'oa_zhsz_experience':$_POST['table'];
	
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master={$_SESSION['uid']} and grade_id<>0"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		if(empty($tid)){
			$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','year'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
			$tid     = $curTerm['id'];
			$tyear   = $curTerm['year'];
		}else{
			$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','year'),'table'=>'oa_zhsz_term','where'=>"id={$tid}"));
			$tyear   = $curTerm['year'];
		}

		//查询记录
		$strWhere = "  1=1 ";
		
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				
				$classId  = join(',',$classId);

				$strWhere .= " and A.department IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and A.department={$classId} ";
			}
		}

		if(!empty($truename)){
			$strWhere .= "  and A.truename LIKE'{$truename}%' ";
		}
		
		if(!empty($tid)){
			if($table!='oa_zhsz_research'){
				$strWhere .= "  and C.term_id={$tid} ";
			}
		}
		
		$order = ' order by CONVERT(A.truename USING GBK) ';
		$order = ' order by uid,C.id DESC ';
		
		$join = '';
		$field = '';
		$title = '';
		
		if($table=='oa_zhsz_experience'||$table=='oa_zhsz_research'||$table=='oa_zhsz_service'||$table=='oa_zhsz_event'||$table=='oa_zhsz_result'){
			if($table!='oa_zhsz_research'){
				$join = "  inner join {$table} as C ON C.create_by=A.username and C.flag_status='已审核' ";
			}else{
				$join = "  inner join {$table} as C ON C.create_by=A.username and C.flag_status='已审核' and C.term_year='{$tyear}' ";
			}
			$field = ',C.*';	
		}
		
		$where  = $strWhere;
		if($table=='oa_zhsz_performance'){
			if($isMaster==false){
			  $where .=" and pertype=0";
			}
			$sql 	= "select A.truename,A.id AS uid,C.class_name,C.performance ";
			$sql 	.= ' from oa_zhsz_users AS A ';
			$sql    .= ' inner join oa_zhsz_performance as C ON A.id=C.user_id ';
			$sql 	.= " where {$where} ";
			$sql 	.=  $order;
			$sql 	.= " limit {$p},20";
			//记录查询
			$record  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		}
	
		if($table=='oa_zhsz_experience'||$table=='oa_zhsz_research'||$table=='oa_zhsz_service'||$table=='oa_zhsz_event'||$table=='oa_zhsz_result'){
			$sql 	= "select A.truename,A.id AS uid,(select concat(class_name,'-',grade_id) from oa_zhsz_class where id=A.department) class_name{$field} ";
			$sql 	.= ' from oa_zhsz_users AS A ';
			$sql    .= $join;
			$sql 	.= " where {$where} ";
			$sql 	.=  $order;
			$sql 	.= " limit {$p},20";
			//记录查询
			$record  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			foreach($record as $k=>$rs){
				$t = explode('-',$rs['class_name']);
				if(!isset($t[1])){
					$record[$k]['class_name']= '无';
				}else{
					if($t[1]==1){
						$record[$k]['class_name'] = '高一'.$t[0];
					}
					if($t[1]==2){
						$record[$k]['class_name'] = '高二'.$t[0];
					}
					if($t[1]==3){
						$record[$k]['class_name'] = '高三'.$t[0];
					}
				}
			}
		}
		
		if($table=='oa_zhsz_object'||$table=='oa_zhsz_myself'||$table=='oa_zhsz_master'||$table=='oa_zhsz_specail'){
		  $rs1=array();
		  $sql2="select A.truename,A.id as uid,C.class_name,C.object,C.self_summary,C.comment,C.sp_course from oa_zhsz_users AS A left join oa_zhsz_main AS C ON A.id=C.user_id ";
		  
		  if(!empty($truename)){
				$sql2 .= "  and A.truename='{$truename}' ";
			}else{   
				$sql1="select A.id as uid,A.truename from oa_zhsz_users AS A left join (select * from oa_zhsz_main_statistics where term_id={$tid}) as C on A.id=C.user_id where flag_type=1 and department='{$cid}' ";
			$sql1 .=  $order;
			$rs1 = $pd->query($sql1)->fetchAll(PDO::FETCH_ASSOC);
		  }
			$sql2	.= " where {$where} ";
		  $sql2 .=  $order;

			//记录查询
		  $rs2 = $pd->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
		  $aa = array_merge($rs2,$rs1);
		  
		  $record= array();
		  foreach ($aa as $k=>$row) { 
			$class_name=$aa[0]['class_name'];
			$key=$row['uid'];
			if (array_key_exists($row['uid'], $record)) {
			   if(!isset($aa[$key]['class_name'])){
				  unset($aa[$key]);
			   }else{
				  $record[$key]=$row;
			   }
			}else{ 
			   if(!isset($row['class_name'])){
				  $record[$key]=$row;
				  $record[$key]['class_name']=$class_name;
				  $record[$key]['object']='';
				  $record[$key]['self_summary']='';
				  $record[$key]['comment']='';
				  $record[$key]['sp_course']='';
				  
			   }else{
				  $record[$key]=$row;  
			   }
			}
		  }
			$record = array_merge($record);
			
			if(!empty($record)){
				foreach($record as $k=>$rs){
				    if($table=='oa_zhsz_object'){
						$content = $rs['object'];	
					}
					
					if($table=='oa_zhsz_myself'){
						$content = $rs['self_summary'];	
					}
					
					if($table=='oa_zhsz_master'){
						$content = $rs['comment'];
					}
					
					if($table=='oa_zhsz_specail'){
						$content = $rs['sp_course'];	
					}
	  
					if(empty($content) || $content==null){
						$content = '';
					}
					$content = trim($content);
					$content = str_replace(' ','',$content);
					$content = str_replace('　','',$content);
					$record[$k]['content'] = $content;
			  }
			}
		}
		echo json_encode($record);
		break;
		
	case "zhsz_checked": #综评审核
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$table = empty($_POST['table'])?'oa_zhsz_experience':$_POST['table'];
	
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master={$_SESSION['uid']} and grade_id<>0"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		if(empty($tid)){
			$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','year'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
			$tid     = $curTerm['id'];
			$tyear   = $curTerm['year'];
		}else{
			$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','year'),'table'=>'oa_zhsz_term','where'=>"id={$tid}"));
			$tyear   = $curTerm['year'];
		}

		//查询记录
		$strWhere = "  1=1 ";
		
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				
				$classId  = join(',',$classId);

				$strWhere .= " and A.department IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and A.department={$classId} ";
			}
		}

		if(!empty($truename)){
			$strWhere .= "  and A.truename LIKE'{$truename}%'";
		}
		
		if(!empty($tid)){
			if($table!='oa_zhsz_research'){
				$strWhere .= "  and C.term_id={$tid} ";
			}
		}
		
		$order = ' order by CONVERT(A.truename USING GBK) ';
		$order = ' order by uid,C.id DESC ';
		
		$join = '';
		$field = '';
		$title = '';
		
		if($table=='oa_zhsz_experience'||$table=='oa_zhsz_research'||$table=='oa_zhsz_service'||$table=='oa_zhsz_event'||$table=='oa_zhsz_result'){
			if($table!='oa_zhsz_research'){
				$join = "  inner join {$table} as C ON C.create_by=A.username";
			}else{
				$join = "  inner join {$table} as C ON C.create_by=A.username and C.term_year='{$tyear}' ";
			}
			$field = ',C.*';	
		}
		
		$where  = $strWhere;
	
		if($table=='oa_zhsz_experience'||$table=='oa_zhsz_research'||$table=='oa_zhsz_service'||$table=='oa_zhsz_event'||$table=='oa_zhsz_result'){
			$sql 	= "select A.truename,A.id AS uid,(select concat(class_name,'-',grade_id) from oa_zhsz_class where id=A.department) class_name{$field} ";
			$sql 	.= ' from oa_zhsz_users AS A ';
			$sql    .= $join;
			$sql 	.= " where {$where} ";
			$sql 	.=  $order;
			$sql 	.= " limit {$p},20";
			//记录查询
			$record  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			foreach($record as $k=>$rs){
				$t = explode('-',$rs['class_name']);
				if(!isset($t[1])){
					$record[$k]['class_name']= '无';
				}else{
					if($t[1]==1){
						$record[$k]['class_name'] = '高一'.$t[0];
					}
					if($t[1]==2){
						$record[$k]['class_name'] = '高二'.$t[0];
					}
					if($t[1]==3){
						$record[$k]['class_name'] = '高三'.$t[0];
					}
				}
			}
		}
		echo json_encode($record);
		break;
		
	case "zhsz_setting_checked": #综合素质档案审核
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$table = empty($_POST['table'])?'oa_zhsz_setting_duty':$_POST['table'];
		$code_no = empty($_POST['code_no'])?0:$_POST['code_no'];
		
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master={$_SESSION['uid']} and grade_id<>0"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		
		//查询记录
		$strWhere = "  1=1 ";
		
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and C.class_id IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and C.class_id={$classId} ";
			}
		}
		if(!empty($truename)){
			$strWhere .= "  and C.truename LIKE'{$truename}%'";
		}
		
		if(!empty($code_no)){
			$strWhere .= "  and C.flag_cat1='{$code_no}' ";
		}
		
		$order = ' order by CONVERT(C.truename USING GBK) ';

		$where  = $strWhere;
		$sql 	= "select C.* ";
		$sql 	.= ' from oa_zhsz_users AS A ';
		$sql    .= " inner join {$table} as C ON A.id=C.user_id ";
		$sql 	.= " where {$where} ";
		$sql 	.=  $order;
		$sql 	.= " limit {$p},20";
		//记录查询
		$record  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		if($table=="oa_zhsz_setting_performance"){
			foreach($record as $k=>$rs){
				$ca = unserialize($rs['performance']);
				$i = 1;
				foreach($ca as $cs){
					if(empty($cs)){
						continue;	
					}
					$record[$k]['performance'] = '<font><strong>'.$i++.'.&nbsp;&nbsp;</strong></font>'.$cs.'<br />';
				}
			}
		}
		
		if($table=='oa_zhsz_setting_morality'){
			$pindeTitle = array(
				'PINDE_GAXD' => array('object'=>'关爱对象','content'=>'行动内容','times'=>'持续时间或次数'),
				'PINDE_GYHD' => array('content'=>'活动内容','address'=>'活动地点','times'=>'持续时间或次数'),
				'PINDE_ZYFW' => array('object'=>'服务对象','content'=>'服务内容','address'=>'服务地点','times'=>'持续时间或次数')
			);
			foreach($record as $k=>$rs){
				$moralityData[$rs['flag_cat1']] = array('id'=>$rs['id'],'content'=>unserialize($rs['performance']));
				$str_total = '';
				foreach($pindeTitle[$rs['flag_cat1']] as $key=>$rsc){
					$str_w = $key=='content'?'':'width="18%"';
					$str_total.='<td '.$str_w.' align="center">'.$rsc.'</td>';
				}
				$record[$k]['str_width'] = $str_total;
				
				$pinde2  = $pd->query("select code_no,code_name from oa_zhsz_code where flag_status=1 and parent_no='{$rs['flag_cat1']}'")->fetchAll(PDO::FETCH_ASSOC);
				foreach($pinde2 as $rsb){
					$maxItem = count($moralityData[$rs['flag_cat1']]['content'][$rsb['code_no']]);
					
					for($i=1;$i<=$maxItem;$i++){
						$str_total1='';
						foreach($pindeTitle[$rs['flag_cat1']] as $key=>$rsc){
							$str_w = $key=='content'?'':'width="18%"';
							$str_total1.='<td '.$str_w.' align="center">'.$moralityData[$rs['flag_cat1']]['content'][$rsb['code_no']][$i-1][$key].'</td>';
						}
						
						$str1 = '<tr>'.$str_total1.'</tr>';
						
						if($rsb['code_name']=='其它'){
							$maxItem=1;	
						}
					}
					$str2 = '<tr><td width="10%" class="tabtd2_L">'.$rsb['code_name'].'</td><td class="tabtd2_R"><table cellpadding="0" cellspacing="0" border="0" width="100%">'.$str1.'</table></td></tr>';
					$record[$k]['str_total'] = $str2;
				}
				
			}
		}
		if($table=='oa_zhsz_setting_interest'){
			$xingquTitle = array(
			 'XINGQU' => array('content'=>'具体内容','times'=>'持续时间','join'=>'参加的社团等组织、项目名称','result'=>'取得的校级及以上主要成绩')
			);

			foreach($record as $k=>$rs){
				$interestData[$rs['flag_cat1']] = array('id'=>$rs['id'],'content'=>unserialize($rs['performance']));
				$maxItem = count($interestData[$rs['flag_cat1']]['content']);
				for($i=1;$i<=$maxItem;$i++){
					$str_total1='';
					foreach($xingquTitle['XINGQU'] as $key=>$rsc){
						$str_w = $key=='content'?'':'width="18%"';
						$str_total1.='<td '.$str_w.' align="center">'.$interestData[$rs['flag_cat1']]['content'][$i-1][$key].'</td>';
					}
					$str1 = '<tr>'.$str_total1.'</tr>';
					
					if($rs['flag_cat2']=='其它'){
						$maxItem=1;	
					}
				}
				$record[$k]['str_interest'] = $str1;
			}
		}
		
		if($table=='oa_zhsz_setting_research'){
			$searchIn = array(
				'subject' => '题目',
				'content' => '内容',
				'time'    => '起止时间',
				'duty'    => '担任组长或成员',
				'task'	  => '承担任务',
				'show'    => '成果呈现形式',
				'result'  => '成绩'
			);
			
			foreach($record as $k=>$rs){
				$researchData = unserialize($rs['performance']);
				foreach($researchData as $rsd){
					$str_total1 = "";
					foreach($searchIn as $key=>$val){
						$str_w = $key=='content'?'':'width="15%"';
						$str_total1.='<td '.$str_w.' align="center">'.$rsd[$key].'</td>';
					}
					$str1 = '<tr>'.$str_total1.'</tr>';
				}
				$record[$k]['str_research'] = $str1;
			}
			
		}
		
		if($table=='oa_zhsz_setting_event'){
			$eventTitle = array('content'=>'内容','time'=>'起止时间','address'=>'地点','result'=>'实践成果');
			foreach($record as $k=>$rs){
				$eventData[$rs['flag_cat1']] = array('id'=>$rs['id'],'content'=>unserialize($rs['performance']));
				$maxItem = count($eventData[$rs['flag_cat1']]['content']);
				for($i=1;$i<=$maxItem;$i++){
					$str_total1 = "";
					foreach($eventTitle as $key=>$rsc){
						$str_w = $key=='content'?'':'width="25%"';
						$str_total1.='<td '.$str_w.' align="center">'.$eventData[$rs['flag_cat1']]['content'][$i-1][$key].'</td>';
					}
					$str1 = '<tr>'.$str_total1.'</tr>';
					if($rs['flag_cat2']=='其它'||$rs['flag_cat2']=='军训'){
						$maxItem=1;	
					}
				}
				$record[$k]['str_event'] = $str1;
			}
		}
		
		echo json_encode($record);
		break;
		
	case "score_manage": #成绩管理
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		$et  = empty($_POST['exam_type'])?'':$_POST['exam_type'];
		$orderBy  = empty($_POST['order'])?'':$_POST['order'];
	
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0 and school={$_SESSION['school']}"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		
		//是否为任课老师
		if($_SESSION['role_id']==8){

			$class = $pd->query('select class_id,course_id from oa_zhsz_teach where user_id='.$_SESSION['uid'].' and school='.$_SESSION['school'].'')->fetchAll(PDO::FETCH_ASSOC);
			
			$courseid = $class[0]['course_id'];
			$classId = "";
			foreach($class as $v){
			  $classId .= ",".$v['class_id'];
			}        
			$gradeId = $pd->query("select grade_id from oa_zhsz_class where id in (".$classId.")")->fetchAll(PDO::FETCH_ASSOC);
			//测试年级数组组成
			$gradeId=array(array("grade_id"=>1),array("grade_id"=>2),array("grade_id"=>1));
			$grade=array();
			foreach ($gradeId as $k=>$v) {
			   if(!in_array($v['grade_id'],$grade)){
				 $grade[]=$v['grade_id'];
			   }
			   if(count($grade)==0){
				  $gid=0;
			   }else if(count($gradeId)==1){
				  $gid=$grade[0];
			   }else{
				  $gid=implode(',',$grade);
			   }
			}
		}

		//查询记录
		$strWhere = "  1=1 ";
		
		//查询所属于班级
		if(strpos($gid,",") == false){
			if($gid>0){
				if($cid==0){
					$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid} and school={$_SESSION['school']}"));
					$classId  = join(',',$classId);
					$strWhere .= " and A.dept IN ({$classId}) ";
				}else{
					$classId = $cid;
					$strWhere .= " and A.dept={$classId} ";
				}
			}
		}else{
			$strWhere .= " and A.dept in ({$classId})";
		}
		
		if(!empty($et)){
			$strWhere .= "  and B.exam_type='{$et}' ";
		}
		if(!empty($truename)){
			$strWhere .= "  and D.truename LIKE'{$truename}%'";
		}
		if(!empty($tid)){
			$strWhere .= "  and B.term_id={$tid} ";
		}
		$order = ' order by CONVERT(D.truename USING GBK) ';
		if(!empty($orderBy)){
			if($orderBy=='sc_order'||$orderBy=='jc_order'){
				$order = " ORDER BY class_id,{$orderBy} ";
			}
			if($orderBy=='sg_order'||$orderBy=='jg_order'){
				$order = " ORDER BY {$orderBy} ";
			}
		}

		$where  = $strWhere;
		$sql 	= 'select D.truename,B.*,C.year,C.term_name ';
		$sql 	.= ' from oa_user_extend AS A ';
		$sql    .= ' inner join oa_zhsz_score as B ON A.userid=B.user_id ';
		$sql    .= ' inner join oa_zhsz_term as C ON C.id=B.term_id  inner join act_member AS D ON B.user_id=D.id';
		$sql 	.= " where {$where} ";
		$sql 	.=  $order;
		$sql 	.= " limit {$p},20";
		//成绩记录查询
		$score  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($score as $k=>$rs){
			$score[$k]['yw'] = $rs['yw']==-1?'-':$rs['yw'];
			$score[$k]['sx'] = $rs['sx']==-1?'-':$rs['sx'];
			$score[$k]['wy'] = $rs['wy']==-1?'-':$rs['wy'];
			$score[$k]['wl'] = $rs['wl']==-1?'-':$rs['wl'];
			$score[$k]['hx'] = $rs['hx']==-1?'-':$rs['hx'];
			$score[$k]['sw'] = $rs['sw']==-1?'-':$rs['sw'];
			$score[$k]['zz'] = $rs['zz']==-1?'-':$rs['zz'];
			$score[$k]['ls'] = $rs['ls']==-1?'-':$rs['ls'];
			$score[$k]['dl'] = $rs['dl']==-1?'-':$rs['dl'];
			$score[$k]['xxjs'] = $rs['xxjs']==-1?'-':$rs['xxjs'];
			$score[$k]['tyjs'] = $rs['tyjs']==-1?'-':$rs['tyjs'];
			$score[$k]['ty'] = $rs['ty']==-1?'-':$rs['ty'];
			$score[$k]['yy'] = $rs['yy']==-1?'-':$rs['yy'];
			$score[$k]['ms'] = $rs['ms']==-1?'-':$rs['ms'];
			$score[$k]['xl'] = $rs['xl']==-1?'-':$rs['xl'];
			$score[$k]['sc_order'] = empty($rs['sc_order'])?'暂无名次':"第{$rs['sc_order']}名";
			$score[$k]['sg_order'] = empty($rs['sg_order'])?'暂无名次':"第{$rs['sg_order']}名";
		}
		
		echo json_encode($score);
		break;
		
	case "stamina_rs_manage": #体能管理
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
		$gid = empty($_POST['gid'])?0:intval($_POST['gid']);
		$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
		$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		//$priv2 = empty($_POST['priv2'])?0:intval($_POST['priv2']);
		//$priv3 = empty($_POST['priv3'])?0:intval($_POST['priv3']);
		if($tid==0){
			$tid = $curTerm['id'];	
		}
		
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master={$_SESSION['uid']} and grade_id<>0"));
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		
		$ttid = 0;
		if($tid>0){
			$ttid = $pd->fetchOne(array('field'=>'term_type','table'=>'oa_zhsz_term','where'=>"id={$tid}"));
		}
		
		$where = '1=1';
		if($gid>0){
			$where .= " and (grade_id=0 OR grade_id={$gid}) ";	
		}
		if($ttid>0){
			$where .= " and (term_type=0 OR term_type={$ttid}) ";
		}
		
		//体能项目
		$stamina = $pd->query("select id,name from oa_zhsz_stamina where {$where} order by id")->fetchAll(PDO::FETCH_ASSOC);
		//查询记录
		$strWhere = ' A.flag_type=1 ';

		//默认为当前学期高一年级
		$gradeName = '';
		//查询所属于班级
		if($gid>0){
			$gradeName = $pd->fetchOne(array('field'=>'grade_name','table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
			if($cid==0){
				$classId  = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
				$classId  = join(',',$classId);
				$strWhere .= " and A.department IN ({$classId}) ";
			}else{
				$classId  = $cid;
				$strWhere .= " and A.department={$classId} ";
			}
		}
		
		if(!empty($truename)){
			$strWhere .= "  and A.truename LIKE'{$truename}%' ";
		}
		
		$where  = $strWhere;
		$sql 	= 'select A.*,B.class_name ';
		$sql 	.= ' from oa_zhsz_users AS A ';
		$sql    .= ' inner join oa_zhsz_class as B ON A.department=B.id ';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by CONVERT(A.truename USING GBK) ';
		$sql 	.= " limit {$p},20";
		//学生查询
		$student  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$len = count($stamina);
		$itemA = array();
		foreach($stamina as $rs){
			$itemA[$rs['id']] = $rs['id'];
		}

		foreach($student as $k=>$rs){
			$where = '';
			if($tid>0){
				$where = " and term_id={$tid}";	
			}
			//查询学生体能记录
			$sm = $pd->query("select item_id,item_name,result from oa_zhsz_stamina_record where user_id={$rs['id']} {$where} order by item_id")->fetchAll(PDO::FETCH_ASSOC);

			$str='';
			foreach($sm as $rsb){
				if(isset($itemA[$rsb['item_id']])){
					$str.="<td class='tabtd'>{$rsb['result']}</td>";	
				}
			}
			if(empty($sm)){
				for($i=0;$i<$len;$i++){
					$str.= "<td class='tabtd'>-</td>";	
				}
				$str.= '<td class="tabtd" style="text-align:left; padding-left:10px; color:#333;">&nbsp;暂无数据</td>';
			}else{
				$str.='<td class="tabtd" style="text-align:left; padding-left:10px;">';
				//if($priv2){
					$str.='<img src="images/037.gif" /> <a href="./?t=stamina_rs_modify&uid='.$rs['id'].'&tid='.$tid.'">修改</a> ';
				//}
				//if($priv3){
					$str.='<img src="images/010.gif" /> <a href="javascript:delStamina('.$rs['id'].','.$tid.');">删除</a>';
				//}
				$str.="</td>";
			}				
			$student[$k]['str_stu'] = $str;
		}
		
		echo json_encode($student);
		break;
		
	case "class_manage": #班级管理
		$strWhere = 'A.grade_id<>0 and A.school='.$_SESSION["school"];
		$curId	  = empty($_POST['grade_id'])?0:$_POST['grade_id'];
		$curId	  = Filter::filter_number($curId);
		//查询条件
		if(!empty($curId)){
			$strWhere .= " AND A.grade_id={$curId} ";
		}
		
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		//分页配置
		$where  = $strWhere;
		$sql 	= 'select A.*,B.grade_name ';
		$sql 	.= ' from oa_zhsz_class AS A ';
		$sql    .= ' INNER JOIN oa_zhsz_grade AS B ON A.grade_id=B.id ';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by B.id,A.id ';
		$sql 	.= " limit {$p},20";
		//班级查询
		$class  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($class as $k=>$rs){
			//查询班主任
			$classMaster = '暂无';
			if(!empty($rs['class_master'])){
				$classMaster = $pd->fetchOne(array('field'=>'truename','table'=>'oa_zhsz_users','where'=>"id={$rs['class_master']}"));	
			}
			$class[$k]['classMaster'] = $classMaster;
		}
		echo json_encode($class);
		break;
		
	case "graduate_class": #班级管理
		$strWhere = 'A.grade_id=0 and A.school='.$_SESSION["school"];
		$start	  = empty($_POST['start'])?'':$_POST['start'];
		$start	  = Filter::filter_html($start);
		$end	  = empty($_POST['end'])?'':$_POST['end'];
		$end	  = Filter::filter_html($end);
		//查询条件
		if(!empty($start)){
			$strWhere .= " AND A.class_start like '{$start}%' ";
		}
		if(!empty($end)){
			$strWhere .= " AND A.class_end like '{$end}%' ";
		}
		
		$p = $_POST["page"]?$_POST["page"]:1;
		$p = ($p-1)*20;
		
		//分页配置
		$where  = $strWhere;
		$sql 	= 'select A.* ';
		$sql 	.= ' from oa_zhsz_class AS A ';
		$sql 	.= " where {$where} ";
		$sql 	.= ' order by A.id ';
		$sql 	.= " limit {$p},20";
		//班级查询
		$class  = $pd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		foreach($class as $k=>$rs){
			//查询班主任
			$classMaster = '暂无';
			if(!empty($rs['class_master'])){
				$classMaster = $pd->fetchOne(array('field'=>'truename','table'=>'oa_zhsz_users','where'=>"id={$rs['class_master']}"));	
			}
			$class[$k]['classMaster'] = $classMaster;
		}
		echo json_encode($class);
		break;
		
	case "code_modify":
		$rs=$pd->query("SELECT id,name from oa_zhsz_privileges where parent_id=0");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
	case "code_add":
		$rs=$pd->query("SELECT id,code_no,code_name from  oa_zhsz_code where parent_no=''");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break;
	case "code_del":
		$id=$_POST['id'];
		$pd->exec("delete from oa_zhsz_code where id=".$id);	
		break;
		
	case "role_del":
		$id=$_POST['id'];
		$pd->exec("delete from oa_zhsz_role_privileges where role_id=".$id);
		$pd->exec("delete from oa_zhsz_roles where id=".$id);
		break;
	case "r_role":
		$id=$_POST['id'];
		$rs=$pd->query("select * from oa_zhsz_role_privileges a left join oa_zhsz_privileges b on a.privilege_id=b.id where role_id=".$id);
		break;
		
	case "zhsz_privileges":
		$rs=$pd->query("SELECT id,name from oa_zhsz_privileges where pid=0");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
	case "role_modify":
		$rs=$pd->query("select * from oa_zhsz_privileges where type>0 and flag_menu=1 and flag_use=1 order by pid,order_value");
		$arr_sub = array();
		$arr_sub2 = array();
		$arr_all = array();
		$arr_end = array();
		$arr_total = array();
		$arr_role=array();

		$r_rs=$pd->query("select privilege_id as id from oa_zhsz_role_privileges where role_id=".$_POST['sid']);
		$ind=0;
		while($r_rs1=$r_rs->fetch(PDO::FETCH_ASSOC)){
			$arr_role[$ind]=$r_rs1["id"];
			$ind++;
		}

		while ($r=$rs->fetch(PDO::FETCH_ASSOC)) {
			$arr_all["id"]=$r["id"];
			$arr_all["name"]=$r["name"];
			if(in_array($r["id"],$arr_role)){
				$arr_all["ckl"]='checked="checked"';
			}else{
				$arr_all["ckl"]="";
			}
			$rs1=$pd->query("select * from oa_zhsz_privileges where pid=".$r["id"]);
			while($r1=$rs1->fetch(PDO::FETCH_ASSOC)){
				$arr_sub["id"]=$r1["id"];
				$arr_sub["name"]=$r1["name"];
				if(in_array($r1['id'],$arr_role)){
					$arr_sub["ckl"]='checked="checked"';
				}else{
					$arr_sub["ckl"]="";
				}
				array_push($arr_sub2,$arr_sub);
			}
			
			$arr_all["list"]=$arr_sub2;
			array_push($arr_end,$arr_all);
			
			$arr_sub = array();
			$arr_sub2 = array();
		}

		$arr_total["results"]=$arr_end;
		$json = json_encode($arr_end,JSON_UNESCAPED_UNICODE);
		echo $json;
		break; 
	
	case "role_modify_feature":
		$rs=$pd->query("select * from oa_features where fea_states=0 and pid=0 order by pid,fea_sort");
		$arr_sub = array();
		$arr_sub2 = array();
		$arr_all = array();
		$arr_end = array();
		$arr_total = array();
		$arr_role=array();

		$r_rs=$pd->query("select privilege_id as id from oa_zhsz_role_privileges where role_id=".$_POST['sid']);
		$ind=0;
		while($r_rs1=$r_rs->fetch(PDO::FETCH_ASSOC)){
			$arr_role[$ind]=$r_rs1["id"];
			$ind++;
		}

		while ($r=$rs->fetch(PDO::FETCH_ASSOC)) {
			$arr_all["id"]=$r["id"];
			$arr_all["name"]=$r["fea_name"];
			if(in_array($r["id"],$arr_role)){
				$arr_all["ckl"]='checked="checked"';
			}else{
				$arr_all["ckl"]="";
			}
			$rs1=$pd->query("select * from oa_features where pid=".$r["id"]);
			while($r1=$rs1->fetch(PDO::FETCH_ASSOC)){
				$arr_sub["id"]=$r1["id"];
				$arr_sub["name"]=$r1["fea_name"];
				if(in_array($r1['id'],$arr_role)){
					$arr_sub["ckl"]='checked="checked"';
				}else{
					$arr_sub["ckl"]="";
				}
				array_push($arr_sub2,$arr_sub);
			}
			
			$arr_all["list"]=$arr_sub2;
			array_push($arr_end,$arr_all);
			
			$arr_sub = array();
			$arr_sub2 = array();
		}

		$arr_total["results"]=$arr_end;
		$json = json_encode($arr_end,JSON_UNESCAPED_UNICODE);
		echo $json;
		break; 
		
	case "r_priv":
		$id=$_POST['id'];
		$pd->exec("delete from oa_zhsz_privileges where id=".$id);
		break;
		
	case "zhsz_features":   //add
		$rs=$pd->query("SELECT id,fea_name from oa_features where fea_states=0 and fea_level in (0)");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
	case "r_features":
		$id=$_POST['id'];
		$pd->exec("delete from oa_features where id=".$id);
		break;
	case "get_menus":
		$id=$_POST['id'];
		$rs=$pd->query("select * from oa_features where fea_states=0 and m_id=".$id);
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		break; 
		
	case "report_edit":
		$id=$_POST['id'];
		$rs=$pd->query("select * from oa_zhsz_report_self where id={$id}");
		echo json_encode($rs->fetchAll(PDO::FETCH_ASSOC));
		break;
		
	case "del_vote":
		$id   = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_votelib','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '评论项删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '评论项删除失败。';
		}
		echo json_encode($returnVal);
		break;
		
	case "del_item":
		$id   = $filter->filter_number($_POST['id']);
		//删除信息
		$params = array('table'=>'oa_zhsz_report_item','where'=>"id={$id}");
		if($pd->delete($params)){
			$returnVal['flag']  = 'true';
			$returnVal['value'] = '内容项删除成功。';
		}else{
			$returnVal['flag']  = 'false';
			$returnVal['value'] = '内容项删除失败。';
		}
		echo json_encode($returnVal);
		break;
}		
$pd->close();
unset($pd);
unset($rs);