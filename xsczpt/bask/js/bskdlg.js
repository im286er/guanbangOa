/*
对话框处理easyui
@lren-su 2015-11-16
*/  
function Mod(d) { 
  if(d.tpl==undefined){ 
   if(typeof(l_tpl) == "undefined")
       d.tpl=queryStr("t");
    else
       d.tpl=l_tpl;
  } 
  d['do']='r'; 
  if(d.frmid==undefined)d.frmid="#dlg1";
  readID(d);//$(frmid).dialog('setTitle', '修改');   
   if(d.tips!=undefined)$(d.frmid+" #tips").text(d.tips);
     else $(d.frmid+" #tips").text("修改数据……");  
  $(d.frmid).data("dotype","m");
  $(d.frmid).dialog("open").window("resize",{top:$(document).scrollTop()+($(window).height())* 0.25});  
  //$(d.frmid).panel("move",{top:$(document).scrollTop() + ($(window).height()-250) * 0.5}); 
}

function addInsetData(e,newid) {
    rows = $("#listbase").html();
    reg = new RegExp("{id}", "g");
    rows = rows.replace(reg, newid);  
    $(e.frmid + " input").each(function (a, b) {
        reg = new RegExp("{" + $(b).attr("id")+ "}", "g");			
        val="";
        if($(b).attr("id")!=undefined&&$(b).attr("id")!="")
          switch ($(b).attr("type")) {
              case "text": val=$(b).val(); break; 
              case "checkbox": val=$(b).is(":checked") ?"1" :"0"; break;
              case "date": val=$(b).datebox("getValue");
          }
        rows = rows.replace(reg,val);  
    });
    $(e.frmid + " select").each(function (a, b) {
        reg = new RegExp("{" + $(b).attr("id")+ "}", "g");	
        if ($(b).attr("flag") != "no" && $(b).val() != "" && $(b).val() != null) {
            rows = rows.replace(reg,$(b).find('option:selected').text());  
        }
    });
    rows = rows.replace(/\{\w*\}/g, "");   
    if ($("#list tr:eq(0)").length>0)
        //$("#list").append(rows);
        $("#list tr:eq(0)").before(rows);  
    else
        $("#list").html(rows);
}
/*修改id*/
function modSaveData(e) {     
    rows = $("#listbase").html();    
    reg = new RegExp("{id}", "g");
    did=$(e.frmid + " #id").val();
    rows = rows.replace(reg,  did); 
    $(e.frmid + " input").each(function (a, b) {
        reg = new RegExp("{" + $(b).attr("id")+ "}", "g");			
        val="";
        if($(b).attr("id")!=undefined&&$(b).attr("id")!="")
          switch ($(b).attr("type")) {
              case "text": val=$(b).val(); break; 
              case "checkbox": val=$(b).is(":checked") ?"1" :"0"; break;
              case "date": val=$(b).datebox("getValue");
          }
        rows = rows.replace(reg,val);  
    });
    $(e.frmid + " select").each(function (a, b) {
        reg = new RegExp("{" + $(b).attr("id")+ "}", "g");	
        if ($(b).attr("flag") != "no" && $(b).val() != "" && $(b).val() != null) {
            rows = rows.replace(reg,$(b).find('option:selected').text());  
        }
    });   
    rows = rows.replace(/\{\w*\}/g, ""); 
	$("#row" + did+ "").replaceWith(rows);
    //$(rows).replaceAll("#row" + did + ""); 
}
/**saveam 回调*/
function bkcallback(e,redata) { 
  $(e.frmid).dialog("close");   
    switch($(e.frmid).data("dotype")){
        case "a":
            msg("添加成功");
            addInsetData(e, redata.substr(redata.indexOf("ok") + 2));
            break;
          case "m":
            msg("修改成功");
            modSaveData(e);
          break;
        case "am":
            msg("成功");
           break;
    }  	 
}
/**添加前初始化select控件*/
function reinitDlgCtrl(e) {
    $(e.frmid + " select").each(function (a, b) {
        if($(b).attr("flag")!="def")$(b).val(""); //$(b).get(0).selectedIndex = 0;
    });
    $(e.frmid + " input").each(function (a, b) {
        if($(b).attr("flag")!="def")$(b).val("");
    });    
}     
/**初始化 btn 新建*/
function initAddBtn(d){
  $(d.btn).bind("click",function () { 
     if(d.tips!=undefined)$(d.frmid+" #tips").text(d.tips);
     else $(d.frmid+" #tips").text("添加数据……");   
     $(d.frmid).dialog("open"); 
     $(d.frmid).data("dotype","a");
     $(d.frmid+" #id").val("").window("resize",{top:$(document).scrollTop()+($(window).height())* 0.25});   
     //$(d.frmid).panel("move",{top:$(document).scrollTop() + ($(window).height()-250) * 0.5});
     reinitDlgCtrl(d);
  });
}
function initAddBtn1(d){    
  $(d.btn).bind("click",function () {  
    if(d.tpl==undefined){ 
       if(typeof(l_tpl) == "undefined")
           d.tpl=queryStr("t")+"_am";
        else
           d.tpl=l_tpl+"_am";
      }  
    layer.open({
        type: 2,
        title: '添加数据……',
        shadeClose: true,
        shade: 0.8,
        area: [d.w, d.h],
        content: './?d=a&t='+d.tpl 
    }); 
  });
}
function Mod1(d) { 
  if(d.tpl==undefined){ 
   if(typeof(l_tpl) == "undefined")
       d.tpl=queryStr("t")+"_am";
    else
       d.tpl=l_tpl+"_am";
    }  
    layer.open({
        type: 2,
        title: '修改数据……',
        shadeClose: true,
        shade: 0.8,
        area: [d.w, d.h],
        content: './?d=m&t='+d.tpl+'&id='+d.id 
    }); 
}

/*
function initCBtn(e){
  $(e.btn).bind("click",function () {
     //$(e.frmid).dialog('setTitle',e.name);  
     $(e.frmid+" #tips").text("修改数据……");
     $(e.frmid).dialog("open"); 
     $(e.frmid).data("dotype","a"); 
     $(e.frmid+" #id").val("");  
     reinitDlgCtrl(e);
  });
} */