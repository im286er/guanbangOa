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
    $.post("./srv/rdo.php?", {tpl:"getsub","id":qaddr}, function (d, e) {         
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
  qsub=queryStr("sub");

  if(qperiod=="")qperiod="0";   
  if(qorgtype=="")qorgtype="0";
  if(qaddr=="")qaddr="0";
  if(qaddr1=="")qaddr1="0";
  if(qsub=="")qsub="0"; 
 
  if(qorgtype!="0"){$("#orgtype option[name='"+qorgtype+"']").attr("selected","selected");} 
  if(qperiod!="0"){$("#period option[name='"+qperiod+"']").attr("selected","selected");}
  if(qaddr!="0"){$("#addr option[name='"+qaddr+"']").attr("selected","selected");}
  if(qsub!="0"){$("#sub option[name='"+qaddr+"']").attr("selected","selected");}
  if(qaddr=="0"){$("#addr1").css("display","none");}
} 
//显示筛选项目
function showData2Page(d){    
  $("#orgtype").html("<option name=0 value=0>机构类型</option>");
  for(i=0;i<d.orgtype.length;i++){
    $("#orgtype").append(' <option name="'+d.orgtype[i].id+'" value="'+d.orgtype[i].id+'">'+d.orgtype[i].name+'</option>');
  }
  $("#period").html("<option name=0 value=0 >学段</option>");
  for(i=0;i<d.period.length;i++){
    $("#period").append(' <option name="'+d.period[i].id+'" value="'+d.period[i].id+'">'+d.period[i].name+'</option>');
  }
  $("#addr").html("<option name=0 value=0>地区</option>");
  for(i=0;i<d.addr.length;i++){
    $("#addr").append('<option name="'+d.addr[i].id+'" value="'+d.addr[i].id+'">'+d.addr[i].name+'</option>');
  }
  $("#sub").html("<option name=0 value=0 >学科 </option>");

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