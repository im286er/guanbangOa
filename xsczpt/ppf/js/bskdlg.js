/*
后台弹出对话框用easyui
lren 2013-10-17 14:15  更2014-9-9
*/ 
var showinsert = true; /*显示添加的数据*/    
/**打开dialog*/ 
function openDlgMod(id,frmid) {  
  d={"do":"r","id":id,"frmid":frmid};      
   if(typeof(l_tpl) == "undefined")
      d.tpl=queryStr("t");
    else
       d.tpl=l_tpl;       	
  readID(d);     
	//readID({tpl:queryStr("t"),"do":"r","id":id,"frmid":frmid}); 
  //$(frmid).dialog('setTitle', '修改数据'); 
  //$(frmid+" #tips").text("修改数据");
  $(frmid).dialog("open"); 
	$(frmid).data("dotype","m");//操作类型
}
/**初始化 btn 新建*/
function initCBtn(e){
  $(e.btn).bind("click",function () {
     $(e.frmid).dialog('setTitle',e.name);  
     $(e.frmid+" #tips").text(e.name);
     $(e.frmid).dialog("open"); 
     $(e.frmid).data("dotype","a");//操作类型 
     $(e.frmid+" #id").val("");  
     reinitDlgCtrl(e);
  });
}
/*添加 新建数据*/
function addInsetData(e,newid) {
    rows = $("#listbase").html();
    reg = new RegExp("{id}", "g");
    rows = rows.replace(reg, newid);
	for (var id in e.data) { 
        if (e.data[id] == null || e.data[id] == "")
            val = "";
        else
            val = $.base64.atob(e.data[id], true);       
		if (typeof (formatunixs) != "undefined" && formatunixs.indexOf(id) > -1) {
			val = utctime(val);
		}
		if (formattrue.indexOf(id) > -1) {
			val = getTrueFalse(val);
		}
		if (rows.indexOf("{" + id + "}") > -1) {                   
			reg = new RegExp("{" + id+ "}", "g");
			rows = rows.replace(reg,val);
		}        
    }
    rows = rows.replace(/\{\w*\}/g, "");   
    if ($("#list tr:eq(0)").length>0)
        $("#list").append(rows);
        //$("#list tr:eq(0)").before(rows);  
    else
        $("#list").html(rows);
}
/*修改id*/
function modSaveData(e) {     
    rows = $("#listbase").html();    
    reg = new RegExp("{id}", "g");
    rows = rows.replace(reg,  e.id);
    //savestr
    for (var id in e.data) { 
        if (e.data[id] == null || e.data[id] == "")
            val = "";
        else
            val = $.base64.atob(e.data[id], true);
        if (typeof (formatunixs) != "undefined" && formatunixs.indexOf(id) > -1) {
			val = utctime(val);
		}
		if (formattrue.indexOf(id) > -1) {
			val = getTrueFalse(val);
		}
		if (rows.indexOf("{" + id + "}") > -1) {                   
			reg = new RegExp("{" + id+ "}", "g");
			rows = rows.replace(reg,val);
		}        
    }
    rows = rows.replace(/\{\w*\}/g, "");
	$("tr[name='" + e.id + "']").replaceWith(rows);
}
/**saveam 回调*/
function amcallback(e, redata) {
  $(e.frmid).dialog("close");
	switch($(e.frmid).data("dotype")){
	    case "a":
	        addInsetData(e, redata.substr(redata.indexOf("ok") + 2));
	        break;
		  case "m":
			 modSaveData(e);
		  break;
		case "am":
	 	   break;
	}  	 
}
/**添加前初始化select控件*/
function reinitDlgCtrl(e) {
    $(e.frmid + " select").each(function (a, b) {
        $(b).val(""); //$(b).get(0).selectedIndex = 0;
    });
    $(e.frmid + " input").each(function (a, b) {
        if($(b).data("flag")!="def")//非定义或初始化
            $(b).val("");
    });    
}     
function showDlgMsg(e) {
    $(e.frmid).dialog("option", "title", e.title);
    $(e.frmid + " #mesg").text(e.msg);    
    $(e.frmid).dialog("open");
}

/*初始化select控件 删除*/
function reinitSelect(frmid) { 
    $(frmid+" select").each(function (a, b) {
        $(b).val('0');
    });
}
/**信息提示对话框 不用待删除 */
function dlgMsgInit(e) {
    $(e.frmid).dialog({
        autoOpen:false,modal: e.modal,
        buttons: {
            "确定": function () {
                $(this).dialog("close");
            }
        }
    });
}   
 