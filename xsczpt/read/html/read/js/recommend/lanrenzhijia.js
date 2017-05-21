/* 代码整理：懒人之家 www.lanrenzhijia.com */
$(function() {
	var sWidth = $("#focus").width(); 
	var len = $("#focus ul li").length; 
	var index = 0;
	var picTimer;
	
	var btn = "<div class='btnBg'></div><div class='btn'>";
	for(var i=0; i < len; i++) {
		btn += "<span>" + (i+1) + "</span>";
	}
	btn += "</div>"
	$("#focus").append(btn);
	$("#focus .btnBg").css("opacity",0.5);
	

	$("#focus .btn span").mouseenter(function() {
		index = $("#focus .btn span").index(this);
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
		$("#focus .btn span").removeClass("on").eq(index).addClass("on")
	}
	
	function showFirPic() { 
		$("#focus ul").append($("#focus ul li:first").clone());
		var nowLeft = -len*sWidth;
		$("#focus ul").stop(true,false).animate({"left":nowLeft},500,function() {
			$("#focus ul").css("left","0");
			$("#focus ul li:last").remove();
		}); 
		$("#focus .btn span").removeClass("on").eq(0).addClass("on");
	}
});

/* 代码整理：懒人之家 www.lanrenzhijia.com */