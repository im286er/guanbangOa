<?php
header("Content-type: text/html; charset=utf-8;"); 
require '../../ppf/fun.php';
require '../../ppf/pdo_mysql.php';
require '../library/Filter.php';
require('../common.php');

$pd = new pdo_mysql();

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

$strWhere = "(flag_status=1 or flag_status=6) and create_by='{$_SESSION['username']}'";
$now = date('Y-m-d H:i:s');

$rs  = $pd->fetchAll(array('field'=>array('*'),'table'=>'oa_zhsz_users_temp','where'=>$strWhere));

$ids = '';

if(!empty($rs)){
	$data = '';
	$now  = date('Y-m-d H:i:s');
	$new_id='a'.time();
	foreach($rs as $r){
		$data = array();
		if($r['flag_status']==1){//添加
			/*$data['username'] 		  = $r['username'];
			$data['userpass'] 		  = md5(substr($r['username'],-6));
			$data['user_card'] 		  = $r['user_card'];
			$data['truename'] 		  = $r['truename'];
			$data['parent_pass'] 	  = md5('000000');
			$data['email'] 			  = $r['email'];
			$data['address'] 		  = $r['address'];
			$data['qq'] 			  = $r['qq'];
			$data['gender'] 		  = $r['gender'];
			$data['birth_date'] 	  = $r['birth_date'];
			$data['person_no'] 		  = $r['person_no'];
			$data['department'] 	  = $r['department'];
			$data['nation'] 		  = $r['nation'];
			$data['political_status'] = $r['political_status'];
			$data['graduate_school']  = $r['graduate_school'];
			$data['telphone'] 		  = $r['telphone'];
			$data['role_id'] 		  = 1;
			$data['flag_type']        = 1;
			$data['flag_status']      = 1;
			$data['create_by'] 		  = $_SESSION['username'];
			$data['create_time'] 	  = $now;
			$r = $pd->insert(array('table'=>'oa_zhsz_users','data'=>$data));*/
			
			$base_data = array(
				'id'         => $new_id,
				'username'         => $r['username'],
				'email'       	   => $r['email'],
				'mobile'           => $r['telphone'],
				'cardno'		   => $r['username'],
				'pmd5'             => md5(substr($r['username'],-6)),
				'idtype'           => 11,
				'truename'         => $r['truename'],
				'ename'            => '',
				'birthday'         => $r['birth_date'],
				'lgnums'           => 0,
				'school'           => $_SESSION['school'],
				'notice'           => 0,
				'state'            => 2,
				'recommend'        => 0,
				'subject'          => 11,
				'grade'            => 0,
				'period'           => 2,
				'timestamp'        =>time()
			);
			$ext_data = array(
				'userid'           => $new_id,
				'alias_name'       => $r['username'],
				'parent_pass'	   => md5('000000'),
				'gender'           => $r['gender'],
				'person_no'    	   => $r['person_no'],
				'dept'             => $r['department'],
				'nation'           => $r['nation'],
				'political_status' => $r['political_status'],
				'graduate_school'  => $r['graduate_school'],
				'qq'       		   => $r['qq'],
				'address'          => $r['address'],
				'role_id'		   => 1,
				'flag_type'		   => 1,
				'flag_status'      => 1,
				'create_by'        => $_SESSION['username'],
				'create_time'      => $now
			);
			$r = $pd->insert(array('data'=>$base_data,'table'=>'act_member'));
			$result11 = $pd->insert(array('data'=>$ext_data,'table'=>'oa_user_extend'));
			
			if($r){
				//查询学生初中信息
				$middleRs = $pd->query("select * from oa_zhsz_about_primary_temp where relate_id={$r}");
				foreach($middleRs as $rs){
					$data = array();
					$data['username']	 = $rs['username'];
					$data['exam_no'] 	 = $rs['exam_no'];	
					$data['yw']      	 = $rs['yw'];
					$data['sx']    	 	 = $rs['sx'];
					$data['wy']      	 = $rs['wy'];
					$data['lh']       	 = $rs['lh'];
					$data['zs']       	 = $rs['zs'];
					$data['ty']       	 = $rs['ty'];
					$data['zf']       	 = $rs['zf'];
					$data['intro']    	 = $rs['intro'];	
					$data['create_time'] = $now;
					$data['create_by']   = $_SESSION['username'];
					$pd->insert(array('data'=>$data,'table'=>'oa_zhsz_about_primary'));
				}
			}
		}
		if($r['flag_status']==6){//更新
			$base_data = array();
			$ext_data = array();
			
			if(!empty($r['truename'])){
				$base_data['truename'] = $r['truename'];
			}
			if(!empty($r['email'])){
				$base_data['email'] = $r['email'];
			}
			if(!empty($r['address'])){
				$ext_data['address'] = $r['address'];
			}
			if(!empty($r['qq'])){
				$ext_data['qq'] = $r['qq'];
			}
			if(!empty($r['gender'])){
				$ext_data['gender'] = $r['gender'];
			}
			if(!empty($r['birth_date'])){
				$base_data['birthday'] = $r['birth_date'];
			}
			if(!empty($r['person_no'])){
				$ext_data['person_no'] = $r['person_no'];
			}
			if(!empty($r['department'])){
				$ext_data['dept'] = $r['department'];
			}
			if(!empty($r['nation'])){
				$ext_data['nation'] = $r['nation'];
			}
			if(!empty($r['political_status'])){
				$ext_data['political_status'] = $r['political_status'];
			}
			if(!empty($r['graduate_school'])){
				$ext_data['graduate_school'] = $r['graduate_school'];
			}
			if(!empty($r['telphone'])){
				$base_data['mobile'] = $r['telphone'];
			}
			if(!empty($base_data)){
				$result = $pd->update(array('data'=>$base_data,'table'=>'act_member','where'=>"username='{$r['username']}'"));
			}
			if(!empty($ext_data)){
				$result11 = $pd->update(array('data'=>$ext_data,'table'=>'oa_user_extend','where'=>"userid='{$r['id']}'"));
			}
		}
	}
	
	//删除临时数据
	$pd->exec("delete from oa_zhsz_users_temp");
	$pd->exec("delete from oa_zhsz_about_primary_temp");
	$tips = array(
	   'tips' => "学生信息导入成功。",
	   'url'  => './?t=user_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}else{
	$tips = array(
	   'tips' => "没有可以导入的数据。",
	   'url'  => './?t=user_manage'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>