<?php 
/* pdo mysql数据库连接操作
*/

class pdo_mysql{	
	public $db;
	//http://www.php.net/manual/zh/pdo.drivers.php
	//dsn: "mysql:host=localhost;port=3306;dbname=test;charset=UTF8";	
	//dsn:sqlite:example.db
	public function __construct()	{
	try {	
		$this->db=new PDO("mysql:host=192.168.10.99;port=3306;dbname=topnt2016","root","lren.org");			 
	  }catch (PDOException $e) {	 
			die("Connect not open pdo_db:".$e->getMessage());	
	  }
	  $this->db->exec("SET NAMES UTF8");	
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
 }