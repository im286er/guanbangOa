<?php
/**
 * Db Class
 * @package    Db
 * @author  Adrian·Tian
 * @date    2009-07-06
 */
include(dirname(__FILE__).'/../config.php'); 
class Db
{
	//配置变量
	protected $host     = '';
	protected $port     = '';
	protected $user     = '';
	protected $pass     = '';
	protected $dbname   = '';
	protected $newLink  = true;
	protected $link     = null;
	protected $charset  = '';
	
	/**
	 * 功能：构造函数
	 * 参数：配置数组
	 *  $params = array('host','port','user','pass','dbname','new_link')
	 */
	 public function __construct($params = array())
	 {
	 	global $dbConfig;
		if(empty($params)){
			//获取配置信息
			$params = $dbConfig;
		}
		$this->host    = empty($params['host'])?'':$params['host'];
		$this->port    = empty($params['port'])?'':$params['port'];
		$this->user    = empty($params['user'])?'':$params['user'];
		$this->pass    = empty($params['pass'])?'':$params['pass'];
		$this->dbname  = empty($params['dbname'])?'':$params['dbname'];	
		$this->newLink = empty($params['new_link'])?true:$params['new_link'];	
		$this->charset = empty($params['charset'])?'utf8':$params['charset'];
		$this->link    = mysql_connect($this->host.':'.$this->port,$this->user,$this->pass,$this->newLink);
		
		if(!$this->link){
			die('数据库连接失败：'.mysql_error($this->link));
		}else{
			mysql_select_db($this->dbname,$this->link) or die('数据库选择失败：'.mysql_error($this->link));
			mysql_query("SET NAMES {$this->charset}",$this->link);
		}
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
		
		$sql    = "SELECT {$params['field']} FROM {$params['table']} WHERE {$params['where']} {$group} {$order}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = mysql_query($sql,$this->link);
		if(empty($result)){
			$record = false;
		}else{
			$record = mysql_fetch_array($result);
			$record = $record[0];
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
		$result = mysql_query($sql,$this->link);
		if(empty($result)){
			return array();
		}else{
			return mysql_fetch_assoc($result); 
		}
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
		
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = mysql_query($sql,$this->link);
		if(empty($result)){
			return array();
		}else{
			//生成数组
			$rsArray = array();
			while($record = mysql_fetch_assoc($result)){
				$rsArray[] = $record;
			}
			return $rsArray; 
		}
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
		$result = mysql_query($sql,$this->link);
		if(empty($result)){
			return array();
		}else{
			//生成数组
			$rsArray = array();
			while($record = mysql_fetch_assoc($result)){
				$rsArray[] = $record["{$params['field']}"];
			}
			return $rsArray; 
		}
	 }
	 
	 /**
	 * 功能：查询语句
	 *  $sql = ''
	 */
	 public function query($sql = '',$debug = 0)
	 {
		if($debug==1){
			die($sql);
		}
		$result = mysql_query($sql,$this->link);
		if(empty($result)){
			return array();
		}else{
			//生成数组
			$rsArray = array();
			while($record = mysql_fetch_assoc($result)){
				$rsArray[] = $record;
			}
			return $rsArray; 
		}
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
			$values  .= '"'.mysql_escape_string($val).'",';
		}
		$fieldes = '('.trim($fields,',').')';
		$values  = '('.trim($values,',').')';
		$sql = "INSERT INTO {$params['table']}{$fieldes} VALUES{$values}";
		if(!empty($params['debug'])){
			die($sql);
		}
		$result = mysql_query($sql,$this->link);
		if(empty($result)){
			return 0;
		}else{
			return mysql_insert_id();
		}
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
		return mysql_query($sql,$this->link);
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
			$set .= $key.'="'.mysql_escape_string($val).'",';
		}
		$set = trim($set,',');
		$sql = "UPDATE {$params['table']} SET {$set} WHERE {$params['where']}";
		if(!empty($params['debug'])){
			die($sql);
		}
		return mysql_query($sql,$this->link);
	 }
	 
	 /**
	 * 功能：执行SQL语句
	 *  $sql = ''
	 */
	 public function execSQL($sql = '',$debug = 0)
	 {
		if($debug==1){
			die($sql);
		}
		return mysql_query($sql,$this->link);
	 }
	 
	/**
	 * 功能：关闭数据库连接
	 */ 
	 public function close()
	 {
	 	mysql_close($this->link);
	 }	
	 
	 //析构函数
	 public function __destruct(){}		 	
}
?>