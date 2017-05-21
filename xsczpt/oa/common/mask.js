/**
 * 功能：遮罩层
 * 作者：Adrian·Tian
 * 日期：2010-07-10
 */
 
//检测是否是Mac下的firefox
function tb_detectMacXFF() {
  var userAgent = navigator.userAgent.toLowerCase();
  if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox')!=-1) {
    return true;
  }
}

//显示会员信息
function tb_pay_show(){
	try {
			//删除原有的控件对象
			$("#TB_HideSelect").remove();
			$("#TB_overlay").remove();
			$("#TB_window").remove();
			//判断是IE6还是其它的浏览器
			if(typeof document.body.style.maxHeight === "undefined"){//IE6
				$("body","html").css({height: "100%", width: "100%"});
				$("html").css("overflow","hidden");
				//隐藏select控件
				if(document.getElementById("TB_HideSelect") === null){
					$("body").append("<iframe id='TB_HideSelect'></iframe><div id='TB_overlay'></div><div id='TB_window'></div>");
				}
			}else{//其它浏览器
				if(document.getElementById("TB_overlay") === null){
					$("body").append("<div id='TB_overlay'></div><div id='TB_window'></div>");
				}
			}
			if(tb_detectMacXFF()){
				//使用png图片做为背景来隐藏flash
				$("#TB_overlay").addClass("TB_overlayMacFFBGHack");
			}else{
				//使用透明背景
				$("#TB_overlay").addClass("TB_overlayBG");
			}
			$('#TB_window').empty();
			html = $("#div_box").clone();
			$("#div_box").remove();
			$('#TB_window').append(html);
			$("#div_box").show();
			//显示
			$('#TB_window').show();
			//取消遮罩
			//$("#TB_overlay").click(tb_remove);
			$("#but_cancel").click(tb_pay_remove);
			document.onkeydown = function(e){ 	
				if(e == null){ // ie
					keycode = event.keyCode;
				}else{ // mozilla
					keycode = e.which;
				}
				if(keycode == 27){ // close
					tb_remove();
				}	
			};
	} catch(e) {
		//异常处理
	}
}

//隐藏会员提示层
function tb_pay_remove() {
	$("#but_cancel").unbind("click");
	$("#TB_window").fadeOut("fast",function(){$('#TB_window,#TB_overlay,#TB_HideSelect').trigger("unload").unbind().remove();});
	//把显示层克隆一份追加到body最后
	var html = $("#div_box").clone();
	$("#div_box").remove();
	$("body").append(html);
	$("#div_box").hide();
	$("#TB_load").remove();
	if(typeof document.body.style.maxHeight == "undefined"){//if IE 6
		$("body","html").css({height: "auto", width: "auto"});
		$("html").css("overflow","");
	}
	document.onkeydown = "";
	document.onkeyup = "";
	return false;
}