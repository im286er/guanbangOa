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

function addCook(k,v){
    var ck = $.cookie(k);
    if (ck == null || ck.length == 0) {       
        $.cookie(k, v, { expires: 1, path: '/' });
    }
    else {  
        if (ck.length> 500) {
            ck = ck.substring(0,500);
        }
        s = ck+','+ v;
        $.cookie(k,s, { expires: 1, path: '/' });
    }
}
function chkCook(k,v){
    var ck= $.cookie(k);
    if(ck==null||ck.length==0)
        return false;
    else{
      if(ck.indexOf(v)>-1)
        return true;
      else
        return false;
    } 
}
function see(d){
 if(chkCook(d.tbl+'see',d.id))return;//+d.id
 d.tpl="see"; 
  $.post("./srv/vote.php", d, function (d1, e) {                 
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	 
	  if(d.callback!=undefined&&d.callback!="")window[d.callback](d1);	
  });
  addCook(d.tbl+'see',d.id);
}

function renums(d){ 
 d.tpl="replay"; 
  $.post("./srv/vote.php", d, function (d1, e) {                 
      if(d.showok!=undefined&&d.showok!="")alert(d.showok);	 
	     if(d.callback!=undefined&&d.callback!="")window[d.callback](d1);	
  }); 
}
 
 /**‘ÿ»Îwpan*/
function treeloadchild(o,pid){  
  $.post("./srv/pan.php", {"t":"get_dir","id":pid}, function (d1, e) {                 
     r=JSON.parse(d1);
        space='';       
        for(i=0;i<$(o).index()+1;i++)
          space+='<span class="tree-indent"></span>'; 
        for(k=0;k<r.length;k++){
          if(r[k].ftype==1)
            addItemFolder(o,r[k].id,r[k].name,space,false,r[k].md5,r[k].size);
          else
            addItemNode(o,r[k].id,r[k].name,space,r[k].md5,r[k].size);
        }
  }); 
}


function treeliseled(o){
   if(treeseled)
     treeseled.parent().removeClass("tree-node-selected");
   treeseled=$(o);
   treeseled.parent().addClass("tree-node-selected");
   /*if(treeseled.parent().data("data")!=""){
      $("#from1").attr("action","t.php?t="+treeseled.parent().data("data")+"&htm="+treeseled.parent().data("data1"));
      $('#from1').submit();
   } */  
} 