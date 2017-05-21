//Õ∂∆±new
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
} 