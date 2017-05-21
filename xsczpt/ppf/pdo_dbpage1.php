<?php
/**ver 2.1版使用数据ppf_tpl
* 后台数据库分页 pdo mysql  
*@lren-zhs 2014-12-28
*/
require $_SERVER['DOCUMENT_ROOT'].'/cfg/config.inc';
require_once 'fun.php';

class pdo_dbpage1{
	protected $tpl;#模板名称 	
	protected $C;#数据配置属性
	#protected $config; 	
 	#protected $save;
 	protected $query;
	protected $table;
 	protected $columns="*";
	public $db;

	function __construct($tplname,$charset=DB_CHARSET)	{		
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
	public $orderby = "order by id asc";
	public $where = "id is not null ";
	#public $qwhere = ""; //url &条件
	public $psize = 20;
	public $pindex = 1;
	public $pgcount = 0;
	public $rowcount = 0;
	public $pgnosize = 10; //页码分组

	/**分页*/
	public function ByPage(){	  
		$this->pindex=$_POST["l_page"];
		if($this->pindex<1)$this->pindex=1;
	
		$this->getWhrOrd();#获取where
	
		$sql=$this->getPageSql();	
    	$rs=$this->db->query($sql);
		#数据返回成json 
		$jsonstr=$this->getJSON($rs);		
		echo '[{"pindex":'.$this->pindex.',"pgcount":'.$this->pgcount.',"rowcount":'.$this->rowcount.',"data":"'.$jsonstr.'"}]';	
		unset($rs);			
	}
	/**获取分页sql*/
	function getPageSql(){	 
		$rs=$this->db->query("select count(1) from `".$this->C["tblname"]."` where ".$this->where);
		#echo "select count(1) from `".$this->C["tblname"]."` where ".$this->where;
		$this->rowcount=$rs->fetchColumn(0);	
		if(!$this->rowcount){
			#echo $this->where;
			echo "null";
			exit;
		}	
		unset($rs);
		$this->pgcount = ceil($this->rowcount / $this->psize);						
		if ($this->pgcount > 0 && $this->pindex >$this->pgcount) 
			$this->pindex = $this->pgcount;        		
		return "select ".$this->columns." from `".$this->C["tblname"]."` where ".$this->where." ".$this->orderby." limit ".
            		(($this->pindex - 1) * $this->psize) .",".$this->psize;  
	}
	function getWhrOrd(){
			$cols=$this->db->query("desc `".$this->C["tblname"]."`")->fetchAll(PDO::FETCH_COLUMN,0);	 #FETCH_COLUMN			
			$whr='';		
			foreach ($_POST as $key => $value) {#$_REQUEST										
				if(!isset($value)||$value==""){
					continue;
				}
				#echo in_array($key , $cols);					
				if(in_array($key , $cols)){						
					if($value==""){
							//为空不设置
					}
					else if(startWith($value," in (")&&endWith($value,")")){ #特殊 in (
						$whr.="and `".$key."`".$value." ";
					}
					else if(is_numeric($value)) #数字 &&$value>0
						$whr.="and `".$key."`=".$value." ";
					else
						$whr.="and `".$key."` like '%".$value."%' ";					
				}
			}			
			if($whr){
				$this->where=substr($whr, 3);#跳过and			
				#if($this->config->Attr('where'))#默认条件
				#	$this->where.=$this->config->Attr('where');
			}  
			$ord='';
			if(isset($_POST["l_order"])){			
				$ord="order by `".$_POST["l_order"]."` ".$_POST["l_orderby"];				
			}
			if($ord!="")
				$this->orderby=$ord;					
		unset($arr);
		unset($cols);							
	}
	/**删除数据*/
	public function Del(){
		if($this->db->exec("delete from `".$this->C["tblname"]."` where `".$this->C["tblkey"]."`='".$_POST["id"]."'"))
			echo "ok";
		else
			echo "Err:Not delete";
	}	
  public function Audit(){
    $col=isset($_POST["col"])?$_POST["col"]:"state";
    $val=isset($_POST["val"])?$_POST["val"]:"2";
    $v=isset($_POST["v"])?$_POST["v"]:"";#orc val
    if($v!="")$wh=" and `".$col."`='".$v."'";else $wh="";    
    if($this->db->exec("update `".$this->C["tblname"]."` set `".$col."`='".$val."' where `".$this->C["tblkey"]."`='".$_POST["id"]."' ".$wh)){
			echo "ok"; 
		}
		else
			 echo "err:Not Change";      
  }
  public function AuditPage(){
    $col=isset($_POST["col"])?$_POST["col"]:"state";
    $val=isset($_POST["val"])?$_POST["val"]:"2";
    $v=isset($_POST["v"])?$_POST["v"]:"";
    if(!empty($v))$wh=" and `".$col."`='".$v."'";else $wh="";    
    if($this->db->exec("update `".$this->C["tblname"]."` set `".$col."`='".$val."' where `".$this->C["tblkey"]."` in (".$_POST["ids"].") ".$wh)){
			echo "ok"; 
		}
		else
			 echo "err:Not Change";      
  }
	public function getJSON($result){	
		return base64_encode(json_encode($result->fetchAll(PDO::FETCH_ASSOC))); 
	}		
}