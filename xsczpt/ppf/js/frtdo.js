/*
ǰ��ҳ�� ADM
lren 2013-10-17 14:15 
*/
/*��������*/
var dotype = 'none';
var showinsert = true; /*��ʾ��ӵ�����*/
var doend=false;/*��������*/
function readID(e) {
if(e.tpl==undefined){
    if(typeof(l_tpl) == "undefined")
       e.tpl=queryStr("t");
    else
      e.tpl=l_tpl;
  } 
	ed=e;
    $.post("frtdo.php?" + Math.random(),e, function (data, state) {
        try { 		 
            var json = eval(data); 
            Read(json, ed.frmid);
            if (ed.callback!=undefined&&ed.callback!=""){ 
      			  window[e.callback](ed,json);
          }
        }
        catch (e) {         
            showErr(e);
        }
    });
}
function Read(json, frmid) { 
    if (!json) return;
    for (var id in json[0]) {
        obj = $(frmid + ' #' + id);  
        if (json[0][id] == null || json[0][id] == "")
            val = "";
        else
            val = json[0][id];
      
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
                if (val == "") val = 0;
                obj.val(val);
                continue;
            }
            switch (obj.attr("type")) {              
                case "text":
                    obj.val(val);
                    break;
                case "date":
                   obj.datebox("setValue",val);  
                   break;    
                case "radio":
                  $(frmid +" input:radio[name='"+id+"'][value='"+val+"']").prop("checked", true);
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
  try{
    getFormData(e);	 	
  	ed=e;	 	
    $.post("frtdo.php?" + Math.random(),e, function (data, state) { 
		if (data.indexOf("ok") > -1) {
        if(ed.showok!=undefined&&ed.showok!=""){alert(ed.showok);}			 
        if (ed.callback!=undefined&&ed.callback!="") 
				  window[ed.callback](ed,data);
           //modSaveOne();
           // reinitSelect();
        }
        else if (data.indexOf("exist") > -1) {
            alert("�����ظ�����:" + data.substr(data.indexOf("exist") + 5));
        }
        else {
            alert("����ʧ��" + data);
        }
    });
  }catch(e){
    alert("SaveAM:"+e);
  }
}
 
/*��ȡjsdata*/
function getFormData(e) {
  try{   
	  e.data={};
    $.base64.utf8encode = true;
    $(e.frmid + " input").each(function (a, b) {
    if($(b).attr("flag") != "no" &&$(b).attr("id")!=undefined&&$(b).attr("id")!="")
      switch ($(b).attr("type")) {
          case "text": e.data[$(b).attr("id")]=$.base64.btoa($(b).val()); break;
          case "password": e.data[$(b).attr("id")]=$.base64.btoa($(b).val()); break;
          case "radio": e.data[$(b).attr("id")]=$.base64.btoa($("input:radio[name='"+$(b).attr("id")+"']:checked").val()); break;
          case "checkbox": e.data[$(b).attr("id")]=$.base64.btoa($(b).is(":checked") ?"1" :"0"); break;
          case "date": e.data[$(b).attr("id")]=$.base64.btoa($(b).datebox("getValue"));
      }
    });
    $(e.frmid + " select").each(function (a, b) {
        if ($(b).attr("flag") != "no" && $(b).attr("flag") != "od" && $(b).val() != null) {
            e.data[$(b).attr("id")]=$.base64.btoa($(b).val());
        }
    });
    $(e.frmid + " textarea").each(function (a, b) {
         switch($(b).data("edit"))  {
          case "ck":e.data[$(b).attr("id")]=$.base64.btoa(CKEDITOR.instances[$(this).attr("id")].getData()); break;
          case "ue":e.data[$(b).attr("id")]=$.base64.btoa(UE.getEditor('ue'+$(b).attr("id")).getContent());break;
          case "um":e.data[$(b).attr("id")]=$.base64.btoa(UM.getEditor('um'+$(b).attr("id")).getContent());break;
          case "ke":e.data[$(b).attr("id")]=$.base64.btoa(keditor[$(b).attr("id")].html());break;
          default: e.data[$(b).attr("id")]=$.base64.btoa($(b).val());break;
        } 
    });  
    
  }catch(e){
    alert("getFormData:"+e);
  }    
}
 
/**for Dialog modify*/
function openDlgMod(id,frmid) {   
    dotype = "m";
    $(frmid).dialog("option", "title", "�޸�����")
    $(frmid+" #tips").text("��ȡ����...");
    $(frmid).dialog("open");
	
    readID(id, frmid);
}
/*��ʼ��select�ؼ�*/
function reinitSelect(frmid) { 
    $(frmid+" select").each(function (a, b) {
        $(b).val('');
    });
}
function reinitInput(frmid){
  $(frmid+" input[type=text]").each(function (a, b) {
        $(b).val('');
    });
   reinitSelect(frmid); 
}

/*����½�id*/
function addInsetOne(id,frmid) { 
    rows = $("#listbase").html();
    reg = new RegExp("{id}", "g");
    rows = rows.replace(reg, id);
    $("#dlg1 input").each(function (a, b) {
        switch ($(b).attr("type")) {
            case "TEXT":           
            case "text": if (rows.indexOf("{" + $(b).attr("id") + "}") > -1) {                   
                    reg = new RegExp("{" + $(b).attr("id") + "}", "g");
                    rows = rows.replace(reg,$(b).val());
                }
                break;
            case "password": break;
        }
    });
    rows = rows.replace(/\{\w*\}/g, "");   
    if ($("#list tr:eq(0)").length>0)
        $("#list tr:eq(0)").before(rows);  
    else
        $("#list").html(rows);
}
/*�޸�id*/
function modSaveOne(frmid) { 
    id = $(frmid+" #id").val(); 
    if ($("tr[name='" + id + "']").attr("class") == undefined || $("tr[name='" + id + "']").attr("class") == 'undefined' || $("tr[name='" + id + "']").attr("class") == "") {
        rows = $("#listbase").html();
    } else {
        switch ($("tr[name='" + id + "']").attr("class")) {
            case "cate2": rows = $("#listbase2").html(); break;
            case "bg cate1": rows = $("#listbase1").html(); break;
        }
    }  
    reg = new RegExp("{id}", "g");
    rows = rows.replace(reg, id);
    //savestr
    var d = eval("["+savestr+"]");
    $(frmid + " dlg1 input").each(function (a, b) {
        switch ($(b).attr("type")) {
            case "TEXT":
            case "text":
                if (rows.indexOf("{" + $(b).attr("id") + "}") > -1) {                    
                    reg = new RegExp("{" + $(b).attr("id") + "}", "g");
                    rows = rows.replace(reg, $.base64.atob(d[0][ $(b).attr("id")],true));
                }
                break;
        }
    });   
    rows = rows.replace(/\{\w*\}/g, "");
	$("tr[name='" + id + "']").replaceWith(rows);
}


/**��ʼ��dialog �Ի��� del*/
function dlgInit(e) {
    $(e.frmid).dialog({
        autoOpen: false, width: e.width, height: e.height, modal: e.modal,
        buttons: {
            "ȷ��": function () {
                SaveAM({ tpl: queryStr("t"), "do": $(e.frmid).attr("dotype"), id: $(e.frmid + " #id").val(), frmid: e.frmid, showok: e.showok, callback: e.callback });

            },
            "ȡ��": function () {
                $(this).dialog("close");
            }
        },
        close: function () {
        }
    });
    $(e.btnid).button().click(function () {
        $(e.frmid).attr("dotype", "a"); //��������
        $(e.frmid + " #id").val("");
        $(e.frmid).dialog("option", "title", "���");
        $(e.frmid + " #tips").text("���");
        reinitDlgCtrl(e);
        $(e.frmid).dialog("open");
    });
}
function deleteID(e) {
if(!confirm("ȷ��Ҫɾ����"))return;
if(e.tpl==undefined){
    if(typeof(l_tpl) == "undefined")
       e.tpl=queryStr("t");
    else
      e.tpl=l_tpl;
  } 	
  $.post("frtdo.php",e, function (data, state) {
        try { 
            if(data.indexOf("ok")>-1){
              if (e.showok!=undefined&&e.showok!="")alert(e.showok); 
              $("#row"+e.id).remove();
            }
            if (e.callback!=undefined&&e.callback!="")window[e.callback](e,data);     
                  
        }
        catch (e) {alert("deleteID:"+e);}
    });
}