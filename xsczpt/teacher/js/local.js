//投票new
function vote(d,o){ //tpl,do,showok,callback,id,tb,col
  d.tbl="vote";
  if(Cookies.get(d.tbl+d.do+d.id)!=undefined)return layer.msg("这条评过了!");//已投
  Cookies.set(d.tbl+d.do+d.id,d.id,{ expires: 43200 });   
  var obj=o;
  $.post("./srv/vote.php",d, function (data, e) {                 
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	
      if(obj!=undefined){  
         v=$(obj).text().replace(/[^0-9]/ig, "");
         if(v=="")v="1";else v= parseInt(v)+1;            
         $(obj).text("["+v+"]");
      }    
	    if(d.callback!=undefined&&d.callback!=""){
			window[d.callback](data);
		};
		switch(d.col){
			case "up":
				layer.msg("顶一下!");
				break;
			case "bad":
				layer.msg("踩一下!");
				break;
		}
  });
}

function see(d,obj){ //tpl,do,showok,callback,id,tb,col 
  d.tbl="vote";
  if(Cookies.get(d.tbl+d.do+d.id)!=undefined) return;//已投
  Cookies.set(d.tbl+d.do+d.id,d.id,{ expires: 43200 });   
  
  $.post("./srv/vote.php",d, function (data, e) {                 
	
  });
}

function creatUe(uid,rid,obj){
	$(obj).parent().append("<textarea   class='form-control' rows='3'></textarea>");
	var ue = UE.getEditor(obj);
	ue.ready(function() {
		ue.setContent('<p>hello!</p>');
	});
}

function comment(id,tname,rid,obj){
  if(isopen=="0"){layer.msg("非本组成员无法发表内容！");return;};
  $("[name='rid']").val(rid);
  $("[name='wid']").val(id);
  $("[name='tname']").val(tname);
  $('#dlg1').modal('show');
}   
function comment2(id,tname,rid,obj){
  if(isopen=="0"){layer.msg("非本组成员无法发表内容！");return;};
  $("[name='rid']").val(rid);
 var wid=$(obj).parent().parent().siblings("p").children(":input").val();
 $("[name='wid']").val(wid);
  $("[name='tname']").val(tname);
  $('#dlg1').modal('show');
}   
function comment3(wid,rid,obj){
  if(isopen=="0"){layer.msg("非本组成员无法发表内容！");return;};
  $("[name='rid']").val(rid);
  $("[name='wid']").val(wid);
  $('#dlg1').modal('show');
}   
function savcomment(tpl){
  var des=$("#form2 #des").val();
  des=delHtmlTag(des);
  if(des.length<1){layer.msg("内容太短(1-250)");return;}    
  if(des.length>250){layer.msg("内容太长(1-250)");return;} 
  $("#form2 #des").val(des);
  $('#dlg1').modal('hide');
  SaveAM({tpl:tpl,'do':'a',frmid:'#form2',showok:'','callback':'dosome'}); 
} 

//去掉所有的html标记
 function delHtmlTag(str){
  return str.replace(/<[^>]+>/g,"");
 }
function gettime(){
   var myDate = new Date();
	var year=myDate.getFullYear();
	var month=myDate.getMonth();
	var day=myDate.getDate();
	var hours=myDate.getHours();
	var minutes=myDate.getMinutes();
	return year+"-"+month+"-"+day+" "+hours+":"+minutes;
}
//获取url参数
function GetRequest() { 
var url = location.search; //获取url中"?"符后的字串 
var theRequest = new Object(); 
if (url.indexOf("?") != -1) { 
var str = url.substr(1); 
strs = str.split("&"); 
for(var i = 0; i < strs.length; i ++) { 
theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]); 
} 
} 
return theRequest; 
} 

//科目
function getSub(num){
	var sub=null;
	$.ajaxSettings.async = false;
	$.getJSON("/data/subject.json.txt",  function (d,e) {
		sub=d[num];
	}); 
	return sub;
};
//学校
function getSch(num){
	var sch=null;
	$.ajaxSettings.async = false;
	$.getJSON("/data/school.json.txt",  function (d,e) {
		sch=d[num];
	}); 
	return sch;
};
//回到顶部显示
$(window).scroll(function(){  //只要窗口滚动,就触发下面代码 
	var scrollt = document.documentElement.scrollTop + document.body.scrollTop; //获取滚动后的高度 
	if(scrollt >200){  //判断滚动后高度超过200px,就显示  
	$(".totop").fadeIn(); //淡出     
	}else{      
	$(".totop").fadeOut(); //如果返回或者没有超过,就淡入.必须加上stop()停止之前动画,否则会出现闪动   
	}
});