/**	function artBySubject(obj,subjectid) {
		$("#lstArtsSubject li[class='on']").removeAttr("class");
		$(obj).parent().attr("class", "on");
		if(subjectid == null) subjectid="";
		$.post("./srv/rdo.php?", {tpl:"chg_art_by_subject", ntid:{noticeId}, sjt:subjectid}, function (d, e) {
			$("#lstArts").html(d);
		});
	}
**/
	function fillGrade(obj) {
		var period = $(obj).val();
		if(period == "") {
			$("#sltGrade").html("<option value=''>年级</option>");
			return;
		}
		$.post("./srv/rdo.php?", {tpl:"fill_grade", p:period}, function (d, e) {
			$("#sltGrade").html(d);
		});
	}

	function resByGrade(obj) {
		var subjectid = $("#lstResSubject li[class='on'] a").attr("data-subject");
		$.post("./srv/rdo.php?", {tpl:"chg_res_by_subject", sjt:subjectid, gra:$(obj).val()}, function (d, e) {
			$("#lstRes").html(d);
		});
	}

	function resBySubject(obj,subjectid) {
		$("#lstResSubject li[class='on']").removeAttr("class");
		$(obj).parent().attr("class", "on");
		if(subjectid == null) subjectid="";
		$.post("./srv/rdo.php?", {tpl:"chg_res_by_subject", sjt:subjectid, gra:$("#sltGrade").val()}, function (d, e) {
			$("#lstRes").html(d);
		});
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
//回到顶部显示
$(window).scroll(function(){  //只要窗口滚动,就触发下面代码 
	var scrollt = document.documentElement.scrollTop + document.body.scrollTop; //获取滚动后的高度 
	if(scrollt >200){  //判断滚动后高度超过200px,就显示  
	$(".totop").fadeIn(); //淡出     
	}else{      
	$(".totop").fadeOut(); //如果返回或者没有超过,就淡入.必须加上stop()停止之前动画,否则会出现闪动   
	}
});