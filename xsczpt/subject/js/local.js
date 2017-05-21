// 评论框字数限定
$(document).ready(function(){
	var limitNum = 300;
	var pattern = '还可以输入' + limitNum + '字';
	
	$('#wordage').html(pattern);
	$('#wordtext').keyup(
		function(){
			var remain = $(this).val().length;
			if(remain > 300) {
				pattern = "字数超过300个限制！";
				$(this).val($(this).val().substr(0, 300));
			}else{
				var result = limitNum - remain;
				pattern = '还可以输入' + result + '字';
			}
			$('#wordage').html(pattern);
		}
	);
});

function goscroll() {
	var url = window.location.toString();//进这个页面的url
	var id = url.split('#')[1];//url例如： www.baidu.com#maodian(这个是你锚点的位置)
	if(id){
		var t = $('#'+id).offset().top;
		$(window).scrollTop(t);//滚动到锚点位置
	}
	//var childHash = parent.location.hash;//这里childHash =="#hot"
	//if(childHash!=""){
	//	location.hash = childHash.substring(1);
	//}
}

// 清空input type="file"的选择状态
function resetInputFile(id) {
	var obj = document.getElementById(id);
	obj.outerHTML = obj.outerHTML;
}

function doLiSelect(obj) {
	var cur = $(obj);
	var node = $(obj).parent().parent().parent().children().first();
	node.html(cur.html().replaceAll("------", ""));
	node.val(cur.attr("value"));
}

// 去掉所有的html标记
function delHtmlTag(str){
	return Regex.Replace(str, "<[^>]*>", "");
}

// 增加浏览次数
function vote(d,obj){
	if(Cookies.get(d.tbl+d.id)!=undefined)return;
	Cookies.set(d.tbl+d.id,d.id,{ expires: 43200 });
	$.post("./srv/sdo.php",d, function (data, e) {
		if(d.showok!=undefined&&d.showok!="")alert(d.showok);
		if(obj!=undefined){
			v=$(obj).text().replace(/[^0-9]/ig, "");
			if(v=="")v="1";else v= parseInt(v)+1;
			$(obj).text(v);
		}
		if(d.callback!=undefined&&d.callback!="")window[d.callback](data);
	});
}

// 评论 鲜花use 鸡蛋nouse 更新
function douse(id, state){
	var tplname = state ? "add_use" : "add_nouse";
	var chgid = state ? ("#use" + id) : ("#nouse" + id);
	
	if(Cookies.get("douse" + id)!=undefined) { alert("这条评论您已经投过票了！");return; }
	Cookies.set("douse" + id, id, { expires: 43200 });
	
	$.post("./srv/sdo.php", {tpl:tplname,id:id}, function (d, e) { 
		if(d.indexOf("ok")>-1) {
			$(chgid).html(parseInt($(chgid).html()) + 1);
		}
	});
}

// 发表评论 1 文章  2 评论
function docomment(type) {
	if(trim($("#wordtext").val()).length == 0){ alert("请输入评论内容！"); $("#wordtext").val(""); $("#wordtext").focus(); return;}
	var template = getTemplate();
	$.post("./srv/sdo.php?", {tpl:"do_comment",a:queryStr("id"),u:$("#isLogin").val(),c:trim($("#wordtext").val()), d:getDate(), t:type}, function (d, e) {
		//页面显示评论
		alert("发表评论成功！");
		$("#wordtext").val("");
		var jsn = JSON.parse(d);
		var newComment = template.replaceAll("{cmtid}", jsn[0]["id"]).replace("{nickname}", jsn[0]["nickname"]).replace("{postdate}", jsn[0]["postdate"]).replace("{content}", jsn[0]["content"]);
		$("#hot-comments").html(newComment + $("#hot-comments").html());
	});
}

// 删除评论
function delcomment(obj) {
	if(confirm("确认删除该条评论？")) {
		var delId = $(obj).parent().parent().parent().parent().parent().attr("id").replace("c_item", "");
		$.post("./srv/sdo.php?", {tpl:"del_comm",id:delId}, function (d, e) {
			$(obj).parent().parent().parent().parent().parent().remove();
		});
	}
}

// 返回单条评论模板
function getTemplate() {
	var sb = new StringBuilder();
	sb.append("<div class='comment-item' id='c_item{cmtid}'>");
	sb.append("	<div class='comment' id='cmt{cmtid}'>");
	sb.append("		<h3>");
	sb.append("			<span class='comment-vote'>");
	sb.append("				<span id='use{cmtid}'>0</span>");
	sb.append("				<a onclick='douse({cmtid}, true);'>鲜花</a>");
	sb.append("				<span id='nouse{cmtid}'>0</span>");
	sb.append("				<a onclick='douse({cmtid}, false);'>鸡蛋</a>");
	sb.append("			</span>");
	sb.append("			<span class='comment-info'>");
	sb.append("				<a href='/zone' target='_blank'>{nickname}</a>&nbsp;");
	sb.append("				<span class='allstar40 rating' title='推荐'></span>&nbsp;");
	sb.append("				<span>评论于</span>&nbsp;&nbsp;");
	sb.append("				<span class=''>");
	sb.append("					{postdate}&nbsp;&nbsp;");
	sb.append("				</span>");
	sb.append("				<span class='comment-del'>");
	sb.append("					<a onclick='delcomment(this)'>删除</a>");
	sb.append("				</span>");
	sb.append("			</span>");
	sb.append("			<br><br>");
	sb.append("		</h3>");
	sb.append("		<p class=''>");
	sb.append("			{content}");
	sb.append("		</p>");
	sb.append("	</div>");
	sb.append("</div>");
	return sb.toString();
}

// 获取查询字符串的值
function getUrlParam(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
	var r = window.location.search.substr(1).match(reg);  //匹配目标参数
	if (r != null) return r[2]; return null; //返回参数值 return unescape(r[2])
}

// 返回当前时间，用于赋值datetime类型，返回格式为 201508181758
function getDate() {
	var d = new Date();
	var vYear = d.getFullYear();
	var vMon = d.getMonth() + 1;
	var vDay = d.getDate();
	var h = d.getHours(); 
	var m = d.getMinutes(); 
	var se = d.getSeconds(); 
	s = vYear
		+ (vMon<10 ? "0" + vMon : vMon)
		+ (vDay < 10 ? "0" + vDay : vDay)
		+ (h < 10 ? "0" + h : h)
		+ (m < 10 ? "0" + m : m)
		+ (se < 10 ? "0" + se : se);
	return s;
}

function StringBuilder(){
    this._strings_=new Array;
}
StringBuilder.prototype.append=function(str){
    this._strings_.push(str);
};
StringBuilder.prototype.toString=function(){
    return this._strings_.join("");
};
String.prototype.replaceAll=function(str, replaceWith){
	return this.replace(new RegExp(str, 'g'), replaceWith);
}
String.prototype.trim=function(){
	return this.replace(/(^\s*)|(\s*$)/g, "");
}