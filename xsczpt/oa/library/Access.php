<?php
/**
 * Db Class (Access2000-2003 For ADO版)
 * @OS 		Windows平台
 * @package	DbAccess
 * @author  Adrian·Tian
 * @date    2011-06-15
 */
 
class Access
{
	//配置变量
	protected $pass    = '';//数据库密码
	protected $db	   = '';//数据库名称(绝对路径D:\webserver\htdocs\test\access.mdb)
	protected $charset = 65001;//数据库编码(utf8) 936(GB2312) 
	protected $conn    = null;
	protected $connstr = '';
	
	/**
	 * 功能：构造函数
	 * 参数：配置数组
	 *  $params = array('db','pass','charset')
	 */
	 public function __construct($params = array())
	 {
		$this->db      = empty($params['db'])?'f:\webserver\htdocs\zhsz\demo(xls)\student.mdb':$params['db'];
		$this->pass    = empty($params['pass'])?'':$params['pass'];
		$this->charset = empty($params['charset'])?'65001':$params['charset'];
		$this->conn    = new COM('ADODB.Connection',NULL,$this->charset) or die ('ADO连接失败!');
		$this->connstr = 'DRIVER={Microsoft Access Driver (*.mdb)};Uid=;Pwd='.$this->pass.';DBQ=' . realpath($this->db);
		$this->conn->open($this->connstr);
	 }
	
	/**
	 * 功能：自定义查询数据语句
	 *  $sql = ''
	 */
	 public function query($sql = '',$more=1,$debug = 0)
	 {
		if($debug==1){
			die($sql);
		}
		$result = array();
		$rs     = $this->conn->execute($sql);
		if($more){
			$row    = 0;
			while(!$rs->eof){
				for($i=0;$i<$rs->fields->count;$i++){
				   $rsf = $rs->fields[$i];
				   $result[$row][$rsf->name] = $rsf->value;
			   }
				$rs->movenext;
				$row++;
			}
		}else{
			if(!$rs->eof){
				for($i=0;$i<$rs->fields->count;$i++){
				   $rsf = $rs->fields[$i];
				   $result[$rsf->name] = $rsf->value;
			   }
			}
		}
		return $result;
	 }
	
	/**
	 * 功能：获取单个字段数据
	 * 参数：配置数组
	 *  $params = array('field','table','where')
	 */
	 public function fetchOne($params = array())
	 {
	 	if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		$sql    = "SELECT {$params['field']} FROM {$params['table']} WHERE {$params['where']}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = array();
		$rs     = $this->conn->execute($sql);
		if(!$rs->eof){
			return $rs->fields[0]->value;
		}else{
			return '';	
		}
	 }
	
	/**
	 * 功能：获取单行数据
	 * 参数：配置数组
	 *  $params = array('filed'=>array(),'table','where')
	 */
	 public function fetchRow($params = array())
	 {
	 	$fields = join(',',$params['field']);
		$sql    = "SELECT {$fields} FROM {$params['table']} WHERE {$params['where']}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = array();
		$rs     = $this->conn->execute($sql);
		if(!$rs->eof){
			for($i=0;$i<$rs->fields->count;$i++){
               $rsf = $rs->fields[$i];
               $result[$rsf->name] = $rsf->value;
           }
		}
		return $result;
	 }
	
	/**
	 * 功能：获取多行数据
	 * 参数：配置数组
	 *  $params = array('filed'=>array(),'table','where','order','page','nums')
	 */
	 public function fetchAll($params = array())
	 {
	 	$fields = join(',',$params['field']);
		$order  = '';
		$limit  = '';
		if(!empty($params['order'])){
			$order  = "ORDER BY {$params['order']}";
		}
		if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		$page = 1;
		$nums = 10000;
		if(!empty($params['page'])){
			$page = $params['page'];
		}
		if(!empty($params['nums'])){
			$nums = $params['nums'];
		}
		$sql    = "SELECT {$fields} FROM {$params['table']} WHERE {$params['where']} {$order}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = array();
		$rs     = new COM('ADODB.Recordset',NULL,$this->charset) or die ('Recordset打开失败!');
		$rs->open($sql,$this->conn,1,1);
		if(!$rs->eof){
			$rs->pagesize     = $nums;
			$rs->absolutepage = $page;
			$currentPage      = $rs->pagesize;
			$row    = 0;
			while(!$rs->eof&&$currentPage>0){
				for($i=0;$i<$rs->fields->count;$i++){
				   $rsf = $rs->fields[$i];
				   $result[$row][$rsf->name] = $rsf->value;
			   }
				$rs->movenext;
				$row++;
				$currentPage--;
			}
			$rs->close();
		}
		return $result;
	 }
	
