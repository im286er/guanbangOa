
<div class="col-sm-12" style="margin-top:10px;">
<div class="col-sm-1"><a href="/user" class="thumbnail" style="width:60px;"><img src="/upd/face/{uid}.jpg"  /></a></div>
<div class="col-sm-8"><h4> {truename} <small>昵称：{nick} 单位：<a href="work.php?t=school"><hsch v="{school}"></hsch></a>
<br/> <code> 微博{weibo} 博客{blog}  照片{photo} <a href="/msg">消息{msg}</a></code>    
</small> </h4></div> 
</div>
<div class="clearfix"></div>
 
<nav class="navbar navbar-default " role="navigation">  
   <div class="navbar-header"><a class="navbar-brand" href="./">首页</a></div>
   <div>          
      <ul class="nav navbar-nav navbar-left">
         <li><a href="./?t=blogs" class="fa fa-street-view"> 个人空间</a></li>
         <li><a href="./edu.php?t=courseware" class="fa fa-leanpub"> 教学空间</a></li>
         <li><a href="./work.php?t=schedule" class="fa fa-laptop"> 办公空间</a></li>       
         <li><a href="./oth.php?t=applist" class="fa fa-th-large"> 应用中心</a></li>    
         <li class="dropdown"><a href="#" class="dropdown-toggle fa fa-gears" data-toggle="dropdown"> 组织机构<b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="work.php?t=school" class="fa fa-building-o"> 我的学校</a></li>
              <li><a href="work.php?t=class" class="fa fa-circle-o-notch"> 我的班级</a> </li>
              <li><a href="work.php?t=group" class="fa fa-group"> 我的群组</a></li>
              <li><a href="work.php?t=famous" class="fa fa-graduation-cap"> 我的工作室</a> </li>  
              <li><a href="/subjecter/man/" class="fa fa-graduation-cap">学科管理</a> </li>
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