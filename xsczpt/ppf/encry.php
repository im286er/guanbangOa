<?php 
class Mcrypt{ 
	/*
$key="LoveLTT";
$mc=new  Mcrypt();
$s=$mc->_encrypt("server=yi-feng.vicp.net;port=3306;database=vchat;charset=utf8;user id=root;password=loveltt;",$key);
echo $mc->_decrypt("﻿twPlwkemgen2nEhmCqh/IOAveLZ1ccTUsnKxhg1c5PyQfE3xfsXgrnVCSyv1Hh6L5+ikNvJDMjS9mNiNUt1e/N1pFcyV2sNCOlcLZTAYoMsdF77K/QyFwDqfnkG/0zlz",$key);
	*/
	/** 
	 * 解密 
	 *  
	 * @param string $encryptedText 已加密字符串 
	 * @param string $key  密钥 
	 * @return string 
	 */ 
	public static function _decrypt($encryptedText,$key = null){ 
		$key = $key === null ? Config::get('secret_key') : $key; 
		$cryptText = base64_decode($encryptedText); 
		$ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND); 
		$decryptText = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $cryptText, MCRYPT_MODE_ECB, $iv); 
		return trim($decryptText); 
	}
	/** 
	 * 加密 
	 * 
	 * @param string $plainText 未加密字符串  
	 * @param string $key        密钥 
	 */ 
	public static function _encrypt($plainText,$key = null){ 
		$key = $key === null ? Config::get('secret_key') : $key; 
		$ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); 
		$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND); 
		$encryptText = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plainText, MCRYPT_MODE_ECB, $iv); 
		return trim(base64_encode($encryptText)); 
	} 
} 
?>