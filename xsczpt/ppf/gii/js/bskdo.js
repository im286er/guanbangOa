/*bask do [add/edit]  ver:3.1 2015-11-16 @lren-su 
数据处理
*/  
function readID(e) {
  if(e.tpl==undefined){
    if(typeof(l_tpl) == "undefined")
       e.tpl=queryStr("t");
    else
      e.tpl=l_tpl;
  } 
  e['do']='r'; 
	ed=e;  
	$.post("bskdo.php?", e, function (o,es) {
	    try {	   
	        var jsn = JSON.parse(o);
	        Read(ed, jsn);
            if (e.l_callback!=undefined&&e.l_callback!="")window[e.l_callback](e,jsn);              
	    }
	    catch (e1){ ralert("readID:" + e1+ o);}
	});
}
function Read(e,d) { 
    if (!d) return;
    for (var id in d[0]) {
        obj = $(e.frmid + ' #' + id);  
        if (d[0][id] == null || d[0][id] == "")
            val = "";
        else
            val = d[0][id];
       
        if (typeof (formats) != "undefined" && formats.indexOf(id) > -1) {
            val = str2DateTime(val, formatsplit);
        }

        if (obj[0]) {
            if (obj.get(0).tagName == 'TEXTAREA') {
              switch(obj.data("edit"))  {
                  case "ck":CKEDITOR.instances[obj.attr("id")].setData(val); break;
                  case "ue": UE.getEditor('ue'+obj.attr("id")).setContent(val, false);  break;
                  case "um": UM.getEditor('um'+obj.attr("id")).setContent(val, false);  break;
                  case "ke": keditor[obj.attr("id")].html(val);break;
                  default: obj.val(val);  break;
                }
                continue;
            }
            if (obj.get(0).tagName == 'SELECT') {
                if (val == "")val = 0;
                    obj.val(val);
                continue;
            }
            switch (obj.attr("type")) {
                case "text":
                case "TEXT":
                    obj.val(val);
                    break;
                case "date":
                   obj.datebox("setValue",val);  
                   break;    
                case "checkbox": obj.prop("checked", val == 1);
                    break;
                default:                    
                    obj.val(val);
                    break;
            }
        }
    }     
}
/*Save data*/
function SaveAM(e) { 
  if(e.tpl==undefined){ 
   if(typeof(l_tpl) == "undefined")
       e.tpl=queryStr("t");
    else
       e.tpl=l_tpl;
  } 
  if(e.do==undefined)e.do=$(e.frmid).data('dotype'); 	 
  getFormData(e);	 
  $.post("bskdo.php?" + Math.random(),e, function (data, state) {    
 	  if (data.indexOf("ok") > -1) {       
        if(e.showok!=undefined&&e.showok!=""){lalert(e.showok);}			 
        if(e.callback!=undefined&&e.callback!="")window[e.callback](e,data);
        bkcallback(e,data);
      }
      else if (data.indexOf("exist") > -1) {
          lalert("存在重覆数据:" + data.substr(data.indexOf("exist") + 5));
      }
      else {
          lalert("保存失败"+data);
      }
  });
}
/*js get post data*/
function getFormData(e) {   
	e.data={};
    $.base64.utf8encode = true;
    $(e.frmid + " input").each(function (a, b) {
        if($(b).attr("id")!=undefined&&$(b).attr("id")!="")
          switch ($(b).attr("type")) {
              case "text": e.data[$(b).attr("id")]=$.base64.btoa(clearQuote($(b).val())); break;
              case "password": e.data[$(b).attr("id")]=$.base64.btoa($(b).val()); break;
              case "checkbox": e.data[$(b).attr("id")]=$.base64.btoa($(b).is(":checked") ?"1" :"0"); break;
              case "date": e.data[$(b).attr("id")]=$.base64.btoa($(b).datebox("getValue"));
          }
    });
    $(e.frmid + " select").each(function (a, b) {
        if ($(b).attr("flag") != "no" && $(b).attr("flag") != "" && $(b).val() != null) {
            e.data[$(b).attr("id")]=$.base64.btoa($(b).val());
        }
    });
    $(e.frmid + " textarea").each(function (a, b) {
       switch($(b).data("edit"))  {
          /*case "ck":e.data[$(b).attr("id")]=$.base64.btoa(CKEDITOR.instances[$(this).attr("id")].getData()); break;
          case "ue":e.data[$(b).attr("id")]=$.base64.btoa(UE.getEditor('ue'+$(b).attr("id")).getContent());break;
          case "um":e.data[$(b).attr("id")]=$.base64.btoa(UM.getEditor('um'+$(b).attr("id")).getContent());break;
          case "ke":e.data[$(b).attr("id")]=$.base64.btoa(keditor[$(b).attr("id")].html());break;
          default: e.data[$(b).attr("id")]=$.base64.btoa($(b).val());break;*/
          case "ck":e.data[$(b).attr("id")]=$.base64.btoa(clearQuote(CKEDITOR.instances[$(this).attr("id")].getData())); break;
          case "ue":e.data[$(b).attr("id")]=$.base64.btoa(clearQuote(UE.getEditor('ue'+$(b).attr("id")).getContent()));break;
          case "um":e.data[$(b).attr("id")]=$.base64.btoa(clearQuote(UM.getEditor('um'+$(b).attr("id")).getContent()));break;
          case "ke":e.data[$(b).attr("id")]=$.base64.btoa(clearQuote(keditor[$(b).attr("id")].html()));break;
          default: e.data[$(b).attr("id")]=$.base64.btoa(clearQuote($(b).val()));break;
        } 
    });    
}