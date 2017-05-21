<?php
function p($res){
	echo "<pre>";
	print_r($res);
	echo "</pre>";
}
function chkLogin($name='uid',$url='./?t=login'){
	if(!isset($_SESSION[$name])){
		header("location: ".$url."&url=".url_base64_encode($_SERVER["REQUEST_URI"]));	
		exit;
	}
}
function chkLoginNoJump($field="uid"){
	if(!isset($_SESSION[$field])){
		echo "δ��¼";exit;
	}
}
/*********************************************
�ַ�������
**********************************************/
/**����ַ�����ͷ��ͬ*/
function startWith($a, $b) {
	return strpos($a, $b) == 0;
}
/**���Դ�д*/
function startWithi($a, $b) {
	return stripos($a, $b) == 0;
}
/**����ַ�����β��ͬ */
function endWith($a, $b) { 
	return ($pos = strpos($a, $b)) !== false && $pos == strlen($a) - strlen($b);
}
/**���Դ�Сд */
function endWithi($a, $b) {
	return ($pos = stripos($a, $b)) !== false && $pos == strlen($a) - strlen($b);
}

function url_base64_encode($string) {
 $data = base64_encode($string);
 return str_replace(array('+','/','='),array('-','_',''),$data);
}
function url_base64_decode($string) {
   $data = str_replace(array('-','_'),array('+','/'),$string);
   $mod4 = strlen($data) % 4;
   if ($mod4) {
       $data .= substr('====', $mod4);
   }
   return base64_decode($data);
}
 /**ѹ����ҳ�ڿ�*/
function compressHtml($str) { 
	return preg_replace(array('/>\s+?</mis','/\r\n\s*/mis','/\s*\/>/mis'),
		array('><','','/>'),
		$str);   
}
#ѹ��html  
function compress_html($string) {  
	$string = str_replace(array("\r\n", "\n","\t"), '', $string);  
	$pattern = array ("/> *([^ ]*) *</", //ȥ��ע�ͱ��  
	"/[\s]+/",  //����ո�
	"/<!--[\\w\\W\r\\n]*?-->/",  //�س�����
	"/\" >/", //��ǩ�����пո�
	"/  \"/",  //����ǰ�пո�
	"'/\*[^*]*\*/'" //
	);  
	$replace = array(">\\1<"," ", "", "\">"," \"",  "");  
	return preg_replace($pattern, $replace, $string);  
} 

/*********************************************
cache ����
**********************************************/
function cache_get($dir){	 
    $fname=md5( $_SERVER['PHP_SELF'].$_SERVER["QUERY_STRING"]);
    if(file_exists($dir.$fname)){
        if((time()-filemtime($dir.$fname))<CACHE_TIME){#����ʾʱ����
            echo file_get_contents($dir.$fname);
            exit;
        }
    }	
}

function cache_set($dir,$html){  
    $fname=md5($_SERVER['PHP_SELF'].$_SERVER["QUERY_STRING"]);	
    file_put_contents($dir.$fname,$html);			
}

/*********************************************
��ҳ����
**********************************************/ 
function getPageHtml($rcount,$psize,$cur,$param){
     $pgcount = ceil($rcount / $psize);
     $sid = $cur - 5; #�����С  10�� / 2 
      if ($sid < 1) $sid = 1;
      $eid = $sid + 5;//����id
      if ($eid > $pgcount) $eid = $pgcount;	          

       $page="��".$rcount."����¼ ".$cur."/". $pgcount."ҳ ";
        $page.="<a href=\"?p=1". $param."\">��ҳ</a>";
        $page.="<a href=\"?p=".( $cur - 1 > 0 ?  $cur - 1 : 1).$param."\">��һҳ</a>";

        for ($i = $sid; $i <= $eid; $i++) {
            if ($i ==  $cur)
                $page.="<big>".$i."</big>";
            else
                $page.="<a href=\"?p=".$i.$param."\">".$i."</a>";
        }
        $page.="<a href=\"?p=".($cur + 1 < $pgcount ? $cur + 1 : $pgcount).$param."\">��һҳ</a>";
        $page.="<a href=\"?p=".$pgcount.$param."\">βҳ</a>";
        return $page;
}
/**��ȡbootstrap ҳ��*/
function getPageHtml_bt($rcount,$psize,$cur,$param){
     $pgcount = ceil($rcount / $psize);
     $sid = $cur - 5; #�����С  10�� / 2 
      if ($sid < 1) $sid = 1;
      $eid = $sid + 5;//����id
      if ($eid > $pgcount) $eid = $pgcount;
        $page="<li><a>".$rcount."��</a><a>ҳ:".$cur."/". $pgcount."</a></li>";
        $page.="<li><a href=\"?p=1". $param."\">|&laquo;</a></li>";
        $page.="<li><a href=\"?p=".( $cur - 1 > 0 ?  $cur - 1 : 1).$param."\">&laquo;</a></li>";

        for ($i = $sid; $i <= $eid; $i++) {
            if ($i ==  $cur)
                $page.="<li><a>".$i."</a></li>";
            else
                $page.="<li><a href=\"?p=".$i.$param."\">".$i."</a></li>";
        }
        $page.="<li><a href=\"?p=".($cur + 1 < $pgcount ? $cur + 1 : $pgcount).$param."\">&raquo;</a></li>";
        $page.="<li><a href=\"?p=".$pgcount.$param."\">&raquo;|</a></li>";
        return $page;       
}

//��ȡ�û�ip
function getIP()
{
    static $realip;
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}