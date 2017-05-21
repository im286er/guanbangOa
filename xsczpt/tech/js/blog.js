/**first comment*/ 
function savcomment(){
  if($("#forma #des").val()==""){alert("请输入回复内容");return;}     
  SaveAM({tpl:'blog_comments','do':'a',frmid:'#forma',showok:'','callback':'docomment'}); 
}
/**second comment*/ 
function comment(vid,rid,rnick){
  $('#dlg1 #bcid').val(vid);
  $('#dlg1 #reid').val(rid);
  if(rid=="0"){rnick="";$("#redo").val('');}
  else{$("#redo").val('回复');}    
  $('#dlg1 #renick').val(rnick);
  $('#dlg1').modal('show');
} 
function savcomment2(){
  if($("#form2 #des").val()==""){alert("请输入回复内容");return;}         
  SaveAM({tpl:'blog_comment_comments','do':'a',frmid:'#form2',showok:'','callback':'docomment2'}); 
}
function docomment2(e,d){  
  id=d.substr(d.indexOf('ok')+2);     
  $.post("./srv/sdo.php", {tpl:"upd_blog_comments_comments","id":id,"reid":$('#reid').val(),"bcid":$('#bcid').val()}, function (d, e) { 
     addComments();
  }); 
  $('#dlg1').modal('hide');
}  
function addComments(){   
  wid= $('#bcid').val();
  res="";
  if($('#dlg1 #reid').val()!="0")
  res=" 回复 <a>"+$("#renick").val()+"</a>";  
  $("#row"+wid+" .well").prepend('<p><a>'+nick+'</a>'+res+' ：'+$("#form2 #des").val()+'<small class="pull-right"><code>'+getlocaltime()+'</code></small></p>');
  $("#row"+wid+" .well").removeClass("hide");   
}



function rm_c(v,obj){
   $.post("./srv/sdo.php", {tpl:"del_blog_comments",id:v}, function (d, e) {                   
      $(obj).parent().parent().parent().parent().remove();
  });
} 
function rm_c_c(v,obj){
   $.post("./srv/sdo.php", {tpl:"del_blog_comment_comments",id:v}, function (d, e) {                   
      $(obj).parent().parent().parent().remove();
  });
} 
function docomment(e,d){  
  id=d.substr(d.indexOf('ok')+2);     
  $("#list").after('<div class="panel panel-default" id="row'+id+'"><div class="panel-heading"><h3 class="panel-title">\
  '+nick+getlocaltime()+' <code></code> <small class=" pull-right"> \
  <a class="glyphicon glyphicon-hand-up" onclick="vote({tbl:blog_comments,do:up,id:+id+},this);" title="赞">(0)</a>  &nbsp; \
<a class="glyphicon glyphicon-share-alt" onclick="vote({tbl:blog_comments,do:share,id:+id+},this);" title="转发">(0)</a> &nbsp;  \
<a class="glyphicon glyphicon-comment" onclick="comment('+id+',0)" title="评论">(0)</a> &nbsp;    \
<a class="glyphicon glyphicon-remove" title="删除" onclick="rm_c('+id+',this)"></a> &nbsp;     \
 </small></h3> </div>  <div class="panel-body"><p>'+$.base64.atob(e.data.des,true)+'</p>    \
<div class="col-sm-11 well hide"><code class="pull-right"> <a onclick="load_comments('+id+',this)" data-page="0">点击加载更多……</a>  \
</code> </div> </div>  </div>');
 $.post("./srv/sdo.php", {tpl:"upd_blog_comments","id":id,bid:$("#bid").val()}, function (d, e) {                   
      
  });
}  


function load_comments(v,obj){  
  //id=d.substr(d.indexOf('ok')+2); 
 page=$(obj).data("page");
  npage=parseInt(page)+1;    
  $(obj).data("page",npage);
  $.post("./srv/rdo.php", {tpl:"get_blog_comment_comments_next","id":v,"page":npage}, function (d, e) { 
    d1=JSON.parse(d);
     for(i=0;i<d1.length;i++){
        $(obj).parent().before('<p><a>'+d1[i].truename+'</a> '+isNulls(d1[i].redo)+' <a>'+isNulls(d1[i].renick)+'</a>：\
   '+d1[i].des+'<small class="pull-right"><code>'+d1[i].created+' \
  <a onclick="comment('+d1[i].bcid+','+d1[i].id+','+d1[i].truename+')" >回复</a> <a onclick="rm_c_c('+d1[i].id+',this)">删除</a></code></small></p>');
     }
  });     
}  
