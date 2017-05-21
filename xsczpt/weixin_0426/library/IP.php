<?php

/**
 * IP Class
 * @desc 获取真实IP及地理位置
 * @package    IP
 * @author  Adrian·Tian
 * @date    2013-04-01
 */
 
class IP
{
	/**
	 * 功能：构造函数
	 */
	 public function __construct($params = array()){}
	 
	/**
	 * 获取用户真实 IP
	 */
	public function getIP()
	{
		$realip = '';
		if(isset($_SERVER)){
			if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
				$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			}else if(isset($_SERVER["HTTP_CLIENT_IP"])){
				$realip = $_SERVER["HTTP_CLIENT_IP"];
			}else{
				$realip = $_SERVER["REMOTE_ADDR"];
			}
		}else{
			if(getenv("HTTP_X_FORWARDED_FOR")){
				$realip = getenv("HTTP_X_FORWARDED_FOR");
			}else if(getenv("HTTP_CLIENT_IP")){
				$realip = getenv("HTTP_CLIENT_IP");
			}else{
				$realip = getenv("REMOTE_ADDR");
			}
		}
		return $realip;
	}
	 
	 
	/**
	 * 获取 IP  地理位置
	 * 淘宝IP接口
	 * @Return: array
	 */
	public function getCity($ip)
	{
		$url = "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
		$ip  = json_decode(file_get_contents($url)); 
		if((string)$ip->code=='1'){
			return false;
		}
		$data = (array)$ip->data;
		return $data; 
	} 

	 //析构函数
	 public function __destruct(){}		 	
}

?>