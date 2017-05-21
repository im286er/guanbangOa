﻿/**tree easyui 基础版*/
/**add Root Node*/
function addTreeRootNode(d){	
	$(d.dlg).data("treeid",d.tree);	
	$(d.dlg).data("pid",'0');
	$(d.dlg+" #name").val('');
	$(d.dlg).dialog("open");  
}
function addTreeNode(d){	
	var t = $(d.tree);
    var node = t.tree('getSelected'); 	
	if(node==undefined){alert("请选择一个节点1");return;}
	if(!node||node==null){alert("请选择一个节点2");return;}		
	$(d.dlg).data("treeid",d.tree);	
	$(d.dlg).data("pid",node.id);	
	$(d.dlg+" #name").val('');
	$(d.dlg).dialog("open");  
}
function delTreeNode(d){
	var t = $(d.tree);
    var node = t.tree('getSelected'); 	
	if(node==undefined){alert("请选择一个节点1");return;}
	if(!node||node==null){alert("请选择一个节点2");return;}	
    $(d.tree).tree('remove', node.target);
	saveToDb({tpl:"delnod",id:node.id});	
}
function addTreeNodeDo(id){
	if($(id+" #name").val()==""){alert("请输入名称");return;}
	 var t = $($(id).data("treeid"));
   var node = t.tree('getSelected'); 	 
   /*   t.tree('append', {
          parent: (node?node.target:null),
          data: [{
              text: $(id+" #name").val()
          }]
      });*/
	  saveToDb({tpl:"addnod",pid:$(id).data("pid"),name:$(id+" #name").val(),odx:$(id+" #odx").val(),dlg:id});
	$(id).dialog('close');
}
function appendNodToTree(d){
  var t = $($(d.dlg).data("treeid"));
  var node = t.tree('getSelected'); 	 
  t.tree('append', {
      parent: (node?node.target:null),      
      data: [{
          id:d.nid,
          text: $(d.dlg+" #name").val()
      }]
  });
}
function saveToDb(d){
d['tbl']=tbl;
$.post("./srv/treedo.php?"+Math.random(), d, function (e,f) {
if(e.indexOf("ok")==-1)alert(e); 
if(d.tpl=="addnod"){d.nid=e.substr(e.indexOf("ok")+2);appendNodToTree(d);}
});
}
function modTreeNode(d){
	var t = $(d.tree);
    var node = t.tree('getSelected'); 	
	if(node==undefined){lalert("请选择一个节点");return;}
	if(!node||node==null){lalert("请选择一个节点");return;}		
	$(d.dlg).data("treeid",d.tree);	
	$(d.dlg).data("id",node.id);	
	$(d.dlg+" #name").val(node.text); 
    $(d.dlg).dialog("open");  
}
function modTreeNodeDo(id){
	if($(id+" #name").val()==""){alert("请输入名称");return;}
	var t = $($(id).data("treeid"));
    var node = t.tree('getSelected'); 	
	t.tree('update', {
		target: node.target,
		text: $(id+" #name").val()
	});
	saveToDb({tpl:"modnod",id:$(id).data("id"),name:$(id+" #name").val(),odx:$(id+" #odx").val()});
	$(id).dialog('close');
}
function getodx(){
   var t = $("#tree");
 var node = t.tree('getSelected'); 	
   if(node==undefined){return;}
   if(!node||node==null){return;}	
     $.post("./srv/treedo.php", {tpl:"getodx","tbl":tbl,id:node.id}, function (e,f) {
     $("#dlg2 #odx").val($.trim(e));}); 		 
}