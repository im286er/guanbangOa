/*
local 1.0
Copyright(c) 2015/7/15
本插件提供动画淡入淡出切换fadeIn
*/
(function($){
    $.fn.local=function(options){
	    var defaults={
		  //需要定义的函数
		  fade:1500,    //定义淡入淡出速度
		  time:3000,//时间间隔
		  dots:true //是否启用图片按钮
		};
		var options=$.extend(defaults,options);
		var t=parseInt(options.time),
		    f=parseInt(options.fade),
			d=options.dots,
			i=0,
			j=0,
			k,l,m,set;
			
			m=$(this).find("li");
			l=m.length;
			if(d){
				//如果启用按钮，则添加鼠标控制按钮
			    $(this).append("<div class='tips'></div>");
				for(j=0;j<l;j++){
				    $(this).find("div.tips").append("<li>"+(j+1)+"</li>");
				}
				$(this).find("div.tips li").eq(0).addClass("active");
			}
			//初始化
			m.hide().eq(0).css("z-index",2).fadeIn(f);
			//图片切换
			function show(i){
			    m.eq(i).css("z-index",2).fadeIn(f).siblings().css("z-index",1).fadeOut(f);
			}
			//切换函数
			function dots(i){
			    $("div.tips li").eq(i).addClass("active").siblings().removeClass();
			}
			
			//图片自动播放
			function play(){
			    if(i++<l-1){
				    set=setTimeout(function(){
					    show(i);
						dots(i)
						play();
					},t+f)
				}else{
				    i=-1;
					play();
				}
			}
			play();
			//鼠标经过停止
			m.hover(function(){
			    clearTimeout(set);
				k=i;
			},function(){
			     i=k-1;
				 play();   
			})
			
			//按钮控制动画
			if(d){
			    $(this).on("click","div.tips li",function(){
				    i=$(this).index();
					dots(i);
					show(i);
				})
			}
            return this;
  }
})(jQuery);