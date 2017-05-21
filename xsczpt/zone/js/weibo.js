function share(d,obj){
  if(uid==""){
      var url = location.pathname+location.search;
      window.location.href="/?t=login&url="+$.base64.encode(url);
      return false;
  }
  d.tpl="weibo_reship";
  if(Cookies.get(d.tbl+d.do+d.id)!=undefined)return;
  if(uid!='')Cookies.set(d.tbl+d.do+d.id,d.id,{ expires: 43200 }); 
  $.post("/zone/srv/sdo.php",d, function (data, e) {
      alert(data);
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	
      if(obj!=undefined){  
         v=$(obj).text().replace(/[^0-9]/ig, "");
         if(v=="")v="1";else v= parseInt(v)+1;            
         $(obj).text("（"+v+"）");
      }                
	    if(d.callback!=undefined&&d.callback!="")window[d.callback](data);	
  });
}
function comment(vid,rid,rnick){
  if(uid==""){
      var url = location.pathname+location.search;
      window.location.href="/?t=login&url="+$.base64.encode(url);
      return false;
  }
  $('#dlg1 #wid').val(vid);
  $('#dlg1 #reid').val(rid);
  if(rid=="0"){rnick="";$("#redo").val('');}
  else{$("#redo").val('回复');}    
  $('#dlg1 #renick').val(rnick);
  $('#dlg1').modal('show');
}  
function savcomment(){
  if(limit_comment!=0){alert("您没有回复权限");return;} 
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
  $("#pb"+wid+" .well").prepend('<p><a>'+mynick+'</a>'+res+' ：'+$("#form1 #des").val()+'<small class="pull-right"><code>'+getlocaltime()+'</code></small></p>');
  $("#pb"+wid+" .well").removeClass("hide");   
}
 
function load_comments(v,obj){  
  //id=d.substr(d.indexOf('ok')+2); 
  page=$("#more"+v).data("page");
  npage=parseInt(page)+1;    
  $("#more"+v).data("page",npage);
  $.post("./srv/rdo.php", {tpl:"get_weibo_comments_next","id":v,"page":npage}, function (d, e) { 
    d1=JSON.parse(d);
     for(i=0;i<d1.length;i++){
        $(obj).parent().before('<p><a>'+d1[i].nick+'</a> '+isNulls(d1[i].redo)+' <a>'+isNulls(d1[i].renick)+'</a>：\
   '+d1[i].des+'<small class="pull-right"><code>'+d1[i].created+' \
  <a onclick="comment('+d1[i].wid+','+d1[i].id+','+mynick+')" >回复</a> <a onclick="del_blog_comment('+d1[i].id+',this)">删除</a></code></small></p>');
     }
  });     
}  
     