<?php 
   header("Content-type:text/html; charset=utf-8");
   header('Access-Control-Allow-Origin:*'); 
   header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); 
   header('Access-Control-Allow-Headers:x-requested-with,content-type,accept,Authorization'); 
   require 'pdo_mysql.php'; 
   require 'tm_mysql.php'; 
   require('tm_push.php');
   $in_paras=file_get_contents('php://input');
   
   $para_json=json_decode($in_paras,true);
   $type = $para_json["type"];
   
   switch($type)
   {
	   case 'ckcode':
	      getCode();
	      break;
	   case 'login':
		  $name=$para_json["uname"];
		  $pass=$para_json["upass"];
		  $t_sql="select * from act_member where username='".$name."' and pmd5='".$pass."'";
		  getDbBySql($t_sql);
	      break;
	   case 'allFriends':
		  getAllFriends();
		  //getAllFrd();
		  break;
	   case 'getUserById':
	      //getUserById();
		  $uid=$para_json["to_uid"];
		  $t_sql="select id,username,email,mobile,nick,truename,ename,sex,pre from act_member where id='".$uid."'";
		  getDbBySql($t_sql);
	      break;
	   case 'searchUserName':
		  $uname=$para_json["uname"];
		  $t_sql="select id,username,email,mobile,nick,truename,ename,sex,pre from act_member where username like '%".$uname."%'";
		  getDbBySql($t_sql);
	      break;
	   case 'checkMyFriend':  //检查朋友
		  $uid=$para_json["uid"];
		  $fid=$para_json["fid"];
		  $t_sql="select * from act_friend where uid='".$uid."' and fid='".$fid."'";
		  getDbBySql($t_sql);
	      break;
	   case 'initSingleMes':  //初始化聊天信息
		  $uid=$para_json["uid"];
		  $to_uid=$para_json["to_uid"];
		  $t_sql="select * from (select * from chat_msg where (uid in ('".$uid."','".$to_uid."')) and (to_uid in ('".$uid."','".$to_uid."'))  order by id desc limit 9) as b order by b.send_date asc";
		  getTmDbBySql($t_sql);
	      break;
	   case 'addMess':   //添加信息
		  $uid=$para_json["uid"];
		  $to_uid=$para_json["to_uid"];
		  $to_data=$para_json["to_data"];
		  $msg_type=$para_json["msg_type"];
		  $msg_typevalue=$para_json["msg_typevalue"];
		  $uname=$para_json["uname"];
		  $to_name=$para_json["to_name"];
		  $t_sql="Insert Into chat_msg(id,uid,to_uid,to_data,send_date,status,msg_type,msg_typevalue,uname,to_name) Values ('".time()."','".$uid."','".$to_uid."','".$to_data."','".date('Y-m-d H:i:s')."',0,'".$msg_type."',".$msg_typevalue.",'".$uname."','".$to_name."')"; 
		  inTmDbBySql($t_sql);
	      break;
	   case 'uStatus':   //更新消息状态
		  $para=$para_json["para"];
		  if(strlen($para)>0){
			 $t_sql="update chat_msg set status=1,recv_date='".date('Y-m-d H:i:s')."' where id in (".$para.")"; 
			 inTmDbBySql($t_sql);
		  }
	      break;
	   case 'nFriendMes':   //查询单用户聊天
		  $uid=$para_json["uid"];
		  $to_uid=$para_json["to_uid"];
		  $t_sql="select * from chat_msg where to_uid='".$uid."' and uid='".$to_uid."' and status=0 and to_days(send_date) = to_days(now()) order by send_date asc";
		  getTmDbBySql($t_sql);
	      break;
	   case 'homeTopPic':  //图片
		  $t_sql="select id,name,pic from  `mo_advers` where state=2  order by timestamp desc limit 4";
		  getDbBySql($t_sql);
	      break;
	   case 'homeNewList':   //新闻
	      $ntype=$para_json["n_type"];
		  $t_sql="select id,name,timestamp,uid,des from mo_articles where cid =(select id from mo_category where name='".$ntype."' and visible=1) and state=2 order by timestamp desc limit 9";
		  getDbBySql($t_sql);
	      break;
	   case 'picDtl':  //图片详细
		  $dtl_id=$para_json["dtl_id"];
		  $t_sql="select * from  `mo_advers` where id=".$dtl_id;
		  getDbBySql($t_sql);
	      break;
	   case 'hnewDtl':   //新闻详细
	      $dtl_id=$para_json["dtl_id"];
		  $t_sql="select * from mo_articles where id=".$dtl_id;
		  getDbBySql($t_sql);
	      break;
	   case 'addAdvise':  //意见反馈
		  $uid=$para_json["uid"];
		  $mero=$para_json["mero"]; 
		  $t_sql="Insert Into user_advise(uid,mero,insert_time) Values ('".$uid."','".$mero."','".date('Y-m-d H:i:s')."')"; 
		  inTmDbBySql($t_sql);
	      break;
	   case 'getAllWb':
	      getAllweiBo();
	      break;
	   case 'getWbById':
	      getWbById();
	      break;
	   case 'getSingleWeiBo':
	      getSingleWeiBo();
	      break;
	   case 'addWb':
	      $uid=$para_json["uid"];
		  $mero=$para_json["mero"]; 
		  $unick=$para_json["unick"]; 
		  $t_sql="Insert Into weibo(id,uid,des,see,up,share,comments,fav,timestamp,tid,unick) Values ('".time()."','".$uid."','".$mero."',0,0,0,0,0,'".time()."',0,'".$unick."')"; 
		  inDbBySql($t_sql);
	      break;
	   case 'addWbPl':
	      $uid=$para_json["uid"];
		  $mero=$para_json["mero"]; 
		  $unick=$para_json["unick"]; 
		  $wid=$para_json["wid"]; 
		  $zzid=$para_json["zzid"]; 
		  $t_sql="Insert Into weibo_comments(id,uid,wid,des,floor,timestamp,reid,up,bad,comments,unick,created) Values ('".time()."','".$uid."','".$wid."','".$mero."',0,'".time()."',0,0,0,0,'".$unick."','".date('Y-m-d H:i:s')."')"; 
		  inDbBySql($t_sql);
		  $s_up="update weibo set  comments=if(isnull(comments),0,comments)+1 where id=".$wid;
		  inDbBySql($s_up);
		  
		  sendSysMes($zzid,$unick.'评论了你的微波','微博消息','sys_mesdtl');
	      break;
	   case 'getGid':
	      getUserByServer();
	      break;
	   case 'z_wb':
	      $uid=$para_json["uid"];
		  $unick=$para_json["unick"]; 
		  $wid=$para_json["wid"]; 
		  $zzid=$para_json["zzid"]; 
		  
		  $s_up="update weibo set  up=if(isnull(up),0,up)+1 where id=".$wid;
		  inDbBySql($s_up);
		  
		  sendSysMes($zzid,$unick.'赞了你的微波','微博消息','sys_mesdtl');
	      break;
	   case 'get_mes_one':
	      $uid=$para_json["uid"];
	      //$one_sql="select * from sys_msg where to_uid='".$uid."' order by send_date desc limit 8";
		  $one_sql="select * from sys_msg where EXISTS (select id from (SELECT max(id) as id,msg_type FROM sys_msg group by msg_type) t where t.id=sys_msg.id) and to_uid='".$uid."' limit 10";
		  getTmDbBySql($one_sql);
		  break;
	   case 'addFrdUser':
	      $uid=$para_json["uid"];
		  $fid=$para_json["fid"];
		  $s_sql="Insert Into act_friend(id,uid,fid,gid,timestamp,remark,state) Values (".time().",'".$uid."','".$fid."',0,".time().",'',0)";
		  inDbBySql($s_sql);
	      break;
	   case 'getRequestFriends':
	      $uid=$para_json["uid"];
		  $s_sql="select a.*,b.username,b.pre from act_friend a left join act_member b on a.fid=b.id where a.uid='".$uid."' and a.state=0 ";
		  getDbBySql($s_sql);
	      break;
	   case 'frd_user_accept':
	      $uid=$para_json["uid"];
		  $fid=$para_json["fid"];
		  $rid=$para_json["rid"];
		  $u_sql="update act_friend set state=2 where id=".$rid;
		  inDbBySql($u_sql);  //修改状态
		  
		  $s_sql="Insert Into act_friend(id,uid,fid,gid,timestamp,remark,state) Values (".time().",'".$fid."','".$uid."',0,".time().",'',2)";
		  inDbBySql($s_sql);
	      break;
	   case 'frd_user_refuse':
	      $rid=$para_json["rid"];
		  $s_sql="delete from act_friend where id=".$rid;
		  inDbBySql($s_sql);
	      break;
	   case 'getAllQun':
	      $uid=$para_json["uid"];
		  $s_sql="select a.id,a.uid as cjid,a.name,a.slogan,a.des,a.school,b.uid as pid,b.isman,a.timestamp,a.nums from topnt2016.group a left join topnt2016.grp_member b on a.id=b.gid where b.uid='".$uid."'";
		  getDbBySql($s_sql);
	      break;
	   case 'initGroupMes':
		  $uid=$para_json["uid"];
		  $to_uid=$para_json["to_uid"];
		  $t_sql="select * from (select * from chat_msg where to_uid='".$to_uid."'  order by id desc limit 10) as b order by b.send_date asc";
		  getTmDbBySql($t_sql);
	      break;
	   case 'NGroupMes':  
		  $uid=$para_json["uid"];
		  $to_uid=$para_json["to_uid"];
		  $t_sql="select * from chat_msg where to_uid='".$to_uid."' and uid<>'".$uid."' and status=0 and to_days(send_date) = to_days(now()) order by send_date asc";
		  getTmDbBySql($t_sql);
	      break;
	   case 'getBlogNews':
		  $s_sql="select * from blog_list order by created desc limit 10";
		  getDbBySql($s_sql);
	      break;
	   case 'getBlogNewsDtl':
	      $bid=$para_json["bid"];
		  $s_sql="select * from blog_list where id=".$bid;
		  getDbBySql($s_sql);
	      break;
	   case 'work_gg':
		  $s_sql="select * from push_list where  push_type=5 and cid in (select id from push_category where push_type=5 and name='公告') order by id desc limit 7";
		  getDbBySql($s_sql);
	      break;
	   case 'work_gg_dtl':
	      $dtl_id=$para_json["dtl_id"];
		  $s_sql="select * from push_list where  push_type=5 and id=".$dtl_id;
		  getDbBySql($s_sql);
	      break;
	   case 'work_dt':
		  $s_sql="select * from famous_article where state=2 order by id desc limit 9";
		  getDbBySql($s_sql);
	      break;
	   case 'work_dt_dtl':
	      $dtl_id=$para_json["dtl_id"];
		  $s_sql="select * from famous_article where id=".$dtl_id;
		  getDbBySql($s_sql);
	      break;
	   case 'group_tz':
		  $s_sql="select f.*,a.truename from grp_forum f join act_member a on a.id=f.uid order by f.see desc limit 9";
		  getDbBySql($s_sql);
	      break;
	   case 'group_tz_dtl':
	      $dtl_id=$para_json["dtl_id"];
		  $s_sql="select f.*,a.truename from grp_forum f join act_member a on a.id=f.uid where f.id=".$dtl_id;
		  getDbBySql($s_sql);
	      break;
	   case 'group_rm':
		  $s_sql="select a.id,a.name,a.slogan,a.mannums,b.name sname,@curRow := @curRow + 1 AS row_number from `group` a join school b on a.school=b.id JOIN (SELECT @curRow := 0) r where a.state=2 order by a.visit desc limit 10";
		  getDbBySql($s_sql);
	      break;
   }

   function getCode()
   {
	    $data = array(
		'versionCode'=>1,//版本号
		'versionName'=>'1.0.1',//版本名称
		'msg'=>"有新版本可供更新.<br/> 1.界面美化 <br/> 2.性能优化 <br/> 3.修复联网功能", //更新提示
		'apk'=>'http://192.168.10.224:8081/txy.apk' //app下载地址
	    );
	    echo urldecode(json_encode($data,JSON_UNESCAPED_UNICODE));
   }
   
   function getAllFriends(){
	   try{
		   global $para_json; 
		   $user_id=$para_json["uid"];
		   
		   $pd=new pdo_mysql(); 
		   $sql="select a.id as pid,a.uid as myid,a.fid,a.gid,b.username,b.email,b.mobile,b.nick,b.truename,b.sex,b.pre,c.`name` as groupName,c.odx from act_friend a "
             ."left join act_member b on a.fid=b.id left join act_friend_group c on a.gid=c.id	where a.state=2 and a.uid='".$user_id."' and username<>'NULL' order by username";
		   $rs=$pd->query($sql); 
		   //$json_result=json_encode($rs->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE); 
           //echo $json_result;
		    $arr_sub = array();
			$arr_sub1 = array();
			$arr_all = array();
			$arr_end = array();
			$arr_total = array();
			$name_tmp = "";
			$is_other = 0;

			while ($r=$rs->fetch(PDO::FETCH_ASSOC)) {
				$name = substr($r["username"],0,1);   //截取字串第一个字符
				if (preg_match('/^[a-zA-Z]+$/',$name)){   //判断是否为字母的处理，按照字母顺序处理
					$name_upper = strtoupper($name);
					if(strcmp($name_tmp,$name_upper) != 0){
						$rs1=$pd->query("select a.id as pid,a.uid as myid,a.fid,a.gid,b.username,b.email,b.mobile,b.nick,b.truename,b.sex,b.pre,c.`name` as groupName,c.odx from act_friend a left join act_member b on a.fid=b.id left join act_friend_group c on a.gid=c.id	where a.state=2 and a.uid='a1401955133' and username<>'NULL' order by username");
						while ($r1=$rs1->fetch(PDO::FETCH_ASSOC)) {
							$name_sub = substr($r1["username"],0,1);   //截取字串第一个字符
							$name_sub_upper = strtoupper($name_sub);
							if(strcmp($name_sub_upper,$name_upper) == 0){
								array_push($arr_sub,$r1);
							}
						}

						$arr_all["pname"]=$name_upper;
						$arr_all["subrows"]=$arr_sub;
						array_push($arr_end,$arr_all);

						$arr_sub = array();
						$name_tmp = $name_upper;
					}
				}else{    //首字母不为字母的处理
					array_push($arr_sub1,$r);
					$is_other = 1;
				}
			}

			if($is_other){
				$arr_all["pname"]="#";
				$arr_all["subrows"]=$arr_sub1;
				array_push($arr_end,$arr_all);
			}

			$arr_total["results"]=$arr_end;
			$json = json_encode($arr_total,JSON_UNESCAPED_UNICODE);
			echo $json;

		   $pd->close(); 
		   unset($pd); 
		   unset($rs);
		   unset($rs1);
	   }catch(Exception $e)
	   {
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   
   function getAllFrd(){
	   $in_txt=file_get_contents("friends.json");
       $sst= json_decode($in_txt,true);
	   echo json_encode($sst);
   }
   function writeLogFile($w_para){
	   $myfile = fopen("newLogo.txt", "w") or die("Unable to open file!");
       fwrite($myfile, $w_para);
	   fclose($myfile);
   }
   //微博
   function getAllweiBo(){
	   try{
		   global $para_json; 
		   $pagenumber=$para_json["pagenumber"];
		   
		   $pd=new pdo_mysql(); 	
		   $wbsql="select T.*,M.truename from weibo T left join act_member M on M.id=T.uid  order by T.timestamp desc limit ".(($pagenumber-1)*10).",10"; 
           $wb_arrs=$pd->query($wbsql);
           $wbarrs=$wb_arrs->fetchAll(PDO::FETCH_ASSOC);
           foreach($wbarrs as $k1=>$v1){
			   $url='http://'.$_SERVER['HTTP_HOST'];
			   $wbarrs[$k1]['des']=str_replace("../..", $url, $v1['des']);	 
		   }	
		   $wb_result["results"]=$wbarrs;
		   $wb_resultjson = json_encode($wb_result,JSON_UNESCAPED_UNICODE);
		   echo $wb_resultjson;
		   $pd->close(); 
		   unset($pd); 
		   unset($wb_arrs);
	   }catch(Exception $e)
	   {
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //根据id微博
   function getWbById(){
	   try{
		   global $para_json; 
		   $wbid=$para_json["wbid"];
		   
		   $pd=new pdo_mysql(); 	
		   $wbsql="select T.*,M.truename from weibo T left join act_member M on M.id=T.uid where T.id=".$wbid."  order by T.timestamp desc"; 
		   $wbcoment="select a.*,b.username,b.truename from weibo_comments a left join act_member b on a.uid=b.id where a.wid=".$wbid;
           $wb_arrs=$pd->query($wbsql);
		   $wbc_arrs=$pd->query($wbcoment);

           $wbarrs=$wb_arrs->fetchAll(PDO::FETCH_ASSOC);
		   $wbcarrs=$wbc_arrs->fetchAll(PDO::FETCH_ASSOC);
           foreach($wbarrs as $k1=>$v1){
			   $url='http://'.$_SERVER['HTTP_HOST'];
			   $wbarrs[$k1]['des']=str_replace("../..", $url, $v1['des']);
			   $wbarrs[$k1]['comment_content']=$wbcarrs;
		   }	
		   $wb_result["results"]=$wbarrs;
		   $wb_resultjson = json_encode($wb_result,JSON_UNESCAPED_UNICODE);
		   echo $wb_resultjson;
		   $pd->close(); 
		   unset($pd); 
		   unset($wb_arrs);
           unset($wbc_arrs);
	   }catch(Exception $e)
	   {
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //获取个人微博
   function getSingleWeiBo(){
	   try{
		   global $para_json; 
		   $pagenumber=$para_json["pagenumber"];
		   $uid=$para_json["uid"];
		   
		   $pd=new pdo_mysql(); 	
		   $wbsql="select T.*,M.truename from weibo T left join act_member M on M.id=T.uid  where uid = '".$uid."'  order by T.timestamp desc limit ".(($pagenumber-1)*10).",10"; 
           $wb_arrs=$pd->query($wbsql);
           $wbarrs=$wb_arrs->fetchAll(PDO::FETCH_ASSOC);
           foreach($wbarrs as $k1=>$v1){
			   $url='http://'.$_SERVER['HTTP_HOST'];
			   $wbarrs[$k1]['des']=str_replace("../..", $url, $v1['des']);	 
		   }	
		   $wb_result["results"]=$wbarrs;
		   $wb_resultjson = json_encode($wb_result,JSON_UNESCAPED_UNICODE);
		   echo $wb_resultjson;
		   $pd->close(); 
		   unset($pd); 
		   unset($wb_arrs);
	   }catch(Exception $e)
	   {
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //用户信息对应
   function getUserByServer(){
	   try{
		   global $para_json; 
		   $gid=$para_json["gid"];
		   $uid=$para_json["uid"];
		   
		   $pd=new tm_mysql(); 
		   $search_sql="select * from user_server where uid='".$uid."'";
		   $rs_search=$pd->query($search_sql);  
		   $search_arry=$rs_search->fetchAll(PDO::FETCH_ASSOC);

		   if($search_arry){ //存在
			   if($gid==$search_arry[0]["gid"]){
				   
			   }else{//update
				   $update_sql="update user_server set gid='".$gid."' where uid='".$uid."'";
				   $pd->exec($update_sql); 
			   }
		   }else{//不存在
			   $i_sql="Insert Into user_server(uid,gid) Values ('".$uid."','".$gid."')";
			   $pd->exec($i_sql);  
		   }
		   $pd->close(); 
		   unset($pd); 
		   unset($rs_search);
	   }catch(Exception $e){
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //发送系统信息
   function sendSysMes($in_uid,$in_content,$msg_type,$p_dtl){
	   try{
		   $pd=new tm_mysql(); 
		   $search_sql="select * from user_server where uid='".$in_uid."'";
		   $rs_search=$pd->query($search_sql);  
		   $search_arry=$rs_search->fetchAll(PDO::FETCH_ASSOC);
		   
		   $t_sql="Insert Into sys_msg(id,uid,to_uid,to_data,send_date,status,msg_type,msg_typevalue,uname,to_name) Values ('".time()."','00001','".$in_uid."','".$in_content."','".date('Y-m-d H:i:s')."',0,'".$msg_type."',5,'','')"; 
		   $pd->exec($t_sql); 
		   
		   if($search_arry){
			   $o_gid=$search_arry[0]["gid"];
			   $my_send=new tm_push();
			   $my_send->sendNoteAlerts($o_gid,$in_content,$msg_type,$p_dtl);
		   }
		   
		   $pd->close(); 
		   unset($pd); 
		   unset($rs_search);
	   }catch(Exception $e){
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //聊天库  执行
   function inTmDbBySql($sql){
	   try{
		   $pd=new tm_mysql(); 
		   $pd->exec($sql);  
		   
		   $pd->close(); 
		   unset($pd); 
	   }catch(Exception $e){
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //聊天库 查询
   function getTmDbBySql($sql){
	   try{
		   $pd=new tm_mysql(); 
		   $rs=$pd->query($sql); 
		   $json_result=json_encode($rs->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE); 
           echo $json_result;
		   $pd->close(); 
		   unset($pd); 
		   unset($rs);
	   }catch(Exception $e)
	   {
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //平台库  执行
   function inDbBySql($sql){
	   try{
		   $pd=new pdo_mysql(); 
		   $pd->exec($sql);  
		   
		   $pd->close(); 
		   unset($pd); 
	   }catch(Exception $e){
		   echo 'Message: ' .$e->getMessage();
	   }
   }
   //平台库 查询
   function getDbBySql($sql){
	   try{
		   $pd=new pdo_mysql(); 
		   $rs=$pd->query($sql); 
		   $json_result=json_encode($rs->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE); 
           echo $json_result;
		   $pd->close(); 
		   unset($pd); 
		   unset($rs);
	   }catch(Exception $e)
	   {
		   echo 'Message: ' .$e->getMessage();
	   }
   }
?>
