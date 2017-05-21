
<div class="container top"> 
   <div class="row">
      <img class="top-bg" src="./img/logo_bg.png">
      <div class="col-xs-12"> 
        <div class="top-face">
          <img class="top-face-img" src="/upd/face/{zid}.jpg" onerror="this.src='./img/logo.jpg';" alt="{name}">
        </div>
        <div class="top-name" alt="{truename}">
          <h3>{name}<small>{explain}</small></h3>
        </div>
        <div class="top-nav">
          <ul class="nav navbar-nav">
            <li><a href="./?t=index&id={zid}">首页</a></li>
            <li><a href="./?t=weibo&id={zid}">微博</a></li>
            <li><a href="./?t=bloga&id={zid}">博客</a></li>
            <li><a href="./?t=album&id={zid}">相册</a></li> 
            <li><a href="./?t=board&id={zid}">留言板</a></li>
          </ul>
        </div>
      </div>
   </div>
</div>

<div class="col-xs-3 pull-right" id="navbar-float">
    <ul class="nav nav-tabs nav-stacked" id="navbar-zone">
    <li><a href="/zone/?id={uid}" title="个人空间"><img src="/zone/img/zone.png" /></a></li>
    <li class="change_zone"></li>
    <li><a href="/user" title="账号中心"><img src="/zone/img/account.png" /></a></li>
    <li><a href="/msg" title="消息中心"><img src="/zone/img/user_msg.png" /></a></li>
    </ul>
</div>
