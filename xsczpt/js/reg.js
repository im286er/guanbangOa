 function regcheck(e){
    if($("#username").val()==""){alert("用户名不能为空");return;}
    if($("#truename").val()==""){alert("姓名不能为空");return;}
    if($("#mobile").val()==""){alert("手机号不能为空");return;}
    if($("#email").val()==""){alert("邮箱不能为空");return;}
    if($("#pwd").val()==""){alert("密码不能为空");return;}
    if($("#pwd").val()!=$("#pwd1").val()){alert("两次密码不相同");return;} 
    $.post("./srv/rpr.php?", {"do":"chkreg","u":$("#username").val(),"t":$("#truename").val(),"m":$("#mobile").val(),"e":$("#email").val()}, function (d, e) {                  
        if(d.indexOf('ok')>-1){
            $("#step1").css("display","none");
            $("#step2").css("display","block");
        }  
        else alert(d);             
    });   
}
function regsend(e) { 
    if($("#school").val()==""){alert("请选择一所学校");return;}   
    $("#id").val('');        
    SaveAM({tpl:"user_reg","do":"a",id:"0",frmid:"#form1",showok:"",callback:"regendrun"}); 
} 
function regendrun(e,data){
   if(data.indexOf('ok')>-1){     
      rid=data.substr(data.indexOf('ok')+2);
      $("#id").val(rid);
      //SaveAM({tpl:"user_reg1","do":"am","id":rid,frmid:"#form1",showok:"",callback:""}); 
      $.post("./srv/sdo.php", {tpl:"set_school","id":rid,sid:$("#school").val()}, function (d, e) {        
         $("#showname").text($("#username").val());
        $("#showmail").text($("#email").val());
        $("#showmob").text($("#mobile").val());
        $('#dlg1').modal('show'); 
      });       
   }
   else
      alert(data);
}
/* 
function getaddr2(v){ 
  $.post("./srv/rdo.php?", {tpl:"getaddr2","id":v}, function (d, e) {                  
    selectShowData('#addr2', eval(d), "id", "name");                
  });
} 
function getunits(v){  
  $("#addr").val($("#addr2").val());
  $.post("./srv/rdo.php", {tpl:"get_school","id":v}, function (d, e) {                  
    selectShowData('#school', eval(d), "id", "name");            
  });
} */
function selsch(id,obj){
 $('#dlg2').modal('hide');
 $("#school").val(id);
 $("#schools").val($(obj).text());
 $.get("/data/id.php?t=school&col=period&v="+id,function(d){
    $.get("/data?t=sys_grade&f=txt&pn=period&pv="+d,function(o){$("#grade").html(o);});  
    $.get("/data?t=sys_subject&f=txt&pn=period&pv="+d,function(o){$("#subject").html(o);});  
 })

}