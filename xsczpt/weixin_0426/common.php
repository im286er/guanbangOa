<?php
/*************************************
  *@ 功能：查询主菜单，返回菜单数组
  *@ 参数：$parent_id(父级ID)
  *@ $db变量
  ************************************/ 

function selectMenu($db,$parent_id=0)
{
	$menu  = $db->query("select id,name,href,target,comment,flag_public from oa_zhsz_privileges where m_parent_id={$parent_id} and flag_menu=1 and flag_use=1 order by order_value")->fetchAll(PDO::FETCH_ASSOC);
	//查询二级菜单
	$c = count($menu);
	for($i=0;$i<$c;$i++){
		$menu[$i]['sub_menu'] = selectMenu($db,$menu[$i]['id']);
	}
	return $menu;
}

//查询是否登录
function check_session(){
	if(empty($_SESSION['admin_name'])){
	$tips = array(
			   'tips' => '请登录后再进行操作。',
			   'url'  => 'index.php'
			  );
	$tips = urlencode(serialize($tips));
	header('Location:./tips.php?gets='.$tips);
	exit;
	}
}

//查询登录用户权限
function selectPrivileges($db,$roleId){
	$params    = array('field'=>'privilege_id','table'=>'oa_zhsz_role_privileges','where'=>"role_id={$roleId}");
	return $db->fetchCol($params);
}

//查询管理权限
function selectMPrivileges($db,$pValue){
	 return $db->query("select id,pid,fun_id from oa_zhsz_privileges where type={$pValue}")->fetchAll(PDO::FETCH_ASSOC);
}

//查询实际拥有权限
function selectHasPrivileges($db,$roleId,$pValue)
{
	//当前登录用户拥有的权限
	$privileges  = selectPrivileges($db,$roleId);
	//查询模块对应的权限
	$mPrivileges = selectMPrivileges($db,$pValue);
	$subP  = array();
	foreach($mPrivileges as $rs){
		$subP[$rs['fun_id']] = $rs['id'];
	}
	//查询实际拥有的权限
	return array_intersect($subP,$privileges);	
}

//查询系统所有权限
function selectAllPrivileges($db)
{
	$params = array(
					'field' => array('id','name'),
					'table' => 'zhsz_privileges',
					'where' => 'type>0 and flag_menu=1 and flag_use=1',
					'order' => 'pid,order_value'
					);
	$privileges = $db->fetchAll($params);
	//查询二级菜单
	$c = count($privileges);
	for($i=0;$i<$c;$i++){
		$privileges[$i]['sub_privileges'] = selectSubPrivileges($db,$privileges[$i]['id']);
	}
	return $privileges;
}
//查询子权限
function selectSubPrivileges($db,$parentId=0)
{
	$params = array(
					'field' => array('id','name'),
					'table' => 'oa_zhsz_privileges',
					'where' => "pid={$parentId} and flag_use=1",
					'order' => 'order_value'
					);
	return $db->fetchAll($params);
}

//下载文件
function downloadFile_s($fpath,$fname='示例文件')
{
	if(!file_exists($fpath)){//检查文件是否存在  
		$tips = array(
				   'tips' => '抱歉，文件不存在。',
				   'url'  => 'systeminfo.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:./tips.php?gets='.$tips);
		exit;
	}else{
		//打开文件
		$fp       = fopen($fpath,'r');
		$fileSize = filesize($fpath);
		$fname = iconv("UTF-8","GBK//IGNORE",$fname);
		// 输入文件标签
		header('Content-type: application/octet-stream');
		header('Accept-Ranges: bytes');
		header('Accept-Length: '.$fileSize);
		header('Content-Disposition: attachment; filename='.$fname);
		//文件读取设置
		$buffer    = 1024;
		$fileCount = 0;
		// 输出文件内容
		while(!feof($fp)&&$fileCount<$fileSize){
			$fileCon   = fread($fp,$buffer);
			$fileCount += $buffer;
			echo $fileCon;
		} 
		fclose($fp);
		exit;
	}	
}

