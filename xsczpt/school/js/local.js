//Í¶Æ±new
/*function vote(d){ //tpl,do,showok,callback,id,tb,col
  d.tbl="vote";
  if(Cookies.get(d.tb+d.do+d.id)!=undefined)return;//ÒÑÍ¶
  Cookies.set(d.tb+d.do+d.id,d.id,{ expires: 43200 });   
  $.post("./srv/vote.php",d, function (data, e) {                 
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	 
	    if(d.callback!=undefined&&d.callback!="")window[d.callback](data);	
  });
}
*/
function vote(d,obj){ //tpl,do,showok,callback,id,tb,col        
  d.tpl="vote";
  if(Cookies.get(d.tbl+d.id)!=undefined)return;
  Cookies.set(d.tbl+d.id,d.id,{ expires: 43200 });   
  $.post("./srv/vote.php",d, function (data, e) {       
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	
      if(obj!=undefined){  
         v=$(obj).text().replace(/[^0-9]/ig, "");
         if(v=="")v="1";else v= parseInt(v)+1;            
         $(obj).text(v);
      }                
	    if(d.callback!=undefined&&d.callback!="")window[d.callback](data);	
  });
}