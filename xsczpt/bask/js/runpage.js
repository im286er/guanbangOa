/**
*Page 操作 ver 3.1
*lren 2013-6-16 17:24
*/
//var fileurl = 'http://file.k83.cn/';
var formatsplit = "-"; /*格式化时间字符串 formats*/
var formattrue = "";
var formatstate="";/*格式化状态*/
var initState=false;   /*初始化状态str*/
//var formatunixs = "";/*格式unix时间字符串*/       
function setOderBy() {
    $("th").bind("mouseup", function () {
        if ($(this).attr("name")) {
            if ($(this).attr("name") == $("#__order").val()) {
                if ($("#__orderby").val() == "asc")
                    $("#__orderby").val("desc");
                else
                    $("#__orderby").val("asc");
            }
            $("#__order").val($(this).attr("name"));
            loadPage({l_page:1});
        }
    });
}
$(function () {
    setOderBy();
});

function initPage(json) {
    id = json[0]["id"];
    data = $.base64.atob(json[0]["data"], true);
    var js = eval(data);
    $("#" + id).append("<option value='0'>—所有—</option>");
    for (i = 0; i < js.length; i++) {
        $("#" + id).append("<option value='" + js[i]["id"] + "'>" + js[i]["name"] + "</option>");
    }

}

function readPage(json) {
    var data = $.base64.atob(json[0].data, true);
    var js1 = eval(data);
    showJData(js1);
    showJPage(json[0]);
}

function loadPage(d) {
  if(d.l_tpl==undefined){
    if(typeof(l_tpl) == "undefined")
       d.l_tpl=queryStr("t");
    else
     d.l_tpl=l_tpl;
  } 
  if(d["l_do"]==undefined)d["l_do"]="page";
  if(d.l_page==undefined)d.l_page=1;
  if(d.l_order==undefined)d.l_order=$("#__order").val();
  if(d.l_orderby==undefined)d.l_orderby=$("#__orderby").val();     
    $("#qry input").each(function (a, b) {
        if ($(b).attr("flag") != "no") {
            switch ($(b).attr("type")) {
                case "text": d[$(b).attr("id")]=$(b).val(); break;
                case "checkbox":d[$(b).attr("id")]=($(b).is(":checked") ? 1 : 0); break;
            }
        }
    });
    $("#qry select").each(function (a, b) {
        if ($(b).attr("flag") != "no" && $(b).attr("flag") != "od" && $(b).val() != null) {
           d[$(b).attr("id")]=$(b).val()
        }
    });   
    
    $.post("runpagedo.php?",d, function (data, e) {
        try {  
            if (data == ""||$.trim(data)=="null"){ $("#list").empty();return;} 
            if($.trim(data).substr(0,3)=="err"){$("#list").empty();alert(data.substr(3));return;}
            var json = eval(data);
            var data = $.base64.atob(json[0].data, true);
            var js1 = eval(data); 
            showJData(js1);
            showJPage(json[0]);
            if(initState){
                reinitStates();
            } 
             if (d.l_callback!=undefined&&d.l_callback!=""){ 
          			  window[d.l_callback]();
              }
        } catch (e) {$("#list").empty(); ralert(e+data); }
    });
}

function showJData(jd) {
    try {		
        $("#list").html("");
        for (i = 0; i < jd.length; i++) {
            rows = $("#listbase").html();        
            for (var _n in jd[i]) {
                try { //设置编码$.base64.utf8encode=true; 加密$.base64.btoa(s,true); 
                    if (jd[i][_n] == null || jd[i][_n] == "")
                        val = "";
                    else
                        val = jd[i][_n];
                } catch (e1) {
                    alert(e1);
                    val = '';
                }                              
                if (rows.indexOf("{" + _n + "}") > -1) {                  
                    if (typeof (formatunixs) != "undefined" && formatunixs.indexOf(_n) > -1) {
                        val = utctime(val);
                    }
                    if (formattrue.indexOf(_n) > -1) {
                        val = getTrueFalse(val);
                    }
                    if(formatstate.indexOf(_n+",")>0){
                       val=getStateVal(_n,val);
                    }
                    reg = new RegExp("{" + _n + "}", "g");
                
                    rows = rows.replace(reg, val);
                }                
            }			
            $("#list").append(rows);
        }
    } catch (e) {
        ralert(e);
    }
}

