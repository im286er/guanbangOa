/* 代码整理：懒人之家 www.lanrenzhijia.com */
$(function(){
	var total=$('.tab_pic ul li').length;
	$('.tab_pic ul li').eq(0).addClass('on');
	$('.tab_txt ul li').eq(0).addClass('on');
	$('.tab_pic ul').width(total*225);
	$('.tab_txt ul').width(total*413);
	$('.next').click(function(){
		var num=$('.tab_pic ul li').length;
	    var index=$('.tab_pic ul li.on').index('.tab_pic ul li');
		var on=index+1<num?index+1:0;
		var pwidth=parseInt(on*225);
		var twidth=parseInt(on*413);
		$('.tab_pic ul li').eq(on).addClass('on').siblings().removeClass('on');
		$('.tab_pic ul').stop().animate({left: -pwidth}, "slow");
		$('.tab_txt ul li').eq(on).addClass('on').siblings().removeClass('on');
		$('.tab_txt ul').stop().animate({left: -twidth}, "slow");
	});
	$('.prev').click(function(){
		var num=$('.tab_pic ul li').length;
	    var index=$('.tab_pic ul li.on').index('.tab_pic ul li');
		var on=index==0?num-1:index-1;
		var pwidth=parseInt(on*225);
		var twidth=parseInt(on*413);
		$('.tab_pic ul li').eq(on).addClass('on').siblings().removeClass('on');
		$('.tab_pic ul').stop().animate({left: -pwidth}, "slow");
		$('.tab_txt ul li').eq(on).addClass('on').siblings().removeClass('on');
		$('.tab_txt ul').stop().animate({left: -twidth}, "slow");
	});
	
})
/* second pic 切换 js */
$(function() {
	var sWidth = $("#focus").width(); 
	var len = $("#focus ul li").length; 
	var index = 0;
	var picTimer;
	
	var btn = "<div class='btnBg'></div><div class='btnr'>";
	for(var i=0; i < len; i++) {
		btn += "<span>" + (i+1) + "</span>";
	}
	btn += "</div>"
	$("#focus").append(btn);
	$("#focus .btnBg").css("opacity",0.5);
	

	$("#focus .btnr span").mouseenter(function() {
		index = $("#focus .btnr span").index(this);
		showPics(index);
	}).eq(0).trigger("mouseenter");

	$("#focus ul").css("width",sWidth * (len + 1));
	
	$("#focus ul li div").hover(function() {
		$(this).siblings().css("opacity",0.7);
	},function() {
		$("#focus ul li div").css("opacity",1);
	});
	
	$("#focus").hover(function() {
		clearInterval(picTimer);
	},function() {
		picTimer = setInterval(function() {
			if(index == len) { 
				showFirPic();
				index = 0;
			} else {
				showPics(index);
			}
			index++;
		},3000); 
	}).trigger("mouseleave");
	
	function showPics(index) { 
		var nowLeft = -index*sWidth; 
		$("#focus ul").stop(true,false).animate({"left":nowLeft},500); 
		$("#focus .btnr span").removeClass("on").eq(index).addClass("on")
	}
	
	function showFirPic() { 
		$("#focus ul").append($("#focus ul li:first").clone());
		var nowLeft = -len*sWidth;
		$("#focus ul").stop(true,false).animate({"left":nowLeft},500,function() {
			$("#focus ul").css("left","0");
			$("#focus ul li:last").remove();
		}); 
		$("#focus .btnr span").removeClass("on").eq(0).addClass("on");
	}
});
