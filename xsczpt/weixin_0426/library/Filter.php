<?php
/**
 * Filter Class
 * @package    Filter
 * @author  Adrian·Tian
 * @date    2009-07-08
 */
 
class Filter
{
	//构造函数
	public function __construct($params = array()){}
	
	//过滤掉html和php标签
	public static function filter_html($string,$allow='')
	{
		return strip_tags($string,$allow);
	}
	
	//过滤掉非数字
	public static function filter_number($num,$flag=1)
	{
		if($flag==0){
			return intval($num);	
		}
		if($flag==1){
			return abs(intval($num));	
		}
		if($flag==-1){
			return (0 - abs(intval($num)));	
		}
	}
	
	//过滤掉单引号，双引号
	public static function safe_string($string)
	{
		return mysql_escape_string($string);
	}
	
	//计算身份证校验码，根据国家标准GB 11643-1999  
	public static function idcard_verify_number($idcard_base)
	{  
		if(strlen($idcard_base)!=17){
			return false;
		}
		//加权因子  
		$factor = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
		//校验码对应值  
		$verify_number_list = array('1','0','X','9','8','7','6','5','4','3','2');

		$checksum = 0;  
		for($i=0;$i<strlen($idcard_base);$i++){  
			$checksum += substr($idcard_base,$i,1)*$factor[$i];  
		}  
		$mod = $checksum%11;  
		$verify_number = $verify_number_list[$mod];  
		return $verify_number;  
	}  
	
	//18位身份证校验码有效性检查  
	public static function idcard_checksum18($idcard){  
		if(strlen($idcard)!=18){
			return false;
		}  
		$idcard_base = substr($idcard,0,17);  
		if(Filter::idcard_verify_number($idcard_base)!=strtoupper(substr($idcard,17,1))){  
			return false;  
		}else{  
			return true;  
		}  
	}
	
	//析构函数
	public function __destruct(){}		 	
}
?>