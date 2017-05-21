//防止my页面被iframe  
function chkSelfTop(){
 if(self != top){
        top.location = self.location;
    }
}    
function msg(s){//need layer.js 
  layer.msg(s);
}
function lalert(s){
    layer.msg(s);    
}
function ralert(s){
    layer.alert(s);     
}
function lopen(id){     
  layer.open({type:1,title: $(id).attr("title"),shadeClose:true,shade: 0.1,area: [$(id).attr("width"),$(id).attr("height")],content:$(id)});
}
function showErr(e) {
    alert(e);
}
function isDate(d) { return d.constructor == Date; }
function isNull(n) { return n == null; }
function isNulls(n) {if(n == null||n=="null")return "";else return n; }  
//function isUndef(n) { return o == undefined; }
function isEmpty(n) { return n ==null||n==""; }
function isNumeric(i) { return typeof (i) == "number"; }
function isObject(o) { return typeof (o) == "object"; }

/*只允许输入数字*/

function queryStr(name) {
    var AllVars = window.location.search.substring(1);
    var Vars = AllVars.split("&");
    for (i = 0; i < Vars.length; i++) {
        var Var = Vars[i].split("=");
        if (Var[0] == name)return Var[1];
    }
    return "";
}
function queryStr1(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}
String.prototype.replaceAll = function (s1, s2) {
    return this.replace(new RegExp(s1, "gm"), s2);
}
function replaceAll(str, s1, s2) {
    return str.replace(new RegExp(s1, "gm"), s2);
}
function getTrueFalse(v) {
    if (v == "0" || v == 0) {
        return "否";
    }
    else {
        return "是";
    }
}
function getlocaltime(){
     var uts = new Date();
     return uts.getFullYear() +'-' + (uts.getMonth() + 1) + '-' + uts.getDate() + " " + uts.getHours() + ":" + uts.getMinutes() ;
}
/*unix时间 time stemp*/
function unixtime() {
    //var dt = new Date();
    //var ux = (Date().UTC(dt.getFullYear(), dt.getMonth(), dt.getDay(), dt.getHours(), dt.getMinutes(), dt.getSeconds())) / 1000;
    timestamp=Math.round(new Date().getTime()/1000) ;
    return timestamp;      
}

function utctime(unixtamp) {
    var uts = new Date(unixtamp * 1000);
    //return unixTimestamp.toLocaleString();
     return uts.getFullYear() +'-' + (uts.getMonth() + 1) + '-' + uts.getDate() + " " + uts.getHours() + ":" + uts.getMinutes() ;
}
function show_TS(ts){
  document.write(utctime(ts));
}
/*字符串转为指定格式的字符串*/
function str2Date(timestr, split) {
    var d = new Date(timestr.replaceAll("-", "/"));
    return d.getFullYear() + split + (d.getMonth() + 1) + split + d.getDate();
}
/**/
function str2DateTime(timestr, split) {
    var d = new Date(timestr.replaceAll("-", "/"));
    return d.getFullYear() + split + (d.getMonth() + 1) + split + d.getDate() + " " + d.getHours() + ":" + d.getMinutes();
}
/**utctolocal*/
function utc2local(utc){
  d= new Date(utc);
 return (new Date(d.getTime())).toLocaleString();  
}
/**return stand date time*/
function date2stand(s){
  dt= new Date(s);
  //dt=new Date(d.getTime());  
  return dt.getFullYear()+"/"+ dt.getMonth()+"/"+ dt.getDay()+" "+ dt.getHours()+":"+dt.getMinutes()+":"+dt.getSeconds();  
}
/**json to str*/ 
function json2Str(obj) {
    var THIS = this;
    switch (typeof (obj)) {
        case 'string':
            return '"' + obj.replace(/(["\\])/g, '\\$1') + '"';
        case 'array':
            return '[' + obj.map(THIS.json2Str).join(',') + ']';
        case 'object':
            if (obj instanceof Array) {
                var strArr = [];
                var len = obj.length;
                for (var i = 0; i < len; i++) {
                    strArr.push(THIS.json2Str(obj[i]));
                }
                return '[' + strArr.join(',') + ']';
            } else if (obj == null) {
                return 'null';

            } else {
                var string = [];
                for (var property in obj) string.push(THIS.json2Str(property) + ':' + THIS.json2Str(obj[property]));
                return '{' + string.join(',') + '}';
            }
        case 'number':
            return obj;
        case false:
            return obj;
    }
}
/**str转JSON*/
function str2JSON(obj) {
    return eval_r('(' + obj + ')');
}
function trim(str) { return $.trim(str); }
//str.replace(/(^\s*)|(\s*$)/g, ""); 
function show_SZ(v){
  document.write(formatSize(v));  
}
function formatSize(v){           
  if(v>1048576000)
      return (v/1048576000).toFixed(2)+"GB";
    if(v>1048576)
      return (v/1048576).toFixed(2)+"MB";
    if(v>1024)
      return (v/1024).toFixed(2)+"KB";
    else
     return v+"B";  
}
function imgerror(ctrid){
  $(ctrid+" img").bind("error",function(){   
    this.src="/error/none.jpg";   
  }); 
  //$("img").error(function(){$(this).attr("src","/error/pic.jpg")});
}
function clearHTML(s){
  return s.replace(/<[^>]*>/g,"");
}
function clearQuote(s){
  return s.replace(/'/g,"&#39;").replace(/"/g,"$#34;"); 
}
function clearQuotes(s){
  return s.replace(/['"]/g,""); 
}
function toJson(s){
  return eval('['+s+']');
}
function chk_ie(){
  s=navigator.userAgent.toLowerCase();   
  if(s.indexOf('firefox')>0)return "FireFox";
  if(s.indexOf('opera')>0)return "Opera"; 
  if(s.indexOf('chrome')>0)return "Chrome";  
  if(s.indexOf('safari')>0)return "Safari"; 
  if(s.indexOf('msie')>0)return "IE";
  return "IE";
}

function ie_ver(){      
	var userAgent = navigator.userAgent.toLowerCase();   
	if(userAgent.match(/msie ([\d.]+)/)!=null){
		//ie6--ie9                
		uaMatch = userAgent.match(/msie ([\d.]+)/);                
		return uaMatch[1];        
	}else if(userAgent.match(/(trident)\/([\w.]+)/)){    
		uaMatch = userAgent.match(/trident\/([\w.]+)/);    
		switch (uaMatch[1]){ 
			case "4.0": return "8" ;break;
			case "5.0": return "9" ;break;
			case "6.0": return "10";break;
			case "7.0": return "11";break;
			default:return "noie" ;
		}  
	}       
	return "noie";  
}
