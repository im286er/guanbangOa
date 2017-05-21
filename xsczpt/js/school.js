//list init
$(function(){
	list_init();
});
function list_init(){       
    $.getJSON("/data/list.arr.txt",function(d){        
      showData2Page(d); 
      showCurSeleted(); 
      getChildData();//读取二级选择  
      //chgLabelTxt();//更改label中的标签内容    
  });
  qso=queryStr("so");	
  $("#so").val(qso);       
}
function SO(){
 // if($("#so").val()==""){alert("请输入查询条件");return;}
  setQry(qso=$("#so").val());
}
/*/更改list中的标签内容
function chgLabelTxt(){
  if(qgrade!="0"){$("grade").text($("#grade a[name='"+qgrade+"']").text());}
  else{
    $("grade").each(function(a,b){
       $(b).text($("#grade a[name='"+$(b).attr("v")+"']").text());
    });
  }
  if(qedition!="0"){$("edition").text($("#edition a[name='"+qedition+"']").text());}
  else{
    $("edition").each(function(a,b){
       $(b).text($("#edition a[name='"+$(b).attr("v")+"']").text());
    });
  }
  if(qtype!="0"){$("type").text($("#type a[name='"+qtype+"']").text());}
  else{
    $("type").each(function(a,b){
       $(b).text($("#type a[name='"+$(b).attr("v")+"']").text());
    });  
  }
  $("size").each(function(a,b){
     $(b).text(formatSize($(b).attr("v")));
  });
}  */
//读取二级选项
function getChildData(){
  if(qaddr!="0"){
    $.post("./srv/rdo.php?", {tpl:"getaddr2","id":qaddr}, function (d, e) {         
        d1=JSON.parse(d);
        $("#addr1").html("<a name=0>街道：</a>");
        if(d1.length>0){           
          for(i=0;i<d1.length;i++){
            $("#addr1").append(' <a name="'+d1[i].id+'">'+d1[i].name+'</a> &nbsp;');
          }
          //event
          $("#addr1 a").bind("click",function(a,b){setQry(qaddr1=$(this).attr("name"));});
          if(qaddr1!="0"){
              $("#addr1 a[name='"+qaddr1+"']").addClass("active"); 
              $("#sed").append('<code>'+$("#addr1 a[name='"+qaddr1+"']").text()+' <i class="glyphicon glyphicon-remove" onclick="setQry(qaddr1=0)"/></code> &nbsp;');
          }
        }
    })
  }
}
//设置选择的项
var qperiod,qorgtype,qaddr,qaddr1,qso;
function showCurSeleted(){ 
  qperiod=queryStr("per");
  qorgtype=queryStr("ot");
  qaddr=queryStr("a");
  qaddr1=queryStr("a1");

  if(qperiod=="")qperiod="0";   
  if(qorgtype=="")qorgtype="0";
  if(qaddr=="")qaddr="0";
  if(qaddr1=="")qaddr1="0"; 
 
   if(qorgtype!="0"){$("#orgtype a[name='"+qorgtype+"']").addClass("active"); 
  $("#sed").append('<code>'+$("#orgtype a[name='"+qorgtype+"']").text()+' <i class="glyphicon glyphicon-remove" onclick="setQry(qorgtype=0)"/></code> &nbsp;');}
  if(qperiod!="0"){$("#period a[name='"+qperiod+"']").addClass("active");
    $("#sed").append('<code>'+$("#period a[name='"+qperiod+"']").text()+' <i class="glyphicon glyphicon-remove" onclick="setQry(qperiod=0)"/></code> &nbsp;');}
  if(qaddr!="0"){$("#addr a[name='"+qaddr+"']").addClass("active");
    $("#sed").append('<code>'+$("#addr a[name='"+qaddr+"']").text()+' <i class="glyphicon glyphicon-remove" onclick="setQry(qaddr=0)"/></code> &nbsp;');}
  if(qaddr=="0"){$("#addr1").css("display","none");}
} 
//显示筛选项目
function showData2Page(d){    
  $("#orgtype").html("<a name=0>机构类型：</a>");
  for(i=0;i<d.orgtype.length;i++){
    $("#orgtype").append(' <a name="'+d.orgtype[i].id+'">'+d.orgtype[i].name+'</a> &nbsp;');
  }
  $("#period").html("<a name=0>学段：</a>");
  for(i=0;i<d.period.length;i++){
    $("#period").append(' <a name="'+d.period[i].id+'">'+d.period[i].name+'</a> &nbsp;');
  }
  $("#addr").html("<a name=0>地区：</a>");
  for(i=0;i<d.addr.length;i++){
    $("#addr").append(' <a name="'+d.addr[i].id+'">'+d.addr[i].name+'</a> &nbsp;');
  }

  $("#period a").bind("click",function(a,b){setQry(qperiod=$(this).attr("name"));});
  $("#orgtype a").bind("click",function(a,b){setQry(qorgtype=$(this).attr("name"));});
  $("#addr a").bind("click",function(a,b){qaddr1=0;setQry(qaddr=$(this).attr("name"));});

}
//添加选择条件
function setQry(v){
  qry="&per="+qperiod+"&ot="+qorgtype+"&a="+qaddr+"&a1="+qaddr1+"&so="+qso;
  toUrl(qry);
} 
 
function toUrl(v){
  location.href="?t="+queryStr("t")+v;
}
      
function formatSize(v){           
  if(v>1048576000)
      return (v/1048576000).toFixed(2)+"GB";
    if(v>1048576)
      return (v/1048576).toFixed(2)+"MB";
    if(v>1024)
      return (v/1024).toFixed(2)+"KB";
    else
     return v+"B";  
}