<?php
include('../library/class.upload.php');
//上传处理
function upload($upFile,$w=0,$h=0,$dir='avatar'){
	$fileType = array('jpg','gif','png');//文件类型
	$maxSize  = 1024; //单位:KB
	//图片处理方式 1缩放 2裁剪
	$flag     = 2;
	$f = 0;
	$t = '未知错误,请联系管理员。';
	$image = '';
	if(!in_array(strtolower(substr($upFile['name'],-3,3)),$fileType)){
		$f = 1;
		$t = '只允许上传的图片格式：jpg, gif, png';
	}
	if(strpos($upFile['type'],'image')===false){
		$f = 1;
		$t = '只允许上传的图片格式：jpg, gif, png';
	}
	if($upFile['size']> $maxSize*10000){
		$f = 2;
		$t = '文件大于1M,请处理后再上传。';
	}
	if($upFile['error'] !=0){
		$f = 3;
		$t = '文件大于1M，处理后再上传。';
	}
	if($f==0){
		$path = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/'.$dir.'/';//图片存放路径
		$filepath = '/oa/uploadfiles/'.$dir.'/';//数据库中图片路径
		$file = $upFile;
		$handle = new upload($file);
			$name = substr(md5(microtime()),0,30);//图片名
			if($w==0){
				//上传处理
				$handle->image_resize         = false;
				$handle->image_ratio_crop     = false;
			}else{
				$width  = $w;
				$height = $h;
				if($flag==1){
					//缩放处理
					$handle->image_resize         = true;
					$handle->image_ratio_crop     = true;
					if($handle->image_src_x>=$handle->image_src_y){
						$handle->image_ratio_y = true;
						$handle->image_x       = $width;
					}else{
						$handle->image_ratio_x = true;
						$handle->image_y       = $height;
					}
				}
				if($flag==2){
					//裁剪处理
					$handle->image_resize         = true;
					$handle->image_ratio_crop     = true;
					$handle->image_x              = $width;
					$handle->image_y              = $height;
				}
			}
			$handle->file_new_name_body   = $name.'_t.';;
			$handle->Process($path);
			
			$image = $name.'_t.'.$handle->file_src_name_ext;
			
			$handle->clean();
			if(!empty($image)){
				$f = 4;
				$t = '文件上传成功。';
			}else{
				$f = 5;
				$t = '没有可以上传的文件';
			}
			$image = $filepath . $image;
	}
	$result = "{$f}|{$image}|{$t}";
	return $result;
}
	
//上传文件处理
function uploadFile($upFile,$dir='0'){
	$fileType = array('rar','zip','rtf','doc','xls','pdf','txt');//文件类型
	$maxSize=10240; //单位:KB
	$f = 0;
	$t = '未知错误,请联系管理员。';
	$file = '';
	/*if(!in_array(strtolower(substr($upFile['name'],-3,3)),$fileType)){
		$f = 1;
		$t = '只允许上传的文件格式：rar,zip,rtf,doc,xls,pdf';
	}*/

	if($upFile['size']> $maxSize*10000){
		$f = 2;
		$t = '文件大于10M,请处理后再上传。';
	}
	if($upFile['error'] !=0){
		$f = 3;
		$t = '文件大于10M，处理后再上传。';
	}
	if($f==0){
		$path     = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/attachment/'.$dir.'/';//文件存放路径
		$filepath = '/oa/uploadfiles/attachment/'.$dir.'/';//数据库中文件存放路径
		$file = $upFile;
		$handle = new upload($file);
		$name   = date('YmdHis').rand(1000,9999).substr(md5(microtime()),5,5);
		//上传处理
		$handle->file_new_name_body = $name;
		$handle->Process($path);
		$file = $name.'.'.$handle->file_src_name_ext;
		$handle->clean();
		if(!empty($file)){
			$f = 4;
			$t = '文件上传成功。';
		}else{
			$f = 5;
			$t = '没有可以上传的文件';
		}
		$file = $filepath . $file;
	}
	return  "{$f}|{$file}";
}
//上传pdf
function uploadpdf($upFile,$dir='0'){
	$fileType = array('pdf');//文件类型
	$maxSize=10240; //单位:KB
	$f = 0;
	$t = '未知错误,请联系管理员。';
	$file = '';
	if(!in_array(strtolower(substr($upFile['name'],-3,3)),$fileType)){
		$f = 1;
		$t = '只允许上传的文件格式：PDF';
	}

	 if($upFile['size']> $maxSize*10000){
	 	$f = 2;
	 	$t = '文件大于10M,请处理后再上传。';
	 }
	 if($upFile['error'] !=0){
	 	$f = 3;
	 	$t = '文件大于10M，处理后再上传。';
	 }
	if($f==0){
		$path     = $_SERVER['DOCUMENT_ROOT'].'/oa/uploadfiles/attachment/'.$dir.'/';//文件存放路径
		$filepath = '/oa/uploadfiles/attachment/'.$dir.'/';//数据库中文件存放路径
		$file = $upFile;
		$handle = new upload($file);
		$name   = date('YmdHis').rand(1000,9999).substr(md5(microtime()),5,5);
		//上传处理
		$handle->file_new_name_body = $name;
		$handle->Process($path);
		$file = $name.'.'.$handle->file_src_name_ext;
		$handle->clean();
		if(!empty($file)){
			$f = 4;
			$t = '文件上传成功。';
		}else{
			$f = 5;
			$t = '没有可以上传的文件';
		}
		$file = $filepath . $file;
	}
	return  "{$f}|{$file}|{$t}";
}
?>