//下载文件
function downloadFile_n($fpath,$fname='示例文件')
{
	if(!file_exists($fpath)){//检查文件是否存在  
		$tips = array(
				   'tips' => '抱歉，文件不存在。',
				   'url'  => 'systeminfo.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:./tips.php?gets='.$tips);
		exit;
	}else{
		//打开文件
		$fp    = fopen($fpath,'r');
		$size  = filesize($fpath);
		$fname = iconv("UTF-8","GBK//IGNORE",$fname);
		$range = 0;
		//Begin writing headers
		header("Cache-Control:");
		header("Cache-Control: public");
		//设置输出浏览器格式
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=" . $fname);
		header("Accept-Ranges: bytes");
		//如果有$_SERVER['HTTP_RANGE']参数
		if (isset ($_SERVER['HTTP_RANGE'])) {
			/*Range头域 　　Range头域可以请求实体的一个或者多个子范围。
			例如，
			表示头500个字节：bytes=0-499
			表示第二个500字节：bytes=500-999
			表示最后500个字节：bytes=-500
			表示500字节以后的范围：bytes=500- 　　
			第一个和最后一个字节：bytes=0-0,-1 　　
			同时指定几个范围：bytes=500-600,601-999 　　
			但是服务器可以忽略此请求头，如果无条件GET包含Range请求头，响应会以状态码206（PartialContent）返回而不是以200 （OK）。
			*/
			// 断点后再次连接 $_SERVER['HTTP_RANGE'] 的值 bytes=4390912-
			list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
			//if yes, download missing part
			str_replace($range, "-", $range); //这句干什么的呢。。。。
			$size2 = $size -1; //文件总字节数
			$new_length = $size2 - $range; //获取下次下载的长度
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: {$new_length}"); //输入总长
			header("Content-Range: bytes {$range}{$size2}/{$size}"); //Content-Range: bytes 4908618-4988927/4988928 95%的时候
		} else {
			//第一次连接
			$size2 = $size -1;
			header("Content-Range: bytes 0-{$size2}/{$size}"); //Content-Range: bytes 0-4988927/4988928
			header("Content-Length: " . $size); //输出总长
		}
		//设置指针位置
		fseek($fp, $range);
		//虚幻输出
		while (!feof($fp)) {
			//设置文件最长执行时间
			set_time_limit(0);
			print (fread($fp, 1024 * 8)); //输出文件
			flush(); //输出缓冲
			ob_flush();
		}
		fclose($fp);
		exit(); 
	}	
}

//下载文件
function downloadFile($fpath,$fname='示例文件')
{
	if(!file_exists($fpath)){//检查文件是否存在  
		$tips = array(
				   'tips' => '抱歉，文件不存在。',
				   'url'  => 'systeminfo.php'
				  );
		$tips = urlencode(serialize($tips));
		header('Location:./tips.php?gets='.$tips);
		exit;
	}else{
		$url = str_replace($_SERVER['DOCUMENT_ROOT'],'',$fpath);
		$url = 'http://'.$_SERVER['HTTP_HOST'].$url;
		header("Location:{$url}");
		exit;
	}	
}

//生成word文档
function saveWord($content,$fileName)
{
	$fp = fopen($fileName,'wb');
	fwrite($fp,$content);
	fclose($fp);
}

//读取文件内容
function readFileContent($fileName)
{
	$fp = fopen($fileName,'r');
	if($fp===false){
		die('文件打开失败。');
		return;
	}
	$c = '';
	while(!feof($fp)){
		$c .= fgets($fp);
	}
	fclose($fp); 
	return $c;
}

