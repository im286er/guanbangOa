function vote(d){ //tpl,do,showok,callback,id,tb,col
  d.tbl="vote";
  if(Cookies.get(d.tbl+d.do+d.id)!=undefined)return;
  Cookies.set(d.tbl+d.do+d.id,d.id,{ expires: 43200 });   
  $.post("./srv/vote.php",d, function (data, e) {                 
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	 
	    if(d.callback!=undefined&&d.callback!="")window[d.callback](data);	
  });
}