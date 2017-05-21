<?php
	header("Content-type: text/html; charset=utf-8;"); 
	require '../../ppf/fun.php';
	require '../../ppf/pdo_mysql.php';
	require '../library/Filter.php';
	require('../common.php');
	
	$pd = new pdo_mysql();
	$filter = new Filter();
	
$submitMethod = $_SERVER["REQUEST_METHOD"];
//判断提交方式
if($submitMethod=='POST'){
	
	//平台基本信息
	$params = array('field'=>array('flag_status','flag_alias','flag_log'),'table'=>'oa_zhsz_system');
	$web    = $pd->fetchRow($params);
	
	
	//获取表单提交过来的数据
	$adminName = $_POST['admin_name'];
	$adminPass = $_POST['admin_pass'];
	$userType  = $_POST['user_type'];
	$o_p_id=$_POST['op_id'];
	if($userType==1||$userType==3){
		$adminName = strtoupper($adminName);	
	}
	
	//禁用后只有管理员可以登录
	if($adminName!='admin'){
		if(isset($web['flag_status'])&&$web['flag_status']==0){
			$tips = array(
			   'tips' => '平台维护中...',
			   'url'  => './?t=login'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);	
			exit;
		}
	}
	
	$where  = 'username="'.Filter::filter_html($adminName).'" and ';
	//启用别名登录
	if($web['flag_alias']==1){
		$where = '(username="'.Filter::filter_html($adminName).'" or alias_name="'.Filter::filter_html($adminName).'") and ';
	}
	
	$param_user    = array(
		'field' => array('id','username','truename','lastip','role_id','flag_status','flag_type','school','dept'),
		'table' => 'v_users',
		'where' => $where . ' pmd5="'.md5($adminPass).'"'
	);
	//如果是家长身份登录则验证parent_pass
	if($userType==3){
		$param_user['where'] = $where . ' pmd5="'.md5($adminPass).'"';
	}
	$rs = $pd->fetchRow($param_user);
	
	if(!$rs){
		//登录出错(用户名或密码有问题)
		$tips = array(
		   'tips' => '用户名或密码有误，请仔细核对一下。',
		   'url'  => './?t=login'
		);
		$tips = urlencode(serialize($tips));
		header('Location:../tips.php?gets='.$tips);
		exit;
	}else{
		//是否被禁用
		if($rs['flag_status']==0){
			//禁止登录
			$tips = array(
			   'tips' => '该帐号已被禁用，请联系管理员。',
			   'url'  => './?t=login'
			);
			$tips = urlencode(serialize($tips));
			header('Location:../tips.php?gets='.$tips);
			exit;	
		}
		//微信代码
		$opendid= $o_p_id;
		
		$y_uid=$rs['id'];
		
		$ck_wx_rs=$pd->query("select * from wx_user where w_uid='".$y_uid."'");//echo '内容：'.$opendid; break;
		if(!$ck_wx_rs){
			$wx_data = array(
				'w_uid'         => $y_uid,
				'opid'         => $opendid
			);
			$y_result = $pd->insert(array('data'=>$wx_data,'table'=>'wx_user'));
		}else{
			$old_oid=$ck_wx_rs['opid'];
			if($opendid!=$old_oid && !empty($opendid)){
				$pd->exec("UPDATE wx_user set opid ='".$opendid."' where w_uid='".$y_uid."'");
			}
		}
		
		//后台Session赋值
		session_start();
		$_SESSION['uid'] = $rs['id'];
		$_SESSION['wname']   = $rs['username'];
		$_SESSION['role_id']    = $rs['role_id'];
		$_SESSION['user_type']  = $userType;
		$_SESSION['flag_type']  = $rs['flag_type'];
		$_SESSION['truename']   = $rs['truename'];
		$_SESSION['school']   = $rs['school'];
		$_SESSION['dept']   = $rs['dept'];
		$now = date('Y-m-d H:i:s');
		$ip  = $_SERVER['REMOTE_ADDR'];
		//更新管理员信息
		$pd->exec("UPDATE act_member SET lgnums = lgnums + 1,lastip = '{$_SERVER['REMOTE_ADDR']}',lasttime = '{$now}' where username='{$rs['username']}'");
		//系统日志
		if($web['flag_log']==1){
			$data = array(
				'content'     => "用户[{$rs['username']}]于{$now}成功登录平台。",
				'flag_type'   => '登录系统T',
				'use_ip'	  => $ip,
				'create_by'   => $rs['username'],
				'create_time' => $now
			);
		}
		if($_SESSION['role_id']==1)
			header('Location:../?t=index');
		elseif($_SESSION['role_id']==4)  //管理员
			header('Location:../?t=admin_index');
		else
			header('Location:../?t=admin_index');
	}
}else{
	//提交方式错误
	$tips = array(
	   'tips' => '不允许的表单提交方式，请按正常流程提交表单。',
	   'url'  => './?t=login'
	);
	$tips = urlencode(serialize($tips));
	header('Location:../tips.php?gets='.$tips);
}
?>
