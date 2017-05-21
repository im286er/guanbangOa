// ���ۿ������޶�
$(document).ready(function(){
	var limitNum = 300;
	var pattern = '����������' + limitNum + '��';
	
	$('#wordage').html(pattern);
	$('#wordtext').keyup(
		function(){
			var remain = $(this).val().length;
			if(remain > 300) {
				pattern = "��������300�����ƣ�";
				$(this).val($(this).val().substr(0, 300));
			}else{
				var result = limitNum - remain;
				pattern = '����������' + result + '��';
			}
			$('#wordage').html(pattern);
		}
	);
});

function goscroll() {
	var url = window.location.toString();//�����ҳ���url
	var id = url.split('#')[1];//url���磺 www.baidu.com#maodian(�������ê���λ��)
	if(id){
		var t = $('#'+id).offset().top;
		$(window).scrollTop(t);//������ê��λ��
	}
	//var childHash = parent.location.hash;//����childHash =="#hot"
	//if(childHash!=""){
	//	location.hash = childHash.substring(1);
	//}
}

// ���input type="file"��ѡ��״̬
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

// ȥ�����е�html���
function delHtmlTag(str){
	return Regex.Replace(str, "<[^>]*>", "");
}

// �����������
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

// ���� �ʻ�use ����nouse ����
function douse(id, state){
	var tplname = state ? "add_use" : "add_nouse";
	var chgid = state ? ("#use" + id) : ("#nouse" + id);
	
	if(Cookies.get("douse" + id)!=undefined) { alert("�����������Ѿ�Ͷ��Ʊ�ˣ�");return; }
	Cookies.set("douse" + id, id, { expires: 43200 });
	
	$.post("./srv/sdo.php", {tpl:tplname,id:id}, function (d, e) { 
		if(d.indexOf("ok")>-1) {
			$(chgid).html(parseInt($(chgid).html()) + 1);
		}
	});
}

// �������� 1 ����  2 ����
function docomment(type) {
	if(trim($("#wordtext").val()).length == 0){ alert("�������������ݣ�"); $("#wordtext").val(""); $("#wordtext").focus(); return;}
	var template = getTemplate();
	$.post("./srv/sdo.php?", {tpl:"do_comment",a:queryStr("id"),u:$("#isLogin").val(),c:trim($("#wordtext").val()), d:getDate(), t:type}, function (d, e) {
		//ҳ����ʾ����
		alert("�������۳ɹ���");
		$("#wordtext").val("");
		var jsn = JSON.parse(d);
		var newComment = template.replaceAll("{cmtid}", jsn[0]["id"]).replace("{nickname}", jsn[0]["nickname"]).replace("{postdate}", jsn[0]["postdate"]).replace("{content}", jsn[0]["content"]);
		$("#hot-comments").html(newComment + $("#hot-comments").html());
	});
}

// ɾ������
function delcomment(obj) {
	if(confirm("ȷ��ɾ���������ۣ�")) {
		var delId = $(obj).parent().parent().parent().parent().parent().attr("id").replace("c_item", "");
		$.post("./srv/sdo.php?", {tpl:"del_comm",id:delId}, function (d, e) {
			$(obj).parent().parent().parent().parent().parent().remove();
		});
	}
}

// ���ص�������ģ��
function getTemplate() {
	var sb = new StringBuilder();
	sb.append("<div class='comment-item' id='c_item{cmtid}'>");
	sb.append("	<div class='comment' id='cmt{cmtid}'>");
	sb.append("		<h3>");
	sb.append("			<span class='comment-vote'>");
	sb.append("				<span id='use{cmtid}'>0</span>");
	sb.append("				<a onclick='douse({cmtid}, true);'>�ʻ�</a>");
	sb.append("				<span id='nouse{cmtid}'>0</span>");
	sb.append("				<a onclick='douse({cmtid}, false);'>����</a>");
	sb.append("			</span>");
	sb.append("			<span class='comment-info'>");
	sb.append("				<a href='/zone' target='_blank'>{nickname}</a>&nbsp;");
	sb.append("				<span class='allstar40 rating' title='�Ƽ�'></span>&nbsp;");
	sb.append("				<span>������</span>&nbsp;&nbsp;");
	sb.append("				<span class=''>");
	sb.append("					{postdate}&nbsp;&nbsp;");
	sb.append("				</span>");
	sb.append("				<span class='comment-del'>");
	sb.append("					<a onclick='delcomment(this)'>ɾ��</a>");
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

// ��ȡ��ѯ�ַ�����ֵ
function getUrlParam(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //����һ������Ŀ�������������ʽ����
	var r = window.location.search.substr(1).match(reg);  //ƥ��Ŀ�����
	if (r != null) return r[2]; return null; //���ز���ֵ return unescape(r[2])
}

// ���ص�ǰʱ�䣬���ڸ�ֵdatetime���ͣ����ظ�ʽΪ 201508181758
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