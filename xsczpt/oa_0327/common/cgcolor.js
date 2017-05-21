$(document).ready(function(){
	$(".oa_left span").each(function(i,n){
		if(i==0){
			$(this).css("color","rgb(141, 222, 213)");
		}else{
			$(this).css("color","black");
		}
		$(this).click(function(){
			//点击切换颜色
			$(".oa_left span").css("color","black");
			$(this).css("color","rgb(141, 222, 213)");
			
		})
	})
})