	/**
	 * 功能：获取多列数据数据
	 * 参数：配置数组
	 *  $params = array('field','table','where','order','page','nums')
	 */
	 public function fetchCol($params = array())
	 {
		$order  = '';
		$limit  = '';
		if(!empty($params['order'])){
			$order  = "ORDER BY {$params['order']}";
		}
		if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		$page = 1;
		$nums = 10000;
		if(!empty($params['page'])){
			$page = $params['page'];
		}
		if(!empty($params['nums'])){
			$nums = $params['nums'];
		}
		$sql    = "SELECT {$params['field']} FROM {$params['table']} WHERE {$params['where']} {$order}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = array();
		$rs     = new COM('ADODB.Recordset',NULL,$this->charset) or die ('Recordset打开失败!');
		$rs->open($sql,$this->conn,1,1);
		if(!$rs->eof){
			$rs->pagesize     = $nums;
			$rs->absolutepage = $page;
			$currentPage      = $rs->pagesize;
			$row    = 0;
			while(!$rs->eof&&$currentPage>0){
			   $rsf = $rs->fields[0];
			   $result[$row] = $rsf->value;
				$rs->movenext;
				$row++;
				$currentPage--;
			}
			$rs->close();
		}
		return $result;
	 }
	
	/**
	 * 功能：插入数据
	 * 参数：配置数组
	 *  $params = array('data'=>array(),'table')
	 */
	 public function insert($params = array())
	 {
	 	$fields = '';
		$values = '';
		foreach($params['data'] as $key=>$val){
			$fields .= $key.',';
			$values  .= "'".$val."',";
		}
		$fieldes = '('.trim($fields,',').')';
		$values  = '('.trim($values,',').')';
		$sql = addslashes("INSERT INTO {$params['table']}{$fieldes} VALUES{$values}");
		if(!empty($params['debug'])){
			die($sql);
		}
		
		$rs = new COM('ADODB.Recordset',NULL,$this->charset) or die ('Recordset打开失败!');
		$rs->open("SELECT * FROM {$params['table']}",$this->conn,1,3);
		$rs->addNew();
		foreach($params['data'] as $key=>$val){
			$rs->fields[$key] = $val;
		}
		$rs->update();
		$id = $rs->fields[0]->value;
		$rs->close();
		return $id;
	 }
	
	 /**
	 * 功能：删除数据
	 * 参数：配置数组
	 *  $params = array('table','where')
	 */
	 public function delete($params = array())
	 {
		if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		$sql = "DELETE FROM {$params['table']} WHERE {$params['where']}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$this->conn->execute($sql);
	 }
	
	/**
	 * 功能：更新数据
	 * 参数：配置数组
	 *  $params = array('data','table','where')
	 */
	 public function update($params = array())
	 {
	 	$set = '';
		foreach($params['data'] as $key=>$val){
			$set .= $key.'="'.$val.'",';
		}
		$set = trim($set,',');
		if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		$sql = addslashes("UPDATE {$params['table']} SET {$set} WHERE {$params['where']}");
		if(!empty($params['debug'])){
			die($sql);
		}
		$rs = new COM('ADODB.Recordset',NULL,$this->charset) or die ('Recordset打开失败!');
		$rs->open("SELECT * FROM {$params['table']} WHERE {$params['where']}",$this->conn,1,3);
		if(!$rs->eof){
			foreach($params['data'] as $key=>$val){
				$rs->fields[$key] = $val;
			}
			$rs->update();
			$rs->close();
		}
	 }
	
	 /**
	 * 功能：执行SQL语句
	 * $sql = ''
	 * 无法返回值
	 */
	 public function execSQL($sql = '',$debug = 0)
	 {
		if($debug==1){
			die($sql);
		}
		$this->conn->execute($sql);
	 }
	
	/**
	 * 功能：关闭数据库连接
	 */ 
	 public function close()
	 {
	 	$this->conn->close();
	 }	
	 
	 //析构函数
	 public function __destruct()
	 {
		 $this->conn->close();
	 }		 	
}
?>