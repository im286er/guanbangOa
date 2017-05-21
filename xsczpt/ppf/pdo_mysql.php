<?php 
/* pdo mysql数据库连接操作
*/
require_once $_SERVER['DOCUMENT_ROOT'].'/cfg/config.inc'; 

class pdo_mysql{	
	public $db;
	//http://www.php.net/manual/zh/pdo.drivers.php
	//dsn: "mysql:host=localhost;port=3306;dbname=test;charset=UTF8";	
	//dsn:sqlite:example.db
	public function __construct($dsn='',$user=DB_USER,$pass=DB_PWD,$charset=DB_CHARSET,$klive=false)	{
	try {
		if(empty($dsn))
			$dsn="mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=".$charset;				 
		$this->db=new PDO($dsn,$user,$pass,array(PDO::ATTR_PERSISTENT=>$klive));			 
	  }catch (PDOException $e) {	 
			die("Connect not open pdo_db:".$e->getMessage());	
	  }
	  $this->db->exec("SET NAMES ".$charset);	
	}
	public function __destruct() {
		$this->close();
	}	 
	public function close(){
		if(isset($this->db))
			unset($this->db);
	 }
	 /*字符串转义*/
	 public function quote($str){
		return $this->db->quote($str);
	 }
	 /*运行insert/update/delete 返回影响行数*/
	 public function exec($sql){
		return $this->db->exec($sql);
	 }
	 /*最后insert的主键*/
	 public function lastInsertId(){
		return $this->db->lastInsertId();
	 }
	 /*查询　*/
	 public function query($sql){
		return $this->db->query($sql);
	 }
	/*预处理*/
	public function prepare($sql){
		return $this->db->prepare($sql);
	}
	
	 public function genid($table,$id='id',$seed=1){			
		$rs=$this->db->query(" select max(`".$id."`) from `".$table."`");	
		$v=	$rs->fetchColumn(0);
		unset($rs);
		if($v)
			return $v+1;			
		else
			return $seed;		
	}
  public function genaid($table,$id='aid',$seed=1){			
		$rs=$this->db->query(" select max(`".$id."`) from `".$table."`");	
		$v=	$rs->fetchColumn(0);
		unset($rs);
		if($v)
			return $v+1;			
		else
			return $seed;		
	} 
  public function gentmrid($table,$id='id'){
    $v=$this->db->query("select max(`".$id."`) from `".$table."`")->fetchColumn(0);	
		if($v&&$v<=time())
			return $v+1;			
		else
			return time();		
  }	
  
   /**
	 * 功能：获取单个字段数据
	 * 参数：配置数组
	 *  $params = array('field','table','where')
	 */
	public function fetchOne($params = array())
	{
	 	$order  = '';
		if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		if(!empty($params['order'])){
			$order  = "ORDER BY {$params['order']}";
		}
		if(empty($params['group'])){
			$group = '';
		}else{
			$group = $params['group'];
		}
		
		$sql = "SELECT {$params['field']} FROM {$params['table']} WHERE {$params['where']} {$group} {$order}";
		if(!empty($params['debug'])){
			die($sql);
		}

		$result=$this->db->query($sql)->fetch();
		if(empty($result)){
			$record = false;
		}else{
			//$record = mysql_fetch_array($result);
			$record = $result[0];
		} 
		return $record;
	}
	
	/**
	 * 功能：获取单行数据
	 * 参数：配置数组
	 *  $params = array('filed'=>array(),'table','where')
	 */
	public function fetchRow($params = array())
	 {
	 	$fields = join(',',$params['field']);
		$order  = '';
		if(empty($params['where'])){
			$params['where'] = '1=1';
		}
		if(!empty($params['order'])){
			$order  = "ORDER BY {$params['order']}";
		}
		if(empty($params['group'])){
			$group = '';
		}else{
			$group = "GROUP BY {$params['group']}";
		}
		$sql    = "SELECT {$fields} FROM {$params['table']} WHERE {$params['where']} {$group} {$order}";
		if(!empty($params['debug'])){
			die($sql);
		}

		$result=$this->db->query($sql)->fetch();
		if(empty($result)){
			return array();
		}else{
			return $result; 
		}
	 }
	 
	 
	  /**
	 * 功能：更新数据
	 * 参数：配置数组
	 *  $params = array('data','table')
	 */
	 public function update($params = array())
	 {
	 	$set = '';
		foreach($params['data'] as $key=>$val){
			$set .= $key.'="'.$val.'",';
		}
		$set = trim($set,',');
		$sql = "UPDATE {$params['table']} SET {$set} WHERE {$params['where']}";
		if(!empty($params['debug'])){
			die($sql);
		}
		return $this->db->exec($sql);
	 }
	 
	 
	 /**
	 * 功能：插入数据
	 * 参数：配置数组
	 *  $params = array('data','table')
	 */
	 public function insert($params = array())
	 {
	 	$fields = '';
		$values = '';
		foreach($params['data'] as $key=>$val){
			$fields .= $key.',';
			$values  .= '"'.$val.'",';
		}
		$fieldes = '('.trim($fields,',').')';
		$values  = '('.trim($values,',').')';
		$sql = "INSERT INTO {$params['table']}{$fieldes} VALUES{$values}";

		if(!empty($params['debug'])){
			die($sql);
		}
		return $this->db->exec($sql);
	 }
	 
	 /**
	 * 功能：删除数据
	 * 参数：配置数组
	 *  $params = array('table','where')
	 */
	 public function delete($params = array())
	 {
		$sql = "DELETE FROM {$params['table']} WHERE {$params['where']}";
		if(!empty($params['debug'])){
			die($sql);
		}
		return $this->db->exec($sql);
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
		if(!empty($params['page'])){
			$offset = ($params['page']-1)*$params['nums'];
			$limit  = "LIMIT {$offset},{$params['nums']}";
		}
		$sql    = "SELECT {$params['field']} FROM {$params['table']} WHERE {$params['where']} {$order} {$limit}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result=$this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(empty($result)){
			return array();
		}else{
			$rsArray = array();
			foreach($result as $rs){
				$rsArray[] = $rs["{$params['field']}"];
			}
			return $rsArray; 
		}
	 }
	 
	  /**
	 * 功能：查询语句
	 *  $sql = ''
	 */
	 public function query_ext($sql = '',$debug = 0)
	 {
		if($debug==1){
			die($sql);
		}
		$result = $this->db->query($sql)->fetch();
		if(empty($result)){
			return array();
		}else{
			//生成数组
		$record = $result;
			}
			return $record;
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
		if(!empty($params['page'])){
			$offset = ($params['page']-1)*$params['nums'];
			$limit  = "LIMIT {$offset},{$params['nums']}";
		}
		if(empty($params['group'])){
			$group = '';
		}else{
			$group = " GROUP BY {$params['group']}";
		}
		$sql    = "SELECT {$fields} FROM {$params['table']} WHERE {$params['where']} {$group} {$order} {$limit}";
		//print_r($sql.'<br>');
		
		if(!empty($params['debug'])){
			     die($sql);
		}
		
		$results=$this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(empty($results)){
			return array();
		}else{
			//生成数组
			//$rsArray = array();
			//foreach($results as $result){
			//	return $result;
			//	$rsArray[] = $results;
			//}
			//while($record = mysql_fetch_assoc($result)){
			//	$rsArray[] = $record;
			//}
			return $results; 
		}
		
	 }
 }