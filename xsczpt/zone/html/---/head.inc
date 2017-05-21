<div class="row" style="background-image:url(img/bg.jpg);">
<div class="col-sm-1">
  <img src="/upd/face/{zid}.jpg"  onerror="this.src='/error/face.jpg';" alt="{nick}" style="width:100px;height:100px;">      
</div>
<div class="col-sm-9">
<div class="top-name" alt="{truename}">
<h3>{name}<small>{explain}</small> </h3>
</div>
<div class="row"> </div>
</div>
</div>


<nav class="navbar navbar-inverse" role="navigation">
   <div class="navbar-header">
       <a class="navbar-brand" class="class1">ZONE</a>
   </div>
   <div>
      <ul class="nav navbar-nav">           
            <li><a href="./?t=index&id={zid}">首页</a></li>
            <li><a href="./?t=weibo&id={zid}">微博</a></li>
            <li><a href="./?t=bloga&id={zid}">博客</a></li>
            <li><a href="./?t=album&id={zid}">相册</a></li> 
            <li><a href="./?t=board&id={zid}">留言板</a></li>
            <li class="hide"><a href="./?t=fav&id={zid}">收藏夹</a></li>
            <li class="set-space hide"><a href="./?t=set&id={zid}">设置</a></li>
            <li class="set-mould hide"><a href="" data-toggle="modal" data-target="#setModal">模板设置</a></li>
      </ul>
   </div>     
</nav>