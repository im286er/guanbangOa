function comment(vid,rid,rnick){
  $('#dlg1 #wid').val(vid);
  $('#dlg1 #reid').val(rid);
  if(rid=="0"){rnick="";$("#redo").val('');}
  else{$("#redo").val('回复');}    
  $('#dlg1 #renick').val(rnick);
  $('#dlg1').modal('show');
}      
  
function savcomment(){
  if($("#form1 #des").val()==""){alert("请输入回复内容");return;}         
  SaveAM({tpl:'weibo_comments','do':'a',frmid:'#form1',showok:'','callback':'dosome'}); 
}
 
function dosome(e,d){  
  id=d.substr(d.indexOf('ok')+2);     
  $.post("./srv/sdo.php", {tpl:"upd_weibo_comments","id":id,"reid":$('#reid').val(),"wid":$('#wid').val()}, function (d, e) { 
     addComments();
  }); 
  $('#dlg1').modal('hide');
}  
function addComments(){   
  wid= $('#wid').val();
  res="";
  if($('#dlg1 #reid').val()!="0")
  res=" 回复 <a>"+$("#renick").val()+"</a>";    
  $("#pb"+wid+" .well").prepend('<p><a>'+truename+'</a>'+res+' ：'+$("#form1 #des").val()+'<small class="pull-right"><code>'+getlocaltime()+'</code></small></p>');
}

function del_blog(v,obj){
   $.post("./srv/sdo.php", {tpl:"del_weibo",id:v}, function (d, e) {                   
      $(obj).parent().parent().parent().parent().remove();
  });
} 
function del_blog_comment(v,obj){
   $.post("./srv/sdo.php", {tpl:"del_weibo_comments",id:v}, function (d, e) {                   
      $(obj).parent().parent().parent().remove();
  });
} 
function load_comments(v,obj){  
  //id=d.substr(d.indexOf('ok')+2); 
  page=$("#more"+v).data("page");
  npage=parseInt(page)+1;    
  $("#more"+v).data("page",npage);
  $.post("./srv/rdo.php", {tpl:"get_weibo_comments_next","id":v,"page":npage}, function (d, e) { 
     d1=JSON.parse(d);
     for(i=0;i<d1.length;i++){
        $(obj).parent().before('<p><a>'+d1[i].truename+'</a> '+isNulls(d1[i].redo)+' <a>'+isNulls(d1[i].renick)+'</a>：\
   '+d1[i].des+'<small class="pull-right"><code>'+utctime(d1[i].timestamp)+' \
  <a onclick="comment('+d1[i].wid+','+d1[i].id+','+d1[i].truename+')" >回复</a> <a onclick="del_blog_comment('+d1[i].id+',this)">删除</a></code></small></p>');
     }
  });     
}  
function sendWb(){
  if(ue.getContent().length<10){alert("内容太短(10-300)");return;}    
  if(ue.getContent().length>300){alert("内容太长(10-300)");return;}      
  SaveAM({tpl:"weibo","do":"a",frmid:"#form",showok:"",callback:"dosend"});
} 
function dosend(e,d){  
  id=d.substr(d.indexOf('ok')+2);     
  $("#weibolist").after('<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">\
  '+nick+getlocaltime()+' <code></code> <small class=" pull-right"> \
  <a class="glyphicon glyphicon-hand-up" onclick="vote({tbl:weibo,do:up,id:+id+},this);" title="赞">(0)</a>  &nbsp; \
<a class="glyphicon glyphicon-share-alt" onclick="vote({tbl:weibo,do:bad,id:+id+},this);" title="转发">(0)</a> &nbsp;  \
<a class="glyphicon glyphicon-comment" onclick="comment('+id+',0)" title="评论">(0)</a> &nbsp;    \
<a class="glyphicon glyphicon-remove" title="删除" onclick="del_blog('+id+',this)"></a> &nbsp;     \
 </small></h3> </div>  <div class="panel-body" id="pb'+id+'">     \
<p>'+$.base64.atob(e.data.des,true)+'</p>  <code class="pull-right"> <a onclick="load_comments('+id+',this)" id="more'+id+'" data-page="0">点击加载更多……</a>  \
</code> </div>  </div>');
$.post("./srv/sdo.php", {tpl:"upd_weibo_send","id":id}, function (d, e) {                   
      
  });
}            
function sendWbI(){ 
  if($("#form #des").val().length<4){alert("内容太短(10-300)");return;}        
    SaveAM({tpl:"weibo","do":"a",frmid:"#form",showok:"",callback:"dosend"});
}      