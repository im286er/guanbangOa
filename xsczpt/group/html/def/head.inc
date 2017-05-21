<h2><img src="/upds/group/logo/{g}.jpg" width="100" height="100" onerror="this.src='/error/logo.jpg';"/>{name}
  <small>{slogan}</small>
</h2>

<nav class="navbar navbar-inverse" role="navigation">
   <div class="navbar-header">
      <a class="navbar-brand" onfocus="this.blur();" href="./?g={g}" class="active">主页</a>
   </div>
   <div>
      <ul class="nav navbar-nav">           
         <li> <a href="/group?t=forum&g={g}">群论坛</a> </li>
         <li><a  href="/group?t=album&g={g}">群相册</a></li>
         <li class="hide"><a href="/group?t=file&g={g}">群文件</a>   </li>
         <li><a  href="/group?t=member&g={g}">群成员</a>   </li>
         <li><a  href="/group/man?g={g}" target=_blank>[管理]</a> </li>
      
      </ul>
      <ul class="nav navbar-nav navbar-right" style="right:15px;position:relative;">
	       <li><a href="/school?s={school}">学校</a></li> 
		    <li><a href="/subject?s={school}">学科</a></li>
      </ul>
   </div>
</nav>