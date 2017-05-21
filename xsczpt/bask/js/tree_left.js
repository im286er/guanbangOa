/**
 * 
 *
 */ 
/**‘ÿ»Î ˜*/
function treeloadchild(o,pid){ 
    $.post("./srv/treedo.php?" + Math.random(), {tpl:"get_dir",id:pid}, function (d, e) { 
        r=eval(d);
        space='';       
        for(i=0;i<$(o).index()+1;i++)
          space+='<span class="tree-indent"></span>'; 
        for(k=0;k<r.length;k++){
          if(r[k].childnums>0)
            addItemFolder(o,r[k].id,r[k].name,space,false,r[k].tpl,r[k].ishtm);
          else
            addItemNode(o,r[k].id,r[k].name,space,r[k].tpl,r[k].ishtm);
        }
    });
}


function treeliseled(o){
   if(treeseled)
     treeseled.parent().removeClass("tree-node-selected");
   treeseled=$(o);
   treeseled.parent().addClass("tree-node-selected");
   if(treeseled.parent().data("data")!=""){
      $("#from1").attr("action","t.php?t="+treeseled.parent().data("data")+"&htm="+treeseled.parent().data("data1"));
      $('#from1').submit();
   }   
} 