function showJPage(jd) {
    pindex = jd["pindex"];
    pcount = jd["pgcount"];
    rowcount = jd["rowcount"];
    pgroup = 5; // 10 / 2

    si = pindex - pgroup;
    if (si < 1) si = 1;
    ei = pindex + pgroup;
    if (ei > pcount) ei = pcount;     
    
    var pstr = "<a>" + rowcount + "条 " + pindex + "/" + pcount + "页</a> ";
    pstr += "<a onclick=loadPage({l_page:1})>|<</a><a onclick=loadPage({l_page:" + (pindex - 1 > 0 ? pindex - 1 : 1) + "})><<</a>";
    for (i = si; i <= ei; i++) {
        if (i == pindex)
            pstr += "<a>" + i + "</a>";
        else
            pstr += "<a onclick=loadPage({l_page:" + i + "})>" + i + "</a>";
    }
    pstr += "<a onclick=loadPage({l_page:" + (pindex + 1 < pcount ? pindex + 1 : pcount) + "})>>></a>";
    pstr += "<a onclick=loadPage({l_page:" + pcount + "})>>|</a>";
    $("#page").html(pstr);    
}

function delRow(d) {   
  layer.confirm('您真的确定要删除吗？', {
      btn: ['确定','取消']
  }, function(index){
    if(d.l_tpl==undefined){
         if(typeof(l_tpl) == "undefined")
           d.l_tpl=queryStr("t");
        else
         d.l_tpl=l_tpl;
      }
      if(d["l_do"]==undefined)d["l_do"]="del";
      $.post("runpagedo.php?" + Math.random(),d, function (o, state) {
          if (o.indexOf('ok') != -1) {
              $("#row" + d.id + "").remove(); 
          }
          else
              msg('操作出错'+data);
           layer.close(index);    
      });
  }, function(index){
      
  }); 

}
function getStateVal(k,v){
  if(v=="")
    return "";
  else
   return eval(k+"["+v+"]"); //     
}

function auditID(e){ 
  if(e.l_tpl==undefined){
    if(typeof(l_tpl) == "undefined")
       e.l_tpl=queryStr("t");
    else
      e.l_tpl=l_tpl;
  } 
  if(e["l_do"]==undefined)e["l_do"]="o";
  $.post("runpagedo.php",e, function (d, state) {
      try {        
         if(d.indexOf("ok")>-1){
            if (e.showok!=undefined&&e.showok!="")lalert(e.showok); 
            if (e.callback!=undefined&&e.callback!="")window[e.callback](e,d);     
             $("#row" + e.id + "").find("st").html('ok'); 
          } 
          else{
            lalert("状态未更改");
          }              
      }
      catch (e1) {lalert("auditID:"+e1);}
  });    
}
function auditPage(e){ 
if(e.l_tpl==undefined){  
  if(typeof(l_tpl) == "undefined")
     e.l_tpl=queryStr("t");
  else
    e.l_tpl=l_tpl; 
}  
  if(e["l_do"]==undefined)e["l_do"]="opage";
  var arr=[];
  $("#list tr").each(function(a,b){arr.push($(b).data("id"));});
  e.ids=arr.join(','); 
  $.post("runpagedo.php",e, function (d, state) {
      try {        
         if(d.indexOf("ok")>-1){
            if (e.showok!=undefined&&e.showok!="")lalert(e.showok); 
            if (e.callback!=undefined&&e.callback!="")window[e.callback](e,d);     
            lalert(d);
          } 
          else{
            lalert("状态未更改");
          }              
      }
      catch (e1) {lalert("auditPage:"+e1);}
  });    
}