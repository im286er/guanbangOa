
<div class="col-sm-12" style="margin-top:10px;">
<div class="col-sm-1"><a href="/user" class="thumbnail" style="width:60px;"><img src="/upd/face/{uid}.jpg"  /></a></div>
<div class="col-sm-8"><h4> {truename} <small>�ǳƣ�{nick} ��λ��<a href="work.php?t=school"><hsch v="{school}"></hsch></a>
<br/> <code> ΢��{weibo} ����{blog}  ��Ƭ{photo} <a href="/msg">��Ϣ{msg}</a></code>    
</small> </h4></div> 
</div>
<div class="clearfix"></div>
 
<nav class="navbar navbar-default " role="navigation">  
   <div class="navbar-header"><a class="navbar-brand" href="./">��ҳ</a></div>
   <div>          
      <ul class="nav navbar-nav navbar-left">
         <li><a href="./?t=blogs" class="fa fa-street-view"> ���˿ռ�</a></li>
         <li><a href="./edu.php?t=courseware" class="fa fa-leanpub"> ��ѧ�ռ�</a></li>
         <li><a href="./work.php?t=schedule" class="fa fa-laptop"> �칫�ռ�</a></li>       
         <li><a href="./oth.php?t=applist" class="fa fa-th-large"> Ӧ������</a></li>    
         <li class="dropdown"><a href="#" class="dropdown-toggle fa fa-gears" data-toggle="dropdown"> ��֯����<b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="work.php?t=school" class="fa fa-building-o"> �ҵ�ѧУ</a></li>
              <li><a href="work.php?t=class" class="fa fa-circle-o-notch"> �ҵİ༶</a> </li>
              <li><a href="work.php?t=group" class="fa fa-group"> �ҵ�Ⱥ��</a></li>
              <li><a href="work.php?t=famous" class="fa fa-graduation-cap"> �ҵĹ�����</a> </li>  
              <li><a href="/subjecter/man/" class="fa fa-graduation-cap">ѧ�ƹ���</a> </li>
            </ul>                                                                                 
         </li>
         
      </ul>
   </div>
</nav>
  

     
<div class="col-sm-2  navbar-default">   
<div class="row">  
{menu}    


</div>  
</div>

 
<script>
$(function(){
  $.get("/data/id.php?t=school&v={school}",function(d){$("hsch").text(d)});
})
</script>