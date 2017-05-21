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



// $s_length = count($s_年份);
// for($i=0;$i<$s_length;$i++){
// 	$year = $s_年份[$i];
//     $pici = $s_批次;
//     $kenei = $s_科类[$i];
//     $lowest_score = $s_最低分[$i];
//     $s_level = $s_选测科目等级[$i];
//     //插入数据库操作
// 	var_dump($year.'fffff');
// }
// die;
if($submitMethod=='POST'){
	$now = date('Y-m-d H:i:s');

	$arr = $_POST;
extract($arr);
//print_R($arr);
// 特色专业
//$major = array();
$ser_major = serialize($major);
$je_major = json_encode($major,JSON_UNESCAPED_UNICODE);
//var_dump($je_major);
// 历年分数线
$year_score = array();
//$year_score['years'] = array();
$year_score['year'] = $s_年份;
$year_score['pici'] = $s_批次;
$year_score['kenei'] = $s_科类;
$year_score['lowest_score'] = $s_最低分;
$year_score['s_level'] = $s_选测科目等级;
$je_year_score = json_encode($year_score,JSON_UNESCAPED_UNICODE);
//var_dump($je_year_score);

// 招生计划
$re_scheme = array();
$re_scheme['major_name'] = $专业名称;
$re_scheme['plan_name'] = $计划名称;
$re_scheme['special'] = $科类;
$re_scheme['plan_num'] = $计划数;
$re_scheme['year'] = $学制;
$je_re_scheme = json_encode($re_scheme,JSON_UNESCAPED_UNICODE);
//var_dump($je_re_scheme);
		
	// $schoolname = Filter::safe_string($_POST['schoolname']);
	// $schoolname = strtoupper($schoolname);
	// var_dump($schoolname);
	// $e = $pd->fetchOne(array('field'=>'count(*)','table'=>'career_acade_info','where'=>"school='{$schoolname}'"));
	// var_dump($e);
	// if($e!=0){
	// 	$tips = array(
	// 	   'tips' => "学校名已存在，请不要重复添加。",
	// 	   'url'  => './?t=school_add'
	// 	);
	// 	$tips = urlencode(serialize($tips));
	// 	header('Location:../tips.php?gets='.$tips);
	// 	exit;
	// }

	// 判断学校是否是985,211
	$aid_ht = $pd->fetchOne(array('field'=>'name','table'=>'career_acade_level','where'=>"id='{$aid}'"));
	$hid_ht = $pd->fetchOne(array('field'=>'name','table'=>'career_acade_feature','where'=>"id='{$hid}'"));
	$hid_ht =="985"?$is_985=1:$is_985 = 0;
	$hid_ht =="211"?$is_211=1:$is_211 = 0;
	// var_dump($aid_ht);
	// var_dump($hid_ht);
	// 数据筛选
	// $school 		 = $_POST['schoolname'];
	// $aid 			 = empty($_POST['aid'])?0:Filter::filter_number($_POST['aid']);//本科
	// $hid 			 = empty($_POST['hid'])?0:Filter::filter_number($_POST['hid']);//985
	// //$sub_id 		 = $_POST['sub_id'];
	// $nature 		 = empty($_POST['nature'])?0:Filter::filter_number($_POST['nature']);
	// $addid 			 = $_POST['addr'];
	// $summary = $_POST['summary'];
	// $major   = $_POST['major'];
	// $fee    	 = $_POST['fee'];
	
	// 判断数据库字段


//Array ( [school_name] => [aid] => 2 [hid] => 4 [school_type] => 综合 [school_nature] => 公办 [summary] => [major] => Array ( [0] => 123 [1] => 123 [2] => 123 ) [fee] => [province] => [city] => [s_年份] => Array ( [0] => ) [s_批次] => Array ( [0] => ) [s_科类] => Array ( [0] => ) [s_最低分] => Array ( [0] => ) [s_选测科目等级] => Array ( [0] => ) [专业名称] => Array ( [0] => ) [计划名称] => Array ( [0] => ) [科类] => Array ( [0] => ) [计划数] => Array ( [0] => ) [学制] => Array ( [0] => ) [but_add] => 添 加 ) string(19) "["123","123","123"]" string(73) "{"year":[""],"pici":[""],"kenei":[""],"lowest_score":[""],"s_level":[""]}" string(79) "{"major_name":[""],"plan_name":[""],"special":[""],"plan_num":[""],"year":[""]}" string(1) "2" string(18) "高职（专科）" string(18) "国家示范高职"
	// echo "$ser_major";
	// var_dump(gettype($je_year_score));die;
	$base_data = array(
		'aid'         => $aid,
		'hid'         => $hid,
		'school_name' => $school_name,
		// 'school_type' =>$school_type,
		// 'school_nature'=>$school_nature,
		'addr'       =>  $province.":".$city,
		'order_num'       => Filter::filter_number($order),
		//'sub_id'      => $sub_id,
		'belong'      =>$belong,
		'nature'      => $school_nature,
		'summary'	  => $summary,
		'major'       => Filter::safe_string($je_major),
		'fee'         => $fee,
		'is_985'=>$is_985,
		'is_211'=>$is_211,
		//'addid'       => $addid,
		// 'major' =>$je_major,
		'je_year_score'=> Filter::safe_string($je_year_score),//历年分数线
		'je_re_scheme' => Filter::safe_string($je_re_scheme),//招生计划
		'timestamp'    =>time()
	);
	//var_dump($base_data);die;


	//$base_data = array('school_name' => "jialidun");

	$result = $pd->insert(array('data'=>$base_data,'table'=>'career_acade_info'));
	
	if(empty($result)){
		$tips = array(
			   'tips' => "学校信息添加失败。",
			   'url'  => './?t=school_add'
			  );
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
	}else{
		/* $data = array();
		$data['username'] = $username;
		//判断是否有中考信息
		$data['exam_no'] 	 = $_POST['exam_no'];
		$data['yw'] 	 	 = intval($_POST['yw']);
		$data['sx'] 	 	 = intval($_POST['sx']);
		$data['wy'] 	 	 = intval($_POST['wy']);
		$data['lh']		 	 = intval($_POST['lh']);
		$data['zs']		 	 = intval($_POST['zs']);
		$data['ty']	     	 = intval($_POST['ty']);
		$data['zf']			 = $data['yw'] + $data['sx'] + $data['wy'] + $data['lh'] + $data['zs'] + $data['ty'];
		$data['intro']   	 = $_POST['intro'];
		$data['create_by']   = $_SESSION['admin_name'];
		$data['create_time'] = $now;
		
		$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_about_primary'));
		
		//添加联系人
		$relation = $_POST['relation'];
		$truename = $_POST['rel_name'];
		$company  = $_POST['company'];
		$phone    = $_POST['phone'];
		
		$i = 0;
		
		foreach($truename as $rs){
			if(!empty($rs)){
				$data = array(
					'user_id'     => $new_id,
					'truename'    => $rs,
					'relation'    => $relation[$i],
					'telphone'    => $phone[$i],
					'company'     => $company[$i++],
					'create_by'   => $username,
					'create_time' => $now
				);
				$params  = array('data'=>$data,'table'=>'oa_zhsz_relative');
				$pd->insert($params);
			}else{
				$i++;	
			}
		} */
		
		$tips = array(
		   'tips' => "学院信息添加成功。",
		   'url'  => './?t=school_add'
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
	header('Location:../tips.php?gets='.$tips);
}
?>