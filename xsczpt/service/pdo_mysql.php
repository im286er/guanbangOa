<?php 
/* pdo mysql���ݿ����Ӳ���
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
	 /*�ַ���ת��*/
	 public function quote($str){
		return $this->db->quote($str);
	 }
	 /*����insert/update/delete ����Ӱ������*/
	 public function exec($sql){
		return $this->db->exec($sql);
	 }
	 /*���insert������*/
	 public function lastInsertId(){
		return $this->db->lastInsertId();
	 }
	 /*��ѯ��*/
	 public function query($sql){
		return $this->db->query($sql);
	 }
	/*Ԥ����*/
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