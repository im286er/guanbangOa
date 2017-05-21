function comment(vid,rid,rnick){
  $('#dlg1 #mid').val(vid);
  $('#dlg1 #reid').val(rid);
  if(rid=="0"){rnick="";$("#redo").val('');}
  else{$("#redo").val('回复');}    
  $('#dlg1 #renick').val(rnick);
  $('#dlg1').modal('show');
}  
function savcomment(){
  if($("#form1 #des").val()==""){alert("请输入回复内容");return;}         
  SaveAM({tpl:'zone_leave_comments','do':'a',frmid:'#form1',showok:'','callback':'dosome'}); 
}
function dosome(e,d){  
  id=d.substr(d.indexOf('ok')+2);     
  $.post("./srv/sdo.php", {tpl:"upd_leave_comments","id":id,"mid":$('#mid').val()}, function (d, e) { 
     addComments();
  }); 
  $('#dlg1').modal('hide');
}  
function addComments(){   
  wid= $('#mid').val(); 
  $("#pb"+wid+" .well").prepend('<p><a>'+nick+'</a>：'+$("#form1 #des").val()+'<small class="pull-right"><code>'+getlocaltime()+'</code></small></p>');
  $("#pb"+wid+" .well").removeClass("hide");    
}




function rm_c(v,obj){
   $.post("./srv/sdo.php", {tpl:"del_leave",id:v}, function (d, e) {                   
      $(obj).parent().parent().parent().parent().remove();
  });
} 
function rm_c_c(v,obj){
   $.post("./srv/sdo.php", {tpl:"del_leave_comments",id:v}, function (d, e) {                   
      $(obj).parent().parent().parent().remove();
  });
} 
