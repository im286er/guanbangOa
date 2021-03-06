/*首页图片点击显示与隐藏js*/
function nTabs(thisObj,Num){
    if(thisObj.className == "active")return;
    var tabObj = thisObj.parentNode.id;
    var tabList = document.getElementById(tabObj).getElementsByTagName("li");
    for(i=0; i <tabList.length; i++)
    {
        if (i == Num)
        {
            thisObj.className = "active";
            document.getElementById(tabObj+"_Content"+i).style.display = "block";
        }else{
            tabList[i].className = "normal";
            document.getElementById(tabObj+"_Content"+i).style.display = "none";
        }
    }
}
var show_king_id = 1;
function show_king_list(e,k)
{
    if(show_king_id == k) return true;
        o = document.getElementById("a"+show_king_id);
        o.className = "bg";
    e.className = " ";
    show_king_id = k;
}
var show_kinga_id = 1;
function show_kinga_list(f,l)
{
    if(show_kinga_id == l) return true;
        o = document.getElementById("b"+show_kinga_id);
        o.className = "bg";
    f.className = " ";
    show_kinga_id = l;
}
/*图片点击显示与隐藏结束*/
function Cookie(key,value)
{
    this.key=key;
    if(value!=null)
    {
        this.value=escape(value);
    }
    this.expiresTime=null;
    this.domain=null;
    this.path="/";
    this.secure=null;
}
Cookie.prototype.setValue=function(value){this.value=escape(value);}
Cookie.prototype.getValue=function(){return unescape(this.value);}

Cookie.prototype.setExpiresTime=function(time){this.expiresTime=time;}
Cookie.prototype.getExpiresTime=function(){return this.expiresTime;}

Cookie.prototype.setDomain=function(domain){this.domain=domain;}
Cookie.prototype.getDomain=function(){return this.domain;}

Cookie.prototype.setPath=function(path){this.path=path;}
Cookie.prototype.getPath=function(){return this.path;}

Cookie.prototype.Write=function(v)
{
    if(v!=null)
    {
        this.setValue(v);
    }
    var ck=this.key+"="+this.value;
    if(this.expiresTime!=null)
    {
        try
        {
            ck+=";expires="+this.expiresTime.toUTCString();;
        }
        catch(err)
        {
    //        alert("expiresTime参数错误");
        }
    }
    if(this.domain!=null)
    {
        ck+=";domain="+this.domain;
    }
    if(this.path!=null)
    {
        ck+=";path="+this.path;
    }
    if(this.secure!=null)
    {
        ck+=";secure";
    }
    document.cookie=ck;
}
Cookie.prototype.Read=function()
{
    try
    {
        var cks=document.cookie.split("; ");
        var i=0;
        for(i=0;i<cks.length;i++)
        {
            var ck=cks[i];
            var fields=ck.split("=");
            if(fields[0]==this.key)
            {
                this.value=fields[1];
                return unescape(this.value);
            }
        }
        return null;
    }
    catch(err)
    {
      //  alert("cookie读取错误");
        return null;
    }
}
/**
* JavaScript脚本实现回到页面顶部示例
* @param acceleration 速度
* @param stime 时间间隔 (毫秒)
**/
function gotoTop(acceleration,stime) {
   acceleration = acceleration || 0.1;
   stime = stime || 10;
   var x1 = 0;
   var y1 = 0;
   var x2 = 0;
   var y2 = 0;
   var x3 = 0;
   var y3 = 0; 
   if (document.documentElement) {
       x1 = document.documentElement.scrollLeft || 0;
       y1 = document.documentElement.scrollTop || 0;
   }
   if (document.body) {
       x2 = document.body.scrollLeft || 0;
       y2 = document.body.scrollTop || 0;
   }
   var x3 = window.scrollX || 0;
   var y3 = window.scrollY || 0;
 
   // 滚动条到页面顶部的水平距离
   var x = Math.max(x1, Math.max(x2, x3));
   // 滚动条到页面顶部的垂直距离
   var y = Math.max(y1, Math.max(y2, y3));
 
   // 滚动距离 = 目前距离 / 速度, 因为距离原来越小, 速度是大于 1 的数, 所以滚动距离会越来越小
   var speeding = 1 + acceleration;
   window.scrollTo(Math.floor(x / speeding), Math.floor(y / speeding));
 
   // 如果距离不为零, 继续调用函数
   if(x > 0 || y > 0) {
       var run = "gotoTop(" + acceleration + ", " + stime + ")";
       window.setTimeout(run, stime);
   }
}
//计算时间差
function countTimeLength(interval, date1, date2) {  
    var objInterval = {'D' : 1000 * 60 * 60 * 24, 'H' : 1000 * 60 * 60, 'M' : 1000 * 60, 'S' : 1000, 'T' : 1};  
    interval = interval.toUpperCase();  
    var dt1 = Date.parse(date1.replace(/-/g, "/"));  
    var dt2 = Date.parse(date2.replace(/-/g, "/"));  
    try{  
        return ((dt2 - dt1) / objInterval[interval]).toFixed(2);//保留两位小数点  
    }catch (e){  
        return e.message;  
    }  
}  


function getTimer(stringTime){
	var minute = 1000 * 60;
	var hour = minute *60;
	var day = hour *24;
	var week = day * 7;
	var month = day * 30;
    var time1 = new Date().getTime();//当前的时间戳
    var time2 = Date.parse(new Date(stringTime));//指定时间的时间戳
    var time = time1 - time2;

    var result = null;
    if(time < 0){
        alert("设置的时间不能早于当前时间！");
    }else if(time/month >= 1){
        result = parseInt(time/month) + "月前";
    }else if(time/week >= 1){
        result = parseInt(time/week) + "周前";
    }else if(time/day >= 1){
        result = parseInt(time/day) + "天前";
    }else if(time/hour >= 1){
        result = parseInt(time/hour) + "小时前";
    }else if(time/minute >= 1){
        result = parseInt(time/minute) + "分钟前";
    }else {
        result = "刚刚";
    } 
    return result;
}
