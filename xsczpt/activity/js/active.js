//active page js operation
$(function(){ 	
    bind_evt();
    $.post("./srv/rdo.php", {tpl:"get_in_active","id":queryStr("id")}, function (d, e) {
        my_in_active=$.trim(d);
   });  
});
var my_in_active="0";
function list_init(){       
   /*$.getJSON("/data/list.arr.txt",function(d){        
      showData2Page(d); 
      showCurSeleted(); 
      getChildData();//��ȡ����ѡ��  
      //chgLabelTxt();//����label�еı�ǩ����    
  });
  qso=queryStr("so");	
  $("#so").val(qso);  */     
}
function bind_evt(){
$("#enroll").bind("click",doenroll);// 
$("#comment").bind("click",docomment); 
$("#btnp").bind("click",dophoto); 
$("#btnart").bind("click",doart);  
$("#btnret").bind("click",doret); 
} 

//art
function doart(){ 
  if(my_in_active=="0"){alert("�ǻ��Ա���޷�����");return;}
  $('#dlgart .modal-title').text('��ĵ�');
  $('#dlgart').data("flag","1");
  $('#dlgart').modal('show');
} 
function doret(){ 
  if(my_in_active=="0"){alert("�ǻ��Ա���޷�����");return;}
  $('#dlgart .modal-title').text('��ܽ�');
  $('#dlgart').data("flag","2");
  $('#dlgart').modal('show');
} 
function savart(){  
   if($('#dlgart').data("flag")=="1")
      tp="active_thing";
   else
      tp="active_summary";
   if($("#fromart #name").val()==""){alert("���������");return;}
   if(um.getContentTxt()==""){alert("����������");return;}
   SaveAM({"tpl":tp,"do":"a",id:"0",frmid:"#fromart",showok:"�����ɹ�",callback:"doartrun"});
} 
function doartrun(ed,data){ 
   if(data.indexOf('ok')>-1){     
      rid=data.substr(data.indexOf('ok')+2);  
      $('#dlgart').modal('hide');  
      if($('#dlgart').data("flag")=="1"){                    
        str='<tr id="art'+rid+'"><td><a href="./?t=active_art&id='+rid+'" target=_blank>'+$('#dlgart #name').val()+'</a></td> \
  <td><code>'+getlocaltime()+''+nick+'<a onclick="del_art('+rid+',this)">ɾ��</a></code></td></tr>';
       $("#arts").prepend(str);
     }else{
        str='<tr id="ret'+rid+'"><td><a href="./?t=active_summary&id='+rid+'" target=_blank>'+$('#dlgart #name').val()+'</a></td> \
  <td><code>'+getlocaltime()+''+nick+'<a onclick="del_ret('+rid+',this)">ɾ��</a></code></td></tr>';
       $("#rets").prepend(str);
     }
   }
   else
      alert(data);
}
function del_art(v,obj){
    $.post("./srv/active.php", {tpl:"del_art","id":v,aid:queryStr("id")}, function (d, e) {
       if(d.indexOf("ok")>-1){          
          $('#art'+v).remove(); 
       }
       else alert(d);
   });  
}
function del_ret(v,obj){
    $.post("./srv/active.php", {tpl:"del_ret","id":v,aid:queryStr("id")}, function (d, e) {
       if(d.indexOf("ok")>-1){          
          $('#ret'+v).remove(); 
       }
       else alert(d);
   });  
}
//photo
function dophoto(){ 
  if(my_in_active=="0"){alert("�ǻ��Ա���޷�����");return;}
   $('#dlgp').modal('show');
}
function addphoto2(fn){     
   $.post("./srv/active.php", {tpl:"add_photo","id":queryStr("id"),n:fn}, function (d, e) {
        
   });       
}

function doenroll(e){     
   $.post("./srv/active.php", {tpl:"enroll","id":queryStr("id")}, function (d, e) {
    if(d.indexOf("err")>-1)
      alert(d.substr(d.indexOf("err")+3));
    else
       alert(d);
   });       
}

//����
function docomment(){
  if(my_in_active=="0"){alert("�ǻ��Ա���޷�����");return;}
   $('#dlgc').modal('show');
}
function savcomment(){
  if($("#dlgc #des").val().length<5){alert("����̫��");return;}
  if($("#dlgc #des").val().length>250){alert("����̫��"+$("#dlgc #des").val().length);return;}
  $.post("./srv/active.php", {tpl:"comment","id":queryStr("id"),"data":$("#dlgc #des").val()}, function (d, e) {         
    
       if(d.indexOf("ok")>-1){
          alert("���۳ɹ�");
          $('#dlgc').modal('hide'); 
          v=d.substr(d.indexOf("ok")+2);
          str='<p id="comment'+v+'"><a>'+nick+'</a> �� '+$('#dlgc #des').val()+'<small class="pull-right"><code> \
          '+getlocaltime()+'   <a onclick="del_comment('+v+',this)">ɾ��</a></code></small></p>';
          $("#comments").prepend(str);
       }
       else alert(d);
              
   });    
} 
function del_comment(v,obj){     
   $.post("./srv/active.php", {tpl:"del_comment","id":v,aid:queryStr("id")}, function (d, e) {
       if(d.indexOf("ok")>-1){          
          $('#comment'+v).remove(); 
       }
       else alert(d);
   });       
}     

