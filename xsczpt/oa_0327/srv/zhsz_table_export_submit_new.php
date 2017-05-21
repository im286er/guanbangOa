<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

$pd = new pdo_mysql();
$filter = new Filter();
//判断用户是否登录
session_start();
if(empty($_SESSION['username'])){
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
	$grade1 = $pd->query("SELECT c.grade_id from oa_user_extend u join oa_zhsz_class c on u.dept=c.id where u.userid='{$_SESSION['uid']}'")->fetchAll(PDO::FETCH_ASSOC);
	
	$gid = empty($grade1[0]['grade_id'])?0:$grade1[0]['grade_id'];
	
	$tid  = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id']);
	$is_all  = isset($_POST['is_all'])?Filter::filter_number($_POST['is_all']):0;    //学生导出页面传来,不需要选择学期了
	$truename = empty($_SESSION['truename'])?'':$_SESSION['truename'];

	//查询课程
	$course = $pd->query("SELECT * from oa_zhsz_course  where is_print=1 order by order_value")->fetchAll(PDO::FETCH_ASSOC);
	
	//查询课程模块
	$c = count($course);
	
	$where = 'flag_type=1';
	$className = '';
	$gradeName = '';
	$class     = array();
	$grade     = array();

	if($gid==0){
		$gradeName = '所有学生';
	}else{
		$grade = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
		$gradeName = $grade['grade_name'];
		//查询班级
		if(!empty($cid)){
			$class     = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$cid}"));
			$className = $class['class_name'];
			$where .= " and dept={$cid} ";
		}else{
			$classId = $pd->fetchCol(array('field'=>'id','table'=>'oa_zhsz_class','where'=>"grade_id={$gid}"));
			$classId = join(',',$classId);
			$where   .= " and dept IN ({$classId}) ";	
		}
	}
	
	$where .= " and truename = '{$truename}' ";
	
	if($is_all){
		$tid = 10000;
	}
	//查询当前学期
	if($tid != 10000){
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>"id={$tid}"));
		$termname = $curTerm['term_type']==1?'一':'二';
		$termType = $curTerm['term_type']==1?'第一学期':'第二学期';
		$dataname = str_replace('/','-',$curTerm['year']).'('.$termType.')'.$gradeName.$className.'综评表';
	}else{
		$dataname = $gradeName.$className.'全部综评表';
	}
	
	$filename  = date('YmdHis').'_'.$_SESSION['uid'];
	

	//查询目录是否存在
	if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
		mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
	}
	$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.doc';
	//查询学生记录
	$users = $pd->query("SELECT id,username,truename,dept,cardno,person_no,select_course_id from v_users  where {$where} order by CONVERT(truename USING GBK)")->fetchAll(PDO::FETCH_ASSOC);
	
	$max = 120;
	$html = readFileContent('zhsz_header.html');
	//读取模板文件
	
	$curTerm1 = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>"flag_default=1"));

	foreach($users as $rs){
		$uid = $rs['id'];

		$terms = $pd->query("SELECT id,year,term_name,term_type from oa_zhsz_term order by end desc")->fetchAll(PDO::FETCH_ASSOC);
		
		$now_time = (int)date('Y');
		$if_time = $now_time - 3;
	
		//if($tid==10000 && $gid==3 && $curTerm1['term_type']==2){
		if($tid==10000){
			$num = 0;
			$terms1 = array_reverse($terms);
			
			foreach($terms1 as $term){
				$term_time = explode("/",$term['year']);
				if($term_time){
					$term_year = (int)$term_time[0];
					
					if($term_year < $if_time)
						continue;
				}
				$num++;
				if($gid==3){
					if($num==1){
						$gradeName = '高一';
						$template = readFileContent('zhsz_template_new1.html');
					}else if($num==2){
						$gradeName = '高一';
						$template = readFileContent('zhsz_template_new2.html');
					}else if($num==3){
						$gradeName = '高二';
						$template = readFileContent('zhsz_template_new3.html');
					}else if($num==4){
						$gradeName = '高二';
						$template = readFileContent('zhsz_template_new4.html');
					}else if($num==5){
						$gradeName = '高三';
						$template = readFileContent('zhsz_template_new5.html');
					}else if($num==6){
						$gradeName = '高三';
						$template = readFileContent('zhsz_template_new6.html');
					}
				}else if($gid==2){
					if($num==1){
						$gradeName = '高一';
						$template = readFileContent('zhsz_template_new1.html');
					}else if($num==2){
						$gradeName = '高一';
						$template = readFileContent('zhsz_template_new2.html');
					}else if($num==3){
						$gradeName = '高二';
						$template = readFileContent('zhsz_template_new3.html');
					}else if($num==4){
						$gradeName = '高二';
						$template = readFileContent('zhsz_template_new4.html');
					}
				}else{
					if($num==1){
						$gradeName = '高一';
						$template = readFileContent('zhsz_template_new1.html');
					}else if($num==2){
						$gradeName = '高一';
						$template = readFileContent('zhsz_template_new2.html');
					}
				}
				$fileC = $template;
				
				
				$termname = $term['term_type']==1?'一':'二';

				//查询主表
				$main = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_main','where'=>"user_id='{$uid}' and term_id={$term['id']}"));
				
				if(empty($main)){
					$main = array();
					$main['id'] = -1;
					if(!empty($class)){//有班级
						$main['class_name']  = $class['class_name'];
						//if($isMaster==true){
							//$main['master_name'] = $_SESSION['truename'];
						//}else{
							$main['master_name'] = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
						//}
					}else{
						$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
						$main['class_name']  = $class['class_name'];
						$main['master_name'] = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
					}
					//continue;
				}
				if(empty($gid)){
					//查询年级
					$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
					$grade = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));	
				}
				//查询当前学生是否有选课
				$cnf = ' and select_course_id=0';
				if(!empty($rs['select_course_id'])){
					$cnf = " and (select_course_id={$rs['select_course_id']} OR select_course_id=0)";
				}
				for($i=0;$i<$c;$i++){
					$course[$i]['module'] = $pd->query("SELECT module_name from oa_zhsz_course_module where course_id={$course[$i]['id']} and grade_id={$grade['id']} and term_type={$term['term_type']} {$cnf} order by id DESC")->fetchAll(PDO::FETCH_ASSOC);
				}
				//查询成绩
				$score1 = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$term['id']} and exam_type='期中'"));
				$score2 = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$term['id']} and exam_type='期末'"));
				$score  = array(0=>$score1,1=>$score2);
				//查询研究性学习
				$research = $pd->fetchRow(array('field'=>array('content'),'table'=>'oa_zhsz_report_custom','where'=>"subject_son='研究性学习' and term_id='{$term['id']}' and uid='{$uid}' and flag_status='已审核'"));
				//查询社会服务
				$service = array();
				//社会实践
				$event = $pd->fetchRow(array('field'=>array('content'),'table'=>'oa_zhsz_report_custom','where'=>"subject_son='社会实践' and term_id='{$term['id']}' and uid='{$uid}' and flag_status='已审核'"));
				
				//查询突出表现
				$perform = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_performance','where'=>"user_id='{$uid}' and term_id={$term['id']}"));
				
				//替换模板文件变量
				$fileC = str_replace('{classname}',$main['class_name'],$fileC);
				$fileC = str_replace('{truename}',$rs['truename'],$fileC);
				$fileC = str_replace('{gradename}',$gradeName,$fileC);
				$fileC = str_replace('{termname}',$termname,$fileC);
				$fileC = str_replace('{stu_no}',$rs['person_no'],$fileC);
				
				
				//研究性学习
				$rmax = 20;
				$yjxxx = '';
				$researchLevel = '';
				$researchXf    = '';
				if(!empty($research['content'])){
					$research['content'] = trim($research['content']);
					$len = iconv_strlen($research['content'],'utf-8');
					$yjxxx = iconv_substr($research['content'],0,$rmax,'utf-8');
					if($len>$rmax){
						$yjxxx .= '...';
					}
					$researchLevel = "";
					$researchXf    = "";
				}
				$fileC = str_replace('{research}',$yjxxx,$fileC);
				$fileC = str_replace('{research_level}',$researchLevel,$fileC);
				$fileC = str_replace('{research_xf}',$researchXf,$fileC);
				//社区服务
				$sqff = '';
				$serviceLevel = '';
				$serviceXf    = '';
				$smax = 25;
				if(!empty($service['subject'])){
					$len = iconv_strlen($service['subject'],'utf-8');
					$sqff = iconv_substr($service['subject'],0,$smax,'utf-8');
					if($len>$smax){
						$sqff .= '...';
					}
					$serviceLevel = "";
					$serviceXf    = "";
				}
				$fileC = str_replace('{service}',$sqff,$fileC);
				$fileC = str_replace('{service_level}',$serviceLevel,$fileC);
				$fileC = str_replace('{service_xf}',$serviceXf,$fileC);
				//社会活动
				$emax = 25;
				$shhd = '';
				$eventLevel = '';
				$eventXf    = '';
				if(!empty($event['content'])){
					$len = iconv_strlen($event['content'],'utf-8');
					$shhd = iconv_substr($event['content'],0,$emax,'utf-8');
					if($len>$emax){
						$shhd .= '...';
					}
					$eventLevel = "";
					$eventXf    = "";
				}
				$fileC = str_replace('{event}',$shhd,$fileC);
				$fileC = str_replace('{event_level}',$eventLevel,$fileC);
				$fileC = str_replace('{event_xf}',$eventXf,$fileC);
				
				//突出表现
				if(empty($perform['performance'])){
					$tcbx = '';
				}else{
					$len = iconv_strlen($perform['performance'],'utf-8');
					$tcbx = iconv_substr($perform['performance'],0,$max,'utf-8');
					if($len>$max){
						$tcbx .= '...';
					}
				}
				//评语
				$cmax = 180;
				$content = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_teacher_comment','where'=>"user_id='{$uid}' and term_id={$term['id']}"));
				if(empty($content['content'])){
					$xqpy = '';
				}else{
					$len = iconv_strlen($content['content'],'utf-8');
					$xqpy = iconv_substr($content['content'],0,$cmax,'utf-8');
					if($len>$cmax){
						$xqpy .= '...';
					}
					
					if(!$content['master_name'])
						$content['master_name'] = $main['master_name'];
				}
			
				$fileC = str_replace('{tuchubiaoxian}',$tcbx,$fileC);
				$fileC = str_replace('{xueqipingyu}',$xqpy,$fileC);
				//班主任、日期
				
				$fileC = str_replace('{master}',$main['master_name'],$fileC);
				$fileC = str_replace('{date}',date('Y 年 m 月 d 日'),$fileC);
				$html .= $fileC;
			}
			if($tid==10000 && $gid==3 && $curTerm1['term_type']==2){     //导出总表
				$template = readFileContent('zhsz_template_total.html');
				$fileC = $template;
				//$main1 = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_main','where'=>"user_id='{$uid}' and term_id={$curTerm1['id']}"));
				
				//替换模板文件变量
				
				$fileC = str_replace('{classname}',$className,$fileC);
				$fileC = str_replace('{truename}',$rs['truename'],$fileC);
				$fileC = str_replace('{gradename}',$grade['grade_name'],$fileC);
				$fileC = str_replace('{termname}',$termname,$fileC);
				$fileC = str_replace('{stu_no}',$rs['person_no'],$fileC);
				
				//日期
				$fileC = str_replace('{date}',date('Y 年 m 月 d 日'),$fileC);
				$html .= $fileC;
			}
			//exit;
		}else{
			//查询主表
			$main = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_main','where'=>"user_id='{$uid}' and term_id={$curTerm['id']}"));
			$is_total_name = 0;
			if(empty($main)){
				$main = array();
				$main['id'] = -1;
				if(!empty($class)){//有班级
					$main['class_name']  = $class['class_name'];
					//if($isMaster==true){
						//$main['master_name'] = $_SESSION['truename'];
					//}else{
						$main['master_name'] = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
					//}
				}else{
					$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
					$main['class_name']  = $class['class_name'];
					$main['master_name'] = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
				}
				
				$is_total_name = 1;
				//continue;
			}
			if(empty($gid)){
				//查询年级
				$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
				$grade = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));	
			}
			if($is_total_name)
				$main['class_name'] = $grade['grade_name'].$main['class_name'];
			
			//查询当前学生是否有选课
			$cnf = ' and select_course_id=0';
			if(!empty($rs['select_course_id'])){
				$cnf = " and (select_course_id={$rs['select_course_id']} OR select_course_id=0)";
			}
			for($i=0;$i<$c;$i++){

				$course[$i]['module'] = $pd->query("SELECT module_name from oa_zhsz_course_module  where course_id={$course[$i]['id']} and grade_id={$grade['id']} and term_type={$curTerm['term_type']} {$cnf} order by id ASC")->fetchAll(PDO::FETCH_ASSOC);
			}
			//查询成绩
			$score1 = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$curTerm['id']} and exam_type='期中'"));
			$score2 = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$curTerm['id']} and exam_type='期末'"));
			$score  = array(0=>$score1,1=>$score2);
			//查询研究性学习
			$research = $pd->fetchRow(array('field'=>array('content'),'table'=>'oa_zhsz_report_custom','where'=>"subject_son='研究性学习' and term_id='{$curTerm['id']}' and uid='{$uid}' and flag_status='已审核'"));
			//查询社会服务
			
			$service = array();
			//社会实践
			$event = $pd->fetchRow(array('field'=>array('content'),'table'=>'oa_zhsz_report_custom','where'=>"subject_son='社会实践' and term_id='{$curTerm['id']}' and uid='{$uid}' and flag_status='已审核'"));
			
			//查询突出表现
			$perform = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_performance','where'=>"user_id='{$uid}' and term_id={$curTerm['id']}"));
			
			//查询学期对应的年级
			$term_info = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_score','where'=>"user_id='{$uid}' and term_id={$tid}"));
			if($term_info==false){
				$tips = array(
						   'tips' => '当前学期错误或无数据，请重新选择',
						   'url'  => 'index.php'
						  );
				$tips = urlencode(serialize($tips));
				header('Location:./tips.php?gets='.$tips);
			}else{
				$is_term_more=0;
				$term_alls  = $pd->query("select * from oa_zhsz_score where user_id='{$uid}' and grade_id={$term_info['grade_id']}")->fetchAll(PDO::FETCH_ASSOC);
				foreach($term_alls as $term_all){
					if(((int)$term_all['term_id'])>((int)$term_info['term_id']))
						$is_term_more=1;
				}

				if($term_info['grade_id']==1){
					if($is_term_more){
						$template = readFileContent('zhsz_template_new1.html');
					}else{
						$template = readFileContent('zhsz_template_new2.html');
					}
				}else if($term_info['grade_id']==2){
					if($is_term_more){
						$template = readFileContent('zhsz_template_new3.html');
					}else{
						$template = readFileContent('zhsz_template_new4.html');
					}
				}else{
					if($is_term_more){
						$template = readFileContent('zhsz_template_new5.html');
					}else{
						$template = readFileContent('zhsz_template_new6.html');
					}
				}
			}
			
			$fileC = $template;
			
			//替换模板文件变量
			$fileC = str_replace('{classname}',$main['class_name'],$fileC);
			$fileC = str_replace('{truename}',$rs['truename'],$fileC);
			$fileC = str_replace('{gradename}',$grade['grade_name'],$fileC);
			$fileC = str_replace('{termname}',$termname,$fileC);
			$fileC = str_replace('{stu_no}',$rs['person_no'],$fileC);
			
			
			//研究性学习
			$rmax = 20;
			$yjxxx = '';
			$researchLevel = '';
			$researchXf    = '';
			if(!empty($research['content'])){
				$research['content'] = trim($research['content']);
				$len = iconv_strlen($research['content'],'utf-8');
				$yjxxx = iconv_substr($research['content'],0,$rmax,'utf-8');
				if($len>$rmax){
					$yjxxx .= '...';
				}
				$researchLevel = "";
				$researchXf    = "";
			}
			$fileC = str_replace('{research}',$yjxxx,$fileC);
			$fileC = str_replace('{research_level}',$researchLevel,$fileC);
			$fileC = str_replace('{research_xf}',$researchXf,$fileC);
			//社区服务
			$sqff = '';
			$serviceLevel = '';
			$serviceXf    = '';
			$smax = 25;
			if(!empty($service['subject'])){
				$len = iconv_strlen($service['subject'],'utf-8');
				$sqff = iconv_substr($service['subject'],0,$smax,'utf-8');
				if($len>$smax){
					$sqff .= '...';
				}
				$serviceLevel = $service['level'];
				$serviceXf    = $service['score'];
			}
			$fileC = str_replace('{service}',$sqff,$fileC);
			$fileC = str_replace('{service_level}',$serviceLevel,$fileC);
			$fileC = str_replace('{service_xf}',$serviceXf,$fileC);
			//社会活动
			$emax = 25;
			$shhd = '';
			$eventLevel = '';
			$eventXf    = '';
			if(!empty($event['content'])){
				$len = iconv_strlen($event['content'],'utf-8');
				$shhd = iconv_substr($event['content'],0,$emax,'utf-8');
				if($len>$emax){
					$shhd .= '...';
				}
				$eventLevel = "";
				$eventXf    = "";
			}
			$fileC = str_replace('{event}',$shhd,$fileC);
			$fileC = str_replace('{event_level}',$eventLevel,$fileC);
			$fileC = str_replace('{event_xf}',$eventXf,$fileC);
			
			
			//突出表现
			if(empty($perform['performance'])){
				$tcbx = '';
			}else{
				$len = iconv_strlen($perform['performance'],'utf-8');
				$tcbx = iconv_substr($perform['performance'],0,$max,'utf-8');
				if($len>$max){
					$tcbx .= '...';
				}
			}
			//评语
			$cmax = 180;
			$content = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_teacher_comment','where'=>"user_id='{$uid}' and term_id={$tid}"));
			
			if(empty($content['content'])){
				$xqpy = '';
			}else{
				$len = iconv_strlen($content['content'],'utf-8');
				$xqpy = iconv_substr($content['content'],0,$cmax,'utf-8');
				if($len>$cmax){
					$xqpy .= '...';
				}
				
				if(!$content['master_name'])
					$content['master_name'] = $main['master_name'];
			}
			$fileC = str_replace('{tuchubiaoxian}',$tcbx,$fileC);
			$fileC = str_replace('{xueqipingyu}',$xqpy,$fileC);
			//班主任、日期
			
			$fileC = str_replace('{master}',$content['master_name'],$fileC);
			$fileC = str_replace('{date}',date('Y 年 m 月 d 日'),$fileC);
			$html .= $fileC;
		}
	}
	$html .= '</body></html>';

	//生成word文档
	saveWord($html,$fileName);
	//插入下载文件到下载表中
	$data = array(
		'file' 		  => '/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.doc',
		'name' 		  => $dataname,
		'flag_type'   => 12,
		'create_by'   => $_SESSION['username'],
		'create_time' => date('Y-m-d H:i:s')
	);
	$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));
	//直接下载
	downloadFile($fileName,$filename.'.doc');
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