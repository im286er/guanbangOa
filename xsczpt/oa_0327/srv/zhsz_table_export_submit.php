<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

$pd = new pdo_mysql();
set_time_limit(0);

	//判断用户是否登录
	session_start();
	if(empty($_SESSION['uid'])){
		$tips = array(
				   'tips' => '请登录后再进行操作。',
				   'url'  => '../?t=login'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}
  
	$submitMethod = $_SERVER["REQUEST_METHOD"];
	//判断提交方式
	if($submitMethod=='POST'){
		//是否为班主任
		$isMaster = false;
		$class = $pd->fetchRow(array('field'=>array('id','grade_id'),'table'=>'oa_zhsz_class','where'=>"class_master='{$_SESSION['uid']}' and grade_id<>0"));
    
		$gid  = empty($_POST['gid'])?0:Filter::filter_number($_POST['gid']);
		$cid  = empty($_POST['cid'])?0:Filter::filter_number($_POST['cid']);
		$tid  = empty($_POST['term_id'])?0:Filter::filter_number($_POST['term_id']);
		$truename = empty($_POST['truename'])?'':$_POST['truename'];
		
		if(!empty($class)){
			$cid = $class['id'];
			$gid = $class['grade_id'];
			$isMaster = true;
		}
		//查询当前学期
		$curTerm = $pd->fetchRow(array('field'=>array('id','year','term_name','term_type'),'table'=>'oa_zhsz_term','where'=>"id={$tid}"));
		$termname = $curTerm['term_type']==1?'一':'二';
		//查询课程                                  
		$course = $pd->query("select * from oa_zhsz_course where is_print=1 order by order_value asc")->fetchAll(PDO::FETCH_ASSOC);
		
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
			$grade     = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_grade','where'=>"id={$gid}"));
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
    
		if(!empty($truename)){
			$where .= " and truename = '{$truename}' ";
		}
		
		$termType = $curTerm['term_type']==1?'第一学期':'第二学期';
		$dataname = str_replace('/','-',$curTerm['year']).'('.$termType.')'.$gradeName.$className.'综评表';
		$filename  = date('YmdHis').'_'.$_SESSION['uid'];
		

		//查询目录是否存在
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'])){
			mkdir($_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'],0757);
		}
		$fileName  = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.doc';
		
		//查询学生记录
    $users = $pd->query("select id,username,truename,dept,cardno,person_no,select_course_id from v_users where ".$where." order by CONVERT(truename USING GBK) desc")->fetchAll(PDO::FETCH_ASSOC);
	$max = 120;
    $html = readFileContent('./zhsz_header.html');
	//读取模板文件
	if($gid!=0){
		$template = readFileContent('./zhsz_template.html');
	}else if($gid==0){
		$template = readFileContent('./zhsz_template_total.html');
	}
	//$template = readFileContent('./zhsz_template.html');
	foreach($users as $rs){
		$fileC = $template;
		$uid = $rs['id'];
		//查询主表
		$main = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_main','where'=>"user_id='{$uid}' and term_id={$curTerm['id']} and class_id={$rs['dept']} "));
		if(empty($main) && $gid!=0){
			$main = array();
			if(!empty($class)){//有班级
				$grade_name = $pd->query("select grade_name from oa_zhsz_grade where id={$class['grade_id']}")->fetchColumn(0);
				$main['class_name']  = $grade_name.$class['class_name'];
				if($isMaster==true){
					$main['master_name'] = $_SESSION['truename'];
				}else{
					$main['master_name'] = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
				}
			}else{
				$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
				$main['class_name']  = $class['class_name'];
				$main['master_name'] = $pd->fetchOne(array('field'=>'truename','table'=>'act_member','where'=>"id='{$class['class_master']}'"));
			}
				//continue;
		}        
		if(empty($gid) && $gid!=0){
			//查询年级
			$class = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_class','where'=>"id={$rs['dept']}"));
			$grade = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_grade','where'=>"id={$class['grade_id']}"));	
	    }
			//查询当前学生是否有选课
			$cnf = ' and select_course_id=0'; 
			if(!empty($rs['select_course_id'])){
				$cnf = " and (select_course_id={$rs['select_course_id']} OR select_course_id=0)";
			} 
      if($gid!=0){
		  for($i=0;$i<$c;$i++){           
			$course[$i]['module'] = $pd->query("select module_name from oa_zhsz_course_module where course_id={$course[$i]['id']} and grade_id={$grade['id']} and term_type={$curTerm['term_type']} {$cnf} order by id asc")->fetchAll(PDO::FETCH_ASSOC);
		  }
	  }	  
      /* for($i=0;$i<$c;$i++){           
        $course[$i]['module'] = $pd->query("select module_name from oa_zhsz_course_module where course_id={$course[$i]['id']} and grade_id={$grade['id']} and term_type={$curTerm['term_type']} {$cnf} order by id asc")->fetchAll(PDO::FETCH_ASSOC);
	  } */
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
			//查询考核等级
			$level = $pd->query("select course_id,level,checker from oa_zhsz_course_level where user_id='{$uid}' and term_id={$curTerm['id']}")->fetchAll(PDO::FETCH_ASSOC);

			$wlevel  = '';                                                       
			$hlevel  = '';
			$slevel  = '';
			$ilevel1 = '';
			$ilevel2 = '';
			$ylevel  = '';
			$plevel  = '';
			$sxlevel = '';
            
			foreach($level as $rsc){
				if($rs['course_id']==3){
					$ylevel = $rsc['level'];
				}
				if($rs['course_id']==4){
					$wlevel = $rsc['level'];
				}
				if($rs['course_id']==5){
					$hlevel = $rsc['level'];
				}
				if($rs['course_id']==6){
					$slevel = $rsc['level'];
				}
				if($rs['course_id']==10){
					$ilevel1 = $rsc['level'];
					$ilevel2 = $rsc['level'];
				}
				if($rs['course_id']==19){
					$plevel = $rsc['level'];
				}
				if($rs['course_id']==20){
					$sxlevel = $rsc['level'];
				}
			}

			//查询体质记录
			$physique = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_physique','where'=>"user_id='{$uid}' and term_id='{$curTerm['id']}'"));
			
		  //查询体能记录
		  $stamina = $pd->query("select * from oa_zhsz_stamina_record where user_id='{$uid}' and term_id={$curTerm['id']} order by item_id desc")->fetchAll(PDO::FETCH_ASSOC);
		
		  //查询突出表现
			$perform = $pd->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_performance','where'=>"user_id='{$uid}' and term_id={$curTerm['id']}"));
			
			//替换模板文件变量
            if($gid!=0){
				$fileC = str_replace('{classname}',$main['class_name'],$fileC);
				$fileC = str_replace('{truename}',$rs['truename'],$fileC);
				$fileC = str_replace('{gradename}',$grade['grade_name'],$fileC);
				$fileC = str_replace('{termname}',$termname,$fileC);
				$fileC = str_replace('{stu_no}',$rs['person_no'],$fileC);
			}else if($gid==0){
				$fileC = str_replace('{truename}',$rs['truename'],$fileC);
				$fileC = str_replace('{termname}',$termname,$fileC);
				$fileC = str_replace('{stu_no}',$rs['person_no'],$fileC);
			}			
			/* $fileC = str_replace('{classname}',$main['class_name'],$fileC);
			$fileC = str_replace('{truename}',$rs['truename'],$fileC);
			$fileC = str_replace('{gradename}',$grade['grade_name'],$fileC);
			$fileC = str_replace('{termname}',$termname,$fileC);
			$fileC = str_replace('{stu_no}',$rs['person_no'],$fileC); */
			
			$courseHtml = '';
			//学分
			$xfTotal  = '';
			$bxTotal  = '';
			$xx1Total = '';
			$xx2Total = '';
			foreach($course as $rsb){
				//查询模块中是否包含选或必的字样
				if(!empty($rsb['module'][0]['module_name'])){
					if(strstr($rsb['module'][0]['module_name'],'选')!==false){
						if(!empty($score[0][$rsb['course_code'].'_xf'])&&intval($score[0][$rsb['course_code'].'_xf'])!=-1){
							$xx1Total += $score[0][$rsb['course_code'].'_xf'];	
						}
						if(!empty($score[1][$rsb['course_code'].'_xf'])&&intval($score[1][$rsb['course_code'].'_xf'])!=-1){
							$xx += $score[1][$rsb['course_code'].'_xf'];	
						}
					}else{
						if(strstr($rsb['module'][0]['module_name'],'必')!==false){
							if(!empty($score[0][$rsb['course_code'].'_xf'])&&intval($score[0][$rsb['course_code'].'_xf'])!=-1){
								$bxTotal += $score[0][$rsb['course_code'].'_xf'];	
							}
							if(!empty($score[1][$rsb['course_code'].'_xf'])&&intval($score[1][$rsb['course_code'].'_xf'])!=-1){
								$bxTotal += $score[1][$rsb['course_code'].'_xf'];	
							}
						}else{
							if(!empty($score[0][$rsb['course_code'].'_xf'])&&intval($score[0][$rsb['course_code'].'_xf'])!=-1){
								$xx1Total += $score[0][$rsb['course_code'].'_xf'];	
							}
							if(!empty($score[1][$rsb['course_code'].'_xf'])&&intval($score[1][$rsb['course_code'].'_xf'])!=-1){
								$xx1Total+= $score[1][$rsb['course_code'].'_xf'];	
							}
						}
					}
				}else{
					if(!empty($score[0][$rsb['course_code'].'_xf'])&&intval($score[0][$rsb['course_code'].'_xf'])!=-1){
						$xx1Total += $score[0][$rsb['course_code'].'_xf'];	
					}
					if(!empty($score[1][$rsb['course_code'].'_xf'])&&intval($score[1][$rsb['course_code'].'_xf'])!=-1){
						$xx1Total+= $score[1][$rsb['course_code'].'_xf'];	
					}
				}
				if($rsb['course_code']=='xx'){
					if(!empty($score[0][$rsb['course_code'].'_xf'])&&intval($score[0][$rsb['course_code'].'_xf'])!=-1){
						$xx2Total += $score[0][$rsb['course_code'].'_xf'];
					}
					if(!empty($score[1][$rsb['course_code'].'_xf'])&&intval($score[1][$rsb['course_code'].'_xf'])!=-1){
						$xx2Total += $score[1][$rsb['course_code'].'_xf'];
					}
				}
				
				$xfTotal = $bxTotal + $xx1Total;
				
				$courseHtml .= "<tr style='mso-yfti-irow:2;height:7.85pt'>";
				$courseHtml .= "<td width=71 style='width:53.35pt;border:solid windowtext 1.0pt;border-top:
  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
  				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center;line-height:18.0pt;
  mso-line-height-rule:exactly;mso-pagination:widow-orphan'>";
				$courseHtml .= "<span
  style='font-size:9.0pt;font-family:宋体;mso-font-kerning:0pt'>{$rsb['course_name']}<span
  lang=EN-US><o:p></o:p></span></span></p>
  </td>";
				$courseHtml .= "<td width=173 colspan=7 style='width:130.0pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:9.0pt;font-family:宋体'>".(empty($rsb['module'][0]['module_name'])?'':$rsb['module'][0]['module_name'])."</span>";
				$courseHtml .= "<span lang=EN-US
  style='font-size:9.0pt;font-family:宋体'><o:p></o:p></span></p>
  </td>";
				$courseHtml .= "<td width=44 colspan=3 style='width:33.05pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center'><span lang=EN-US
  style='font-size:9.0pt;font-family:宋体'>".(empty($score[0][$rsb['course_code']])||$score[0][$rsb['course_code']]==-1?'':$score[0][$rsb['course_code']])."<o:p></o:p></span></p>
  </td>";
				$courseHtml .= "<td width=34 colspan=3 style='width:25.45pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center'><span lang=EN-US
  style='font-size:9.0pt;font-family:宋体'>".(empty($score[0][$rsb['course_code'].'_xf'])||$score[0][$rsb['course_code'].'_xf']==-1?'':$score[0][$rsb['course_code'].'_xf'])."<o:p></o:p></span></p>
  </td>";
				$courseHtml .= "<td width=170 colspan=11 style='width:127.6pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:9.0pt;font-family:宋体'>".(empty($rsb['module'][1]['module_name'])?'':$rsb['module'][1]['module_name'])."</span>";
				$courseHtml .= "<span lang=EN-US
  style='font-size:9.0pt;font-family:宋体'><o:p></o:p></span></p>
  </td>";
				$courseHtml .= "<td width=57 colspan=4 style='width:43.1pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center;line-height:18.0pt;
  mso-line-height-rule:exactly;mso-pagination:widow-orphan'><span lang=EN-US
  style='font-size:9.0pt;font-family:宋体'>".(empty($score[1][$rsb['course_code']])||$score[1][$rsb['course_code']]==-1?'':$score[1][$rsb['course_code']])."<o:p></o:p></span></p>
  </td>";
				$courseHtml .= "<td width=44 style='width:32.85pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm;height:7.85pt'>";
				$courseHtml .= "<p class=MsoNormal align=center style='text-align:center;line-height:18.0pt;
  mso-line-height-rule:exactly;mso-pagination:widow-orphan'><span lang=EN-US
  style='font-size:9.0pt;font-family:宋体'>".(empty($score[1][$rsb['course_code'].'_xf'])||$score[1][$rsb['course_code'].'_xf']==-1?'':$score[1][$rsb['course_code'].'_xf'])."<o:p></o:p></span></p>
  </td>
 </tr>";
			}
			
			$fileC = str_replace('{xf_total}',$xfTotal,$fileC);
			$fileC = str_replace('{bx_total}',$bxTotal,$fileC);
			$fileC = str_replace('{xx1_total}',$xx1Total,$fileC);
			$fileC = str_replace('{xx2_total}',$xx2Total,$fileC);			
			
			$fileC = str_replace('{course}',$courseHtml,$fileC);
			//研究性学习
			$rmax = 20;
			$yjxxx = '';
			$researchLevel = '';
			$researchXf    = '';
			if(!empty($research['content'])){
				$research['content'] = trim($research['content']);
				$len = iconv_strlen($research['content'],'utf-8');
				$yjxxx = '主题：'.iconv_substr($research['content'],0,$rmax,'utf-8');
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
			//等级考核
			$fileC = str_replace('{ylevel}',$ylevel,$fileC);
			$fileC = str_replace('{wlevel}',$wlevel,$fileC);
			$fileC = str_replace('{hlevel}',$hlevel,$fileC);
			$fileC = str_replace('{slevel}',$slevel,$fileC);
			$fileC = str_replace('{ilevel1}',$ilevel1,$fileC);
			$fileC = str_replace('{ilevel2}',$ilevel2,$fileC);
			$fileC = str_replace('{plevel}',$plevel,$fileC);
			$fileC = str_replace('{sxlevel}',$sxlevel,$fileC);
			//体质
		$fileC = str_replace('{height}',empty($physique['height'])?'':$physique['height'],$fileC);
  		$fileC = str_replace('{weight}',empty($physique['weight'])?'':$physique['weight'],$fileC);
  		$fileC = str_replace('{leye}',empty($physique['left_eye'])?'':$physique['left_eye'],$fileC);
  		$fileC = str_replace('{reye}',empty($physique['right_eye'])?'':$physique['right_eye'],$fileC);
  		$fileC = str_replace('{guts}',empty($physique['guts'])?'':$physique['guts'],$fileC);
  		$fileC = str_replace('{liver}',empty($physique['liver'])?'':$physique['liver'],$fileC);
  		$fileC = str_replace('{blood}',empty($physique['blood'])?'':$physique['blood'],$fileC);
  		$fileC = str_replace('{spleen}',empty($physique['spleen'])?'':$physique['spleen'],$fileC);
  		$fileC = str_replace('{yanke}',empty($physique['yanke'])?'':$physique['yanke'],$fileC);
  		$fileC = str_replace('{xftz}',empty($physique['xf_ting'])?'':$physique['xf_ting'],$fileC);
  		$fileC = str_replace('{wkcg}',empty($physique['wkcg'])?'':$physique['wkcg'],$fileC);
  		$fileC = str_replace('{mouth}',empty($physique['mouth'])?'':$physique['mouth'],$fileC);
  		$fileC = str_replace('{vc}',empty($physique['vc'])?'':$physique['vc'],$fileC);
  		$fileC = str_replace('{tzteacher}',empty($physique['teacher_name'])?'':$physique['teacher_name'],$fileC);
			
			
			//体能
			$tcRecord = '';
			$staminaNums = count($stamina);
			//最多显示6个，防止页面错位
			if($staminaNums>5){
				$staminaNums = 5;	
			}
			//体能项目少于6个时，社区服务与社会实践高度设置
			$sWidth = '20.5pt';
			if($staminaNums==6){
				$sWidth = '32.5pt';
			}
			$fileC = str_replace('{sWidth}',$sWidth,$fileC);
			if($staminaNums>0){
				$tcRecord = "<table class=MsoTableGrid border=0 cellspacing=0 cellpadding=0
   style='border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
   mso-yfti-tbllook:1184;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
   <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes;
    height:20.25pt'>";
				for($i=0;$i<$staminaNums;$i++){
					
					$tcRecord .= "<td width=71 valign=middle style='width:50pt;border:solid windowtext 1.0pt;
		mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;
		height:20.25pt;border-top:none; border-bottom:none;'>
		<p class=MsoNormal style='mso-line-height-alt:0pt'><span lang=EN-US
		style='font-size:9.0pt;font-family:宋体;mso-font-kerning:0pt'>{$stamina[$i]['item_name']}</span><span
		lang=EN-US style='font-size:9.0pt;font-family:宋体'><o:p></o:p></span></p>
		</td>";
					$tcRecord .= "<td width=52 valign=middle style='width:35pt;border:solid windowtext 1.0pt;
		border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
		solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.25pt'>
		<p class=MsoNormal style='mso-line-height-alt:0pt'><span lang=EN-US
		style='font-size:9.0pt;font-family:宋体;mso-font-kerning:0pt'>{$stamina[$i]['result']}</span></p>
		</td>";
				}
				$tcRecord .= "</tr></table>";
			}else{
				$tcRecord = "&nbsp;无";
			}
			$fileC = str_replace('{tn_record}',$tcRecord,$fileC);
			$fileC = str_replace('{tcteacher}',empty($stamina[0]['teacher_name'])?'':$stamina[0]['teacher_name'],$fileC);
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
			if($gid!=0){
				$fileC = str_replace('{master}',$content['master_name'],$fileC);
			}
			//$fileC = str_replace('{master}',$content['master_name'],$fileC);
			$fileC = str_replace('{date}',empty($main['comment_date'])?date('Y-m-d'):$main['comment_date'],$fileC);
			$html .= $fileC;
		}
		$html .= '</body></html>';
    //echo $fileC;exit;
		//生成word文档
		saveWord($html,$fileName);
    //插入下载文件到下载表中
		/*$data = array(          
						'file' 		  => '/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.doc',
						'name' 		  => $dataname,
						'flag_type'   => 12,
						'create_by'   => $_SESSION['username'],
						'create_time' => date('Y-m-d H:i:s')
						
					  );
		$pd->insert(array('table'=>'oa_zhsz_table_downs','data'=>$data));*/
    $create_time = date('Y-m-d H:i:s');
    $pd->exec("insert into oa_zhsz_table_downs(file,name,flag_type,create_by,create_time) values('/oa/uploadfiles/down/".$_SESSION['uid']."/".$filename.".doc','".$dataname."',12,'".$_SESSION['username']."','".$create_time."')");
    //直接下载
		downloadFile($fileName,$filename.'.doc');
   // echo '/uploadfiles/down/'.$_SESSION['uid'].'/'.$filename.'.doc';
		exit;
	}else{
		//提交方式出错
		$tips = array(
				   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
				   'url'  => '../?t=zhsz_table_export'
				  );
		$tips = urlencode(serialize($tips));
		//header('Location:./tips.php?gets='.$tips);
    echo $tips;
	}
?>