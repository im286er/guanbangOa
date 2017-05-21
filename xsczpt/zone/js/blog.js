/**first comment*/ 

function savcomment(){
  if(ouid==""){
      var url = location.pathname+location.search;
      window.location.href="/?t=login&url="+$.base64.encode(url);
      return false;
  }
  if(limit_comment!=0){alert("您没有回复权限");return;}   
  if(ue.getContent().length<10){alert("请输入回复内容");return;}      
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
  if(limit_comment!=0){alert("您没有回复权限");return;} 
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


function docomment(e,d){  
  id=d.substr(d.indexOf('ok')+2);     
  $("#list").after('<div class="panel panel-default" id="row'+id+'"><div class="panel-heading"><h3 class="panel-title">\
  '+nick+getlocaltime()+' <code></code> <small class=" pull-right"> \
  <a class="glyphicon glyphicon-hand-up" onclick="vote({tbl:blog_comments,do:up,id:+id+},this);" title="赞">(0)</a>  &nbsp; \
<a class="glyphicon glyphicon-share-alt" onclick="vote({tbl:blog_comments,do:share,id:+id+},this);" title="转发">(0)</a> &nbsp;  \
<a class="glyphicon glyphicon-comment" onclick="comment('+id+',0)" title="评论">(0)</a> &nbsp;    \
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
  <a onclick="comment('+d1[i].bcid+','+d1[i].id+','+d1[i].truename+')" >回复</a> </code></small></p>');
     }
  });     
}  

//收藏博客操作
function fav(id){
  if(ouid==""){
      var url = location.pathname+location.search;
      window.location.href="/?t=login&url="+$.base64.encode(url);
      return false;
  }else{
    if(Cookies.get("zone_fav"+id)!=undefined)return;
    $('#dlg2').modal('show');
  }
}

function savefav(){
   var id = $("#fav_blog_id").val();
   var type = $("#fav_type").val();
   $.post("./srv/sdo.php",{"tpl":"save_fav","id":id,type:type,"url":window.location.href,"unick":nick}, function (data, e) { 
  	  if(data.indexOf('ok')>-1){
        if(Cookies.get("zone_fav"+id)!=undefined)return;
        Cookies.set("zone_fav"+id,id,{ expires: 43200 });
        alert(data.substr(2)); 
      }else{
        alert(data); 
      }
      $('#dlg2').modal('hide');	
   });
}
