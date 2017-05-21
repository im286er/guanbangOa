/**新微博首页+列表页*/
//评论弹出框

var renick='';
function comment(wid,rid,rnick){
  var com = "replay('"+wid+"','"+rid+"','"+rnick+"')";
	$(".comment_btn_"+wid).attr("onclick",com);
    $("#p"+wid).show();
    $(".type-show").remove();
    
    switch (limit_comment){
          case "0":
            if(rid!=0){
              renick = '回复'+rnick+':'; 
            }else{
              renick = '';
            }
            $("#re"+wid).val(renick);
            break; 
          case "2":
            $.post("/zone/srv/sdo.php?", {tpl:"check_urd","id":$("#p"+wid).attr("uid")}, function (da, e) {
              if($(".type-show").length==0){
                if(da == 0){
                  $("#p"+wid).prepend('<div class="type-show" style="position: relative;height: 30px;line-height: 30px;">发布者只允许自己评论</div>')
                }
              }
            });
            break;
          case "3":
            if($(".type-show").length==0){
              $("#p"+wid).prepend('<div class="type-show" style="position: relative;height: 30px;line-height: 30px;">发布者禁止任何人评论</div>')
            }
            break;
     } 
}

//评论回复
function replay(wid,rid,rnick){
   if(uid==""){
      var url = location.pathname+location.search;
      window.location.href="/?t=login&url="+$.base64.encode(url);
      return false;
   }
   var des = $("#re"+wid).val();
   des = des.replace(renick,"");
   if(rid=="0"){rnick=""; redo = "";}
   else{redo = '回复';}
   $.post("/zone/srv/sdo.php?", {tpl:"weibo_comment","wid":wid,"des":des,"reid":rid,"redo":redo,"renick":rnick}, function (da, e) {
      if(da.indexOf("ok")>-1){
        $("#re"+wid).val("");
        var length = da.length;
        id = da.substring(2,length);
        $.post("/zone/srv/sdo.php?", {tpl:"get_comment","id":id}, function (da, e) {
           da=JSON.parse(da);
           var str = '<div class="comm-box-body" id="pc'+da[0].id+'">'+ 
                          '<div class="comm-act"><img src="/upd/face/'+da[0].uid+'.jpg" width="30" height="30" onerror=this.src="/error/face.jpg";></div>'+ 
                          '<div class="comm-con">'+
                            '<div style="word-break:break-all; word-wrap:break-word;"><a> '+da[0].nick+' </a> '+da[0].redo+' <a>'+da[0].renick+' </a> ：'+da[0].des+
                            "&nbsp;<a style='top:2px;right:5px;' class='glyphicon glyphicon-comment' onclick=comment('"+da[0].wid+"','"+da[0].id+"','"+da[0].nick+"') ></a></div>"+
                            "<small class=mes_tit><code style=padding:0;><span>"+da[0].ncreated+"</span> </code>"+
                            '</small></div></div>'; 
           $("#pa"+wid).prepend(str);
           if(isme==1){$(".isshow").show();}else{$(".isshow").hide();}
        });
      }
   }); 
}

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

function load_comments(v,obj){
  //id=d.substr(d.indexOf('ok')+2); 
  page=$("#more"+v).data("page");
  npage=parseInt(page)+1;
  $("#more"+v).data("page",npage);
  $.post("./srv/rdo.php", {tpl:"get_weibo_comments_next","id":v,"page":npage}, function (d, e) { 
     da=JSON.parse(d);
     for(i=0;i<da.length;i++){
        var str = '<div class="comm-box-body" id="pc'+da[i].id+'">'+ 
                          '<div class="comm-act"><img src="/upd/face/'+da[i].uid+'.jpg" width="30" height="30" onerror=this.src="/error/face.jpg";></div>'+ 
                          '<div class="comm-con">'+
                            '<div style="word-break:break-all; word-wrap:break-word;"><a> '+da[i].nick+' </a> '+da[i].nredo+' <a>'+da[i].nrenick+' </a> ：'+da[i].des+
                            "&nbsp;<a style='top:2px;right:5px;' class='glyphicon glyphicon-comment' onclick=comment('"+da[i].wid+"','"+da[i].id+"','"+da[i].nick+"') ></a></div>"+
                            "<small class=mes_tit><code style=padding:0;><span>"+da[i].ncreated+"</span> </code>"+
                            '</small></div></div>';
        $(obj).parent().before(str);
        if(isme==1){$(".isshow").show();}else{$(".isshow").hide();}
     }
  });     
} 


/*时间戳转换*/
function getTime() {  
    var ts = arguments[0] || 0;  
    var t,y,m,d,h,i,s;
    ts = (ts.length==10) ? (ts*1000) : ts;  
    t = ts ? new Date(ts) : new Date();  
    y = t.getFullYear();  
    m = t.getMonth()+1;  
    d = t.getDate();  
    h = t.getHours();  
    i = t.getMinutes();  
    s = t.getSeconds();  
    return y+'-'+(m<10?'0'+m:m)+'-'+(d<10?'0'+d:d)+' '+(h<10?'0'+h:h)+':'+(i<10?'0'+i:i)+':'+(s<10?'0'+s:s);  
}



        