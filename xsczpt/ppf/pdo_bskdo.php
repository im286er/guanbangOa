<?php
/*ver1.1
pdo mysql 后台数据 增删改读 版本3。0
*cplus@lren.org 　2014-4-26
*/ 
require $_SERVER['DOCUMENT_ROOT'].'/cfg/config.inc';
require_once 'fun.php'; 

class  pdo_bskdo{ 	
	protected $tpl;#模板名称 	
	protected $C;#数据配置属性	 
	protected $id;
	protected $query;
	protected $seed=1;
	public $db;
	public $nid=0;#新建用户编号
	
	public function __construct($tplname,$charset=DB_CHARSET){
		$this->tpl=$tplname;  			
		$this->connect($charset);	
		$this->getTpl();
	}
	protected function connect($charset=DB_CHARSET){
		try{
			$this->db=new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=".$charset
				,DB_USER,DB_PWD,array(PDO::ATTR_PERSISTENT=>false));			 
		}
		catch(PDOException $e){
			die("Connect not open pdo_db:" . $e->getMessage () );	
		}
		$this->db->exec("SET NAMES ".$charset);
	}
	protected function getTpl(){#获取模板信息		
		$arr=$this->db->query("select * from ppf_tpl where tpl='".$this->tpl."'")->fetchAll(PDO::FETCH_ASSOC);
		$this->C=$arr[0];		
	}
	
	public function close(){
		if($this->db)
			unset($this->db);
	} 
	
	public function Read($id){		
		$whr=$this->getReadSql($id);		
		$sql="select * from `".$this->C["tblname"]."` where ".$whr;		
		#echo $sql;exit;		
		$rs=$this->db->query($sql); 		
		#$jsonstr=$this->getJSON($rs);
		$jsonstr=json_encode($rs->fetchAll(PDO::FETCH_ASSOC));	
		echo $jsonstr;
		unset($rs);
	}
	function getReadSql($id){		
		if(empty($id))return "id=0";
		if(is_numeric($id))
			return "`".$this->C["tblkey"]."`=".$id;
		else
			return "`".$this->C["tblkey"]."`='".$id."'";	
	}
	/****************************************************************
	 * 
	*保存
	*
	***************************************************************/
	public function Save($d){		
		#检测唯一字段
		if($this->C['tblunique']){
			if($this->checkUnique($d)){							
				return;
			}
		}	
		$sql=$this->get_saveSql($d) ;#生成保存sql			
		if($sql){						
			if($this->db->exec($sql))
				echo "ok";
			else
				echo "no".$sql;			
		}
		else 
			echo "err";
	}
	protected function checkUnique($d){	
		if($this->C['tblunique']){			
			$arr1=$this->db->query("select * from ppf_tpl_unique where ptid=".$this->C["id"])->fetchAll();
			$ids=" `".$this->C["tblkey"]."`!='".base64_decode($d[$this->C["tblkey"]])."' and ";
			for($k=0;$k<count($arr1);$k++){	 
					$val=base64_decode($d[$arr1[$k]["col"]]);   
					if($this->db->query("select count(1) from `".$this->C["tblname"]."` where ".$ids." `".$arr1[$k]["col"]."`='".$val."'")->fetchColumn(0)){
						echo	 "exist".$arr1[$k]["note"];
						unset($arr1);					
						return 1;
					}	
			} 				
			unset($arr1);	
			return 0;		
		}
		else 
			return 0;
	}
	/**生成保存sql*/
	protected function get_saveSql($d){
		#desc|describe 表名;#show columns from 表名;#show create table 表名;			 
		$cols=$this->db->query("desc `".$this->C["tblname"]."`")->fetchAll(PDO::FETCH_COLUMN,0);	 
		$sql= " update `".$this->C["tblname"]."` "."set ";	
		foreach ($d as $key => $value) {							
			if(in_array($key , $cols)&&$value!=""&&$key!=$this->C["tblkey"]){			
				#$sql.="`".$key."`=".(is_numeric($value)?$value:"'". base64_decode($value)."'").",";
				$val=base64_decode($value);#echo $value;
				if($val&&$val[0]=="'")
					$sql.="`".$key."`=".$val.",";
				else
					$sql.="`".$key."`='".$val."',";#(is_numeric($val)?$val.",":"'".$val."',")
			}			
		}		 
		#echo $sql;exit;
		#md5 和 sha1 密码
		if($this->C['tblmd5']&&!empty($d[$this->C['ctrlpass']]))
				$sql.="`".$this->C['tblmd5']."`='".md5(base64_decode($d[$this->C['ctrlpass']]))."',";			
		if($this->C['tblsha1']&&!empty($d[$this->C['ctrlpass']]))
				$sql.="`".$this->C['tblsha1']."`='".sha1(base64_decode($d[$this->C['ctrlpass']]))."',";		
		if($this->C['tbldefault']){#默认值				 
			$arr1=$this->db->query("select * from ppf_tpl_default where ptid=".$this->C["id"])->fetchAll();#PDO::FETCH_COLUMN
			for($k=0;$k<count($arr1);$k++){
				if(!strpos($sql,'`'.$arr1[$k]["col"].'`')){
					$sql.="`".$arr1[$k]["col"]."`=".$arr1[$k]["val"].",";					
				}
			} 	
			unset($arr1);			
		}
		
		if(empty($d[$this->C["tblkey"]]))
			$sql.="where `".$this->C["tblkey"]."`=0";
		else{
			$id=base64_decode($d[$this->C["tblkey"]]);
			if(is_numeric($id))
				$sql.="where `".$this->C["tblkey"]."`=".$id." ";
			else
				$sql.="where `".$this->C["tblkey"]."`='".$id."' ";				
		}						
		unset($arr);
		unset($cols);
		return str_replace(',where',' where',$sql);	
	}
	/****************************************************************
	 * 
	*插入
	*
	***************************************************************/
	public function Insert($d){		
		#检测唯一字段
		if($this->C['tblunique']){
			if($this->checkNewUnique($d)){							
				return;
			}
		}		
		$sql=$this->get_insertSql($d);		
		if($sql){
			if($this->db->exec($sql))
				echo "ok".$this->nid;
			else
				echo "no".$sql;
		}
		else 
			echo "err";
	}
	function checkNewUnique($d){#检测数据是否相同
		if($this->C['tblunique']){			
			$arr1=$this->db->query("select * from ppf_tpl_unique where ptid=".$this->C["id"])->fetchAll();			
			for($k=0;$k<count($arr1);$k++){	 
					$val=base64_decode($d[$arr1[$k]["col"]]);					
					if($this->db->query("select count(1) from `".$this->C["tblname"]."` where `".$arr1[$k]["col"]."`='".$val."'")->fetchColumn(0)){
						echo	 "exist".$arr1[$k]["note"];
						unset($arr1);					
						return 1;
					}	
			} 				
			unset($arr1);	
			return 0;		
		}
		else 
			return 0;
	}
	function get_insertSql($d){		 
		$cols=$this->db->query("desc ".$this->C["tblname"])->fetchAll(PDO::FETCH_COLUMN,0); 
		$sql= " insert into `".$this->C["tblname"]."`(";	
		$sql1=" values(";
		#print_r($d);
		foreach ($d as $key => $value) {	
			if(empty($value)&&$value!="0")continue; #值为空 0不是空	
			if(in_array($key , $cols)){	
				$val=base64_decode($value);	
				if(empty($val)&&$val!="0")continue; #值为空 0不是空
				$sql.="`".$key."`,";
				if($val&&$val[0]=="'")
					$sql1.=$val.",";
				else
					$sql1.=is_numeric($val)?$val.",":"'".$val."',";
			}			
		}
		if($this->C['tblmd5']&&!empty($d[$this->C['ctrlpass']])){ 	#md5和sha1密码
		$sql.="`".$this->C['tblmd5']."`,";	
		$sql1.="'". md5(base64_decode($d[$this->C['ctrlpass']]))."',";				
		}
		if($this->C['tblsha1']&&!empty($d[$this->C['ctrlpass']]))	{
				$sql.="`".$this->C['tblsha1']."`,";	
				$sql1.="'". sha1(base64_decode($d[$this->C['ctrlpass']]))."',";
		}
		if($this->C['tbldefault']){#默认值				 
			$arr1=$this->db->query("select * from ppf_tpl_default where ptid=".$this->C["id"])->fetchAll();
			for($k=0;$k<count($arr1);$k++){
				if(!strpos($sql,'`'.$arr1[$k]["col"].'`')){
					$sql.="`".$arr1[$k]["col"]."`,";
					$sql1.=$arr1[$k]["val"].",";			 				
				}
			} 	
			unset($arr1);
		} 
		unset($arr);
		unset($cols);	  
		if(!strpos($sql,'`'.$this->C["tblkey"].'`')){
      if($this->C['usepre']){#使用前缀       
        $this->nid=DATA_PRE.time();
      }
  		else{    
        if($this->C['tblseed']=="time"){ #自增种子
          $v=$this->db->query("select max(`".$this->C["tblkey"]."`) from `".$this->C["tblname"]."`")->fetchColumn(0);	
      		if($v&&$v>time())
      			$this->nid=$v+1;			
      		else
      			$this->nid=time(); 
        }
        else
  			 $this->nid=$this->gen_id($this->C['tblseed']);
      }
			/*if($this->C['usepre']) $pre=DATA_PRE;#使用前缀			
			if($this->C['tblseed']=="time") #自增种子
				$this->nid=$pre.time();
			else
				$this->nid=$pre.$this->gen_id($this->C['tblseed']);
      */  
			return $sql.$this->C["tblkey"].")".$sql1."'".$this->nid."')";	
		}
		else{
			$this->nid=base64_decode($d[$this->C['tblkey']]);
			return substr($sql,0,strlen($sql)-1).")".substr($sql1,0,strlen($sql1)-1).")";		
		}
	}
	/*****************************************************************
	* 
	* 保存数据使用已存在的id
	* 
	****************************************************************/
	public function SaveInsert($d) {                   			
		//$arr=get_object_vars ($d);
		if(isset($d[$this->C["tblkey"]])){				
			$sql="select count(1) from `". $this->C["tblname"] ."` where `". $this->C["tblkey"]."`='" .base64_decode($d[$this->C["tblkey"]])."'";	
			#echo $sql;exit;
			if(!$this->db->query($sql)->fetchColumn(0)){
				#echo "insert into ".$this->C["tblname"]."(`".$this->C["tblkey"]."`) values(".base64_decode($d[$this->C["tblkey"]]).")";
				$this->db->exec("insert into ".$this->C["tblname"]."(`".$this->C["tblkey"]."`) values('".base64_decode($d[$this->C["tblkey"]])."')");
			}
		}       
		$this->Save($d);           
	}	 
	/****************************************************************
	 * 
	*数据库操作
	*
	***************************************************************/
	function gen_id($seed=1){			
		return $this->db->query(" select ifnull(max(`".$this->C["tblkey"]."`)+1,".$seed.") from `".$this->C["tblname"]."`")->fetchColumn(0); 		 	
	} 	
	public function getJSON($result){	
		$jsonstr=json_encode($result->fetchAll(PDO::FETCH_ASSOC));	
	}	
  /**删除数据*/
	public function Del($id){
		if($this->db->exec("delete from `".$this->C["tblname"]."` where `".$this->C["tblkey"]."`='".$id."'")){
			echo "ok"; 
		}
		else
			 echo "Err:Not delete";
	}	
}#266