<?php
function getCache(){
	if(CACHE_PAGE){
		$fname=md5( $_SERVER['PHP_SELF'].$_SERVER["QUERY_STRING"]);
		if(file_exists(CACHE_DIR.$fname)){
			if((time()-filemtime(CACHE_DIR.$fname))<CACHE_TIME){#在显示时间内
				echo file_get_contents(CACHE_DIR.$fname);
				exit;
			}
		}
	}
}

function putCache($html){ 
	if(CACHE_PAGE){
		$fname=md5($_SERVER['PHP_SELF'].$_SERVER["QUERY_STRING"]);	
		file_put_contents(CACHE_DIR.$fname,$html);		
	}	
}