//更新综评表记录
function updateMain($db,$userId,$termId,$flag='',$mainId=0)
{
	$now = date('Y-m-d H:i:s');
	//查询当前是否已经记录过
	$statitics = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_main_statistics','where'=>"user_id={$userId} and term_id={$termId}"));
	//添加记录
	if(empty($statitics)){
		$user   = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_users','where'=>"id={$userId}"));
		$classD = $db->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['department']}"));
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
		$data = array(
		   'user_id'     => $user['id'],
		   'truename'    => $user['truename'],
		   'user_card'   => $user['user_card'],
		   'term_id'     => $termId,
		   'grade_id'    => $classD['grade_id'],
		   'class_id'    => $user['department'],
		   'class_name'  => $gradeName.$classD['class_name'],
		   'main_id'     => $mainId,
		   'create_time' => $now
		);
		if($flag=='object'){
			$data['object'] = 1;
		}
		if($flag=='hope'){
			$data['hope'] = 1;
		}
		return $db->insert(array('table'=>'oa_zhsz_main_statistics','data'=>$data));
	}else{
		//更新记录
		$id   = $statitics['id'];
		$data = array();
		switch($flag){
			//个人目标
			case 'object':
				$data['object'] = 1;
				break;
			//家长期望
			case 'hope':
				$data['hope'] = 1;
				break;
			//个人成长经历
			case 'exp':
				$data['exp'] = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_experience','where'=>"main_id={$statitics['main_id']}"));
				break;
			//有意义的成果
			case 'result':
				$data['result'] = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_result','where'=>"main_id={$statitics['main_id']}"));
				break;
			//研究性学习
			case 'research':
				$data['research'] = 1;
				break;
			//社区服务
			case 'service':
				$data['service'] = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_service','where'=>"main_id={$statitics['main_id']}"));
				break;
			//社会活动
			case 'event':
				$data['event_active'] = $db->fetchOne(array('field'=>'count(*)','table'=>'oa_zhsz_event','where'=>"main_id={$statitics['main_id']}"));
				break;
		}
		if(empty($data)){
			return false;
		}else{
			return $db->update(array('data'=>$data,'table'=>'oa_zhsz_main_statistics','where'=>"id={$id}"));
		}
	}
}
//更新动态
function updateFeeds($db,$userId,$termId,$mainId,$flag,$content='')
{
	$now = date('Y-m-d H:i:s');
	$data = array();
	$user   = $db->fetchRow(array('field'=>array('*'),'table'=>'oa_zhsz_users','where'=>"id={$userId}"));
	$classD = $db->fetchRow(array('field'=>array('class_name','grade_id'),'table'=>'oa_zhsz_class','where'=>"id={$user['department']}"));
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
	$data = array(
	   'user_id'     => $user['id'],
	   'truename'    => $user['truename'],
	   'user_card'   => $user['user_card'],
	   'term_id'     => $termId,
	   'grade_id'    => $classD['grade_id'],
	   'class_id'    => $user['department'],
	   'class_name'  => $gradeName.$classD['class_name'],
	   'create_time' => $now
	);
	switch($flag){
		case 'main':
			$data['feeds_type'] = '综评信息';
			$data['feeds']      = "于 {$now} 更新了 综合素质评价基本信息";
			break;
		//个人目标
		case 'object':
			$data['feeds_type'] = '个人目标';
			$data['feeds']      = "于 {$now} 更新了 个人目标：{$content}";
			break;
		break;
		//家长期望
		case 'hope':
			$data['feeds_type'] = '家长期望';
			$data['feeds']      = "于 {$now} 更新了 家长期望：{$content}";
			break;
		break;
		//个人成长经历
		case 'exp':
			$data['feeds_type'] = '成长经历';
			$data['feeds']      = "于 {$now} 更新了 成长经历：{$content}";
			break;
		break;
		//有意义的成果
		case 'result':
			$data['feeds_type'] = '有意义的成果';
			$data['feeds']      = "于 {$now} 更新了 有意义的成果：{$content}";
			break;
		break;
		//研究性学习
		case 'research':
			$data['feeds_type'] = '研究性学习';
			$data['feeds']      = "于 {$now} 更新了 研究性学习：{$content}";
			break;
		break;
		//社区服务
		case 'service':
			$data['feeds_type'] = '社区服务';
			$data['feeds']      = "于 {$now} 更新了 社区服务：{$content}";
			break;
		break;
		//社会活动
		case 'event':
			$data['feeds_type'] = '社会活动';
			$data['feeds']      = "于 {$now} 更新了 社会活动：{$content}";
			break;
		break;
	}
	return $db->insert(array('table'=>'oa_zhsz_main_feeds','data'=>$data));
}
//二维数组排序
function array_sort($arr,$keys,$type='desc')
{
	$keysvalue = $new_array = array();
	foreach($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type=='asc'){
		asort($keysvalue,SORT_NUMERIC);
	}else{
		arsort($keysvalue,SORT_NUMERIC);
	}
	$i = 0;
	foreach($keysvalue as $k=>$v){
		$new_array[$i++] = $arr[$k];
	}
	return $new_array;
} 
//显示学期
function showTerm($db,$userId)
{
	$data = array();
	$termN = array('1'=>'高一上','2'=>'高一下','3'=>'高二上','4'=>'高二下','5'=>'高三上','6'=>'高三下');
	$curTerm = $db->fetchRow(array('field'=>array('id','year','term_name','start','end','term_type'),'table'=>'oa_zhsz_term','where'=>'flag_default=1'));
	
	$department = $db->fetchOne(array('field'=>'department','table'=>'oa_zhsz_users','where'=>"id={$userId}"));
	$gradeId    = $db->fetchOne(array('field'=>'grade_id','table'=>'oa_zhsz_class','where'=>"id={$department}"));
	if($gradeId==0){//已经毕业的学生会有问题
		$gradeId = 3;	
	}
	
	if($gradeId==1){//高一
		if($curTerm['term_type']==1){//第一学期
			$n = 1;
		}else{
			$n = 2;	
		}
	}
	if($gradeId==2){//高二
		if($curTerm['term_type']==1){//第一学期
			$n = 3;
		}else{
			$n = 4;	
		}
	}
	if($gradeId==3){//高三
		if($curTerm['term_type']==1){//第一学期
			$n = 5;
		}else{
			$n = 6;	
		}
	}
	//查询学期
	$term = $db->fetchAll(array('field'=>array('id','year','term_type'),'table'=>'oa_zhsz_term','where'=>"end<='{$curTerm['end']}'",'order'=>'end desc','page'=>1,'nums'=>$n));
	//$term  = $db->query("select id,year,term_type from oa_zhsz_term where end<='{$curTerm['end']}' order by end desc limit 1,{$n}")->fetchAll(PDO::FETCH_ASSOC);
	$returnD = array();
	$c = count($term);
	$index = $c - 1;
	foreach($term as $rs){
		$returnD[$index] = $rs;
		$returnD[$index++]['term'] = $termN[$c--];
	}
	return $returnD;
}
?>