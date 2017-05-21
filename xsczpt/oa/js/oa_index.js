function hideClick(tm){
	 $('#'+tm).toggle();
	 var d_state=$('#'+tm).is(":hidden");
	 if(d_state){
		 $('#'+tm+'1').removeClass('stu-menu-nomal');
		 $('#'+tm+'1').addClass('stu-menu-check');
		 $('#'+tm+'_1').css('color','');
	 }else{
		 $('#'+tm+'1').removeClass('stu-menu-check');
		 $('#'+tm+'1').addClass('stu-menu-nomal');
		 $('#'+tm+'_1').css('color','#8DDED5');
	 }
 }
function initMenu(_menu_name){
	$('#sz_1').css('color','#8DDED5');
	for(var em in _menu_name){
	  $(_menu_name[em]).hide();
	}
}
 function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) return unescape(r[2]); return null; //返回参数值
}